<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['amount', 'type', 'due_date', 'recipient', 'station_id'];
    protected $dates = ['due_date'];


    protected $casts = [
        'due_date' => 'datetime',
    ];
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }
}
