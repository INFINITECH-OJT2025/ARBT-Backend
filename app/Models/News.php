<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{

    
    use HasFactory;
 
    protected $fillable = ['title', 'image', 'link', 'content', 'archived'];

    protected $casts = [
        'archived' => 'boolean',
    ];
    
    public function images()
    {
        return $this->hasMany(\App\Models\NewsImage::class);
    }


}