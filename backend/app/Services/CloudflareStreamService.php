<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareStreamService
{
    protected ?string $accountId;
    protected ?string $apiToken;
    protected string $baseUrl;

    public function __construct()
    {
        $this->accountId = config('services.cloudflare.account_id');
        $this->apiToken = config('services.cloudflare.api_token');
        $this->baseUrl = "https://api.cloudflare.com/client/v4/accounts/" . ($this->accountId ?? 'default') . "/stream";
    }

    /**
     * Create a direct upload URL (TUS or simple).
     * We'll use the authenticated direct upload URL.
     */
    public function createUploadUrl(int $sizeBytes, array $metadata = []): ?array
    {
        $response = Http::withToken($this->apiToken)
            ->post($this->baseUrl . '/direct_upload', [
                'maxDurationSeconds' => 30, // 30 seconds max for Waves
                'uploadLength'       => $sizeBytes,
                'meta'               => array_merge(['source' => 'zumi-app'], $metadata),
            ]);

        if ($response->failed()) {
            Log::error('Cloudflare Stream Direct Upload Failed', [
                'response' => $response->json(),
                'status'   => $response->status()
            ]);
            return null;
        }

        return $response->json('result');
    }

    /**
     * Get video details from Cloudflare.
     */
    public function getVideoDetails(string $videoId): ?array
    {
        $response = Http::withToken($this->apiToken)
            ->get($this->baseUrl . '/' . $videoId);

        if ($response->failed()) {
            return null;
        }

        return $response->json('result');
    }

    /**
     * Verify the webhook signature from Cloudflare.
     */
    public function verifyWebhookSignature(string $signature, string $payload): bool
    {
        $secret = config('services.cloudflare.webhook_secret');
        if (!$secret) {
            return false;
        }

        // Cloudflare signature is in format: time=TIMESTAMP,sig1=SIGNATURE
        // Official docs: https://developers.cloudflare.com/stream/webhooks/
        
        $parts = explode(',', $signature);
        $timePart = str_replace('time=', '', $parts[0] ?? '');
        $sigPart = str_replace('sig1=', '', $parts[1] ?? '');

        if (!$timePart || !$sigPart) {
            return false;
        }

        $expectedSig = hash_hmac('sha256', $timePart . '.' . $payload, $secret);

        return hash_equals($expectedSig, $sigPart);
    }
}
