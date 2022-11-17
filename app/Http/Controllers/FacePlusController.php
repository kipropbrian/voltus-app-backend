<?php

namespace App\Http\Controllers;

use App\FacePlusClient;
use App\Models\Faceset;
use App\Models\Image;
use Illuminate\Support\Facades\Log;

class FacePlusController extends Controller
{
    /*
    * Function for detecting face, add face to faceset then set userID so faceset can be able to search
    *
    */
    public function getFaceTokenAddFacesetSetUserID(Image $image, FacePlusClient $faceplus){
        // Send image link (image_url)
        $response = $faceplus->detectFace(['image_url' => $image->image_url]); //url
        $data = $response->object();

        if(isset($data->error_message)){
            return "There was in issue with the request" . $data->error_message;
        }

        //one face, as expected
        if($data->face_num == 1) {
            $face = $data->faces[0];
            //save face token to image table
            $image->face_token = $face->face_token;
            $image->save();
            //TODO: Save face rectangle? For what purpose?

        } else if ($data->face_num > 1){
            Log::channel('stderr')->info($data->error_message);
            return back()->with("status", "There image had more than one person.");
        }

        //Get faceTokens and send face_token to face_set
        $faceSet = Faceset::where('status', 'active')->first();
        $response = $faceplus->addFaceset(['faceset_token' => $faceSet->faceset_token, 'face_tokens' => $image->face_token]); // face_token, face_set
        $data = $response->object();

        if(isset($data->error_message)){
            Log::channel('stderr')->info($data->error_message);
            return back()->with("status", "There was in issue with the add request");
        }

        //add faceset_id to image table
        $image->faceset_id = $faceSet->id;
        $image->save();

        //Send faceset_token and $person->uuid
        $response = $faceplus->setUserIdFace(['face_token' => $image->face_token, 'user_id' => $image->person->uuid]);// face_token, user_id
        $data = $response->object();

        if(isset($data->error_message)){
            Log::channel('stderr')->info($data->error_message);
            return back()->with("status", "There was in issue with the set request");
        }

        //set detected as true in image table
        $image->detected = true;
        $image->save();

        return back();
    }

}
