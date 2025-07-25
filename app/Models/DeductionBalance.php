<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeductionBalance extends Model
{
    protected $fillable = ['employee_id', 'balance'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
