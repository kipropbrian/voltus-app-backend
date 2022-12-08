<x-app-layout>
    <div class="overflow-x-auto">
        <div class="min-w-screen bg-gray-100 flex items-center justify-center font-sans overflow-hidden">
            <div class="w-full lg:w-2/3 p-5">
                <div class="mt-6 bg-white shadow-sm rounded-lg divide-y">
                    <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                        <div class="bg-gray-200 px-4 py-5 sm:px-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">
                                Politian Information
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Edit Personal details.
                            </p>
                        </div>

                        <div class="border-t border-gray-200">
                            <form  method="POST" action="{{ route('person.update', $person) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="overflow-hidden shadow sm:rounded-md">
                                    <div class="bg-white px-4 py-5 sm:p-6">
                                        <div class="grid grid-cols-6 gap-6" x-data="{src: '{{ $person->latestImage ? $person->latestImage->image_url_secure : '' }}'}">
                                            <div class="col-span-6 sm:col-span-3">
                                                <label for="full-names"
                                                    class="block text-sm font-medium text-gray-700">Full names </label>
                                                <input type="text" name="name" id="full-names"
                                                    autocomplete="given-name"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    value="{{ old('name', $person->name)}}" />
                                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                            </div>

                                            <div class="col-span-6 sm:col-span-4">
                                                <label for="email"
                                                    class="block text-sm font-medium text-gray-700">Email address</label>
                                                <input type="text" name="email" id="email"
                                                    autocomplete="email"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    value="{{ old('email', $person) }}" />
                                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                            </div>

                                            <div class="col-span-6 sm:col-span-3">
                                                <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                                                <select id="gender" name="gender" autocomplete="gender-name" class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                                                  <option value="Male" {{ old('gender', $person->gender) == "Male" ? 'selected' : "" }}>Male</option>
                                                  <option value="Female" {{ old('gender', $person->gender) == "Female" ? 'selected' : "" }}>Female</option>
                                                </select>
                                                <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                                            </div>


                                            <div class="col-span-6 sm:col-span-4">
                                                <label class="block text-sm font-medium text-gray-700">Cover photo</label>
                                                <div class="mt-1 flex justify-center rounded-md border-2 border-dashed border-gray-300 px-6 pt-5 pb-6">
                                                    <div class="space-y-1 text-center">
                                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                        <div class="flex text-sm text-gray-600">
                                                            <label for="image" class="relative cursor-pointer rounded-md bg-white font-medium text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 hover:text-indigo-500">
                                                            <span>Upload a file</span>
                                                            <input id="image" name="image" type="file" class="sr-only" @change="src = URL.createObjectURL(event.target.files[0])">
                                                            </label>

                                                            <p class="pl-1">or drag and drop</p>
                                                        </div>
                                                        <p class="text-xs text-gray-500">PNG or JPG up to 2MB</p>
                                                    </div>
                                                </div>
                                                <x-input-error :messages="$errors->get('image')" class="mt-2" />
                                            </div>

                                            <div  class="col-span-6 sm:col-span-4">
                                                <figure class="max-w-lg">
                                                    <img class="max-w-full h-auto rounded-lg" :src="src ? src : '/blank-person-612x612.jpeg' " alt="image description">
                                                    <figcaption class="mt-2 text-sm text-center text-gray-500 dark:text-gray-400">{{ old('name', $person->name)}}</figcaption>
                                              </figure>
                                            </div>

                                            <div class="col-span-6 sm:col-span-4 lg:col-span-4">
                                                <label for="about"
                                                    class="block text-sm font-medium text-gray-700">
                                                    About</label>
                                                <input type="text" name="about" id="about"
                                                    autocomplete="about"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    value="{{ old('about', $person->about )}}" />
                                                    <x-input-error :messages="$errors->get('about')" class="mt-2" />
                                            </div>

                                        </div>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 text-left sm:px-6">
                                        <x-primary-button>{{ __('Update') }}</x-primary-button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
