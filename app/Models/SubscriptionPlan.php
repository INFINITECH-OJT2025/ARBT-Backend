<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'description', 'features'];

    protected $casts = [
        'features' => 'array', // Ensure features are stored as an array in JSON format
    ];
}