<?php
/*
 *  Test class for applepay adapter
 *
 *  Copyright (C) 2021 - today Unzer E-Com GmbH
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 *  @link  https://docs.unzer.com/
 *
 *  @author  David Owusu <development@unzer.com>
 *
 *  @package  UnzerSDK
 *
 */

namespace UnzerSDK\test\integration;

use UnzerSDK\Adapter\ApplepayAdapter;
use UnzerSDK\Exceptions\ApplepayMerchantValidationException;
use UnzerSDK\Resources\ExternalResources\ApplepaySession;
use UnzerSDK\Services\EnvironmentService;
use UnzerSDK\test\BaseIntegrationTest;

class ApplepayAdapterTest extends BaseIntegrationTest
{
    private $merchantValidationUrl;
    private $merchantValidationCertificatePath;
    private $appleCaCertificatePath;

    public function domainShouldBeValidatedCorrectlyDP()
    {
        return [
            'invalid: example.domain.com' => ['https://example.domain.com', false],
            'valid: https://apple-pay-gateway.apple.com/some/path' => ['https://apple-pay-gateway.apple.com/some/path', true],
            'valid: https://cn-apple-pay-gateway.apple.com' => ['https://cn-apple-pay-gateway.apple.com', true],
            'invalid: apple-pay-gateway-nc-pod1.apple.com' => ['apple-pay-gateway-nc-pod1.apple.com', false],
            'invalid: (empty)' => ['', false],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->merchantValidationUrl = 'https://apple-pay-gateway-cert.apple.com/paymentservices/startSession';
        $this->merchantValidationCertificatePath = EnvironmentService::getAppleMerchantCertificatePath();
        $this->appleCaCertificatePath = EnvironmentService::getAppleCaCertificatePath();
    }

    /**
     * test merchant validation request.
     *
     * @test
     *
     * @throws ApplepayMerchantValidationException
     */
    public function verifyMerchantValidationRequest(): void
    {
        $applepaySession = $this->createApplepaySession();
        $appleAdapter = new ApplepayAdapter();
        $appleAdapter->init($this->merchantValidationCertificatePath, null, $this->appleCaCertificatePath);

        $validationResponse = $appleAdapter->validateApplePayMerchant(
            $this->merchantValidationUrl,
            $applepaySession
        );

        $this->assertNotNull($validationResponse);
    }

    /**
     * test merchant validation request without ca certificate.
     *
     * @test
     *
     * @throws ApplepayMerchantValidationException
     */
    public function merchantValidationWorksWithoutCaCert(): void
    {
        $applepaySession = $this->createApplepaySession();
        $appleAdapter = new ApplepayAdapter();
        $appleAdapter->init($this->merchantValidationCertificatePath);

        $validationResponse = $appleAdapter->validateApplePayMerchant(
            $this->merchantValidationUrl,
            $applepaySession
        );

        $this->assertNotNull($validationResponse);
    }

    /**
     * Merchant validation call should throw Exception if domain of Validation url is invalid.
     *
     * @test
     *
     */
    public function merchantValidationThrowsErrorForInvalidDomain(): void
    {
        $applepaySession = $this->createApplepaySession();
        $appleAdapter = new ApplepayAdapter();
        $appleAdapter->init($this->merchantValidationCertificatePath);

        $this->expectException(ApplepayMerchantValidationException::class);
        $this->expectExceptionMessage('Invalid URL used for merchantValidation request.');

        $appleAdapter->validateApplePayMerchant(
            'https://invalid.domain.com/some/path',
            $applepaySession
        );
    }

    /**
     * test merchant validation request without ca certificate.
     *
     * @dataProvider domainShouldBeValidatedCorrectlyDP
     * @test
     *
     * @param mixed $validationUrl
     * @param mixed $expectedResult
     *
     */
    public function domainShouldBeValidatedCorrectly($validationUrl, $expectedResult): void
    {
        $appleAdapter = new ApplepayAdapter();

        $domainValidation = $appleAdapter->validMerchantValidationDomain($validationUrl);
        $this->assertEquals($expectedResult, $domainValidation);
    }

    /**
     * @return ApplepaySession
     */
    private function createApplepaySession(): ApplepaySession
    {
        return new ApplepaySession('merchantIdentifier', 'displayName', 'domainName');
    }
}
