@extends("layouts.app")

@section("header")
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __("Edit Video: ") }} {{ $video->title }}
    </h2>
@endsection

@section("content")
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <form action="{{ route("videos.update", $video) }}" method="POST">
                    @csrf
                    @method("PUT")

                    <!-- Title -->
                    <div class="mb-4">
                        <label for="title" class="block font-medium text-sm text-gray-700">{{ __("Title") }}</label>
                        <input id="title" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" type="text" name="title" value="{{ old("title", $video->title) }}" required autofocus />
                        @error("title")
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="block font-medium text-sm text-gray-700">{{ __("Description") }}</label>
                        <textarea id="description" name="description" rows="4" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old("description", $video->description) }}</textarea>
                        @error("description")
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    {{-- Display current video file info, but no re-upload here for simplicity --}}
                    <div class="mb-4">
                        <p class="block font-medium text-sm text-gray-700">Current Video File:</p>
                        <p class="mt-1 text-sm text-gray-600">{{ $video->original_filename }} ({{ round($video->size / 1024 / 1024, 2) }} MB)</p>
                        {{-- Optionally add a link to view/download if storage is public and accessible --}}
                        {{-- <a href="{{ Storage::url($video->file_path) }}" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-900">View Current Video</a> --}}
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <a href="{{ route("videos.index") }}" class="underline text-sm text-gray-600 hover:text-gray-900 mr-4">
                            {{ __("Cancel") }}
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __("Update Video Details") }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

