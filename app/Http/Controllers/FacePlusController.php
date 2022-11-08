<?php

namespace App\Http\Controllers;

use App\FacePlusClient;
use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\Image;

class FacePlusController extends Controller
{
    /*
    * Function for detecting face, add face to faceset then set userID so faceset can be able to search
    *
    */
    public function getFaceTokenAddFacesetSetUserID(Person $person, Image $image, FacePlusClient $faceplus){
        //Send image link (image_url)

        dd($person->name);

        $response = $faceplus->detectFace($options); //url

        //Send face_token to face_set
        $response = $faceplus->addFaceset($options); // face_token, face_set

        //Send faceset_token and $person->uuid
        $response = $faceplus->setUserIdFace($options);// face_token, user_id
    }

}
