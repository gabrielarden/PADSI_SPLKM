<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Ubah kolom unit_id agar boleh NULL (kosong)
            // Pastikan Anda sudah menginstal dbal: composer require doctrine/dbal
            // Jika belum, dan error, cara termudah adalah me-recreate tabel jika data boleh hilang.
            
            // Alternatif sederhana tanpa doctrine/dbal untuk Postgres/MySQL modern:
            $table->foreignId('unit_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Kembalikan ke tidak boleh null (hati-hati jika ada data null)
            //$table->foreignId('unit_id')->nullable(false)->change();
        });
    }
};