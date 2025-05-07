@extends("layouts.app")

@section("header")
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __("Livestream Details: ") }} {{ $livestream->title ?? "Untitled Stream" }}
    </h2>
@endsection

@section("content")
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                @if(session("success"))
                    <div class="mb-4 p-4 bg-green-100 text-green-700 border border-green-300 rounded">
                        {{ session("success") }}
                    </div>
                @endif
                @if(session("error"))
                    <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-300 rounded">
                        {{ session("error") }}
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Stream Information</h3>
                        <p class="mt-1 text-sm text-gray-600"><strong>Title:</strong> {{ $livestream->title ?? "N/A" }}</p>
                        <p class="mt-1 text-sm text-gray-600"><strong>Video:</strong> {{ $livestream->video->title }}</p>
                        <p class="mt-1 text-sm text-gray-600"><strong>Status:</strong> <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $livestream->status === "streaming" ? "bg-green-100 text-green-800" : ($livestream->status === "failed" || $livestream->status === "stopped" ? "bg-red-100 text-red-800" : "bg-yellow-100 text-yellow-800") }}">{{ ucfirst($livestream->status) }}</span></p>
                        <p class="mt-1 text-sm text-gray-600"><strong>Scheduled At:</strong> {{ $livestream->scheduled_at ? $livestream->scheduled_at->format("Y-m-d H:i:s") : "Not Scheduled" }}</p>
                        <p class="mt-1 text-sm text-gray-600"><strong>Started At:</strong> {{ $livestream->started_at ? $livestream->started_at->format("Y-m-d H:i:s") : "Not Started" }}</p>
                        <p class="mt-1 text-sm text-gray-600"><strong>Ended At:</strong> {{ $livestream->ended_at ? $livestream->ended_at->format("Y-m-d H:i:s") : "Not Ended" }}</p>
                        @if($livestream->error_message)
                            <p class="mt-1 text-sm text-red-600"><strong>Error:</strong> {{ $livestream->error_message }}</p>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Actions</h3>
                        <div class="mt-2 space-x-2">
                            @if(in_array($livestream->status, ["pending", "scheduled", "stopped", "failed"]) && $livestream->video)
                                <form action="{{ route("livestreams.start", $livestream) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:border-green-700 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">Start Stream</button>
                                </form>
                            @elseif(in_array($livestream->status, ["starting", "streaming"])) 
                                <form action="{{ route("livestreams.stop", $livestream) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-400 active:bg-yellow-600 focus:outline-none focus:border-yellow-600 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">Stop Stream</button>
                                </form>
                            @endif
                            {{-- Add Edit button if functionality is added --}}
                            {{-- <a href="{{ route("livestreams.edit", $livestream) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">Edit</a> --}}
                            <form action="{{ route("livestreams.destroy", $livestream) }}" method="POST" class="inline-block" onsubmit="return confirm("Are you sure you want to delete this livestream job?");">
                                @csrf
                                @method("DELETE")
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">Delete Job</button>
                            </form>
                        </div>
                    </div>
                </div>

                <h3 class="text-lg font-medium text-gray-900 mb-2">Stream Destinations</h3>
                @if($livestream->destinations->isEmpty())
                    <p>No destinations configured for this livestream.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Platform</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RTMP URL</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($livestream->destinations as $destination)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $destination->platform_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $destination->rtmp_url }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($destination->status) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                <div class="mt-6">
                     <a href="{{ route("livestreams.index") }}" class="underline text-sm text-gray-600 hover:text-gray-900">
                        &larr; Back to Livestreams
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

