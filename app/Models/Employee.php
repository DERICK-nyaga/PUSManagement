<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'station_id',
        'position',
        'salary',
        'hire_date',
        'status'
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}
