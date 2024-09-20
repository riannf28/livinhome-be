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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('nama');
            $table->longText('deskripsi');
            $table->date('tanggal_dibuat');
            $table->date('tanggal_mulai_sewa');
            $table->string('sewa_untuk');
            $table->longText('latitude');
            $table->longText('longitude');
            $table->string('kategori');
            $table->string('provinsi');
            $table->string('kota');
            $table->string('kecamatan');
            $table->longText('alamat');
            $table->longText('catatan_alamat')->nullable();
            $table->string('fasilitas');
            $table->integer('lebar_tanah');
            $table->integer('daya_listrik');
            $table->string('sumber_air');
            $table->integer('total_kamar');
            $table->integer('total_lemari');
            $table->integer('minimum_sewa');
            $table->integer('kapasitas_motor')->nullable();
            $table->integer('kapasitas_mobil')->nullable();
            $table->boolean('meja');
            $table->boolean('kasur');
            $table->double('harga_sewa_tahun')->nullable();
            $table->double('harga_sewa_3_bulan')->nullable();
            $table->double('harga_sewa_1_bulan')->nullable();
            $table->string('bank');
            $table->longText('rekening');
            $table->string('status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
