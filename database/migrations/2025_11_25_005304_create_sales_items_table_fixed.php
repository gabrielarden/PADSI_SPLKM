<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cek jika tabel sudah ada, hapus dulu agar bersih (opsional)
        Schema::dropIfExists('sales_items');

        Schema::create('sales_items', function (Blueprint $table) {
            $table->id();
            
            // Hubungkan ke tabel 'sales' (Header Transaksi)
            // onDelete('cascade') artinya jika transaksi dihapus, item-itemnya ikut terhapus
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            
            // INI SOLUSI ERROR SEBELUMNYA:
            // Buat product_id jadi NULLABLE.
            // Artinya: Jika produk dari CSV tidak ada di database master produk, biarkan NULL.
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            
            // Kolom untuk menyimpan data dari CSV
            $table->string('product_name'); // Nama Produk
            $table->integer('quantity');    // Jumlah
            $table->decimal('unit_price', 15, 2)->default(0); // Harga Satuan
            $table->decimal('total_price', 15, 2)->default(0); // Total Harga per Item
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_items');
    }
};