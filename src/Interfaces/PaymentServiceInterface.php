<?php
/**
 * The interface for the PaymentService.
 *
 * Copyright (C) 2019 heidelpay GmbH
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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\Interfaces
 */
namespace heidelpayPHP\Interfaces;

use DateTime;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\AbstractHeidelpayResource;
use heidelpayPHP\Resources\Basket;
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\InstalmentPlans;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\BasePaymentType;
use heidelpayPHP\Resources\PaymentTypes\Paypage;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\Resources\TransactionTypes\Payout;
use heidelpayPHP\Resources\TransactionTypes\Shipment;
use RuntimeException;

interface PaymentServiceInterface
{
    /**
     * Performs an Authorization transaction and returns the resulting Authorization resource.
     *
     * @param float                  $amount        The amount to authorize.
     * @param string                 $currency      The currency of the amount.
     * @param string|BasePaymentType $paymentType   The PaymentType object or the id of the PaymentType to use.
     * @param string                 $returnUrl     The URL used to return to the shop if the process requires leaving it.
     * @param Customer|string|null   $customer      The Customer object or the id of the customer resource to reference.
     * @param string|null            $orderId       A custom order id which can be set by the merchant.
     * @param Metadata|null          $metadata      The Metadata object containing custom information for the payment.
     * @param Basket|null            $basket        The Basket object corresponding to the payment.
     *                                              The Basket object will be created automatically if it does not exist
     *                                              yet (i.e. has no id).
     * @param bool|null              $card3ds       Enables 3ds channel for credit cards if available. This parameter is
     *                                              optional and will be ignored if not applicable.
     * @param string|null            $invoiceId     The external id of the invoice.
     * @param string|null            $referenceText A reference text for the payment.
     *
     * @return Authorization The resulting object of the Authorization resource.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function authorize(
        $amount,
        $currency,
        $paymentType,
        $returnUrl,
        $customer = null,
        $orderId = null,
        $metadata = null,
        $basket = null,
        $card3ds = null,
        $invoiceId = null,
        $referenceText = null
    ): Authorization;

    /**
     * Performs a Charge transaction and returns the resulting Charge resource.
     *
     * @param float                  $amount           The amount to charge.
     * @param string                 $currency         The currency of the amount.
     * @param string|BasePaymentType $paymentType      The PaymentType object or the id of the PaymentType to use.
     * @param string                 $returnUrl        The URL used to return to the shop if the process requires leaving it.
     * @param Customer|string|null   $customer         The Customer object or the id of the customer resource to reference.
     * @param string|null            $orderId          A custom order id which can be set by the merchant.
     * @param Metadata|null          $metadata         The Metadata object containing custom information for the payment.
     * @param Basket|null            $basket           The Basket object corresponding to the payment.
     *                                                 The Basket object will be created automatically if it does not exist
     *                                                 yet (i.e. has no id).
     * @param bool|null              $card3ds          Enables 3ds channel for credit cards if available. This parameter is
     *                                                 optional and will be ignored if not applicable.
     * @param string|null            $invoiceId        The external id of the invoice.
     * @param string|null            $paymentReference A reference text for the payment.
     *
     * @return Charge The resulting object of the Charge resource.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function charge(
        $amount,
        $currency,
        $paymentType,
        $returnUrl,
        $customer = null,
        $orderId = null,
        $metadata = null,
        $basket = null,
        $card3ds = null,
        $invoiceId = null,
        $paymentReference = null
    ): Charge;

    /**
     * Performs a Charge transaction for the Authorization of the given Payment object.
     * To perform a full charge of the authorized amount leave the amount null.
     *
     * @param string|Payment $payment   The Payment object the Authorization to charge belongs to.
     * @param float|null     $amount    The amount to charge.
     * @param string|null    $orderId   The order id from the shop.
     * @param string|null    $invoiceId The invoice id from the shop.
     *
     * @return Charge The resulting object of the Charge resource.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function chargeAuthorization(
        $payment,
        float $amount = null,
        string $orderId = null,
        string $invoiceId = null
    ): Charge;

    /**
     * Performs a Charge transaction for a specific Payment and returns the resulting Charge object.
     *
     * @param Payment|string $payment   The Payment object to be charged.
     * @param float|null     $amount    The amount to charge.
     * @param string|null    $currency  The Currency of the charged amount.
     * @param string|null    $orderId   The order id from the shop.
     * @param string|null    $invoiceId The invoice id from the shop.
     *
     * @return Charge The resulting Charge object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function chargePayment(
        $payment,
        float $amount = null,
        string $currency = null,
        string $orderId = null,
        string $invoiceId = null
    ): Charge;

    /**
     * Performs a Payout transaction and returns the resulting Payout resource.
     *
     * @param float                  $amount        The amount to payout.
     * @param string                 $currency      The currency of the amount.
     * @param string|BasePaymentType $paymentType   The PaymentType object or the id of the PaymentType to use.
     * @param string                 $returnUrl     The URL used to return to the shop if the process requires leaving it.
     * @param Customer|string|null   $customer      The Customer object or the id of the customer resource to reference.
     * @param string|null            $orderId       A custom order id which can be set by the merchant.
     * @param Metadata|null          $metadata      The Metadata object containing custom information for the payment.
     * @param Basket|null            $basket        The Basket object corresponding to the payment.
     *                                              The Basket object will be created automatically if it does not exist
     *                                              yet (i.e. has no id).
     * @param string|null            $invoiceId     The external id of the invoice.
     * @param string|null            $referenceText A reference text for the payment.
     *
     * @return Payout|AbstractHeidelpayResource The resulting object of the Payout resource.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function payout(
        $amount,
        $currency,
        $paymentType,
        $returnUrl,
        $customer = null,
        $orderId = null,
        $metadata = null,
        $basket = null,
        $invoiceId = null,
        $referenceText = null
    ): Payout;

    /**
     * Performs a Shipment transaction and returns the resulting Shipment object.
     *
     * @param Payment|string $payment   The Payment object the the id of the Payment to ship.
     * @param string|null    $invoiceId The id of the invoice in the shop.
     * @param string|null    $orderId   The id of the order in shop.
     *
     * @return Shipment The resulting Shipment object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function ship($payment, string $invoiceId = null, string $orderId = null): Shipment;

    /**
     * Initializes a PayPage for charge transaction and returns the PayPage resource.
     * Use the id of the PayPage resource to render the embedded PayPage.
     * Or redirect the client to the redirectUrl of the PayPage to show him the PayPage hosted by heidelpay.
     * Please keep in mind, that payment types requiring an authorization will not be shown on the PayPage when
     * initialized for charge.
     *
     * @param Paypage              $paypage  The PayPage resource to initialize.
     * @param Customer|string|null $customer The optional customer object.
     *                                       Keep in mind that payment types with mandatory customer object might not be
     *                                       available to the customer if no customer resource is referenced here.
     * @param Basket|null          $basket   The optional Basket object.
     *                                       Keep in mind that payment types with mandatory basket object might not be
     *                                       available to the customer if no basket resource is referenced here.
     * @param Metadata|null        $metadata The optional metadata resource.
     *
     * @return Paypage The updated PayPage resource.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function initPayPageCharge(
        Paypage $paypage,
        Customer $customer = null,
        Basket $basket = null,
        Metadata $metadata = null
    ): Paypage;

    /**
     * Initializes a PayPage for authorize transaction and returns the PayPage resource.
     * Use the id of the PayPage resource to render the embedded PayPage.
     * Or redirect the client to the redirectUrl of the PayPage to show him the PayPage hosted by heidelpay.
     * Please keep in mind, that payment types requiring a charge transaction will not be shown on the PayPage when
     * initialized for authorize.
     *
     * @param Paypage              $paypage  The PayPage resource to initialize.
     * @param Customer|string|null $customer The optional customer object.
     *                                       Keep in mind that payment types with mandatory customer object might not be
     *                                       available to the customer if no customer resource is referenced here.
     * @param Basket|null          $basket   The optional Basket object.
     *                                       Keep in mind that payment types with mandatory basket object might not be
     *                                       available to the customer if no basket resource is referenced here.
     * @param Metadata|null        $metadata The optional metadata resource.
     *
     * @return Paypage The updated PayPage resource.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function initPayPageAuthorize(
        Paypage $paypage,
        Customer $customer = null,
        Basket $basket = null,
        Metadata $metadata = null
    ): Paypage;

    /**
     * Returns an InstallmentPlans object containing all available instalment plans.
     *
     * @param float         $amount            The amount to be charged via FlexiPay Rate.
     * @param string        $currency          The currency code of the transaction.
     * @param float         $effectiveInterest The effective interest rate.
     * @param DateTime|null $orderDate         The date the order took place, is set to today if left empty.
     *
     * @return InstalmentPlans|AbstractHeidelpayResource The object containing all possible instalment plans.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function fetchDirectDebitInstalmentPlans(
        $amount,
        $currency,
        $effectiveInterest,
        DateTime $orderDate = null
    ): InstalmentPlans;
}
