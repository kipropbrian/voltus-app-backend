<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    /**
     * Get the person that the picture belongs to
     */
    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
