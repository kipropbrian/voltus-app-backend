<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
    <div class="overflow-auto">
        <div class="min-w-screen flex items-center justify-center bg-gray-100 font-sans overflow-hidden">
            <div x-data="{ src: '/blank-person-612x612.jpeg' }" class="w-full lg:w-2/3 columns-2">
                <form  method="POST" action="{{ route('faceplus.search') }}" enctype="multipart/form-data">
                    @csrf
                <div class="shadow-md bg-gray-100 rounded my-2">
                    <div class="flex items-center justify-center w-full py-2">
                        <label for="dropzone-file"
                            class="flex flex-col items-center justify-center w-full h-40 border-2  border-dashed rounded-lg cursor-pointer hover:bg-bray-200 bg-gray-100 hover:bg-gray-300 border-gray-600 hover:border-gray-500">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg aria-hidden="true" class="w-10 h-10 mb-3 text-gray-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                    </path>
                                </svg>
                                <p class="mb-2 text-sm text-gray-400"><span class="font-semibold">Click to
                                        upload</span> or drag and drop</p>
                                <p class="text-xs text-gray-400">SVG, PNG, JPG or GIF (MAX. 800x400px)</p>
                            </div>
                            <input id="dropzone-file" type="file" class="hidden" name="image" id="image"
                                @change="src = URL.createObjectURL(event.target.files[0])" />
                        </label>
                    </div>
                    <div class="shadow-md bg-white relative rounded-lg my-2">
                        <img class="max-w-full h-auto rounded" :src="src" alt="image description">
                        <div style="position: absolute; outline: rgb(74, 171, 232) solid 2px;z-index: 1;transform: rotateZ(6.97501deg);width: 88.53px;height: 88.53px;left: 505.557px;top: 219.968px;"></div>
                        <div class="bg-gray-50 px-4 py-3 text-left sm:px-6">
                            <x-input-error :messages="$errors->get('image')" class="mt-2" />
                            <x-primary-button>{{ __('Search') }}</x-primary-button>
                        </div>
                    </div>
                </div>
                </form>

                <div class="overflow-hidden bg-white shadow sm:rounded-lg  inline-block my-4">
                    <div class="px-4 py-5 sm:px-6">
                      <h3 class="text-lg font-medium leading-6 text-gray-900">Politician Information</h3>
                      <p class="mt-1 max-w-2xl text-sm text-gray-500">Personal details and application.</p>
                    </div>
                    <div class="border-t border-gray-200">
                      <dl>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                          <dt class="text-sm font-medium text-gray-500">Full name</dt>
                          <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">Margot Foster</dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                          <dt class="text-sm font-medium text-gray-500">Application for</dt>
                          <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">Backend Developer</dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                          <dt class="text-sm font-medium text-gray-500">Email address</dt>
                          <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">margotfoster@example.com</dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                          <dt class="text-sm font-medium text-gray-500">Salary expectation</dt>
                          <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">$120,000</dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                          <dt class="text-sm font-medium text-gray-500">About</dt>
                          <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">Fugiat ipsum ipsum deserunt culpa aute sint do nostrud anim incididunt cillum culpa consequat. Excepteur qui ipsum aliquip consequat sint. Sit id mollit nulla mollit nostrud in ea officia proident. Irure nostrud pariatur mollit ad adipisicing reprehenderit deserunt qui eu.</dd>
                        </div>
                      </dl>
                    </div>
                  </div>

            </div>
        </div>
    </div>
</x-app-layout>
