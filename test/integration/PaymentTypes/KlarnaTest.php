<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and
 * functionality of the payment method klarna.
 *
 * Copyright (C) 2022 - today Unzer E-Com GmbH
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
 * @package  UnzerSDK\test\integration\PaymentTypes
 */
namespace UnzerSDK\test\integration\PaymentTypes;

use UnzerSDK\Resources\PaymentTypes\Klarna;
use UnzerSDK\test\BaseIntegrationTest;

class KlarnaTest extends BaseIntegrationTest
{
    /**
     * Verify klarna can be created.
     *
     * @test
     *
     * @return Klarna
     */
    public function klarnaShouldBeCreatableAndFetchable(): Klarna
    {
        $klarna = $this->unzer->createPaymentType(new Klarna());
        $this->assertInstanceOf(Klarna::class, $klarna);
        $this->assertNotNull($klarna->getId());

        /** @var Klarna $fetchedKlarna */
        $fetchedKlarna = $this->unzer->fetchPaymentType($klarna->getId());
        $this->assertInstanceOf(Klarna::class, $fetchedKlarna);
        $this->assertEquals($klarna->expose(), $fetchedKlarna->expose());
        $this->assertNotEmpty($fetchedKlarna->getGeoLocation()->getClientIp());

        return $fetchedKlarna;
    }
}
