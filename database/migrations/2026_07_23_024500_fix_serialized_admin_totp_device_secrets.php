<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('admin_totp_devices')
            ->select(['id', 'secret'])
            ->orderBy('id')
            ->each(function (object $device): void {
                try {
                    $secret = Crypt::decryptString($device->secret);
                } catch (Throwable $exception) {
                    report($exception);

                    return;
                }

                if (! str_starts_with($secret, 's:')) {
                    return;
                }

                $unserialized = @unserialize($secret, ['allowed_classes' => false]);

                if (! is_string($unserialized) || preg_match('/^[A-Z2-7]{16,128}$/', $unserialized) !== 1) {
                    return;
                }

                DB::table('admin_totp_devices')
                    ->where('id', $device->id)
                    ->update([
                        'secret' => Crypt::encryptString($unserialized),
                        'secret_fingerprint' => hash('sha256', $unserialized),
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void {}
};
