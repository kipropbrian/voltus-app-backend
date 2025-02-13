<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Person;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\FacePlusClient;
use App\Models\FaceplusRequest;
use App\Models\Face;
use App\Models\Faceset;

class PersonController extends Controller
{
    protected $facePlusClient;

    public function __construct(FacePlusClient $facePlusClient)
    {
        $this->facePlusClient = $facePlusClient;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
         // Get pagination parameters from the request
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);

        // Paginate the results
        $people = Person::with('images')->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            'people' => $people->items(),
            'total' => $people->total(),
            'currentPage' => $people->currentPage(),
            'pageSize' => $people->perPage(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return response()->json(['people' => 'sdsd']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|max:255|unique:people,name',
                'email' => 'nullable',
                'gender' => 'required',
                'about' => 'required|max:65535',
                'image' => 'mimes:jpg,jpeg,png|max:2048'
            ]);

            $person = new Person($validated);
            $person->uuid = (string) Str::uuid();
            $person->save();

            //store file on cloudinary
            if ($request->hasFile('image')) {

                $faceData = $this->detectFaceInImage($request->file('image'));

                if (count($faceData->faces) > 1) {
                    return response()->json([
                        'message' => 'An error occurred while saving the person and image.',
                        'error' => 'The image contains multiple faces',
                    ], 400);
                }

                // Assign a user ID to the face token
                $this->setUserIdForFace($person, $faceData->faces[0]->face_token);

                // Add the face to the active faceset
                $this->addFaceToFaceset($faceData->faces[0]->face_token);

                // Store image on Cloudinary and save in the database
                $image = $this->storeImage($request->file('image'), $person);

                // Save face data to the database
                $this->saveFaceData($faceData, $image, $person);
            }
            return response()->json([
                'message' => 'Person and image saved successfully!',
                'person' => $person,
                'image' => $image ?? null, // Return image data if an image was uploaded
            ], 201);
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'An error occurred while saving the person and image.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Person  $person
     * @return \Illuminate\Http\Response
     */
    public function show(Person $person)
    {
        $person->load('images');

        return response()->json([
            'status' => 'success',
            'person' => $person
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Person  $person
     * @return \Illuminate\Http\Response
     */
    public function edit(Person $person) {}

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Person  $person
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Person $person)
    {
        try {
            //TODO: Rollback on failure
            $validated = $request->validate([
                'name' => 'required|max:255',
                'gender' => 'required',
                'about' => 'required|max:65535',
                'image' => 'nullable|mimes:jpg,jpeg,png|max:2048'
            ]);

            if ($request->hasFile('image')) {
                $faceData = $this->detectFaceInImage($request->file('image'));

                if (count($faceData->faces) > 1) {
                    return response()->json([
                        'message' => 'An error occurred while saving the person and image.',
                        'error' => 'The image contains multiple faces',
                    ], 400);
                }

                // Assign a user ID to the face token
                $this->setUserIdForFace($person, $faceData->faces[0]->face_token);

                // Add the face to the active faceset
                $this->addFaceToFaceset($faceData->faces[0]->face_token);

                // Store image on Cloudinary and save in the database
                $image = $this->storeImage($request->file('image'), $person);

                // Save face data to the database
                $this->saveFaceData($faceData, $image, $person);
            }
 
            $person->update($validated);

            return response()->json([
                'message' => 'Person updated successfully!',
                'person' => $person,
                'image' => $image ?? null,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error creating person: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while updating the persons details.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Person  $person
     * @return \Illuminate\Http\Response
     */
    public function destroy(Person $person)
    {
        try {
            if ($person->images) {
                foreach ($person->images as $image) {
                    $image->delete();
                }
            }
            $person->delete();

            Log::info('Person deleted with ID: ' . $person->id);

            // Step 5: Return success response
            return response()->json([
                'message' => 'Person and associated images deleted successfully!',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting person and images: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while deleting the person and images.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detect face in the image using the FacePlus API.
     *
     * @param  \Illuminate\Http\UploadedFile  $image
     * @return object
     */
    protected function detectFaceInImage($image)
    {
        $response = $this->facePlusClient->detectFace(['image_file' => $image]);
        return $response->object();
    }

    /**
     * Set user ID for the face token using the FacePlus API.
     *
     * @param  \App\Models\Person  $person
     * @param  string  $faceToken
     */
    protected function setUserIdForFace(Person $person, string $faceToken)
    {
        $response = $this->facePlusClient->setUserIdFace([
            'face_token' => $faceToken,
            'user_id' => $person->uuid,
        ]);
        $result = $response->object();

        if (isset($result->error_message)) {
            throw new \Exception('Error setting user ID for face: ' . $result->error_message);
        }
    }

    /**
     * Add face to the active faceset.
     *
     * @param  string  $faceToken
     */
    protected function addFaceToFaceset(string $faceToken)
    {
        $faceSet = Faceset::where('status', 'active')->first();
        $response = $this->facePlusClient->addFaceset([
            'faceset_token' => $faceSet->faceset_token,
            'face_tokens' => $faceToken,
        ]);
        $result = $response->object();

        if (isset($result->error_message)) {
            throw new \Exception('Error adding face to faceset: ' . $result->error_message);
        }
    }

    /**
     * Save face data from FacePlus API response to the database.
     *
     * @param  object  $faceData
     * @param  \App\Models\Image  $image
     * @param  \App\Models\Person  $person
     */
    protected function saveFaceData($faceData, Image $image, Person $person)
    {
        $facePlusRequest = FaceplusRequest::where('request_id', $faceData->request_id)->first();
        foreach ($faceData->faces as $faceDetails) {

            $face = new Face();
            $face->face_token = $faceDetails->face_token;
            $face->image_id = $image->id;
            $face->faceplusrequest_id = $facePlusRequest->id;
            $face->face_rectangle = json_encode($faceDetails->face_rectangle);
            $face->landmarks = $faceDetails->landmark ?? null;
            $face->attributes = $faceDetails->attributes ?? null;
            $face->person_id = $person->id;

            $face->save();
        }
    }

    /**
     * Store the uploaded image on Cloudinary and save it in the database.
     *
     * @param  \Illuminate\Http\UploadedFile  $imageFile
     * @param  \App\Models\Person  $person
     * @return \App\Models\Image
     */
    protected function storeImage($imageFile, Person $person)
    {
        $md5Hash = md5_file($imageFile->getRealPath());
        $result = $imageFile->storeOnCloudinary('voltus');

        $image = new Image();
        $image->uuid = Str::uuid();
        $image->image_url = $result->getPath();
        $image->image_url_secure = $result->getSecurePath();
        $image->size = $result->getReadableSize();
        $image->filetype = $result->getFileType();
        $image->originalFilename = $result->getOriginalFileName();
        $image->publicId = $result->getPublicId();
        $image->extension = $result->getExtension();
        $image->width = $result->getWidth();
        $image->height = $result->getHeight();
        $image->timeUploaded = $result->getTimeUploaded();
        $image->md5 = $md5Hash;

        // Save the image associated with the person
        $person->images()->save($image);

        return $image;
    }
}
