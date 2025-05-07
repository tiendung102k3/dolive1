@extends("layouts.app")

@section("header")
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __("Upload New Video") }}
    </h2>
@endsection

@section("content")
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <form action="{{ route("videos.store") }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Title -->
                    <div class="mb-4">
                        <label for="title" class="block font-medium text-sm text-gray-700">{{ __("Title") }}</label>
                        <input id="title" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" type="text" name="title" value="{{ old("title") }}" required autofocus />
                        @error("title")
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="block font-medium text-sm text-gray-700">{{ __("Description") }}</label>
                        <textarea id="description" name="description" rows="4" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old("description") }}</textarea>
                        @error("description")
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Video File -->
                    <div class="mb-4">
                        <label for="video_file" class="block font-medium text-sm text-gray-700">{{ __("Video File") }}</label>
                        <input id="video_file" type="file" name="video_file" class="block mt-1 w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" required>
                        <p class="mt-1 text-sm text-gray-500">MP4, MPEG, MOV, AVI, FLV, MKV. Max 100MB.</p>
                        @error("video_file")
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <a href="{{ route("videos.index") }}" class="underline text-sm text-gray-600 hover:text-gray-900 mr-4">
                            {{ __("Cancel") }}
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __("Upload Video") }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

