<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Sale extends Model
{
    use HasFactory;

    // Pastikan terhubung ke tabel 'sales'
    protected $table = 'sales';

    protected $fillable = [
        'transaction_id',
        'customer_id',
        'user_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'change_amount',
        'payment_method',
        'status',
        'notes',
        'created_at', // PENTING: Agar tanggal historis dari CSV bisa masuk
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Generate unique transaction ID otomatis jika kosong
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($sale) {
            if (empty($sale->transaction_id)) {
                $sale->transaction_id = 'TRX-' . date('Ymd') . '-' . str_pad((string)random_int(1, 99999), 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * RELASI PENTING:
     * Fungsi ini WAJIB ADA agar error 'RelationNotFoundException' hilang.
     */
    public function salesItems(): HasMany
    {
        // Pastikan Anda memiliki model SalesItem di project Anda.
        // Jika belum ada, buat dengan: php artisan make:model SalesItem
        return $this->hasMany(SalesItem::class);
    }

    public function getFormattedTransactionIdAttribute(): string
    {
        return $this->transaction_id;
    }

    public function getFormattedPaymentMethodAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Tunai',
            'card' => 'Kartu',
            'transfer' => 'Transfer',
            'qris' => 'QRIS',
            default => ucfirst($this->payment_method),
        };
    }

    public function getFormattedStatusAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }
}