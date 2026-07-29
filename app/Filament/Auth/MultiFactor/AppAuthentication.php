<?php

namespace App\Filament\Auth\MultiFactor;

use App\Models\AdminTotpDevice;
use App\Models\User;
use App\Services\AdminTotpDeviceService;
use Closure;
use Filament\Actions\Action;
use Filament\Auth\MultiFactor\App\AppAuthentication as BaseAppAuthentication;
use Filament\Facades\Filament;
use Filament\Forms\Components\OneTimeCodeInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Auth\Authenticatable;
use SensitiveParameter;

class AppAuthentication extends BaseAppAuthentication
{
    public function isEnabled(Authenticatable $user): bool
    {
        return $user instanceof User && $user->totpDevices()->exists();
    }

    public function getManagementSchemaComponents(): array
    {
        /** @var User $user */
        $user = Filament::auth()->user();
        $devices = $user->totpDevices()->orderBy('slot')->get();

        return [
            ...$devices->map(fn (AdminTotpDevice $device): Flex => Flex::make([
                Group::make([
                    Text::make($device->name)
                        ->weight(FontWeight::Bold)
                        ->color('neutral'),
                    Text::make('Aktif · Ditambahkan '.$device->created_at->translatedFormat('d M Y, H:i'))
                        ->color('success'),
                    Text::make($device->last_used_at
                        ? 'Terakhir digunakan '.$device->last_used_at->diffForHumans()
                        : 'Belum pernah digunakan untuk login')
                        ->color('neutral'),
                ]),
                Actions::make([
                    $this->deleteDeviceAction($device, $devices->count()),
                ])->grow(false),
            ])->extraAttributes(['class' => 'admin-totp-device-row']))->all(),

            Actions::make($this->getActions())
                ->label('Kelola keamanan')
                ->belowContent('Simpan kode pemulihan di tempat aman.')
                ->alignStart()
                ->extraAttributes(['class' => 'admin-account-mfa-actions']),
        ];
    }

    public function getActions(): array
    {
        /** @var User $user */
        $user = Filament::auth()->user();
        $deviceCount = $user?->totpDevices()->count() ?? 0;

        return [
            SetUpAppAuthenticationAction::make($this)
                ->label($deviceCount === 0 ? 'Daftarkan perangkat' : 'Tambah perangkat')
                ->visible(fn (): bool => $deviceCount < AdminTotpDeviceService::MAX_DEVICES),
            RegenerateRecoveryCodesAction::make($this)
                ->button()
                ->outlined()
                ->visible(fn (): bool => $deviceCount > 0
                    && $this->isRecoverable()
                    && $this->canRegenerateRecoveryCodes()),
        ];
    }

    public function getChallengeFormComponents(Authenticatable $user): array
    {
        $isRecoverable = $this->isRecoverable();

        return [
            OneTimeCodeInput::make('code')
                ->label('Kode autentikasi 6 digit')
                ->belowContent(fn (Get $get): Action => Action::make('useRecoveryCode')
                    ->label('Gunakan kode pemulihan')
                    ->link()
                    ->action(fn (Set $set) => $set('useRecoveryCode', true))
                    ->visible(fn (): bool => $isRecoverable && ! $get('useRecoveryCode')))
                ->required(fn (Get $get): bool => ! $isRecoverable || ! $get('useRecoveryCode') || blank($get('recoveryCode')))
                ->rule(function () use ($user): Closure {
                    return function (string $attribute, #[SensitiveParameter] mixed $value, Closure $fail) use ($user): void {
                        if (
                            $user instanceof User
                            && is_string($value)
                            && app(AdminTotpDeviceService::class)->verifyAny($user, $value)
                        ) {
                            return;
                        }

                        $fail('Kode autentikator tidak valid atau sudah digunakan.');
                    };
                }),
            TextInput::make('recoveryCode')
                ->label('Kode pemulihan')
                ->password()
                ->revealable(Filament::arePasswordsRevealable())
                ->rule(function () use ($user): Closure {
                    return function (string $attribute, #[SensitiveParameter] mixed $value, Closure $fail) use ($user): void {
                        if (blank($value)) {
                            return;
                        }

                        if (is_string($value) && $this->verifyRecoveryCode($value, $user)) {
                            return;
                        }

                        $fail('Kode pemulihan tidak valid atau sudah digunakan.');
                    };
                })
                ->visible(fn (Get $get): bool => $isRecoverable && $get('useRecoveryCode'))
                ->live(onBlur: true),
        ];
    }

    private function deleteDeviceAction(AdminTotpDevice $device, int $deviceCount): Action
    {
        if ($deviceCount <= 1) {
            return Action::make('keepDevice'.$device->getKey())
                ->label('Hapus')
                ->icon(Heroicon::OutlinedTrash)
                ->color('danger')
                ->extraAttributes(['class' => 'app-delete-action'])
                ->requiresConfirmation()
                ->modalIcon(Heroicon::OutlinedExclamationTriangle)
                ->modalIconColor('danger')
                ->modalHeading('Gagal menghapus perangkat')
                ->modalDescription('Perangkat ini tidak dapat dihapus. Minimal satu perangkat autentikator harus tetap aktif.')
                ->extraModalWindowAttributes(['class' => 'admin-mfa-error-modal'])
                ->modalCloseButton(false)
                ->closeModalByClickingAway(false)
                ->closeModalByEscaping(false)
                ->modalSubmitAction(false)
                ->modalCancelAction(fn (Action $action): Action => $action
                    ->label('Mengerti')
                    ->color('danger'));
        }

        return Action::make('deleteDevice'.$device->getKey())
            ->label('Hapus')
            ->icon(Heroicon::OutlinedTrash)
            ->color('danger')
            ->extraAttributes(['class' => 'app-delete-action'])
            ->requiresConfirmation()
            ->modalHeading('Hapus perangkat autentikator')
            ->modalDescription("Hapus {$device->name}? Perangkat ini tidak dapat digunakan lagi untuk login.")
            ->schema([
                TextInput::make('password')
                    ->label('Kata sandi admin')
                    ->password()
                    ->currentPassword(guard: Filament::getAuthGuard())
                    ->required(),
            ])
            ->modalSubmitAction(fn (Action $action): Action => $action
                ->label('Hapus perangkat')
                ->extraAttributes(['class' => 'app-delete-action']))
            ->action(function (array $data) use ($device): void {
                /** @var User $user */
                $user = Filament::auth()->user();
                app(AdminTotpDeviceService::class)->delete(
                    $user,
                    $device->getKey(),
                    $data['password'],
                );
            });
    }
}
