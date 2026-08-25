<?php

namespace Tests\Feature;

use Tests\TestCase;

class EmailVerificationCorsTest extends TestCase
{
    public function test_認証メール再送のcorsプリフライトを許可する(): void
    {
        $response = $this
            ->withHeaders([
                'Origin' => 'http://localhost:5173',
                'Access-Control-Request-Method' => 'POST',
                'Access-Control-Request-Headers' => 'x-xsrf-token,x-requested-with',
            ])
            ->options('/email/verification-notification');

        $response
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173')
            ->assertHeader('Access-Control-Allow-Credentials', 'true');

        $this->assertStringContainsString(
            'POST',
            (string) $response->headers->get('Access-Control-Allow-Methods')
        );
    }
}
