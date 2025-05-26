<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Relasi ke user
            $table->enum('type', ['income', 'expense']); // jenis: pemasukan/pengeluaran
            $table->string('category'); // misal: makanan, transportasi, dll
            $table->string('description')->nullable(); // deskripsi opsional
            $table->integer('amount'); // nominal transaksi
            $table->date('date'); // tanggal transaksi
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
