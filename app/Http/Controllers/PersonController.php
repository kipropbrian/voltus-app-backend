<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Person;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\FacePlusClient;
use App\Models\FaceplusRequest;
use App\Models\Faces;
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
    public function index()
    {
        //Get all
        $people = Person::with('images')->get();

        return response()->json(['people' => $people]);
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
                'about' => 'required|max:255',
                'image' => 'nullable|mimes:jpg,jpeg,png|max:2048'
            ]);

            $person = new Person($validated);
            $person->uuid = (string) Str::uuid();
            $person->save();

            //store file on cloudinary
            if ($request->hasFile('image')) {

                $response = $this->facePlusClient->detectFace([
                    'image_file' => $request->file('image')
                ]);
                $data = $response->object();

                if (count($data->faces) > 1) {
                    return response()->json([
                        'message' => 'An error occurred while saving the person and image.',
                        'error' => 'The image contains multiple faces',
                    ], 400);
                }

                //set a user id for the facetoken
                $response = $this->facePlusClient->setUserIdFace([
                    'face_token' => $data->faces[0]->face_token,
                    'user_id' => $person->uuid,
                ]);
                $setUIDresp = $response->object();

                if (isset($setUIDresp->error_message)) {
                    return response()->json([
                        'message' => 'There was in issue with the set request',
                        'error' => $setUIDresp->error_message,
                    ], 500);
                }

                //add face to faceset so that we can track it. 
                $faceSet = Faceset::where('status', 'active')->first();
                $response = $this->facePlusClient->addFaceset(
                    [
                        'faceset_token' => $faceSet->faceset_token,
                        'face_tokens' => $data->faces[0]->face_token
                    ]
                );
                $addFace = $response->object();

                if (isset($addFace->error_message)) {
                    return response()->json([
                        'message' => 'There was in issue with the add face request',
                        'error' => $addFace->error_message,
                    ], 500);
                }

                $md5Hash = md5_file($request->file('image')->getRealPath());

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
                $image->md5 = $md5Hash;

                $person->images()->save($image);

                $facePlusRequest = FaceplusRequest::where('request_id', $data->request_id)->first();

                $faceTokens = [];
                foreach ($data->faces as $face) {
                    $faceTokens[] = $face->face_token;

                    // Save face detection data in the faces table
                    $newFace = new Faces();
                    $newFace->face_token = $face->face_token;
                    $newFace->image_id = $image->id;
                    $newFace->faceplusrequest_id = $facePlusRequest->id;
                    $newFace->face_rectangle = json_encode($face->face_rectangle);
                    $newFace->landmarks = $face->landmark ?? NULL;
                    $newFace->attributes = $face->attributes ?? NULL;
                    $newFace->person_id = $person->id;
                    $newFace->save();
                }
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
                'about' => 'required|max:255',
                'image' => 'nullable|mimes:jpg,jpeg,png|max:2048'
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

                $person->images()->save($image);
                Log::channel('stderr')->info('Image saved and attached to person');
            }

            $person->update($validated);
            Log::info('Person info updated' . $person);

            return response()->json([
                'message' => 'Person updated successfully!',
                'person' => $person,
                'image' => $image ?? null,
            ], 201);
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
}
