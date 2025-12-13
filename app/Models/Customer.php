<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $fillable = [
        'id',      // Kita buka akses ID agar bisa diisi dari CSV
        'name',
        'loyalty_points',   // Akan diisi Poin Loyalitas
        'phone',
        'address',
    ];

    // Relasi ke Sale (Opsional, untuk referensi masa depan)
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}