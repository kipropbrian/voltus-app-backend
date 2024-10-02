<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Face extends Model
{
    use HasFactory;

    /**
     * Get the image that owns the face.
     */
    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    /**
     * Get the FacePlus request that owns the face.
     */
    public function facePlusRequest()
    {
        return $this->belongsTo(FacePlusRequest::class);
    }

    /**
     * Get the person associated with the face.
     */
    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Get the faceset associated with the face.
     */
    public function faceset()
    {
        return $this->belongsTo(FaceSet::class);
    }
}
