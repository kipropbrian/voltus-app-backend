<?php 

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class TwitterImages extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'faces';
}