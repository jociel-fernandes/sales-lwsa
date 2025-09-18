<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'seller_id',
        'date',
        'value',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'value' => 'decimal:2',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}

