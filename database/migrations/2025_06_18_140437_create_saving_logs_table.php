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
        Schema::create('saving_logs', function (Blueprint $table) {
            $table->id();

            // User & Saving Reference
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('saving_id')->constrained()->onDelete('cascade');

            // Informasi Tabungan Harian
            $table->date('date');
            $table->integer('amount');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saving_logs');
    }
};
