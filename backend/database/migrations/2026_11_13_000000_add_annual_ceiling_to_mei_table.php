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
            $table->decimal('annual_ceiling', 15, 2)->default('81000.00');
        });
    }

    public function down(): void
    {
        Schema::table('mei', function (Blueprint $table): void {
            $table->dropColumn('annual_ceiling');
        });
    }
};
