<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and
 * functionality of the Customer resource.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link  https://docs.unzer.com/
 *
 * @package  UnzerSDK\test\integration
 */
namespace UnzerSDK\test\integration;

use UnzerSDK\Constants\ApiResponseCodes;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\Giropay;
use UnzerSDK\test\BaseIntegrationTest;

class ExceptionTest extends BaseIntegrationTest
{
    /**
     * Verify that the UnzerApiException holds a special message for for the client.
     * Verify that there are different messages for different languages.
     *
     * @test
     */
    public function apiExceptionShouldHoldClientMessage(): void
    {
        $giropay             = $this->unzer->createPaymentType(new Giropay());
        $firstClientMessage  = '';
        $secondClientMessage = '';

        try {
            $this->unzer->authorize(1.0, 'EUR', $giropay, self::RETURN_URL);
        } catch (UnzerApiException $e) {
            $this->assertInstanceOf(UnzerApiException::class, $e);
            $this->assertEquals(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED, $e->getCode());
            $this->assertNotNull($e->getErrorId());
            $firstClientMessage = $e->getClientMessage();
            $this->assertNotEmpty($firstClientMessage);
            $this->assertNotEquals($e->getMerchantMessage(), $firstClientMessage);
        }

        try {
            $this->unzer->setLocale('de-DE');
            $this->unzer->authorize(1.0, 'EUR', $giropay, self::RETURN_URL);
        } catch (UnzerApiException $e) {
            $this->assertInstanceOf(UnzerApiException::class, $e);
            $this->assertEquals(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED, $e->getCode());
            $this->assertNotNull($e->getErrorId());
            $secondClientMessage = $e->getClientMessage();
            $this->assertNotEmpty($secondClientMessage);
            $this->assertNotEquals($e->getMerchantMessage(), $secondClientMessage);
        }

        $this->assertNotEquals($firstClientMessage, $secondClientMessage);
    }
}
