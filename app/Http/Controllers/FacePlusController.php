<?php

namespace App\Http\Controllers;

use App\FacePlusClient;
use Illuminate\Http\Request;

class FacePlusController extends Controller
{
    
    public function getFaceTokenaddFacesetSetUserID(FacePlusToken $faceplus){
        
        $response = $faceplus->detectFace($options);
        
        $response = $faceplus->addFaceset($options);
        
        $response = $faceplus->setUserIdFace($options);
    }

}
