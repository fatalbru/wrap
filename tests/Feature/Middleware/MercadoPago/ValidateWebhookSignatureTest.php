<?php

use App\Http\Middleware\MercadoPago\ValidateWebhookSignature;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use function Pest\Laravel\freezeTime;
use function Pest\Laravel\post;
use function Pest\Laravel\travelBack;

beforeEach(function () {
    Route::post('/test-webhook', fn() => response()->noContent())
        ->middleware(ValidateWebhookSignature::class);
});

test('doenst retain logs outside local', function () {
    app()->detectEnvironment(fn() => 'production');
    Log::spy();
    Log::shouldReceive('debug')->never();
    config(['mrr.webhook_signature' => null]);
    post('/test-webhook')
        ->assertForbidden()
        ->assertSeeText('Webhook Signature conflict');
});

test('only logs locally', function () {
    app()->detectEnvironment(fn() => 'local');
    Log::spy();
    Log::shouldReceive('debug')->once();
    config(['mrr.webhook_signature' => null]);
    post('/test-webhook')
        ->assertForbidden()
        ->assertSeeText('Webhook Signature conflict');
});

test('validate signature is configured', function () {
    config(['mrr.webhook_signature' => null]);
    post('/test-webhook')
        ->assertForbidden()
        ->assertSeeText('Webhook Signature conflict');
});

test('missing request-id', function () {
    post('/test-webhook', headers: [
        ValidateWebhookSignature::SIGNATURE_HEADER => 'signature',
    ])
        ->assertForbidden()
        ->assertSeeText('Webhook params missing');
});

test('missing signature', function () {
    post('/test-webhook', headers: [
        ValidateWebhookSignature::REQUEST_ID_HEADER => 'request-id',
    ])
        ->assertForbidden()
        ->assertSeeText('Webhook params missing');
});

test('missing data.id', function () {
    post('/test-webhook', [], headers: [
        ValidateWebhookSignature::REQUEST_ID_HEADER => 'request-id',
        ValidateWebhookSignature::SIGNATURE_HEADER => 'signature',
    ])
        ->assertForbidden()
        ->assertSeeText('Webhook params missing');
});

test('malformed signature', function () {
    post('/test-webhook', [
        'data' => [
            'id' => 'id',
        ]
    ], headers: [
        ValidateWebhookSignature::REQUEST_ID_HEADER => 'request-id',
        ValidateWebhookSignature::SIGNATURE_HEADER => 'ts=123',
    ])
        ->assertForbidden()
        ->assertSeeText('Malformed signature');

    post('/test-webhook', [
        'data' => [
            'id' => 'id',
        ]
    ], headers: [
        ValidateWebhookSignature::REQUEST_ID_HEADER => 'request-id',
        ValidateWebhookSignature::SIGNATURE_HEADER => 'v1=345678',
    ])
        ->assertForbidden()
        ->assertSeeText('Malformed signature');
});

test('stale timestamp forbidden', function () {
    $timestamp = now()->subSeconds(config('mrr.webhook_tolerance') + 1)->timestamp;
    post('/test-webhook', [
        'data' => [
            'id' => 'id',
        ]
    ], headers: [
        ValidateWebhookSignature::REQUEST_ID_HEADER => 'request-id',
        ValidateWebhookSignature::SIGNATURE_HEADER => "ts={$timestamp},v1=345678",
    ])
        ->assertForbidden()
        ->assertSeeText('Stale/invalid timestamp');
});

test('invalidates signature', function () {
    $timestamp = now()->timestamp;
    $signature = Str::random(128);
    $dataId = 123456;
    $requestId = 'request-id';
    $secret = Str::random(128);
    config([
        'mrr.webhook_signature' => $secret,
    ]);
    $manifest = "id:{$dataId};request-id:{$requestId};ts:{$timestamp};";
    $hash = hash_hmac('sha256', $manifest, $secret);
    post('/test-webhook', [
        'data' => [
            'id' => 654321,
        ]
    ], headers: [
        ValidateWebhookSignature::REQUEST_ID_HEADER => $requestId,
        ValidateWebhookSignature::SIGNATURE_HEADER => "ts={$timestamp},v1={$hash}",
    ])
        ->assertForbidden();
});

test('validates signature', function () {
    $timestamp = now()->timestamp;
    $signature = Str::random(128);
    $dataId = 123456;
    $requestId = 'request-id';
    $secret = Str::random(128);
    config([
        'mrr.webhook_signature' => $secret,
    ]);
    $manifest = "id:{$dataId};request-id:{$requestId};ts:{$timestamp};";
    $hash = hash_hmac('sha256', $manifest, $secret);
    post('/test-webhook', [
        'data' => [
            'id' => $dataId,
        ]
    ], headers: [
        ValidateWebhookSignature::REQUEST_ID_HEADER => $requestId,
        ValidateWebhookSignature::SIGNATURE_HEADER => "ts={$timestamp},v1={$hash}",
    ])
        ->assertNoContent();
});
