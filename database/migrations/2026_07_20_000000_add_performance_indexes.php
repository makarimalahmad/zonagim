<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->index(
                ['category_id', 'created_at', 'id'],
                'products_category_created_id_index',
            );
            $table->index(
                ['category_id', 'price', 'id'],
                'products_category_price_id_index',
            );
            $table->index('created_at', 'products_created_at_index');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->index('created_at', 'users_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex('products_category_created_id_index');
            $table->dropIndex('products_category_price_id_index');
            $table->dropIndex('products_created_at_index');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_created_at_index');
        });
    }
};
