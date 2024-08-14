<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 */

namespace UnzerSDK\test\integration;

use UnzerSDK\Services\JwtService;
use UnzerSDK\test\BaseIntegrationTest;

class AuthTokenTest extends BaseIntegrationTest
{
    /** @test */
    public function verifyTokenCanBeCreated()
    {
        $authResponse = $this->getUnzerObject()->createAuthToken();
        $this->assertNotNull($authResponse);
        $jwtToken = $authResponse->getAccessToken();
        $this->assertNotNull($jwtToken);

        // Validate expiry time with default buffer of 60 seconds.
        $this->assertTrue(JwtService::validateExpiryTime($jwtToken));
        // Validate expiry time with buffer set to have 1 second remaining.
        $this->assertTrue(JwtService::validateExpiryTime($jwtToken, 60 * 7 - 1));

        // Validate expiry time with buffer set to have 0 second remaining.
        $this->assertFalse(JwtService::validateExpiryTime($jwtToken, 60 * 7));
    }
}
