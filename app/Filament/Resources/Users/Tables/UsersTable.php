<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Services\UserSuspensionService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID Pengguna')
                    ->prefix('#')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('phone')
                    ->label('Nomor Telepon')
                    ->searchable()
                    ->placeholder('-')
                    ->copyable(),

                TextColumn::make('account_status')
                    ->label('Status Akun')
                    ->state(fn (User $record): string => $record->isSuspended() ? 'Ditangguhkan' : 'Aktif')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Ditangguhkan' ? 'danger' : 'success')
                    ->description(fn (User $record): ?string => $record->suspended_at?->format('d M Y, H:i')),

                TextColumn::make('email_verified_at')
                    ->label('Status Email')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Terverifikasi' : 'Belum terverifikasi')
                    ->color(fn ($state): string => $state ? 'success' : 'warning'),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('suspend')
                    ->label('Tangguhkan')
                    ->icon(Heroicon::OutlinedNoSymbol)
                    ->color('danger')
                    ->visible(fn (User $record): bool => ! $record->isSuspended())
                    ->authorize(fn (User $record): bool => Gate::allows('suspend', $record))
                    ->schema([
                        Textarea::make('reason')
                            ->label('Alasan penangguhan')
                            ->required()
                            ->minLength(10)
                            ->maxLength(500)
                            ->rows(4),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Tangguhkan akun pengguna')
                    ->modalDescription('Pengguna akan langsung dikeluarkan dari semua sesi dan tidak dapat masuk kembali sampai akun diaktifkan.')
                    ->modalSubmitActionLabel('Tangguhkan akun')
                    ->action(function (User $record, array $data): void {
                        app(UserSuspensionService::class)->suspend(
                            $record,
                            auth()->user(),
                            $data['reason'],
                        );

                        Notification::make()
                            ->title('Akun pengguna berhasil ditangguhkan')
                            ->success()
                            ->send();
                    }),

                Action::make('reactivate')
                    ->label('Aktifkan')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (User $record): bool => $record->isSuspended())
                    ->authorize(fn (User $record): bool => Gate::allows('reactivate', $record))
                    ->schema([
                        TextInput::make('password')
                            ->label('Kata sandi admin')
                            ->password()
                            ->revealable(false)
                            ->autocomplete('current-password')
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Aktifkan kembali akun pengguna')
                    ->modalDescription('Masukkan kata sandi admin untuk mengonfirmasi. Pengguna harus masuk kembali dan sesi lama tetap tidak dapat digunakan.')
                    ->modalSubmitActionLabel('Aktifkan akun')
                    ->action(function (User $record, array $data): void {
                        app(UserSuspensionService::class)->reactivate(
                            $record,
                            auth()->user(),
                            $data['password'],
                        );

                        Notification::make()
                            ->title('Akun pengguna berhasil diaktifkan')
                            ->success()
                            ->send();
                    }),
            ])
            ->searchPlaceholder('Cari nama atau ID pengguna')
            ->paginationPageOptions([10, 25, 50])
            ->defaultPaginationPageOption(25)
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada pengguna')
            ->emptyStateDescription('Pengguna yang mendaftar akan muncul di sini.');
    }
}
