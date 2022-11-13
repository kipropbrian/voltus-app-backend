
<div class="w-full max-w-xs bg-white rounded-lg border shadow-md dark:bg-gray-50 mr-2 ">
    <form method="POST" action="{{ route('image.destroy', $image) }}">
        @csrf
        @method('delete')
        <div class="flex flex-col items-end pb-2 px-2 pt-2">
            <img :href="route('image.destroy', $image)" onclick="event.preventDefault(); this.closest('form').submit();" class="rounded-full shadow-lg" src="/icons/icons8-delete.svg" alt="Bonnie image">
        </div>
    </form>
    <div class="flex flex-col items-center pb-6 px-8 pt-8">
        <img class="mb-3 w-32 h-32 rounded-full shadow-lg" src="{{ $formatedImageUrl }}" alt="Politico image">
    </div>
    <div class="flex p-2 bg-gray-200 space-x-3 place-content-center rounded">
        <button type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-indigo-500 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">FacePlus Connect</button>
    </div>
</div>
