@extends("layouts.app")

@section("header")
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __("Create New Livestream for: ") }} {{ $video->title }}
    </h2>
@endsection

@section("content")
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <form action="{{ route("livestreams.store") }}" method="POST" id="livestream-form">
                    @csrf
                    <input type="hidden" name="video_id" value="{{ $video->id }}">

                    <!-- Livestream Title -->
                    <div class="mb-4">
                        <label for="title" class="block font-medium text-sm text-gray-700">{{ __("Livestream Title (optional, defaults to video title)") }}</label>
                        <input id="title" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" type="text" name="title" value="{{ old("title", $video->title) }}" />
                        @error("title")
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Scheduled At -->
                    <div class="mb-4">
                        <label for="scheduled_at" class="block font-medium text-sm text-gray-700">{{ __("Schedule At (optional, leave blank to start manually)") }}</label>
                        <input id="scheduled_at" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" type="datetime-local" name="scheduled_at" value="{{ old("scheduled_at") }}" />
                        @error("scheduled_at")
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Destinations -->
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Streaming Destinations</h3>
                    <div id="destinations-container" class="space-y-4 mb-4">
                        <!-- Initial Destination Entry -->
                        <div class="destination-entry p-4 border border-gray-200 rounded-md">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="destinations[0][platform_name]" class="block font-medium text-sm text-gray-700">Platform Name</label>
                                    <input type="text" name="destinations[0][platform_name]" class="mt-1 block w-full rounded-md shadow-sm border-gray-300" required placeholder="e.g., YouTube, Facebook">
                                </div>
                                <div>
                                    <label for="destinations[0][rtmp_url]" class="block font-medium text-sm text-gray-700">RTMP URL</label>
                                    <input type="url" name="destinations[0][rtmp_url]" class="mt-1 block w-full rounded-md shadow-sm border-gray-300" required placeholder="rtmp://a.rtmp.youtube.com/live2">
                                </div>
                                <div>
                                    <label for="destinations[0][stream_key]" class="block font-medium text-sm text-gray-700">Stream Key</label>
                                    <input type="text" name="destinations[0][stream_key]" class="mt-1 block w-full rounded-md shadow-sm border-gray-300" required placeholder="your-stream-key">
                                </div>
                            </div>
                        </div>
                    </div>
                    @error("destinations")
                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                    @enderror
                     @error("destinations.*.platform_name") <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                     @error("destinations.*.rtmp_url") <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                     @error("destinations.*.stream_key") <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

                    <button type="button" id="add-destination-btn" class="mb-4 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Add Another Destination
                    </button>

                    <div class="flex items-center justify-end mt-4">
                        <a href="{{ route("livestreams.index") }}" class="underline text-sm text-gray-600 hover:text-gray-900 mr-4">
                            {{ __("Cancel") }}
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __("Create Livestream Job") }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let destinationIndex = 0; // Start with 0 for the first pre-rendered one
        const destinationsContainer = document.getElementById("destinations-container");
        const addDestinationButton = document.getElementById("add-destination-btn");

        addDestinationButton.addEventListener("click", function() {
            destinationIndex++;
            const newEntry = document.createElement("div");
            newEntry.classList.add("destination-entry", "p-4", "border", "border-gray-200", "rounded-md", "mt-4");
            newEntry.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="destinations[${destinationIndex}][platform_name]" class="block font-medium text-sm text-gray-700">Platform Name</label>
                        <input type="text" name="destinations[${destinationIndex}][platform_name]" class="mt-1 block w-full rounded-md shadow-sm border-gray-300" required placeholder="e.g., YouTube, Facebook">
                    </div>
                    <div>
                        <label for="destinations[${destinationIndex}][rtmp_url]" class="block font-medium text-sm text-gray-700">RTMP URL</label>
                        <input type="url" name="destinations[${destinationIndex}][rtmp_url]" class="mt-1 block w-full rounded-md shadow-sm border-gray-300" required placeholder="rtmp://a.rtmp.youtube.com/live2">
                    </div>
                    <div>
                        <label for="destinations[${destinationIndex}][stream_key]" class="block font-medium text-sm text-gray-700">Stream Key</label>
                        <input type="text" name="destinations[${destinationIndex}][stream_key]" class="mt-1 block w-full rounded-md shadow-sm border-gray-300" required placeholder="your-stream-key">
                    </div>
                </div>
                <button type="button" class="remove-destination-btn mt-2 text-sm text-red-600 hover:text-red-800">Remove Destination</button>
            `;
            destinationsContainer.appendChild(newEntry);
        });

        destinationsContainer.addEventListener("click", function(event) {
            if (event.target.classList.contains("remove-destination-btn")) {
                // Do not remove if it is the only one left
                if (destinationsContainer.querySelectorAll(".destination-entry").length > 1) {
                    event.target.closest(".destination-entry").remove();
                } else {
                    alert("At least one destination is required.");
                }
            }
        });
    });
</script>
@endsection

