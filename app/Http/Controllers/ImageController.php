<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\FacePlusClient;
use App\Models\Faceset;
use Illuminate\Database\Eloquent\Collection;
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
            $resp = $this->searchOnFp($image);

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
    public function searchOnFp(Image $image)
    {
        $faceplus = new FacePlusClient();
        //send to fp and save search details
        $faceSet = Faceset::where('status', 'active')->first();
        $response = $faceplus->searchFace(['image_file' => $image, 'faceset_token' => $faceSet->faceset_token]); //url
        $data = $response->object();

        if (isset($data->error_message)) {
            return "There was in issue with the request " . $data->error_message;
        }

        $imageUuids = [];
        //if match search for uuid in person db
        foreach ($data->results as $result) {
            if (isset($result->user_id)) {
                array_push($imageUuids, $result->user_id);
            }
        }
        $images = Image::whereIn('uuid', $imageUuids)->get();

        $resp = ['images' => $images, 'facepResponse' => $data, 'imageuuids' => $imageUuids];

        return $resp;
    }

    public function dummyResponse()
    {
        $resp = '
        "facepResponse": {
            "image_id": "O2VoqtkUQ4+MKqn8Yj+Yrw==",
            "faces": [
              {
                "face_rectangle": {
                  "width": 65,
                  "top": 79,
                  "left": 325,
                  "height": 65
                },
                "face_token": "0a954721c8a04c8d35038f7796e468c2"
              },
              {
                "face_rectangle": {
                  "width": 62,
                  "top": 102,
                  "left": 111,
                  "height": 62
                },
                "face_token": "71fb53b278d6ca66c846ce85d9f89033"
              },
              {
                "face_rectangle": {
                  "width": 58,
                  "top": 96,
                  "left": 542,
                  "height": 58
                },
                "face_token": "79321b4d027be5251f999f591e3b3238"
              }
            ],
            "time_used": 517,
            "thresholds": {
              "1e-3": 62.327,
              "1e-5": 73.975,
              "1e-4": 69.101
            },
            "request_id": "1671194504,f50e344f-ab67-4f8b-9018-b5734737285f",
            "results": [
              {
                "confidence": 93.457,
                "user_id": "e20be08c-ec62-4789-91bd-5fd57684678b",
                "face_token": "c81cafa44bc32ffe4d40edfc630373f3"
              }
            ]
          }';
        return response()->json(['message' => 'Succesfully processed', 'info' => json_decode($resp)]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Image  $image
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
