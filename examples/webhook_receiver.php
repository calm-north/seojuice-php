<?php

/**
 * Webhook Receiver — Handle SEOJuice change lifecycle events.
 *
 * Verifies HMAC-SHA256 signatures, routes events by type, and
 * processes heavy work async to respond 200 quickly.
 *
 * Setup:
 *   1. Set SEOJUICE_WEBHOOK_SECRET in your environment (from the dashboard)
 *   2. Configure the webhook URL in SEOJuice: https://yoursite.com/webhooks/seojuice
 *
 * This example uses plain PHP. For Laravel/Symfony, adapt the routing
 * and request handling to your framework.
 *
 * Requirements:
 *     composer require seojuice/seojuice
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$webhookSecret = getenv('SEOJUICE_WEBHOOK_SECRET') ?: '';

if ($webhookSecret === '') {
    http_response_code(500);
    echo json_encode(['error' => 'SEOJUICE_WEBHOOK_SECRET not configured']);
    exit;
}

// --- Signature verification ---

function verifySignature(string $payload, ?string $signature, string $secret): bool
{
    if ($signature === null || $signature === '') {
        return false;
    }

    $expected = hash_hmac('sha256', $payload, $secret);

    return hash_equals($expected, $signature);
}

// --- Event handlers ---

/**
 * @param array<string, mixed> $payload
 */
function onChangeCreated(array $payload): void
{
    $change = $payload['change'];
    error_log(sprintf(
        '[webhook] New %s change #%d for %s',
        $change['change_type'],
        $change['id'],
        $change['page_url'] ?? '(unknown)',
    ));
}

/**
 * @param array<string, mixed> $payload
 */
function onChangeApproved(array $payload): void
{
    $change = $payload['change'];
    error_log(sprintf(
        '[webhook] Change #%d approved — ready for integration to pull',
        $change['id'],
    ));
}

/**
 * @param array<string, mixed> $payload
 */
function onChangeApplied(array $payload): void
{
    $change = $payload['change'];
    $domain = $payload['website']['domain'] ?? 'unknown';
    error_log(sprintf(
        '[webhook] Change #%d applied to %s',
        $change['id'],
        $change['page_url'] ?? '(unknown)',
    ));

    triggerRebuild($domain, $change);
}

/**
 * @param array<string, mixed> $payload
 */
function onChangeReverted(array $payload): void
{
    $change = $payload['change'];
    $domain = $payload['website']['domain'] ?? 'unknown';
    $reason = $payload['revert_reason'] ?? '';
    error_log(sprintf(
        '[webhook] Change #%d reverted%s',
        $change['id'],
        $reason !== '' ? ": {$reason}" : '',
    ));

    triggerRebuild($domain, $change);
}

/**
 * @param array<string, mixed> $payload
 */
function onChangeRejected(array $payload): void
{
    $change = $payload['change'];
    $reason = $payload['reason'] ?? '';
    error_log(sprintf(
        '[webhook] Change #%d rejected%s',
        $change['id'],
        $reason !== '' ? ": {$reason}" : '',
    ));
}

/**
 * @param array<string, mixed> $change
 */
function triggerRebuild(string $domain, array $change): void
{
    // Replace with your actual build trigger:
    //   - Vercel: POST https://api.vercel.com/v1/deployments
    //   - Netlify: POST https://api.netlify.com/build_hooks/YOUR_HOOK
    //   - WordPress: wp_remote_post() to a deploy endpoint
    error_log(sprintf(
        '[rebuild] Triggering rebuild for %s (change #%d)',
        $domain,
        $change['id'],
    ));
}

// --- Handle incoming request ---

header('Content-Type: application/json');

$rawBody = file_get_contents('php://input');

if ($rawBody === false || $rawBody === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Empty request body']);
    exit;
}

$signature = $_SERVER['HTTP_X_SEOJUICE_SIGNATURE'] ?? null;

if (!verifySignature($rawBody, $signature, $webhookSecret)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid signature']);
    error_log('[webhook] Invalid signature, rejecting');
    exit;
}

$payload = json_decode($rawBody, true);

if (!is_array($payload) || !isset($payload['event'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

// Respond 200 immediately
http_response_code(200);
echo json_encode(['received' => true]);

// Flush the response so the caller gets 200 quickly
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}

// Route by event type
match ($payload['event']) {
    'change.created' => onChangeCreated($payload),
    'change.approved' => onChangeApproved($payload),
    'change.applied' => onChangeApplied($payload),
    'change.reverted' => onChangeReverted($payload),
    'change.rejected' => onChangeRejected($payload),
    default => error_log("[webhook] Unhandled event: {$payload['event']}"),
};

// Usage:
//   SEOJUICE_WEBHOOK_SECRET=whsec_xxx php -S localhost:8080 examples/webhook_receiver.php
//
// Test with curl:
//   BODY='{"event":"change.created","change":{"id":1,"change_type":"meta_description","page_url":"/blog/test","status":"pending"},"website":{"domain":"example.com"},"timestamp":"2026-03-10T00:00:00Z"}'
//   SIG=$(echo -n "$BODY" | openssl dgst -sha256 -hmac "your-secret" | awk '{print $2}')
//   curl -X POST http://localhost:8080/webhooks/seojuice \
//     -H "Content-Type: application/json" \
//     -H "X-SEOJuice-Signature: $SIG" \
//     -d "$BODY"
