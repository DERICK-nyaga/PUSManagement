<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['amount', 'type', 'due_date', 'recipient', 'station_id'];
    // Add this to cast due_date to a Carbon instance
    protected $dates = ['due_date'];

    // Or for Laravel 8+
    protected $casts = [
        'due_date' => 'datetime',
    ];
    // Define the inverse relationship with station
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }
}