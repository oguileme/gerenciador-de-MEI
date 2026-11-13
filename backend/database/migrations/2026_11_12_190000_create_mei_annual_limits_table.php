<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mei_annual_limits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mei_id')->constrained('mei')->restrictOnDelete();
            $table->unsignedSmallInteger('year');
            $table->decimal('amount', 15, 2);
            $table->timestamps();
            $table->unique(['mei_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mei_annual_limits');
    }
};
