<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'named_repeater',
        'unnamed_repeater',
    ];

    protected $casts = [
        'named_repeater' => 'array',
        'unnamed_repeater' => 'array',
    ];
}
