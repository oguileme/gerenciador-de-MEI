<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mei_id')->constrained('mei')->restrictOnDelete();
            $table->string('type', 20)->default('nfse');
            $table->string('status', 20)->default('active');
            $table->char('access_key', 50);
            $table->string('invoice_number', 13);
            $table->string('customer_name', 300)->nullable();
            $table->decimal('service_amount', 15, 2);
            $table->decimal('net_amount', 15, 2);
            $table->date('issue_date');
            $table->date('competence_date');
            $table->text('description');
            $table->string('xml_path');
            $table->char('xml_sha256', 64);
            $table->timestamps();

            $table->unique(['mei_id', 'access_key']);
            $table->unique(['mei_id', 'xml_sha256']);
            $table->index(['mei_id', 'status', 'issue_date']);
            $table->index(['mei_id', 'status', 'competence_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
