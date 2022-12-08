<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\FacePlusClient;
use App\Models\Faceset;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
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


    /**
     * Search for image on facePlusPlus and save the details to image table
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function facePlusSearch(Request $request, FacePlusClient $faceplus)
    {
        //upload image to cloudinary
        $validated = $request->validate([
            'image' => 'required|mimes:jpg,jpeg,png|max:2048'
        ]);

        //store file on cloudinary
        $image = new Image;
        if ($request->hasFile('image')){
            $result = $request->image->storeOnCloudinary('voltus');
            Log::channel('stderr')->info('Image '. $result->getFileName(). ' saved on cloudinary! on URL '. $result->getPath());

            //save details on image table
            $image = new Image;
            $image->uuid = Str::uuid();
            $image->image_url = $result->getPath();
            $image->image_url_secure =  $result->getSecurePath();
            $image->size = $result->getReadableSize();
            $image->filetype = $result->getFileType();
            $image->originalFilename = $result->getOriginalFileName();
            $image->publicId = $result->getPublicId();
            $image->extension = $result->getExtension();
            $image->width = $result->getWidth();
            $image->height = $result->getHeight();
            $image->person_id = 1; //TODO remove.
            $image->timeUploaded = $result->getTimeUploaded();
            $image->save();
        }
        else {
            return response()->json(['error' => 'Image not found bruh']);
        }

        //send to fp and save search details
        $faceSet = Faceset::where('status', 'active')->first();
        $response = $faceplus->searchFace(['image_url' => $image->image_url, 'faceset_token' => $faceSet->faceset_token]); //url
        $data = $response->object();

        if(isset($data->error_message)){
            return "There was in issue with the request" . $data->error_message;
        }

        $imageUuids = [];
        //if match search for uuid in person db
        foreach( $data->results as $result ) {
            if (isset($result->user_id)) {
                array_push($imageUuids, $result->user_id);
            }
        }
        $images = Image::whereIn('uuid', $imageUuids)->get();

        $resp = ['images' => $images, 'facepResponse' => $data, 'imageuuids' => $imageUuids];

        return response()->json($resp);
    }

}
