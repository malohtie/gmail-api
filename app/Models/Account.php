<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'is_active'
    ];

    protected $casts = [
        'token' => 'array',
        'is_active' => 'boolean'
    ];
}
