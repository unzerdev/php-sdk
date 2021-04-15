<?php
/*
 * This class defines integration tests to verify interface and
 * functionality of the payment method Applepay.
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

namespace UnzerSDK\test\integration\PaymentTypes;

use UnzerSDK\Resources\PaymentTypes\Applepay;
use UnzerSDK\test\BaseIntegrationTest;

class ApplepayTest extends BaseIntegrationTest
{
    /**
     * Verify applepay can be created and fetched.
     *
     * @test
     */
    public function applepayShouldBeCreatableAndFetchable(): void
    {
        $applepay = $this->createApplepayObject();
        $this->unzer->createPaymentType($applepay);
        $this->assertNotNull($applepay->getId());

        /** @var Applepay $fetchedPaymentTyp */
        $fetchedPaymentTyp = $this->unzer->fetchPaymentType($applepay->getId());
        $this->assertInstanceOf(Applepay::class, $fetchedPaymentTyp);
        $this->assertNull($fetchedPaymentTyp->getVersion());
        $this->assertNull($fetchedPaymentTyp->getData());
        $this->assertNull($fetchedPaymentTyp->getSignature());
        $this->assertNull($fetchedPaymentTyp->getHeader());
    }

    /**
     * Verify that applepay is chargeable
     *
     * @test
     */
    public function applepayShouldBeChargeable(): void
    {
        $applepay = $this->createApplepayObject();
        $this->unzer->createPaymentType($applepay);
        $charge = $applepay->charge(100.0, 'EUR', self::RETURN_URL);
        $this->assertNotNull($charge->getId());
        $this->assertNull($charge->getRedirectUrl());
    }

    /**
     * Verify that applepay is chargeable
     *
     * @test
     */
    public function applepayCanBeAuthorized(): void
    {
        $applepay = $this->createApplepayObject();
        $this->unzer->createPaymentType($applepay);
        $charge = $applepay->authorize(100.0, 'EUR', self::RETURN_URL);

        $this->assertNotNull($charge->getId());
        $this->assertNull($charge->getRedirectUrl());
    }

    /**
     * Verify the applepay can charge the full amount of the authorization and the payment state is updated accordingly.
     *
     * @test
     */
    public function fullChargeAfterAuthorize(): void
    {
        $applepay = $this->createApplepayObject();
        $this->unzer->createPaymentType($applepay);

        $authorization = $applepay->authorize(1.0, 'EUR', self::RETURN_URL, null, null, null, null, false);
        $payment = $authorization->getPayment();

        // pre-check to verify changes due to fullCharge call
        $this->assertAmounts($payment, 1.0, 0.0, 1.0, 0.0);
        $this->assertTrue($payment->isPending());

        $charge     = $this->unzer->chargeAuthorization($payment->getId());
        $paymentNew = $charge->getPayment();

        // verify payment has been updated properly
        $this->assertAmounts($paymentNew, 0.0, 1.0, 1.0, 0.0);
        $this->assertTrue($paymentNew->isCompleted());
    }

    /**
     * Verify applepay authorize can be canceled.
     *
     * @test
     */
    public function applepayAuthorizeCanBeCanceled(): void
    {
        /** @var Applepay $applepay */
        $applepay      = $this->unzer->createPaymentType($this->createApplepayObject());
        $authorize = $applepay->authorize(100.0, 'EUR', self::RETURN_URL, null, null, null, null, false);

        $cancel = $authorize->cancel();
        $this->assertNotNull($cancel);
        $this->assertNotEmpty($cancel->getId());
    }

    /**
     * @test
     */
    public function fullCancelAfterCharge(): void
    {
        $applepay    = $this->createApplepayObject();
        $this->unzer->createPaymentType($applepay);
        $charge  = $applepay->charge(100.0, 'EUR', self::RETURN_URL, null, null, null, null, false);
        $payment = $charge->getPayment();

        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 0.0);
        $this->assertTrue($payment->isCompleted());

        $payment->cancelAmount();
        $this->assertAmounts($payment, 0.0, 0.0, 100.0, 100.0);
        $this->assertTrue($payment->isCanceled());
    }

    /**
     * @return Applepay
     */
    private function createApplepayObject(): Applepay
    {
        $applepayAutorization = '{
          "version": "EC_v1",
          "data": "TwQBBorcg6aEb5eidSJm5fNG5sih+R+xgeJbvAX8oMQ7EXhIWOE+ACnvBFHOkZOjI+ump/zVrBXTMRYSw32WMWXPuiRDlYu8DMNuV3qKrbC+G5Du5qfxsm8BxJCXkc/DqtGqc70o8TJCn9lM5ePQjS3io4HDonkN4b4L20GfyEVW1QyvozaMa1u7/gaS6OhhXNk65Z70+xCZlOGmgDtgcdZK+TQIYgRLzyP+1+mpqd61pQ3vJELB8ngMoleCGd1DHx2kVWsudZQ5q97sUjpZV2ySfPXLMhWHYYfvcvSx3dKDAywUoR8clUeDKtoZ4LsBO/B8XM/T4JKnFmWfr7Z25E88vfMWIs8JpxIC5OKAPZfVZoDSNs+4LR+twVxlD5B2xkvG6ln6j4cQ+CFmiq9FPSDgQJsn8O7K9Ag0odXiK6mZczOWt2HCHaw0thF/WpudObVlmw5NN1r54/Jxoichp+DJ2Hl1NJqDHKS1fNyXQcR5jqID7QOcpQi0gE332bOTIz/xe+u328GMCl6Rms3JJxFnnskfEA7nicIH8DLFeSbG8jloLyKBBLk=",
          "signature": "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",
          "header": {
            "ephemeralPublicKey": "MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEW7hYAxjCeE/r9SSRX/hJsfO+VxLvUqIzyeGn6lZ1v/pYYS66Bz0dsSzoMMZg8G32TAPXUr97AD4zCXfcQoZaOA==",
            "publicKeyHash": "zqO5Y3ldWWm4NnIkfGCvJILw30rp3y46Jsf21gE8CNg=",
            "transactionId": "94f6b37149ae2098efb287ed0ade704284cff3f672ef7f0dc17614b31e926b9d"
          }
        }';

        $applepay = new Applepay(null, null, null, null);
        $applepay->handleResponse(json_decode($applepayAutorization));
        return $applepay;
    }
}
