<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\FacePlusClient;

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
        return view('person.index', [
            'people' => Person::latest()->paginate(10),
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
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Person  $person
     * @return \Illuminate\Http\Response
     */
    public function show(Person $person, FacePlusClient $fp)
    {

        $response = $fp->searchFace(['image_url' => $person->latestImage->image_url]);

        return view('person.show', [
            'person' => $person
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Person  $person
     * @return \Illuminate\Http\Response
     */
    public function edit(Person $person)
    {
        //
        return view('person.edit', [
            'person' => $person
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Person  $person
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Person $person)
    {
        //
        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required',
            'gender' => 'required',
            'about' => 'required|max:255',
            'image' => 'nullable|mimes:jpg,jpeg,png|max:2048'
        ]);

        //store file on cloudinary
        if ($request->hasFile('image')){
            $result = $request->image->storeOnCloudinary('voltus');
            Log::channel('stderr')->info('Image '. $result->getFileName(). ' saved on cloudinary! on URL '. $result->getPath());

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
        Log::channel('stderr')->info('Person info updated');

        return redirect(route('person.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Person  $person
     * @return \Illuminate\Http\Response
     */
    public function destroy(Person $person)
    {
        //
    }
}
