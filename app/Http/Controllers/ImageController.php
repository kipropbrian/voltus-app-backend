<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\FacePlusClient;
use App\Models\Faceset;
use App\Models\FaceToken;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ImageController extends Controller
{

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		//
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		//TODO: Might be easier to do this async and just send image to FP as its uploading to Cloudinary
		//TODO: Sockets for comms with frontend
		//TODO: Rollback on failure
		$validated = $request->validate([
			'image' => 'mimes:jpg,jpeg,png|max:2048'
		]);

		//store file on cloudinary
		if ($request->hasFile('image')) {
			$result = $request->image->storeOnCloudinary('voltus');
			Log::channel('stderr')->info('Image ' . $result->getFileName() . ' saved on cloudinary! on URL ' . $result->getPath());

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
			$image->timeUploaded = $result->getTimeUploaded();
			$image->person_id = 1;
			$image->save();

			Log::channel('stderr')->info('Image saved and attached to person');

			//TODO: send on sockets that upload to CD is done. Face search about to start
			//Search on faceplus
			$resp = $this->searchOnFp($request, $image);

			return response()->json([
				'message' => 'Image succesfuly processed!',
				'data' => $resp
			]);
		}
	}

	/**
	 * Search the face on FP
	 *
	 * @param String ImgUrl on Cloudinary
	 * @return Json Response() with
	 */
	public function searchOnFp(Request $request, Image $image)
	{
		if ($request->hasFile('image') && $request->file('image')->isValid()) {
			Log::channel('stderr')->info("Image Log -> {$request->image}");

			$faceplus = new FacePlusClient();
			//send to fp and save search details
			$faceSet = Faceset::where('status', 'active')->first();
			Log::channel('stderr')->info("Faceset found ->  {$faceSet->display_name} | Faceset Token -> {$faceSet->faceset_token}");
			$response = $faceplus->searchFace(['image_file' => $request->file("image"), 'faceset_token' => $faceSet->faceset_token]); //url
			$data = $response->object();

			if (isset($data->error_message)) {
				return "There was in issue with the request " . $data->error_message;
			}

			$personUuids = [];
			//if match search for uuid in person db
			foreach ($data->results as $result) {
				if (isset($result->user_id)) {
					array_push($personUuids, $result->user_id);
				}
			}
			$persons = Person::whereIn('uuid', $personUuids)->get();

			$resp = ['persons' => $persons, 'facepResponse' => $data, 'imageuuids' => $personUuids];

			return response()->json(['message' => 'Succesfully processed', 'info' => $resp]);
		}
	}


	/**
	 * Search the face on FP
	 *
	 * @param String ImgUrl on Cloudinary
	 * @return Json Response() with
	 */
	public function detectFaces(Request $request, Image $image)
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
	 * Gets the image url from the image table
	 * Sends the image to face++ search endpoint and returns a token
	 * Sets the user ID on face++ to be $personUuid
	 * adds the token to a faceset
	 *
	 * @param  \Illuminate\Http\Request
	 * @return \Illuminate\Http\Response
	 */
	public function syncImage(Request $request)
	{
		try {
			$imageId = $request->input('imageId');
			$personUuid = $request->input('personUuid');

			$faceplusClient = new FacePlusClient();

			// 1. Retrieve the image URL from the image table
			$image = Image::find($imageId);
			if (!$image) {
				Log::channel('stderr')->info("Image not found");
				return response()->json(['message' => 'Image not found'], 404);
			}

			// 2. Use FacePlusClient to detect a face and get a token
			$searchResponse = $faceplusClient->detectFace(['image_url' => $image->image_url]); //url

			$searchObject = $searchResponse->object();
			if (isset($searchObject->error_message)) {
				return response()->json(['message' => 'Face++ search failed'], 500);
			}

			// Check if exactly one face is detected
			if ($searchObject->face_num !== 1) {
				return response()->json(['message' => 'Error: Image must contain exactly one face'], 400);
			}

			$faceToken = $searchResponse['faces'][0]['face_token'];
			Log::channel('stderr')->info("Face token retrieved: " . $faceToken);

			// 3. Set the user ID on Face++
			$setUserIdResponse = $faceplusClient->setUserIdFace(['face_token' => $faceToken, 'user_id' => $personUuid]);

			$setUserIdResponseObject = $setUserIdResponse->object();
			if (isset($setUserIdResponseObject->error_message)) {
				return response()->json(['message' => 'Failed to set user ID on Face++'], 500);
			}
			Log::channel('stderr')->info("user ID " . $personUuid . " set for face token " . $faceToken . " !");

			// Get faceset token from db
			$faceSet = Faceset::where('status', 'active')->first();

			// 4. Add the token to a FaceSet
			$addToFaceSetResponse = $faceplusClient->addFaceset(['faceset_token' => $faceSet->faceset_token, 'face_tokens' => $faceToken]);

			$addToFaceSetResponseObject = $addToFaceSetResponse->object();
			if (isset($addToFaceSetResponseObject->error_message)) {
				return response()->json(['message' => 'Failed to add face token to FaceSet'], 500);
			}
			Log::channel('stderr')->info("Face token " . $faceToken . " added to the Faceset !");

			//save facetoken to image
			$faceTokens = new FaceToken();
			$faceTokens->face_token = $faceToken;
			$faceTokens->faceset_id = $faceSet->id;
			$image->faceTokens()->save($faceTokens);
			Log::channel('stderr')->info("Facetoken for image saved to DB! ");

			//set image as detected
			$image->detected = true;
			$image->save();
			Log::channel('stderr')->info("Image now connected! ");

			return response()->json(['message' => 'Image synced successfully']);
		} catch (\Exception $e) {
			return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\Models\Image  $image`
	 * @return \Illuminate\Http\Response
	 */
	public function show(Image $image)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\Models\Image  $image
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Image $image)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Models\Image  $image
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, Image $image)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\Models\Image  $image
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Image $image)
	{
		// //Soft delete from db
		try {
			$image->delete();
			return back()->with('status', 'Image has been deleted! ');
		} catch (\Exception $e) {
			return back()->with('status', 'There was a problem! ');
		}
		//remove from face set if set.

		//Delete from cloudinary
	}
}
