<?php

use App\Http\Controllers\Api\Webhook\CloudflareWebhookController;
use App\Http\Controllers\Api\Webhook\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/cloudflare', [CloudflareWebhookController::class, 'handle']);
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle']);
