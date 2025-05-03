<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\FacePlusClient;
use App\Models\Faceset;
use App\Models\FaceToken;
use App\Models\TwitterImages;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;

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
	 * @param  String $image_id
	 * @return \App\Models\Image
	 */
	public function store(Request $request, String $image_id)
	{
		//store file on cloudinary
		if ($request->hasFile('image')) {
			// Calculate MD5 hash from the uploaded file
			$md5Hash = md5_file($request->file('image')->getRealPath());

			$similarImage = Image::where('md5', $md5Hash)->first();

			if ($similarImage) {
				return $similarImage;
			}

			$result = $request->image->storeOnCloudinary('voltus');

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
			$image->faceplusimage_id = $image_id;
			$image->md5 = $md5Hash;
			$image->save();

			return $image;
		}
		throw new \Exception('No valid image provided');
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

	/**
	 * Uploads the image to Cloudinary and creates an Image record.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \App\Models\Image
	 */
	public function uploadImageToCloudinary(Request $request)
	{
		if ($request->hasFile('image')) {
			$result = $request->image->storeOnCloudinary('voltus');

			Log::info('Image ' . $result->getFileName() . ' saved on cloudinary! on URL ' . $result->getPath());

			// Create new Image record
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
			$image->save();

			return $image; // Return the saved image record
		}

		throw new \Exception('No valid image provided');
	}

	public function getImagesFromMongo(Request $request)
	{
		// Get the images from MongoDB and Paginate them
		$request->validate([
			'page' => 'required|integer|min:1',
			'limit' => 'required|integer|min:1|max:100',
		]);
		$page = $request->input('page');
		$limit = (int) $request->input('limit');
		$offset = ($page - 1) * $limit;
		
		// Get the images from MongoDB and group by image_name
		$mongoData = TwitterImages::raw(function($collection) use ($limit) {
			return $collection->aggregate([
				[
					'$match' => [
						'confidence' => ['$gt' => 0.9]
					]
				],
				[
					'$group' => [
						'_id' => '$image_name',
						'documents' => ['$push' => '$$ROOT'],
						'count' => ['$sum' => 1]
					]
				],
				['$sort' => ['_id' => -1]],
				['$limit' => $limit]
			]);
		});
		// Check if the data is empty
		$total = TwitterImages::count();
		if ($mongoData->isEmpty()) {
			return response()->json(['message' => 'No images found'], 404);
		}
		// Calculate pagination details
		$lastPage = ceil($total / $limit);
		$nextPage = $page < $lastPage ? $page + 1 : null;
		$prevPage = $page > 1 ? $page - 1 : null;

		return response()->json([
			'data' => $mongoData,
			'pagination' => [
				'current_page' => $page,
				'next_page' => $nextPage,
				'prev_page' => $prevPage,
				'total_pages' => $lastPage,
				'total_items' => $total,
			],
		]);		
	}

	/**
	 * Handles the image search functionality.
	 *
	 * @param \Illuminate\Http\Request $request The HTTP request instance.
	 *
	 * @return \Illuminate\Http\JsonResponse The JSON response containing the search result or an error message.
	 *
	 * @throws \Illuminate\Validation\ValidationException If the request validation fails.
	 * @throws \Exception If the search operation times out or encounters an error.
	 */
	public function search(Request $request)
	{
		$request->validate(['image' => 'required|image|max:2048']);
		
		
		// Retrieve requestID from headers
		$requestID = Str::uuid()->toString();
		
		// Save image temporarily
		$tempPath = $request->file('image')->storeAs('temp', $requestID . '.jpg');
		$imagePath = storage_path('app/' . $tempPath);
		

		try {


			// Publish search request
			$data = [
				'request_id' => $requestID,
				'image_path' => $imagePath,
				'reply_topic' => 'face_search_replies_' . $requestID,
				'timestamp' => now()->toISOString()
			];

			$message = new Message(
				body: ['request' => Json::encode($data)],
			);


			$producer = Kafka::publish('localhost')->onTopic('face_search_requests')->withMessage($message);
			$producer->send();
			
			// Wait for response (with timeout)
            $startTime = time();
            $timeout = 30; // seconds

			while (time() - $startTime < $timeout) {
				Log::info('Processing response ....');
                if ($result = Kafka::consumer(['face_search_responses'])) {
                    Storage::delete($tempPath);
                    return response()->json($result);
                }
                sleep(1);
            }

			throw new \Exception('Search timeout');
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		} finally {
			// Clean up temporary file
			if (file_exists($imagePath)) {
				unlink($imagePath);
			}
		}
	}
}
