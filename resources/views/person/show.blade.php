<x-app-layout>
    <div class="overflow-x-auto">
        <div class="min-w-screen bg-gray-100 flex items-center justify-center bg-gray-100 font-sans overflow-hidden">
            <div class="w-full lg:w-2/3 p-5">
                <div class="mt-6 bg-white shadow-sm rounded-lg divide-y">
                    <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">
                                Politian Information
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Personal details.
                            </p>
                        </div>
                        <div class="border-t border-gray-200">
                            <dl>
                                <div class="bg-gray-100 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">
                                        Full name
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                        {{ $person->name }}
                                    </dd>
                                </div>
                                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">
                                        Email
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                        {{ $person->email }}
                                    </dd>
                                </div>
                                <div class="bg-gray-100 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">
                                        Gender
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                        {{ $person->gender == "F" ? "Female" : "Male"}}
                                    </dd>
                                </div>
                                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">
                                        Unique ID
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                        {{ $person->uuid }}
                                    </dd>
                                </div>
                                <div class="bg-gray-100 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">
                                        Image
                                    </dt>
                                    <div x-data="{ img:'{{ $person->latestImage ? $person->latestImage->image_url_secure : ''  }}'}" class="min-h-80 aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-md bg-gray-200 group-hover:opacity-75 lg:aspect-none lg:h-80">
                                        <img :src="img ? img : '/blank-person-612x612.jpeg' " alt="Politician image" class="h-full w-full object-cover object-center lg:h-full lg:w-full">
                                    </div>
                                    <br />
                                </div>
                                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">
                                        About
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                        {{ $person->about }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="w-full p-5">
                    <div class="mt-6 bg-white shadow-sm rounded-lg divide-y">
                        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                            <div class="px-4 py-5 sm:px-6 bg-gray-200">
                                <div x-data="{ open: {{ session('status') ? 'true' : 'false' }} }" class="float-right place-items-center">
                                    <div x-show="open" id="toast-danger" class="flex items-center p-4 max-w-xs text-gray-500 bg-white rounded-lg shadow" role="alert">
                                        <div class="inline-flex flex-shrink-0 justify-center items-center w-8 h-8 text-red-500 bg-red-100 rounded-lg">
                                            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                            <span class="sr-only">Error icon</span>
                                        </div>
                                        <div class="ml-3 text-red-600 text-sm font-normal">{{ session('status') }}</div>
                                        <button x-on:click="open = ! open" type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8" data-dismiss-target="#toast-danger" aria-label="Close">
                                            <span class="sr-only">Close</span>
                                            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                        </button>
                                    </div>
                                </div>
                                <h3 class="text-lg font-medium leading-6 text-gray-900">
                                    Politian Images
                                </h3>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                    Add the images to FacePlusplus image recognition
                                </p>
                            </div>
                            <div class="flex flex-row p-6 bg-white shadow-sm rounded-lg divide-y">
                                @foreach ($person->images as $image)
                                    <x-ImageDetect :image="$image"/>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
