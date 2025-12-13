<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SalesItem extends Model
{
    use HasFactory;

    protected $table = 'sales_items';

    protected $fillable = [
        'sale_id',
        'product_id',   
        'product_name', 
        'quantity',     
        'unit_price',   
        'total_price',  
    ];

    // Relasi ke Header Transaksi (Sale)
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * PERBAIKAN DI SINI:
     * Tambahkan relasi ke model Product agar error 'undefined relationship [product]' hilang.
     */
    public function product(): BelongsTo
    {
        // Relasi ini nullable karena di migrasi product_id boleh null
        // (untuk kasus import produk yang tidak ada di master)
        return $this->belongsTo(Product::class)->withDefault([
            'name' => $this->product_name, // Fallback: jika produk master dihapus, pakai nama snapshot
        ]);
    }
}