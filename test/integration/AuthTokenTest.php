<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 */

namespace UnzerSDK\test\integration;

use UnzerSDK\test\BaseIntegrationTest;

class AuthTokenTest extends BaseIntegrationTest
{
    /** @test */
    public function verifyTokenCanBeCreated()
    {
        $authResponse = $this->getUnzerObject()->createAuthToken();
        $this->assertNotNull($authResponse);
        $this->assertNotNull($authResponse->getAccessToken());
    }
}
