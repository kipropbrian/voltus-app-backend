<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'image_url', 'about', 'gender'];

    /**
     * Get the pictures for the person
     */
    public function images()
    {
        return $this->hasMany(Image::class);
    }

    /**
     * Get the person's most recent image.
     */
    public function latestImage()
    {
        return $this->hasOne(Image::class)->latestOfMany();
    }
}
