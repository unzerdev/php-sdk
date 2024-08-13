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

        $this->assertTrue(JwtService::validateExpireTime($jwtToken, 60 * 7 - 1));
    }
}
