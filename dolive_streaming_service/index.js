require("dotenv").config();
const express = require("express");
const ffmpeg = require("fluent-ffmpeg");
// const amqp = require("amqplib"); // For RabbitMQ, if used later

const app = express();
app.use(express.json());

const PORT = process.env.STREAMING_SERVICE_PORT || 4000;
const FFMPEG_PATH = process.env.FFMPEG_PATH || "ffmpeg"; // Ensure ffmpeg is in PATH or specify path

// In-memory store for active streams (for simplicity, replace with DB/Redis in production)
const activeStreams = {};

/**
 * @param {string} streamId - A unique ID for this streaming job
 * @param {string} videoPath - Absolute path to the video file on the server
 * @param {Array<object>} destinations - Array of { platform: string, rtmpUrl: string, streamKey: string }
 */
async function startStreaming(streamId, videoPath, destinations) {
    if (activeStreams[streamId]) {
        console.log(`Stream ${streamId} is already active.`);
        return { success: false, message: `Stream ${streamId} is already active.` };
    }

    console.log(`[${streamId}] Starting stream for video: ${videoPath}`);
    console.log(`[${streamId}] Destinations:`, destinations);

    const outputOptions = [];
    destinations.forEach(dest => {
        if (dest.rtmpUrl && dest.streamKey) {
            const fullRtmpUrl = dest.rtmpUrl.endsWith("/") ? dest.rtmpUrl + dest.streamKey : dest.rtmpUrl + "/" + dest.streamKey;
            // fluent-ffmpeg might require separate output for each RTMP stream
            // or some complex filter graph for multiple outputs from one input.
            // For simplicity, we will create one ffmpeg process per destination initially.
            // This is not efficient but easier to manage for a start.
            // A more advanced setup would use -tee option or complex filters if supported by fluent-ffmpeg easily.
        } else {
            console.warn(`[${streamId}] Invalid RTMP URL or Stream Key for platform ${dest.platform}`);
        }
    });

    // For now, let's handle a single destination to test the core logic
    if (destinations.length === 0 || !destinations[0].rtmpUrl || !destinations[0].streamKey) {
        console.error(`[${streamId}] No valid destinations provided.`);
        return { success: false, message: "No valid destinations provided." };
    }

    const firstDestination = destinations[0];
    const rtmpTarget = firstDestination.rtmpUrl.endsWith("/") 
        ? firstDestination.rtmpUrl + firstDestination.streamKey 
        : firstDestination.rtmpUrl + "/" + firstDestination.streamKey;

    // Update PHP app about stream starting (via API call or message queue)
    // Example: await callPhpApi("updateStreamStatus", { streamId, status: "streaming" });

    const command = ffmpeg(videoPath)
        .setFfmpegPath(FFMPEG_PATH)
        .inputOptions([
            "-re", // Read input at native frame rate
            "-stream_loop -1" // Loop the video indefinitely (like GoStream for pre-recorded)
        ])
        .videoCodec("libx264")
        .audioCodec("aac")
        .audioBitrate("128k")
        .videoBitrate("2500k") // Adjust as needed
        .size("1280x720") // Adjust as needed
        .outputOptions([
            "-preset veryfast",
            "-tune zerolatency",
            "-g 60", // Group of pictures (keyframe interval)
            "-f flv"
        ])
        .output(rtmpTarget)
        .on("start", (commandLine) => {
            console.log(`[${streamId}] FFmpeg started with command: ${commandLine}`);
            activeStreams[streamId] = { command, status: "streaming", destinations };
        })
        .on("error", (err, stdout, stderr) => {
            console.error(`[${streamId}] FFmpeg error: `, err.message);
            console.error(`[${streamId}] FFmpeg stderr: `, stderr);
            delete activeStreams[streamId];
            // Update PHP app about stream error
        })
        .on("end", () => {
            console.log(`[${streamId}] FFmpeg stream ended.`);
            delete activeStreams[streamId];
            // Update PHP app about stream ended (if not looped)
            // If looped, this "end" might not be hit unless an error occurs or it's manually stopped.
        });

    try {
        command.run();
        return { success: true, message: `Stream ${streamId} started successfully to ${firstDestination.platform}.` };
    } catch (error) {
        console.error(`[${streamId}] Failed to run FFmpeg command:`, error);
        return { success: false, message: `Failed to start stream ${streamId}.` };
    }
}

