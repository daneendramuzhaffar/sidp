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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('no_spp')->unique();
            $table->date('date');
            $table->unsignedBigInteger('id_worker');
            $table->unsignedBigInteger('duration'); // in minutes
            $table->string('plat');
            $table->time('waktu_mulai')->default('00:00:00'); // default start time
            $table->time('timer')->default('00:00:00'); // timer for tracking duration
            $table->time('waktu_selesai')->default('00:00:00'); // default end time
            $table->enum('status', ['belum dimulai', 'proses', 'selesai'])->default('belum dimulai');
            $table->string('keterangan')->nullable(); // optional notes or description
            $table->timestamps();
            $table->string('nama_mobil');
            $table->foreign('id_worker')->references('id')->on('workers')->onDelete('cascade');
            $table->foreign('duration')->references('id')->on('work_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
