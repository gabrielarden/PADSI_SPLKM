<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            // Izinkan ID diisi manual (karena dari CSV teman)
            $table->unsignedBigInteger('id')->primary(); 
            $table->string('name');
            $table->string('phone')->nullable();
            
            // UBAH DISINI: Dari email menjadi loyalty_points
            // Gunakan integer (angka) dan default 0
            $table->integer('loyalty_points')->default(0); 
            
            $table->text('address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};