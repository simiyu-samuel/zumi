<?php

use App\Http\Controllers\Api\Webhook\CloudflareWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/cloudflare', [CloudflareWebhookController::class, 'handle']);
