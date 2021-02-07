<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Api extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'client_id',
        'client_secret',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
}