function stopStreaming(streamId) {
    if (activeStreams[streamId] && activeStreams[streamId].command) {
        console.log(`[${streamId}] Attempting to stop stream...`);
        activeStreams[streamId].command.kill("SIGKILL"); // Force kill FFmpeg process
        delete activeStreams[streamId];
        console.log(`[${streamId}] Stream stopped.`);
        // Update PHP app about stream stopped
        return { success: true, message: `Stream ${streamId} stopped.` };
    } else {
        console.log(`[${streamId}] Stream not found or already stopped.`);
        return { success: false, message: `Stream ${streamId} not found or already stopped.` };
    }
}

// API Endpoint to start a stream
app.post("/stream/start", async (req, res) => {
    const { streamId, videoPath, destinations } = req.body;
    if (!streamId || !videoPath || !destinations || !Array.isArray(destinations) || destinations.length === 0) {
        return res.status(400).json({ success: false, message: "Missing required parameters: streamId, videoPath, destinations array." });
    }
    // In a real app, videoPath should be validated to prevent access to arbitrary files.
    // It should be an absolute path accessible by the Node.js service.
    const result = await startStreaming(streamId, videoPath, destinations);
    if (result.success) {
        res.status(200).json(result);
    } else {
        res.status(500).json(result);
    }
});

// API Endpoint to stop a stream
app.post("/stream/stop", (req, res) => {
    const { streamId } = req.body;
    if (!streamId) {
        return res.status(400).json({ success: false, message: "Missing streamId." });
    }
    const result = stopStreaming(streamId);
    if (result.success) {
        res.status(200).json(result);
    } else {
        res.status(404).json(result); // Or 500 if it was an internal error stopping
    }
});

// API Endpoint to get status of a stream
app.get("/stream/status/:streamId", (req, res) => {
    const { streamId } = req.params;
    if (activeStreams[streamId]) {
        res.status(200).json({ success: true, streamId, status: activeStreams[streamId].status, destinations: activeStreams[streamId].destinations });
    } else {
        res.status(404).json({ success: false, message: `Stream ${streamId} not found.` });
    }
});

app.listen(PORT, () => {
    console.log(`Dolive Streaming Service listening on port ${PORT}`);
});

// TODO: Implement AMQP listener for job queue if that approach is chosen
// async function startAmqpListener() {
//     try {
//         const connection = await amqp.connect(process.env.AMQP_URL || "amqp://localhost");
//         const channel = await connection.createChannel();
//         const queue = process.env.AMQP_QUEUE_NAME || "dolive_stream_jobs";

//         await channel.assertQueue(queue, { durable: true });
//         console.log(`[*] Waiting for messages in ${queue}. To exit press CTRL+C`);

//         channel.consume(queue, async (msg) => {
//             if (msg !== null) {
//                 try {
//                     const job = JSON.parse(msg.content.toString());
//                     console.log("[x] Received job:", job);
//                     await startStreaming(job.streamId, job.videoPath, job.destinations);
//                     channel.ack(msg);
//                 } catch (error) {
//                     console.error("Error processing AMQP message:", error);
//                     channel.nack(msg, false, false); // Don't requeue, or handle retries
//                 }
//             }
//         });
//     } catch (error) {
//         console.error("Failed to start AMQP listener:", error);
//     }
// }

// if (process.env.USE_AMQP === "true") {
//     startAmqpListener();
// }

