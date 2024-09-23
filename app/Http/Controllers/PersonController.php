<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Person;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Cloudinary\Transformation\Resize;
use Cloudinary\Transformation\Gravity;
use Cloudinary\Transformation\FocusOn;

class PersonController extends Controller
{
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
                $result = $request->image->storeOnCloudinary('voltus');
                Log::info('Image ' . $result->getFileName() . ' saved on cloudinary! on URL ' . $result->getPath());

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
            }

            Log::info('Person Created');

            return response()->json([
                'message' => 'Person and image saved successfully!',
                'person' => $person,
                'image' => $image ?? null, // Return image data if an image was uploaded
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating person: ' . $e->getMessage());

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
