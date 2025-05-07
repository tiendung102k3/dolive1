<?php

namespace App\Http\Controllers;

use App\Models\Livestream;
use App\Models\StreamDestination;
use App\Models\Video;
use App\Models\User; // Import User model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; // For making API calls to Node.js service
use Illuminate\Support\Str;

class LivestreamController extends Controller
{
    private $nodeStreamingServiceUrl;

    public function __construct()
    {
        $this->nodeStreamingServiceUrl = env("NODE_STREAMING_SERVICE_URL", "http://localhost:4000");
    }

    public function index()
    {
        $livestreams = Auth::user()->livestreams()->with("video", "destinations")->latest()->paginate(10);
        return view("livestreams.index", compact("livestreams"));
    }

    public function create(Video $video)
    {
        if ($video->user_id !== Auth::id()) {
            abort(403, "You can only create livestreams for your own videos.");
        }
        $user = Auth::user();
        // Check max destinations limit before showing create form
        // This is a soft check; final check is in store()
        if (count($request->destinations ?? []) > $user->max_destinations_per_stream) {
             // This check is better in store, but can provide early feedback if desired
        }
        return view("livestreams.create", compact("video", "user"));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            "video_id" => ["required", "exists:videos,id"],
            "title" => ["nullable", "string", "max:255"],
            "scheduled_at" => ["nullable", "date", "after_or_equal:now"],
            "destinations" => ["required", "array", "min:1"],
            "destinations.*.platform_name" => ["required", "string", "max:255"],
            "destinations.*.rtmp_url" => ["required", "url"],
            "destinations.*.stream_key" => ["required", "string", "max:255"],
        ]);

        // Check max destinations per stream limit
        if (count($request->destinations) > $user->max_destinations_per_stream) {
            return back()->with("error", "You have exceeded the maximum number of destinations allowed per stream ({$user->max_destinations_per_stream}).")->withInput();
        }

        $video = Video::findOrFail($request->video_id);
        if ($video->user_id !== $user->id) {
            return back()->with("error", "You can only use your own videos.")->withInput();
        }

        $livestream = new Livestream();
        $livestream->user_id = $user->id;
        $livestream->video_id = $video->id;
        $livestream->title = $request->title ?? $video->title;
        $livestream->status = "pending";
        $livestream->scheduled_at = $request->scheduled_at;
        $livestream->stream_identifier = "dolive-" . Str::uuid();
        $livestream->save();

        foreach ($request->destinations as $destData) {
            $destination = new StreamDestination();
            $destination->livestream_id = $livestream->id;
            $destination->user_id = $user->id;
            $destination->platform_name = $destData["platform_name"];
            $destination->rtmp_url = $destData["rtmp_url"];
            $destination->stream_key = $destData["stream_key"];
            $destination->status = "pending";
            $destination->save();
        }

        return redirect()->route("livestreams.index")->with("success", "Livestream job created successfully.");
    }

    public function show(Livestream $livestream)
    {
        if ($livestream->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403);
        }
        $livestream->load("video", "destinations");
        return view("livestreams.show", compact("livestream"));
    }

    public function startStream(Livestream $livestream)
    {
        $user = Auth::user();
        if ($livestream->user_id !== $user->id) {
            abort(403);
        }

        // Check max concurrent streams
        $activeUserStreams = Livestream::where("user_id", $user->id)
                                ->whereIn("status", ["starting", "streaming"])
                                ->count();
        if ($activeUserStreams >= $user->max_concurrent_streams) {
            return back()->with("error", "You have reached your maximum limit of concurrent streams ({$user->max_concurrent_streams}).");
        }

        // Check monthly streaming minutes (basic check, actual deduction is more complex)
        // This assumes video duration is known. For now, we just check if they *can* stream.
        // A more robust solution would involve getting video duration first.
        if ($user->current_monthly_streaming_minutes >= $user->max_monthly_streaming_minutes) {
            return back()->with("error", "You have exceeded your monthly streaming minutes limit.");
        }

        if (!in_array($livestream->status, ["pending", "stopped", "failed", "scheduled"])) {
            return back()->with("error", "Stream cannot be started in its current state: " . $livestream->status);
        }

        $videoPath = storage_path("app/public/" . $livestream->video->file_path);
        if (!file_exists($videoPath)) {
            $livestream->status = "failed";
            $livestream->error_message = "Video file not found.";
            $livestream->save();
            return back()->with("error", "Video file not found for this stream.");
        }

        $destinationsPayload = $livestream->destinations->map(function ($dest) {
            return [
                "platform" => $dest->platform_name,
                "rtmpUrl" => $dest->rtmp_url,
                "streamKey" => $dest->stream_key,
            ];
        })->toArray();

        try {
            $response = Http::post($this->nodeStreamingServiceUrl . "/stream/start", [
                "streamId" => $livestream->stream_identifier,
                "videoPath" => $videoPath,
                "destinations" => $destinationsPayload,
            ]);

            if ($response->successful() && $response->json("success")) {
                $livestream->status = "starting";
                $livestream->started_at = now();
                $livestream->error_message = null;
                $livestream->save();
                return redirect()->route("livestreams.show", $livestream)->with("success", "Stream start command sent successfully!");
            } else {
                $errorMessage = $response->json("message") ?? "Failed to start stream via Node.js service.";
                $livestream->status = "failed";
                $livestream->error_message = $errorMessage;
                $livestream->save();
                return back()->with("error", "Failed to start stream: " . $errorMessage);
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $livestream->status = "failed";
            $livestream->error_message = "Could not connect to streaming service: " . $e->getMessage();
            $livestream->save();
            return back()->with("error", "Could not connect to the streaming service. Please ensure it is running.");
        }
    }

    public function stopStream(Livestream $livestream)
    {
        $user = Auth::user();
        if ($livestream->user_id !== $user->id) {
            abort(403);
        }

        if (!in_array($livestream->status, ["starting", "streaming"])) {
            return back()->with("error", "Stream is not currently active or starting.");
        }

        try {
            $response = Http::post($this->nodeStreamingServiceUrl . "/stream/stop", [
                "streamId" => $livestream->stream_identifier,
            ]);

            if ($response->successful() && $response->json("success")) {
                $livestream->status = "stopped";
                $livestream->ended_at = now();
                $livestream->save();
                
                // Update current_monthly_streaming_minutes
                if ($livestream->started_at) {
                    $durationInMinutes = $livestream->started_at->diffInMinutes($livestream->ended_at);
                    $user->current_monthly_streaming_minutes += $durationInMinutes;
                    // Ensure it doesn't exceed max, though it should have been checked before starting
                    if ($user->current_monthly_streaming_minutes > $user->max_monthly_streaming_minutes) {
                         $user->current_monthly_streaming_minutes = $user->max_monthly_streaming_minutes;
                    }
                    $user->save();
                }

                $livestream->destinations()->update(["status" => "stopped"]);
                return redirect()->route("livestreams.show", $livestream)->with("success", "Stream stop command sent successfully!");
            } else {
                $errorMessage = $response->json("message") ?? "Failed to stop stream via Node.js service.";
                return back()->with("error", "Failed to stop stream: " . $errorMessage);
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return back()->with("error", "Could not connect to the streaming service to stop the stream.");
        }
    }

    public function destroy(Livestream $livestream)
    {
        if ($livestream->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403);
        }
        // Consider stopping the stream via Node.js if it's active before deleting
        if (in_array($livestream->status, ["starting", "streaming"])) {
             // Call stopStream or directly call Node.js API to ensure FFmpeg process is killed.
             // For now, we assume manual stop or it will be handled by Node.js service cleanup.
        }

        $livestream->destinations()->delete();
        $livestream->delete();

        return redirect()->route("livestreams.index")->with("success", "Livestream job deleted successfully.");
    }
}

