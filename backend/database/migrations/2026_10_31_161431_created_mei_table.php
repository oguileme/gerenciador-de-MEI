<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::create('mei', function (Blueprint $table) {
            $table->id();
            $table->char('cnpj', 14)->unique();
            $table->string('mei_name');
            $table->date('opening_date');
            $table->string('ibge_municipality_code', 7);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        }); 

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
