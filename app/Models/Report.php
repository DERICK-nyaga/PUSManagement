<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category',
        'status',
        'file_path',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
