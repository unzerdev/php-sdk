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
use UnzerSDK\Resources\ExternalResources\ApplepaySession;
use UnzerSDK\Services\EnvironmentService;
use UnzerSDK\test\BaseIntegrationTest;

class ApplepayAdapterTest extends BaseIntegrationTest
{
    private $merchantValidationUrl;
    private $merchantValidationCertificatePath;
    private $appleCaCertificatePath;

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
     */
    public function verifyMerchantValidationRequest(): void
    {
        $applepaySession = $this->createApplepaySession();
        $appleAdapter = new ApplepayAdapter();

        $validationResponse = $appleAdapter->validateApplePayMerchant(
            $this->merchantValidationUrl,
            $applepaySession,
            $this->merchantValidationCertificatePath,
            $this->appleCaCertificatePath
        );

        $this->assertNotNull($validationResponse);
    }

    /**
     * test merchant validation request without ca certificate.
     *
     * @test
     */
    public function merchantValidationWorksWithoutCaCert(): void
    {
        $merchantValidationCertificatePath = EnvironmentService::getAppleMerchantCertificatePath();

        $applepaySession = $this->createApplepaySession();
        $appleAdapter = new ApplepayAdapter();

        $validationResponse = $appleAdapter->validateApplePayMerchant(
            $this->merchantValidationUrl,
            $applepaySession,
            $this->merchantValidationCertificatePath
        );

        $this->assertNotNull($validationResponse);
    }

    /**
     * @return ApplepaySession
     */
    private function createApplepaySession(): ApplepaySession
    {
        return new ApplepaySession('merchantIdentifier', 'displayName', 'domainName');
    }
}
