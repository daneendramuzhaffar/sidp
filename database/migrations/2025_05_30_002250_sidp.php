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
        Schema::create('status_pengerjaan', function (Blueprint $table) {
            $table->id();
            $table->enum('progres_pengerjaan', ['belum dimulai','proses','selesai'])->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('status_teknisi', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['aktif','sedang memperbaiki','izin','sakit','cuti','training','off'])->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('flatrate', function (Blueprint $table) {
            $table->id();
            $table->time('lama_pengerjaan');
        });

        Schema::create('jenis_pekerjaan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pekerjaan');
            $table->foreignId('id_flatrate')->constrained('flatrate')->onDelete('cascade');
        });

        Schema::create('sif', function (Blueprint $table) {
            $table->id();
            $table->time('mulai');
            $table->time('selesai');
        });

        Schema::create('teknisi', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->foreignId('id_status')->constrained('status_teknisi')->onDelete('cascade');
            $table->foreignId('id_sif')->constrained('sif')->onDelete('cascade');
        });

        Schema::create('pengerjaan', function (Blueprint $table) {
            $table->id();
            $table->integer('no_spp');
            $table->foreignId('id_teknisi')->constrained('teknisi')->onDelete('cascade');
            $table->date('tanggal_pengerjaan');
            $table->timestamp('waktu_mulai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->string('catatan');
            $table->foreignId('id_pekerjaan')->constrained('jenis_pekerjaan')->onDelete('cascade');
            $table->string('no_polisi');
            $table->foreignId('id_status')->constrained('status_pengerjaan')->onDelete('cascade');
            $table->timestamps();
        });

}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_pengerjaan');
        Schema::dropIfExists('status_teknisi');
        Schema::dropIfExists('flatrate');
        Schema::dropIfExists('jenis_pekerjaan');
        Schema::dropIfExists('sif');
        Schema::dropIfExists('teknisi');
        Schema::dropIfExists('pengerjaan');
    }
};
