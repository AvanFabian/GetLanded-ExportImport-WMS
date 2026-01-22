<?php

namespace App\Services;

use App\Models\Webhook;
use App\Models\WebhookLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Dispatch an event to all subscribed webhooks
     */
    public function dispatch(int $companyId, string $event, array $payload): void
    {
        $webhooks = Webhook::where('company_id', $companyId)
            ->where('is_active', true)
            ->get()
            ->filter(fn($webhook) => $webhook->supportsEvent($event));

        foreach ($webhooks as $webhook) {
            $this->send($webhook, $event, $payload);
        }
    }

    /**
     * Send webhook request
     */
    protected function send(Webhook $webhook, string $event, array $payload): void
    {
        try {
            $body = [
                'event' => $event,
                'payload' => $payload,
                'timestamp' => now()->toISOString(),
            ];

            $headers = ['Content-Type' => 'application/json'];
            
            if ($webhook->secret) {
                $signature = hash_hmac('sha256', json_encode($body), $webhook->secret);
                $headers['X-Webhook-Signature'] = $signature;
            }

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->post($webhook->url, $body);

            WebhookLog::create([
                'webhook_id' => $webhook->id,
                'event' => $event,
                'payload' => $payload,
                'response_code' => $response->status(),
                'response_body' => substr($response->body(), 0, 1000),
            ]);

        } catch (\Exception $e) {
            Log::error("Webhook failed: {$e->getMessage()}", [
                'webhook_id' => $webhook->id,
                'event' => $event,
            ]);

            WebhookLog::create([
                'webhook_id' => $webhook->id,
                'event' => $event,
                'payload' => $payload,
                'response_code' => 0,
                'response_body' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Test a webhook endpoint
     */
    public function test(Webhook $webhook): array
    {
        try {
            $body = [
                'event' => 'test',
                'payload' => ['message' => 'This is a test webhook'],
                'timestamp' => now()->toISOString(),
            ];

            $response = Http::timeout(10)->post($webhook->url, $body);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 500),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 0,
                'body' => $e->getMessage(),
            ];
        }
    }
}
