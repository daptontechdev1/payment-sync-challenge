<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'amount',
        'provider_id',
        'status',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    /**
     * Possible statuses: pending, completed, failed, refunded
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
