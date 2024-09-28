<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\FacePlusClient;
use App\Models\FaceplusRequest;
use App\Models\Faces;
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
    public function getFaceTokenAddFacesetSetUserID(Image $image, FacePlusClient $faceplus)
    {
        // Send image link (image_url)
        $response = $faceplus->detectFace(['image_url' => $image->image_url]); //url
        $data = $response->object();

        if (isset($data->error_message)) {
            return "There was in issue with the request" . $data->error_message;
        }

        //one face, as expected
        if ($data->face_num == 1) {
            $face = $data->faces[0];
            //save face token to image table
            $image->face_token = $face->face_token;
            $image->save();
            //TODO: Save face rectangle? For what purpose?

        } else if ($data->face_num > 1) {
            Log::channel('stderr')->info($data->error_message);
            return back()->with("status", "There image had more than one person.");
        }

        //Get faceTokens and send face_token to face_set
        $faceSet = Faceset::where('status', 'active')->first();
        $response = $faceplus->addFaceset(['faceset_token' => $faceSet->faceset_token, 'face_tokens' => $image->face_token]); // face_token, face_set
        $data = $response->object();

        if (isset($data->error_message)) {
            Log::channel('stderr')->info($data->error_message);
            return back()->with("status", "There was in issue with the add request");
        }

        //add faceset_id to image table
        $image->faceset_id = $faceSet->id;
        $image->save();

        //Send faceset_token and $person->uuid
        $response = $faceplus->setUserIdFace(['face_token' => $image->face_token, 'user_id' => $image->person->uuid]); // face_token, user_id
        $data = $response->object();

        if (isset($data->error_message)) {
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

            $faceplus = new FacePlusClient();
            //send to fp and save search details
            $faceSet = Faceset::where('status', 'active')->first();

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

    /**
     * Detects all faces in the uploaded image, performs a search for each face using the face_token in the FaceSet,
     * and combines the results (including person data, confidence score, and face position) into a simplified response object.
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\FacePlusClient $faceplus
     * @return \Illuminate\Http\JsonResponse
     */
    public function detectAndSearchFaces(Request $request, FacePlusClient $faceplus)
    {
        $request->validate([
            'image' => 'mimes:jpg,jpeg,png|max:2048'
        ]);
        // Step 1: Validate if the request has a valid image file
        if ($request->hasFile('image') && $request->file('image')->isValid()) {

            // Send image to Face++ detectFace API
            $response = $faceplus->detectFace(['image_file' => $request->file('image')]);
            $data = $response->object();

            $imageController = new ImageController();
            $image = $imageController->store($request, $data->image_id);

            $facePlusRequest = FaceplusRequest::where('request_id', $data->request_id)->first();

            // Check for error in response
            if (isset($data->error_message)) {
                return response()->json(['error' => $data->error_message], 400);
            }

            // Step 2: Extract face tokens for all detected faces
            // Step 2: Extract face tokens and save the detected face data
            $faceTokens = [];
            foreach ($data->faces as $face) {
                $faceTokens[] = $face->face_token;

                // Save face detection data in the faces table
                $newFace = new Faces();
                $newFace->face_token = $face->face_token;
                $newFace->image_id = $image->id;
                $newFace->faceplusrequest_id = $facePlusRequest->id;
                $newFace->face_rectangle = json_encode($face->face_rectangle);
                $newFace->landmarks = json_encode($face->landmark ?? null); // Handle if landmarks are not present
                $newFace->save();
            }

            // Check if any faces were detected
            if (count($faceTokens) === 0) {
                return response()->json(['message' => 'No faces detected.']);
            }

            // Step 3: Search for each face in the FaceSet and store results
            $faceSet = Faceset::where('status', 'active')->first();
            $searchResults = [];

            foreach ($faceTokens as $faceToken) {
                $searchResponse = $faceplus->searchFace([
                    'face_token' => $faceToken,
                    'faceset_token' => $faceSet->faceset_token,
                ]);
                $searchData = $searchResponse->object();

                // Check for any error during face search
                if (isset($searchData->error_message)) {
                    return response()->json(['error' => $searchData->error_message], 400);
                }

                // Extract matched persons by user_id from search results
                $personUuids = [];
                foreach ($searchData->results as $result) {
                    if (isset($result->user_id)) {
                        $personUuids[] = $result->user_id;
                    }
                }

                // Fetch persons from the database
                $persons = Person::whereIn('uuid', $personUuids)->with('latestImage')->get();

                // Combine face data and search results into a single object
                $searchResults[] = [
                    'face_token' => $faceToken,
                    'person_data' => $persons,
                    'confidence' => isset($searchData->results[0]) ? $searchData->results[0]->confidence : null,
                    'face_rectangle' => $data->faces[array_search($faceToken, array_column($data->faces, 'face_token'))]->face_rectangle,
                ];
            }

            // Step 4: Return the combined results to the frontend
            return response()->json([
                'message' => 'Successfully processed',
                'searchResults' => $searchResults,
            ]);
        }

        return response()->json(['error' => 'Invalid image file'], 400);
    }
}
