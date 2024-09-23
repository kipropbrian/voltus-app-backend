<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cloudinary\Asset\Image as CloudinaryImage;
use Cloudinary\Transformation\Resize;
use Cloudinary\Transformation\Gravity;
use Cloudinary\Transformation\FocusOn;

class Image extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Append transformed_url to the model's JSON representation
    protected $appends = ['transformed_url'];

    /**
     * Get the person that the picture belongs to
     */
    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function faceTokens()
    {
        return $this->hasMany(FaceToken::class);
    }

    // Define the accessor for transformed_url
    public function getTransformedUrlAttribute()
    {
        return (new CloudinaryImage($this->publicId))
            ->resize(
                Resize::crop()
                    ->gravity(
                        Gravity::focusOn(
                            FocusOn::face()
                        )
                    )
            )->toUrl();
    }
}
