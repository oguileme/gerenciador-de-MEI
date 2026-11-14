<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mei_id')->constrained('mei')->restrictOnDelete();
            $table->string('issuer_name');
            $table->string('recipient_name');
            $table->date('issue_date');
            $table->string('status', 20)->default('pending');
            $table->decimal('total_amount', 15, 2);
            $table->timestamps();

            $table->index(['mei_id', 'status', 'issue_date']);
        });

        Schema::create('quote_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->text('description');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('quantity', 12, 3);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();

            $table->index(['quote_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
        Schema::dropIfExists('quotes');
    }
};
