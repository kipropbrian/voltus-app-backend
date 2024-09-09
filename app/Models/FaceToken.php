<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaceToken extends Model
{
    use HasFactory;

    /**
     * Get the pictures for the person
     */
    public function image()
    {
        return $this->belongsTo(Image::class);
    }
}
