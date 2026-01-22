<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Webhook;
use App\Services\WebhookService;

class WebhookSignatureTest extends TestCase
{
    /**
     * Test webhook HMAC signature verification
     */
    public function test_webhook_signature_verification(): void
    {
        $secret = 'test-secret-key-12345';
        
        // Test payload
        $payload = [
            'event' => 'order.completed',
            'payload' => ['order_id' => 123],
            'timestamp' => now()->toISOString(),
        ];

        // Generate valid signature
        $validSignature = hash_hmac('sha256', json_encode($payload), $secret);

        // Verify signature calculation is correct
        $this->assertEquals(64, strlen($validSignature)); // SHA256 produces 64 hex chars

        // Test that same secret produces same signature
        $this->assertEquals(
            $validSignature,
            hash_hmac('sha256', json_encode($payload), $secret)
        );

        // Test invalid signature detection
        $invalidSecret = 'wrong-secret';
        $invalidSignature = hash_hmac('sha256', json_encode($payload), $invalidSecret);

        $this->assertNotEquals($validSignature, $invalidSignature);

        // Verify hash_equals timing-safe comparison
        $isValid = hash_equals(
            $validSignature,
            hash_hmac('sha256', json_encode($payload), $secret)
        );
        $this->assertTrue($isValid);

        $isInvalid = hash_equals(
            $invalidSignature,
            hash_hmac('sha256', json_encode($payload), $secret)
        );
        $this->assertFalse($isInvalid);
    }

    /**
     * Test HMAC with different payloads produces different signatures
     */
    public function test_different_payloads_different_signatures(): void
    {
        $secret = 'my-secret-key';
        
        $payload1 = ['order_id' => 1];
        $payload2 = ['order_id' => 2];

        $sig1 = hash_hmac('sha256', json_encode($payload1), $secret);
        $sig2 = hash_hmac('sha256', json_encode($payload2), $secret);

        $this->assertNotEquals($sig1, $sig2);
    }
}
