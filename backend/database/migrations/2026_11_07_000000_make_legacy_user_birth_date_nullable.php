<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bancos antigos possuem esse campo, que não faz parte do contrato de cadastro.
        if (Schema::hasColumn('users', 'birth_date')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->date('birth_date')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Restaurar NOT NULL invalidaria usuários cadastrados sem data de nascimento.
    }
};
