<?php
/*
 *  Tests for additional transaction data trait
 *
 *  Copyright (C) 2022 - today Unzer E-Com GmbH
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
 *  @package  UnzerSDK
 *
 */

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */

namespace UnzerSDK\test\unit\Traits;

use UnzerSDK\Resources\EmbeddedResources\CardTransactionData;
use UnzerSDK\Resources\EmbeddedResources\RiskData;
use UnzerSDK\Resources\EmbeddedResources\ShippingData;
use UnzerSDK\Resources\PaymentTypes\Paypal;
use UnzerSDK\test\BasePaymentTest;

class HasAdditionalTransactionDataTest extends BasePaymentTest
{
    /**
     * Verify setters and getters.
     *
     * @test
     */
    public function gettersAndSettersShouldWorkProperly(): void
    {
        $dummy = new TraitDummyHasAdditionalTransactionData();

        $this->assertNull($dummy->getShipping());
        $this->assertNull($dummy->getRiskData());
        $this->assertNull($dummy->getPrivacyPolicyUrl());
        $this->assertNull($dummy->getTermsAndConditionUrl());
        $this->assertNull($dummy->getCheckoutType());

        $shipping = (new ShippingData())
            ->setDeliveryService('deliveryService')
            ->setDeliveryTrackingId('deliveryTrackingId')
            ->setReturnTrackingId('returnTrackingId');

        $riskData = (new RiskData())
            ->setThreatMetrixId('threatMetrixId');

        $privacyPolicyUrl = 'privacyPolicyUrl';
        $termsAndConditionUrl = 'termsAndConditionUrl';
        $dummy->setShipping($shipping)
            ->setRiskData($riskData)
            ->setPrivacyPolicyUrl($privacyPolicyUrl)
            ->setCheckoutType('express', new Paypal())
            ->setTermsAndConditionUrl($termsAndConditionUrl);

        $this->assertNotNull($dummy->getShipping());
        $this->assertNotNull($dummy->getRiskData());
        $this->assertNotNull($dummy->getPrivacyPolicyUrl());
        $this->assertNotNull($dummy->getTermsAndConditionUrl());
        $this->assertNotNull($dummy->getCheckoutType());

        $this->assertEquals($shipping, $dummy->getShipping());
        $this->assertEquals($riskData, $dummy->getRiskData());
        $this->assertEquals($privacyPolicyUrl, $dummy->getPrivacyPolicyUrl());
        $this->assertEquals($termsAndConditionUrl, $dummy->getTermsAndConditionUrl());
    }

    /**
     * Setting and getting card data should work as expected.
     *
     * @test
     */
    public function setAndGetCardData(): void
    {
        $dummy = new TraitDummyHasAdditionalTransactionData();
        $this->assertNull($dummy->getCardTransactionData());

        $cardTransactionData = (new CardTransactionData())
            ->setRecurrenceType('recurrenceType')
            ->setExemptionType('exemptionType');

        $dummy->setCardTransactionData($cardTransactionData);

        $cardData = $dummy->getCardTransactionData();
        $this->assertNotNull($cardData);
        $this->assertEquals('exemptionType', $cardData->getExemptionType());
        $this->assertNull($cardData->getLiability());
        $this->assertEquals('recurrenceType', $cardData->getRecurrenceType());
    }

    /**
     * CardData should be exposed correctly.
     *
     * @test
     */
    public function exposeCardDataAsExpected(): void
    {
        $dummy = new TraitDummyHasAdditionalTransactionData();
        $this->assertNull($dummy->getCardTransactionData());

        $cardTransactionData = (new CardTransactionData())
            ->setRecurrenceType('recurrenceType')
            ->setExemptionType('exemptionType');

        $dummy->setCardTransactionData($cardTransactionData);

        $exposedResource = $dummy->expose();
        $this->assertNotNull($exposedResource['additionalTransactionData']);
        $additionalTransactionData = $exposedResource['additionalTransactionData'];

        $this->assertFalse(isset($additionalTransactionData->card['liability']));
        $this->assertEquals('recurrenceType', $additionalTransactionData->card['recurrenceType']);
        $this->assertEquals('exemptionType', $additionalTransactionData->card['exemptionType']);
    }

    /**
     * Verify checkoutType can be set via typeId correctly.
     *
     * @test
     */
    public function checkoutTypeCanBeSetViaTypeId(): void
    {
        $dummy = new TraitDummyHasAdditionalTransactionData();
        $dummy->setCheckoutType('checkoutType', 's-ppl-xyz');

        $additionalTransactionData = $dummy->getAdditionalTransactionData();
        $this->assertTrue(property_exists($additionalTransactionData, 'paypal'));
        $this->assertEquals($additionalTransactionData->paypal->checkoutType, 'checkoutType');
    }

    /**
     * @test
     */
    public function getterShouldReturnNullIfAdittionalTransactionDataDoNOtContainProperObject()
    {
        $dummy = new TraitDummyHasAdditionalTransactionData();
        $dummy->addAdditionalTransactionData('shipping', 'This is not a shippingObject!');
        $dummy->addAdditionalTransactionData('riskData', 'This is not a riskDataObject!');

        $this->assertEquals('This is not a shippingObject!', $dummy->getAdditionalTransactionData()->shipping);
        $this->assertEquals('This is not a riskDataObject!', $dummy->getAdditionalTransactionData()->riskData);

        $this->assertNull($dummy->getShipping());
        $this->assertNull($dummy->getRiskData());
    }
}
