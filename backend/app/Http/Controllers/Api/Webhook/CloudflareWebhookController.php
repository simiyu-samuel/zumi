<?php
namespace App\Http\Controllers\Api\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Wave;
use App\Services\CloudflareStreamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CloudflareWebhookController extends Controller
{
    public function __construct(
        protected CloudflareStreamService $cloudflareStream
    ) {}

    public function handle(Request $request)
    {
        $signature = $request->header('Webhook-Signature');
        
        if (!$signature || !$this->cloudflareStream->verifyWebhookSignature($signature, $request->getContent())) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $payload = $request->all();
        $videoId = $payload['uid'] ?? null;
        $status = $payload['status']['state'] ?? null;

        if (!$videoId) {
            return response()->json(['message' => 'Missing video ID'], 400);
        }

        $wave = Wave::where('stream_id', $videoId)->first();

        if (!$wave) {
            Log::warning('Cloudflare Webhook: Wave not found for stream_id', ['stream_id' => $videoId]);
            return response()->json(['message' => 'Wave not found'], 404);
        }

        switch ($status) {
            case 'ready':
                $wave->update([
                    'status' => Wave::STATUS_READY,
                    'thumbnail_url' => $payload['thumbnail'] ?? $wave->thumbnail_url,
                    'video_metadata' => array_merge($wave->video_metadata ?? [], [
                        'duration' => $payload['duration'] ?? 0,
                        'size' => $payload['size'] ?? 0,
                    ])
                ]);
                break;
            
            case 'error':
                $wave->update(['status' => Wave::STATUS_FAILED]);
                break;

            case 'processing':
                $wave->update(['status' => Wave::STATUS_PROCESSING]);
                break;
        }

        return response()->json(['message' => 'Webhook processed']);
    }
}
