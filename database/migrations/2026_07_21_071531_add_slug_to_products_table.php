<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
        });

        DB::table('products')
            ->select(['id', 'title', 'game_name'])
            ->orderBy('id')
            ->each(function (object $product): void {
                $base = Str::slug($product->title ?: $product->game_name) ?: 'akun-game';

                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['slug' => $base.'-'.Str::lower(Str::random(7))]);
            });

        Schema::table('products', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
