<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\FacePlusClient;
use App\Models\Faceset;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FacePlusController extends Controller
{
    /*
    * Function for detecting face, add face to faceset then setuserID so faceset can be able to search
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
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            Log::channel('stderr')->info("Image Log -> {$request->image}");

            $faceplus = new FacePlusClient();
            //send to fp and save search details
            $faceSet = Faceset::where('status', 'active')->first();
            Log::channel('stderr')->info("Faceset found ->  {$faceSet->display_name} | Faceset Token -> {$faceSet->faceset_token}");
            
            $response = $faceplus->detectFace(['image_file' => $request->file("image"), 'faceset_token' => $faceSet->faceset_token]); //url
            $data = $response->object();

            if (isset($data->error_message)) {
                return "There was in issue with the request " . $data->error_message;
            }

            return response()->json(['message' => 'Succesfully processed', 'info' => $data]);
        }
    }


    /**
     * Detect faces in an mage on facePlusPlus
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function facePlusDetect(Request $request, FacePlusClient $faceplus)
    {
        if ($request->hasFile('image') && $request->file('image')->isValid()) {

			$faceplus = new FacePlusClient();
			//send to fp and save search details
			$faceSet = Faceset::where('status', 'active')->first();
			
			$response = $faceplus->searchFace(['image_file' => $request->file("image"), 'faceset_token' => $faceSet->faceset_token]); //url

			$data = $response->object();

			if (isset($data->error_message)) {
				return response()->json($data);
			}

			$personUuids = [];
			//if match search for uuid in person db
			foreach ($data->results as $result) {
				if (isset($result->user_id)) {
					array_push($personUuids, $result->user_id);
				}
			}
			$persons = Person::whereIn('uuid', $personUuids)->with('latestImage')->get();

			$resp = ['persons' => $persons, 'facepResponse' => $data, 'imageuuids' => $personUuids];

			return response()->json(['message' => 'Succesfully processed', 'info' => $resp]);
		}
    }
}
