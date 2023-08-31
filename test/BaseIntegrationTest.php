<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class is the base class for all integration tests of this SDK.
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

namespace UnzerSDK\test;

use UnzerSDK\Resources\PaymentTypes\PaylaterInvoice;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\test\Helper\TestEnvironmentService;
use PHPUnit\Runner\BaseTestRunner;

class BaseIntegrationTest extends BasePaymentTest
{
    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->getUnzerObject(TestEnvironmentService::getTestPrivateKey());
    }

    /**
     * If verbose test output is disabled echo debug log when test did not pass.
     *
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        $debugHandler = self::getDebugHandler();

        if ($this->getStatus() === BaseTestRunner::STATUS_PASSED) {
            $debugHandler->clearTempLog();
        } else {
            echo "\n";
            $debugHandler->dumpTempLog();
            echo "\n";
        }
    }

    /** Creates a Paylater Invoice authorization transaction with an amount of 99.99€.
     *
     * @return Authorization
     *
     * @throws \UnzerSDK\Exceptions\UnzerApiException
     */
    protected function createPaylaterInvoiceAuthorization(): Authorization
    {
        $paylaterInvoice = $this->unzer->createPaymentType(new PaylaterInvoice());

        $authorization = new Authorization(99.99, 'EUR', 'https://unzer.com');
        $authorization->setInvoiceId('202205021237');

        $customer = $this->getMaximumCustomerInclShippingAddress();
        $basket = $this->createV2Basket();

        $authorization = $this->unzer->performAuthorization($authorization, $paylaterInvoice, $customer, null, $basket);
        return $authorization;
    }

    /**
     * @return void
     */
    protected function useNon3dsKey(): void
    {
        $this->getUnzerObject()->setKey(TestEnvironmentService::getTestPrivateKey(true));
    }

    /**
     * @return void
     */
    protected function useLegacyKey(): void
    {
        $this->getUnzerObject()->setKey(TestEnvironmentService::getLegacyTestPrivateKey());
    }
}
