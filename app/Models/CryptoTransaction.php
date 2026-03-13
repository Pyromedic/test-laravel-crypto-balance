<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CryptoTransaction extends Model
{
    protected $fillable = [
        'user_id', 'type', 'amount', 'currency', 'status', 'tx_hash', 'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:8',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
