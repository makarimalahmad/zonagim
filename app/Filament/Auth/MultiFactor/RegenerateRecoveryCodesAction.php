<?php

namespace App\Filament\Auth\MultiFactor;

use App\Models\User;
use App\Services\AdminTotpDeviceService;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Facades\Filament;
use Filament\Forms\Components\OneTimeCodeInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\UnorderedList;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Js;
use SensitiveParameter;

class RegenerateRecoveryCodesAction
{
    public static function make(AppAuthentication $appAuthentication): Action
    {
        return Action::make('regenerateAppAuthenticationRecoveryCodes')
            ->label('Buat ulang kode pemulihan')
            ->color('gray')
            ->icon(Heroicon::ArrowPath)
            ->link()
            ->modalWidth(Width::Medium)
            ->modalFooterActionsAlignment(Alignment::End)
            ->modalIcon(Heroicon::OutlinedArrowPath)
            ->modalIconColor('primary')
            ->modalHeading('Buat ulang kode pemulihan')
            ->modalDescription('Pilih satu metode verifikasi. Kode pemulihan lama akan langsung dinonaktifkan.')
            ->extraModalWindowAttributes(['class' => 'admin-mfa-manage-modal'])
            ->schema([
                Group::make([
                    ToggleButtons::make('method')
                        ->label('Metode verifikasi')
                        ->options([
                            'authenticator' => 'Kode autentikator',
                            'password' => 'Kata sandi',
                        ])
                        ->icons([
                            'authenticator' => Heroicon::OutlinedDevicePhoneMobile,
                            'password' => Heroicon::OutlinedKey,
                        ])
                        ->colors([
                            'authenticator' => 'primary',
                            'password' => 'primary',
                        ])
                        ->default('authenticator')
                        ->grouped()
                        ->inline()
                        ->extraAttributes([
                            'class' => 'admin-mfa-method-picker',
                        ])
                        ->required(),

                    OneTimeCodeInput::make('code')
                        ->label('Kode 6 digit dari aplikasi autentikator')
                        ->required(fn (Get $get): bool => $get('method') === 'authenticator')
                        ->visibleJs("\$get('method') === 'authenticator'")
                        ->rule(self::authenticatorCodeRule($appAuthentication)),

                    TextInput::make('password')
                        ->label('Kata sandi admin')
                        ->currentPassword(guard: Filament::getAuthGuard())
                        ->password()
                        ->revealable(Filament::arePasswordsRevealable())
                        ->required(fn (Get $get): bool => $get('method') === 'password')
                        ->visibleJs("\$get('method') === 'password'")
                        ->dehydrated(false),
                ])->extraAttributes([
                    'class' => 'admin-mfa-method-scope',
                ]),
            ])
            ->modalSubmitAction(fn (Action $action) => $action
                ->label('Buat ulang kode')
                ->color('danger'))
            ->action(function (HasActions $livewire) use ($appAuthentication): void {
                $recoveryCodes = $appAuthentication->generateRecoveryCodes();

                /** @var HasAppAuthenticationRecovery $user */
                $user = Filament::auth()->user();
                $appAuthentication->saveRecoveryCodes($user, $recoveryCodes);

                $livewire->mountAction('showNewRecoveryCodes', arguments: [
                    'recoveryCodes' => $recoveryCodes,
                ]);

                Notification::make()
                    ->title('Kode pemulihan berhasil dibuat ulang')
                    ->success()
                    ->send();
            })
            ->registerModalActions([
                Action::make('showNewRecoveryCodes')
                    ->modalHeading('Simpan kode pemulihan baru')
                    ->modalDescription('Kode lama sudah tidak berlaku. Simpan kode baru berikut di tempat aman.')
                    ->schema(fn (array $arguments): array => [
                        Group::make([
                            UnorderedList::make(fn (): array => array_map(
                                fn (string $recoveryCode): Component => Text::make($recoveryCode)
                                    ->fontFamily(FontFamily::Mono)
                                    ->size('xs')
                                    ->color('neutral'),
                                $arguments['recoveryCodes'],
                            ))
                                ->size('xs')
                                ->extraAttributes(['class' => 'admin-mfa-recovery-code-list']),
                            Actions::make([
                                Action::make('copy')
                                    ->label('Salin semua kode')
                                    ->button()
                                    ->outlined()
                                    ->color('primary')
                                    ->icon(Heroicon::OutlinedClipboardDocument)
                                    ->alpineClickHandler('window.navigator.clipboard.writeText('.Js::from(implode(PHP_EOL, $arguments['recoveryCodes'])).')'),
                                Action::make('download')
                                    ->label('Unduh kode')
                                    ->button()
                                    ->outlined()
                                    ->color('gray')
                                    ->icon(Heroicon::OutlinedArrowDownTray)
                                    ->url('data:application/octet-stream,'.urlencode(implode(PHP_EOL, $arguments['recoveryCodes'])))
                                    ->extraAttributes(['download' => 'zonagim-recovery-codes.txt']),
                            ])->extraAttributes(['class' => 'admin-mfa-recovery-code-actions']),
                        ])
                            ->dense()
                            ->extraAttributes(['class' => 'admin-mfa-recovery-code-content']),
                    ])
                    ->modalWidth(Width::Medium)
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->extraModalWindowAttributes(['class' => 'admin-mfa-recovery-codes-modal'])
                    ->closeModalByClickingAway(false)
                    ->closeModalByEscaping(false)
                    ->modalCloseButton(false)
                    ->modalSubmitAction(fn (Action $action) => $action
                        ->label('Sudah disimpan')
                        ->color('primary'))
                    ->modalCancelAction(false)
                    ->cancelParentActions(),
            ])
            ->rateLimit(5);
    }

    private static function authenticatorCodeRule(AppAuthentication $appAuthentication): Closure
    {
        return function (Get $get): Closure {
            return function (
                string $attribute,
                #[SensitiveParameter] mixed $value,
                Closure $fail,
            ) use ($get): void {
                if ($get('method') !== 'authenticator') {
                    return;
                }

                $key = 'filament-regenerate-recovery-codes:'.Filament::auth()->id();

                if (RateLimiter::tooManyAttempts($key, 5)) {
                    $fail('Terlalu banyak percobaan. Silakan coba kembali nanti.');

                    return;
                }

                RateLimiter::hit($key, 300);

                /** @var User $user */
                $user = Filament::auth()->user();

                if (is_string($value) && app(AdminTotpDeviceService::class)->verifyAny($user, $value)) {
                    RateLimiter::clear($key);

                    return;
                }

                $fail('Kode autentikator tidak valid.');
            };
        };
    }
}
