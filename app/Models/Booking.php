<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'datetime', 'service', 'contact_number'];


    // ✅ Link booking to user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
