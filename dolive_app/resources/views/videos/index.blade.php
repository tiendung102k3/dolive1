@extends("layouts.app") {{-- Assuming Breeze provides a layouts.app --}}

@section("header")
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __("My Videos") }}
    </h2>
@endsection

@section("content")
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="mb-4">
                    <a href="{{ route("videos.create") }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Upload New Video
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

                @if($videos->isEmpty())
                    <p>You haven\'t uploaded any videos yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded At</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($videos as $video)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $video->title }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($video->status) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $video->created_at->format("Y-m-d H:i") }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route("videos.edit", $video) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                            {{-- <a href="{{ Storage::url($video->file_path) }}" target="_blank" class="text-green-600 hover:text-green-900 mr-3">View</a> --}}
                                            <form action="{{ route("videos.destroy", $video) }}" method="POST" class="inline-block" onsubmit="return confirm("Are you sure you want to delete this video?");">
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
                        {{ $videos->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

