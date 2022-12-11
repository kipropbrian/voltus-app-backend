
<div x-data="{ open: {{ $image->detected }} ? true : false }" class="w-full max-w-xs bg-white rounded-lg border shadow-md bg-gray-50 mr-2 ">
    <form method="POST" action="{{ route('image.destroy', $image) }}">
        @csrf
        @method('delete')
        <div class="flex flex-col items-end pb-2 px-2 pt-2">
            <img href="{{ route('image.destroy', $image) }}" onclick="event.preventDefault(); this.closest('form').submit();" class="rounded-full shadow-lg" src="/icons/icons8-delete.svg" alt="Bonnie image">
        </div>
    </form>
    <div class="flex flex-col items-center pb-6 px-8 pt-8">
        <img class="mb-3 w-32 h-32 rounded-full shadow-lg" src="{{ $formatedImageUrl }}" alt="Politico image">
        <span x-text="open ? 'Connected' : 'Not Connected' " class="text-sm text-gray-500 text-gray-400">

        </span>
    </div>

    <div class="flex p-2 bg-gray-200 space-x-3 place-content-center rounded">
        <div x-show="open">
            <button type="button" disabled class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 bg-blue-600 hover:bg-blue-700 focus:ring-blue-800">
                Connected
                <span class="bg-white-100 text-white-300 text-sm font-semibold inline-flex items-center p-1.5 rounded-full mr-2 ml-2 bg-green-400 text-white-300">
                    <svg aria-hidden="true" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    <span class="sr-only">Tick</span>
                </span>
            </button>
        </div>
        <div x-show="!open">
            <button onclick="location.href=`{{ route('faceplus.connect', $image) }}`" type="button" class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 bg-blue-600 hover:bg-blue-700 focus:ring-blue-800">FacePlus Connect
                <span class="bg-white-100 text-white-300 text-sm font-semibold inline-flex items-center rounded-full mr-2 ml-2 bg-grey-400 text-white-300">
                    <img class="w-6 h-6" src="/icons/icons8-synchronize-40.png" />
                </span>
            </button>
        </div>
    </div>
</div>
