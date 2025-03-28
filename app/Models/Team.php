<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'name', 'role', 'description', 'image_url', 'status'
    ];
}
