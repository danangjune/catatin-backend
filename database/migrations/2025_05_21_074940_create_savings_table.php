<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSavingsTable extends Migration
{
    public function up()
    {
        Schema::create('savings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->integer('target_amount'); // target tabungan per bulan
            $table->integer('saved_amount')->default(0); // total tabungan sampai saat ini di bulan ini
            $table->date('month'); // tanggal yang mewakili bulan (misal 2023-05-01)
            $table->timestamps();

            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('savings');
    }
}
