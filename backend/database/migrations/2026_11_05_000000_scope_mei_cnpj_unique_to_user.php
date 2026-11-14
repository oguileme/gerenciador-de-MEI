<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mei', function (Blueprint $table): void {
            $table->dropUnique(['cnpj']);
            $table->unique(['user_id', 'cnpj']);
            $table->index(['user_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::table('mei', function (Blueprint $table): void {
            $table->dropIndex(['user_id', 'id']);
            $table->dropUnique(['user_id', 'cnpj']);
            $table->unique('cnpj');
        });
    }
};
