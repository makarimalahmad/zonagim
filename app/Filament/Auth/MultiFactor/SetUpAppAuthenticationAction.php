<?php

namespace App\Filament\Auth\MultiFactor;

use App\Models\User;
use App\Services\AdminTotpDeviceService;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Image;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\UnorderedList;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FAQRCode\Google2FA;
use SensitiveParameter;
use Throwable;

class SetUpAppAuthenticationAction
{
    public static function make(AppAuthentication $appAuthentication): Action
    {
        /** @var User|null $currentUser */
        $currentUser = Filament::auth()->user();
        $isFirstDevice = ! $currentUser?->totpDevices()->exists();

        return Action::make('setUpAppAuthentication')
            ->label('Siapkan autentikator')
            ->button()
            ->color('primary')
            ->mountUsing(function (HasActions $livewire, Schema $schema) use ($appAuthentication): void {
                $schema->fill([
                    'deviceName' => '',
                    'secretDisplay' => null,
                    'hasViewedSecret' => null,
                ]);

                $livewire->mergeMountedActionArguments([
                    'encrypted' => encrypt([
                        'secret' => $appAuthentication->generateSecret(),
                        ...($appAuthentication->isRecoverable()
                            ? ['recoveryCodes' => $appAuthentication->generateRecoveryCodes()]
                            : []),
                        'userId' => Filament::auth()->id(),
                        'enrollmentId' => (string) Str::uuid(),
                        'issuedAt' => now()->timestamp,
                    ]),
                ]);
            })
            ->modalWidth(Width::FourExtraLarge)
            ->modalFooterActionsAlignment(Alignment::End)
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalIcon(Heroicon::OutlinedShieldCheck)
            ->modalIconColor('primary')
            ->modalHeading('Daftarkan perangkat autentikator')
            ->modalDescription('Beri nama perangkat, hubungkan aplikasi, lalu verifikasi dua kode berurutan.')
            ->extraModalWindowAttributes(['class' => 'admin-mfa-aws-modal'])
            ->schema(fn (Action $action): array => [
                TextInput::make('deviceName')
                    ->label('Nama perangkat')
                    ->placeholder('Contoh: iPhone pribadi atau Laptop kerja')
                    ->helperText('Gunakan nama yang mudah dikenali. Maksimal 64 karakter.')
                    ->minLength(2)
                    ->maxLength(64)
                    ->rule(function (): Closure {
                        return function (string $attribute, mixed $value, Closure $fail): void {
                            /** @var User $user */
                            $user = Filament::auth()->user();

                            if (app(AdminTotpDeviceService::class)->deviceNameExists($user, (string) $value)) {
                                $fail('Nama perangkat sudah terpakai. Gunakan nama lain.');
                            }
                        };
                    })
                    ->required()
                    ->columnSpanFull(),

                Hidden::make('secretDisplay')
                    ->live()
                    ->columnSpanFull(),

                Hidden::make('hasViewedSecret')
                    ->rule('accepted')
                    ->required()
                    ->validationMessages([
                        'accepted' => 'Tampilkan kode QR atau kunci rahasia sebelum mendaftarkan perangkat.',
                        'required' => 'Tampilkan kode QR atau kunci rahasia sebelum mendaftarkan perangkat.',
                    ])
                    ->columnSpanFull(),

                Flex::make([
                    Text::make('1')
                        ->grow(false)
                        ->extraAttributes(['class' => 'admin-mfa-step-number']),
                    Group::make([
                        Text::make('Beri nama dan pasang aplikasi autentikator')
                            ->weight(FontWeight::Bold)
                            ->color('neutral'),
                        Text::make('Gunakan Google Authenticator, Microsoft Authenticator, Authy, atau aplikasi TOTP lain di perangkat Anda.')
                            ->color('neutral'),
                    ]),
                ])
                    ->dense()
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'admin-mfa-inline-step']),

                Flex::make([
                    Text::make('2')
                        ->grow(false)
                        ->extraAttributes(['class' => 'admin-mfa-step-number']),
                    Group::make([
                        Text::make('Hubungkan aplikasi ke akun admin')
                            ->weight(FontWeight::Bold)
                            ->color('neutral'),
                        Text::make('Tampilkan kode QR atau kunci rahasia, lalu tambahkan ke aplikasi autentikator Anda.')
                            ->color('neutral'),

                        Actions::make([
                            Action::make('showQrCode')
                                ->label('Kode QR')
                                ->button()
                                ->color(fn (Get $get): string => $get('secretDisplay') === 'qr' ? 'primary' : 'gray')
                                ->outlined(fn (Get $get): bool => $get('secretDisplay') !== 'qr')
                                ->icon(Heroicon::OutlinedQrCode)
                                ->action(function (Get $get, Set $set): void {
                                    $set('hasViewedSecret', true);
                                    $set('secretDisplay', $get('secretDisplay') === 'qr' ? null : 'qr');
                                }),
                            Action::make('showSecretKey')
                                ->label('Kunci rahasia')
                                ->button()
                                ->color(fn (Get $get): string => $get('secretDisplay') === 'secret' ? 'primary' : 'gray')
                                ->outlined(fn (Get $get): bool => $get('secretDisplay') !== 'secret')
                                ->icon(Heroicon::OutlinedKey)
                                ->action(function (Get $get, Set $set): void {
                                    $set('hasViewedSecret', true);
                                    $set('secretDisplay', $get('secretDisplay') === 'secret' ? null : 'secret');
                                }),
                        ])
                            ->fullWidth()
                            ->extraAttributes(['class' => 'admin-mfa-secret-actions']),

                        Group::make([
                            Image::make(
                                url: fn (): string => $appAuthentication->generateQrCodeDataUri(
                                    decrypt($action->getArguments()['encrypted'])['secret'],
                                ),
                                alt: 'Kode QR aplikasi autentikator',
                            )
                                ->imageHeight('8rem')
                                ->alignCenter(),
                            Text::make('Pindai kode QR ini melalui aplikasi autentikator Anda.')
                                ->color('neutral'),
                        ])
                            ->visible(fn (Get $get): bool => $get('secretDisplay') === 'qr')
                            ->extraAttributes(['class' => 'admin-mfa-reveal-panel admin-mfa-qr-panel']),

                        Group::make([
                            Text::make('Masukkan kunci berikut secara manual di aplikasi autentikator:')
                                ->color('neutral'),
                            Text::make(fn (): string => decrypt($action->getArguments()['encrypted'])['secret'])
                                ->fontFamily(FontFamily::Mono)
                                ->weight(FontWeight::Bold)
                                ->copyable()
                                ->copyMessage('Kunci rahasia berhasil disalin')
                                ->extraAttributes(['class' => 'admin-mfa-secret-value']),
                        ])
                            ->visible(fn (Get $get): bool => $get('secretDisplay') === 'secret')
                            ->extraAttributes(['class' => 'admin-mfa-reveal-panel admin-mfa-secret-panel']),
                    ]),
                ])
                    ->dense()
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'admin-mfa-inline-step']),

                Flex::make([
                    Text::make('3')
                        ->grow(false)
                        ->extraAttributes(['class' => 'admin-mfa-step-number']),
                    Group::make([
                        Text::make('Masukkan dua kode MFA berurutan')
                            ->weight(FontWeight::Bold)
                            ->color('neutral'),
                        Text::make('Masukkan kode saat ini, tunggu beberapa detik hingga kode berganti, lalu masukkan kode berikutnya.')
                            ->color('neutral'),
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])
                            ->schema([
                                TextInput::make('firstCode')
                                    ->label('Kode MFA pertama')
                                    ->validationAttribute('kode MFA pertama')
                                    ->placeholder('000000')
                                    ->inputMode('numeric')
                                    ->autocomplete('one-time-code')
                                    ->minLength(6)
                                    ->maxLength(6)
                                    ->rule('digits:6')
                                    ->required(),
                                TextInput::make('secondCode')
                                    ->label('Kode MFA kedua')
                                    ->validationAttribute('kode MFA kedua')
                                    ->placeholder('000000')
                                    ->inputMode('numeric')
                                    ->autocomplete(false)
                                    ->minLength(6)
                                    ->maxLength(6)
                                    ->rule('digits:6')
                                    ->required()
                                    ->different('firstCode')
                                    ->rule(self::consecutiveCodesRule($action, $appAuthentication)),
                            ]),
                    ])->extraAttributes(['class' => 'admin-mfa-code-step']),
                ])
                    ->dense()
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'admin-mfa-inline-step']),

                Flex::make([
                    Text::make('4')
                        ->grow(false)
                        ->extraAttributes(['class' => 'admin-mfa-step-number']),
                    Group::make([
                        Text::make('Simpan kode pemulihan')
                            ->weight(FontWeight::Bold)
                            ->color('neutral'),
                        Text::make('Kode pemulihan akan ditampilkan satu kali setelah perangkat berhasil diaktifkan.')
                            ->color('neutral'),
                    ]),
                ])
                    ->dense()
                    ->columnSpanFull()
                    ->visible($appAuthentication->isRecoverable() && $isFirstDevice)
                    ->extraAttributes(['class' => 'admin-mfa-inline-step']),
            ])
            ->modalSubmitAction(fn (Action $action) => $action->label('Daftarkan perangkat'))
            ->action(function (array $data, array $arguments, HasActions $livewire) use ($appAuthentication): void {
                /** @var User $user */
                $user = Filament::auth()->user();
                $encrypted = decrypt($arguments['encrypted']);

                if (! hash_equals((string) $user->getAuthIdentifier(), (string) $encrypted['userId'])) {
                    return;
                }

                $google2FA = app(Google2FA::class);
                $firstTimestamp = $google2FA->verifyKey(
                    $encrypted['secret'],
                    $data['firstCode'],
                    $appAuthentication->getCodeWindow(),
                    $google2FA->getTimestamp(),
                    0,
                );
                $secondTimestamp = is_int($firstTimestamp)
                    ? $google2FA->verifyKeyNewer(
                        $encrypted['secret'],
                        $data['secondCode'],
                        $firstTimestamp,
                        $appAuthentication->getCodeWindow(),
                        $google2FA->getTimestamp(),
                    )
                    : false;

                if (! is_int($secondTimestamp) || $secondTimestamp !== $firstTimestamp + 1) {
                    throw ValidationException::withMessages([
                        'secondCode' => 'Kode MFA kedua harus berasal dari periode berikutnya.',
                    ]);
                }

                $wasFirstDevice = $user->totpDevices()->doesntExist();

                if (! $wasFirstDevice) {
                    $livewire->mountAction('confirmAdditionalDevicePassword', arguments: [
                        'encryptedEnrollment' => encrypt([
                            'userId' => $encrypted['userId'],
                            'enrollmentId' => $encrypted['enrollmentId'],
                            'issuedAt' => $encrypted['issuedAt'],
                            'deviceName' => $data['deviceName'],
                            'secret' => $encrypted['secret'],
                            'lastUsedTimestep' => $secondTimestamp,
                        ]),
                    ]);

                    return;
                }

                $service = app(AdminTotpDeviceService::class);
                $pendingEnrollment = $service->pendingEnrollment(
                    $user,
                    $data['deviceName'],
                    $encrypted['secret'],
                );
                $service->activate($user, $pendingEnrollment, $secondTimestamp);

                if ($appAuthentication->isRecoverable()) {
                    $appAuthentication->saveRecoveryCodes($user, $encrypted['recoveryCodes']);
                }

                Notification::make()
                    ->title('Perangkat autentikator berhasil didaftarkan')
                    ->success()
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->send();

                if ($wasFirstDevice && $appAuthentication->isRecoverable()) {
                    $livewire->mountAction('showInitialRecoveryCodes', arguments: [
                        'recoveryCodes' => $encrypted['recoveryCodes'],
                    ]);
                }
            })
            ->registerModalActions([
                Action::make('confirmAdditionalDevicePassword')
                    ->modalHeading('Konfirmasi kata sandi admin')
                    ->modalDescription('Untuk menambahkan perangkat MFA, silakan masukkan kata sandi admin.')
                    ->schema([
                        TextInput::make('password')
                            ->label('Kata sandi admin')
                            ->password()
                            ->revealable(Filament::arePasswordsRevealable())
                            ->autocomplete('current-password')
                            ->required(),
                    ])
                    ->modalWidth(Width::Medium)
                    ->extraModalWindowAttributes(['class' => 'admin-mfa-add-device-password-modal'])
                    ->closeModalByClickingAway(false)
                    ->closeModalByEscaping(false)
                    ->modalSubmitAction(fn (Action $action): Action => $action
                        ->label('Konfirmasi dan tambahkan')
                        ->color('primary'))
                    ->action(function (Action $action, array $data, array $arguments, HasActions $livewire): void {
                        /** @var User $user */
                        $user = Filament::auth()->user();

                        try {
                            $enrollment = decrypt($arguments['encryptedEnrollment']);
                        } catch (Throwable) {
                            $livewire->mountAction('additionalDeviceEnrollmentFailed');
                            $action->halt();
                        }

                        if (
                            ! is_array($enrollment)
                            || ! hash_equals((string) $user->getAuthIdentifier(), (string) ($enrollment['userId'] ?? ''))
                            || now()->timestamp - (int) ($enrollment['issuedAt'] ?? 0) > 600
                            || blank($enrollment['enrollmentId'] ?? null)
                            || blank($enrollment['deviceName'] ?? null)
                            || blank($enrollment['secret'] ?? null)
                            || ! is_numeric($enrollment['lastUsedTimestep'] ?? null)
                        ) {
                            $livewire->mountAction('additionalDeviceEnrollmentFailed');
                            $action->halt();
                        }

                        $service = app(AdminTotpDeviceService::class);

                        try {
                            $service->confirmAdditionalDevicePassword(
                                $user,
                                $enrollment['enrollmentId'],
                                $data['password'],
                            );
                        } catch (ValidationException $exception) {
                            if (
                                $service->passwordAttempts($user, $enrollment['enrollmentId'])
                                >= AdminTotpDeviceService::PASSWORD_MAX_ATTEMPTS
                            ) {
                                $livewire->mountAction('additionalDeviceEnrollmentFailed');
                                $action->halt();
                            }

                            $livewire->addError(
                                'mountedActions.'.($action->getNestingIndex() ?? 0).'.data.password',
                                'Kata sandi admin salah. Silakan coba lagi.',
                            );
                            $action->halt();
                        }
                        $pendingEnrollment = $service->pendingEnrollment(
                            $user,
                            $enrollment['deviceName'],
                            $enrollment['secret'],
                            $data['password'],
                        );
                        $service->activate(
                            $user,
                            $pendingEnrollment,
                            (int) $enrollment['lastUsedTimestep'],
                        );

                        Notification::make()
                            ->title('Perangkat autentikator berhasil didaftarkan')
                            ->success()
                            ->icon(Heroicon::OutlinedShieldCheck)
                            ->send();
                    })
                    ->registerModalActions([
                        Action::make('additionalDeviceEnrollmentFailed')
                            ->modalIcon(Heroicon::OutlinedExclamationTriangle)
                            ->modalIconColor('danger')
                            ->modalHeading('Gagal menambahkan perangkat MFA')
                            ->modalDescription('Silakan ulangi langkah pendaftaran dan masukkan kata sandi admin yang benar.')
                            ->extraModalWindowAttributes(['class' => 'admin-mfa-error-modal'])
                            ->modalWidth(Width::Medium)
                            ->modalCloseButton(false)
                            ->closeModalByClickingAway(false)
                            ->closeModalByEscaping(false)
                            ->modalSubmitAction(fn (Action $action): Action => $action
                                ->label('Mengerti')
                                ->color('danger'))
                            ->modalCancelAction(false)
                            ->cancelParentActions(),
                    ])
                    ->cancelParentActions(),

                Action::make('showInitialRecoveryCodes')
                    ->modalHeading('Simpan kode pemulihan')
                    ->modalDescription('Perangkat sudah aktif. Simpan kode berikut di tempat aman; setiap kode hanya dapat digunakan sekali.')
                    ->schema(fn (array $arguments): array => [
                        Group::make([
                            UnorderedList::make(fn (): array => array_map(
                                fn (string $recoveryCode): Component => Text::make($recoveryCode)
                                    ->fontFamily(FontFamily::Mono)
                                    ->size('xs')
                                    ->color('neutral'),
                                $arguments['recoveryCodes'],
                            ))->size('xs'),
                            Text::make(fn (): Htmlable => new HtmlString(
                                Action::make('copyInitialRecoveryCodes')
                                    ->label('Salin semua kode')
                                    ->link()
                                    ->alpineClickHandler('window.navigator.clipboard.writeText('.Js::from(implode(PHP_EOL, $arguments['recoveryCodes'])).')')
                                    ->toHtml().' · '.
                                Action::make('downloadInitialRecoveryCodes')
                                    ->label('Unduh kode')
                                    ->link()
                                    ->url('data:application/octet-stream,'.urlencode(implode(PHP_EOL, $arguments['recoveryCodes'])))
                                    ->extraAttributes(['download' => 'zonagim-recovery-codes.txt'])
                                    ->toHtml()
                            )),
                        ])->dense(),
                    ])
                    ->modalWidth(Width::Medium)
                    ->closeModalByClickingAway(false)
                    ->closeModalByEscaping(false)
                    ->modalCloseButton(false)
                    ->modalSubmitAction(fn (Action $action) => $action->label('Sudah disimpan'))
                    ->modalCancelAction(false)
                    ->cancelParentActions(),
            ])
            ->databaseTransaction()
            ->rateLimit(5);
    }

    private static function consecutiveCodesRule(
        Action $action,
        AppAuthentication $appAuthentication,
    ): Closure {
        return function (Get $get) use ($action, $appAuthentication): Closure {
            return function (
                string $attribute,
                #[SensitiveParameter] mixed $value,
                Closure $fail,
            ) use ($get, $action, $appAuthentication): void {
                $rateLimitKey = 'filament-set-up-app-authentication:'.Filament::auth()->id();

                if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
                    $fail('Terlalu banyak percobaan. Silakan coba lagi nanti.');

                    return;
                }

                RateLimiter::hit($rateLimitKey, 300);

                $firstCode = (string) $get('firstCode');
                $secondCode = (string) $value;
                $secret = decrypt($action->getArguments()['encrypted'])['secret'];
                $google2FA = app(Google2FA::class);
                $window = $appAuthentication->getCodeWindow();
                $currentTimestamp = $google2FA->getTimestamp();
                $firstTimestamp = $google2FA->verifyKey(
                    $secret,
                    $firstCode,
                    $window,
                    $currentTimestamp,
                    0,
                );

                if (! is_int($firstTimestamp)) {
                    $fail('Kode MFA pertama tidak valid.');

                    return;
                }

                $secondTimestamp = $google2FA->verifyKeyNewer(
                    $secret,
                    $secondCode,
                    $firstTimestamp,
                    $window,
                    $currentTimestamp,
                );

                if (! is_int($secondTimestamp) || $secondTimestamp !== $firstTimestamp + 1) {
                    $fail('Kode MFA kedua harus berasal dari periode berikutnya. Tunggu kode berubah lalu coba kembali.');

                    return;
                }

                RateLimiter::clear($rateLimitKey);
            };
        };
    }
}
