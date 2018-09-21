<?php
/**
 * Description
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/${Package}
 */
namespace heidelpay\NmgPhpSdk\test;

use heidelpay\NmgPhpSdk\Constants\Currency;
use heidelpay\NmgPhpSdk\Customer;
use heidelpay\NmgPhpSdk\Payment;

class CustomerTests extends BasePaymentTest
{
    /**
     * Customer should be creatable via the sdk.
     *
     * @test
     */
    public function customerCreation(): Customer
    {
        $customer = $this->getCustomer();
        $this->assertEmpty($customer->getId());
        $this->heidelpay->createCustomer($customer);
        $this->assertNotEmpty($customer->getId());

        echo 'CustomerId: ' .$customer->getId();

        return $customer;
    }

    /**
     * @param Customer $customer
     * @depends customerCreation
     * @test
     */
    public function customerCanBeFetched(Customer $customer)
    {
        $fetchedCustomer = $this->heidelpay->fetchCustomerById($customer->getId());
        $this->assertEquals($customer->getId(), $fetchedCustomer->getId());
    }

    /**
     * Customer can be referenced by payment.
     *
     * @depends customerCreation
     * @test
     * @param Customer $customer
     */
    public function transactionShouldCreateAndReferenceCustomer(Customer $customer)
    {
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $payment = $this->createPayment()->setPaymentType($card);
        $payment->setCustomer($customer);

        $payment->authorize(12.0, Currency::EUROPEAN_EURO, self::RETURN_URL);

        /** @var Payment $secPayment */
        $secPayment = $this->heidelpay->fetchPaymentById($payment->getId());

        /** @var Customer $secCustomer */
        $secCustomer = $secPayment->getCustomer();
        $this->assertNotNull($secCustomer);
        $this->assertEquals($customer->getId(), $secCustomer->getId());
    }
}
