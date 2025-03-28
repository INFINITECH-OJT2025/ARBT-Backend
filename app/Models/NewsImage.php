<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsImage extends Model
{
    use HasFactory;

    // ✅ Now safe for mass assignment of `image` and `news_id`
    protected $fillable = ['news_id', 'image'];

    public function news()
    {
        return $this->belongsTo(\App\Models\News::class);
    }
}