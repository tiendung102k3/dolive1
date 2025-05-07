@extends("layouts.app")

@section("header")
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __("My Livestreams") }}
    </h2>
@endsection

@section("content")
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="mb-4">
                    {{-- Link to select a video to stream --}}
                    <a href="{{ route("videos.index") }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Create New Livestream (Select Video First)
                    </a>
                </div>

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

                @if($livestreams->isEmpty())
                    <p>You haven"t created any livestreams yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Video</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled At</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($livestreams as $livestream)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <a href="{{ route("livestreams.show", $livestream) }}" class="text-indigo-600 hover:text-indigo-900">
                                                {{ $livestream->title ?? "Untitled Stream" }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $livestream->video->title }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($livestream->status) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $livestream->scheduled_at ? $livestream->scheduled_at->format("Y-m-d H:i") : "Not scheduled" }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route("livestreams.show", $livestream) }}" class="text-blue-600 hover:text-blue-900 mr-3">Details</a>
                                            @if(in_array($livestream->status, ["pending", "scheduled", "stopped", "failed"]) && $livestream->video)
                                                <form action="{{ route("livestreams.start", $livestream) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-900 mr-3">Start</button>
                                                </form>
                                            @elseif(in_array($livestream->status, ["starting", "streaming"])) 
                                                <form action="{{ route("livestreams.stop", $livestream) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900 mr-3">Stop</button>
                                                </form>
                                            @endif
                                            <form action="{{ route("livestreams.destroy", $livestream) }}" method="POST" class="inline-block" onsubmit="return confirm("Are you sure you want to delete this livestream job?");">
                                                @csrf
                                                @method("DELETE")
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $livestreams->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

