<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class VideoController extends Controller
{
    /**
     * Display a listing of the user's videos.
     */
    public function index()
    {
        $videos = Auth::user()->videos()->latest()->paginate(10);
        return view("videos.index", compact("videos"));
    }

    /**
     * Show the form for creating a new video.
     */
    public function create()
    {
        return view("videos.create");
    }

    /**
     * Store a newly created video in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "title" => ["required", "string", "max:255"],
            "description" => ["nullable", "string"],
            "video_file" => ["required", "file", "mimetypes:video/mp4,video/mpeg,video/quicktime,video/x-msvideo,video/x-flv,video/x-matroska", "max:102400"], // Max 100MB for now
        ]);

        if ($request->hasFile("video_file")) {
            $file = $request->file("video_file");
            $originalFilename = $file->getClientOriginalName();
            $filePath = $file->store("user_videos/" . Auth::id(), "public"); // Store in storage/app/public/user_videos/{user_id}

            $video = new Video();
            $video->user_id = Auth::id();
            $video->title = $request->input("title");
            $video->description = $request->input("description");
            $video->file_path = $filePath;
            $video->original_filename = $originalFilename;
            $video->mime_type = $file->getMimeType();
            $video->size = $file->getSize();
            // Duration would ideally be extracted using FFmpeg/FFprobe, which is complex for now.
            // We can add a placeholder or implement it later with Node.js interaction.
            $video->status = "ready"; // Assuming direct readiness, can be 'processing' if we add transcoding
            $video->save();

            return redirect()->route("videos.index")->with("success", "Video uploaded successfully!");
        }

        return back()->with("error", "Video upload failed. Please try again.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Video $video)
    {
        // Ensure the authenticated user owns the video or is an admin
        if (Auth::id() !== $video->user_id && !Auth::user()->is_admin) {
            abort(403);
        }
        return view("videos.show", compact("video")); // TODO: Create videos.show view if needed for playback/details
    }

    /**
     * Show the form for editing the specified video.
     */
    public function edit(Video $video)
    {
        if (Auth::id() !== $video->user_id) {
            abort(403, "Unauthorized action.");
        }
        return view("videos.edit", compact("video"));
    }

    /**
     * Update the specified video in storage.
     */
    public function update(Request $request, Video $video)
    {
        if (Auth::id() !== $video->user_id) {
            abort(403, "Unauthorized action.");
        }

        $request->validate([
            "title" => ["required", "string", "max:255"],
            "description" => ["nullable", "string"],
        ]);

        $video->title = $request->input("title");
        $video->description = $request->input("description");
        $video->save();

        return redirect()->route("videos.index")->with("success", "Video details updated successfully.");
    }

    /**
     * Remove the specified video from storage.
     */
    public function destroy(Video $video)
    {
        if (Auth::id() !== $video->user_id && !Auth::user()->is_admin) { // Allow admin to delete any video
            abort(403, "Unauthorized action.");
        }

        // Delete the physical file
        Storage::disk("public")->delete($video->file_path);
        $video->delete();

        return redirect()->route("videos.index")->with("success", "Video deleted successfully.");
    }
}

