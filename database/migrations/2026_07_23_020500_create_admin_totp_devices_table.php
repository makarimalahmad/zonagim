<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_totp_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('slot');
            $table->string('name', 64);
            $table->char('name_key', 64);
            $table->text('secret');
            $table->char('secret_fingerprint', 64);
            $table->unsignedBigInteger('last_used_timestep')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'slot']);
            $table->unique(['user_id', 'name_key']);
            $table->unique(['user_id', 'secret_fingerprint']);
            $table->index(['user_id', 'last_used_at']);
        });

        User::query()
            ->where('role', 'admin')
            ->whereNotNull('app_authentication_secret')
            ->orderBy('id')
            ->each(function (User $user): void {
                try {
                    $secret = $user->getAppAuthenticationSecret();
                } catch (Throwable $exception) {
                    report($exception);

                    return;
                }

                if (blank($secret)) {
                    return;
                }

                $name = 'Perangkat utama';

                DB::table('admin_totp_devices')->insertOrIgnore([
                    'user_id' => $user->getKey(),
                    'slot' => 1,
                    'name' => $name,
                    'name_key' => hash('sha256', Str::lower($name)),
                    'secret' => Crypt::encryptString($secret),
                    'secret_fingerprint' => hash('sha256', $secret),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_totp_devices');
    }
};
