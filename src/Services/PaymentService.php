<?php
/**
 * This service provides for functionalities concerning payment transactions.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/services
 */
namespace heidelpay\MgwPhpSdk\Services;

use heidelpay\MgwPhpSdk\Constants\IdStrings;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Interfaces\HeidelpayResourceInterface;
use heidelpay\MgwPhpSdk\Resources\AbstractHeidelpayResource;
use heidelpay\MgwPhpSdk\Resources\Customer;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\BasePaymentType;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Card;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Giropay;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Ideal;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Invoice;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\InvoiceGuaranteed;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Paypal;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Prepayment;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Przelewy24;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\SepaDirectDebit;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\SepaDirectDebitGuaranteed;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Sofort;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\AbstractTransactionType;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Shipment;

class PaymentService
{
    /** @var Heidelpay */
    private $heidelpay;

    /** @var ResourceService $resourceService */
    private $resourceService;

    /**
     * PaymentService constructor.
     *
     * @param Heidelpay $heidelpay
     */
    public function __construct(Heidelpay $heidelpay)
    {
        $this->heidelpay = $heidelpay;
        $this->resourceService = $heidelpay->getResourceService();
    }

    /**
     * Fetch the payment type with the given Id from the API.
     *
     * @param string $typeId
     *
     * @return BasePaymentType|AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function fetchPaymentType($typeId): HeidelpayResourceInterface
    {
        $paymentType = null;

        $typeIdParts = [];
        preg_match('/^[sp]{1}-([a-z]{3}|p24)-[a-z0-9]*/', $typeId, $typeIdParts);

        switch ($typeIdParts[1]) {
            case IdStrings::CARD:
                $paymentType = new Card(null, null);
                break;
            case IdStrings::GIROPAY:
                $paymentType = new Giropay();
                break;
            case IdStrings::IDEAL:
                $paymentType = new Ideal();
                break;
            case IdStrings::INVOICE:
                $paymentType = new Invoice();
                break;
            case IdStrings::INVOICE_GUARANTEED:
                $paymentType = new InvoiceGuaranteed();
                break;
            case IdStrings::PAYPAL:
                $paymentType = new Paypal();
                break;
            case IdStrings::PREPAYMENT:
                $paymentType = new Prepayment();
                break;
            case IdStrings::PRZELEWY24:
                $paymentType = new Przelewy24();
                break;
            case IdStrings::SEPA_DIRECT_DEBIT_GUARANTEED:
                $paymentType = new SepaDirectDebitGuaranteed(null);
                break;
            case IdStrings::SEPA_DIRECT_DEBIT:
                $paymentType = new SepaDirectDebit(null);
                break;
            case IdStrings::SOFORT:
                $paymentType = new Sofort();
                break;
            default:
                throw new HeidelpaySdkException(sprintf('Payment type "%s" is not allowed!', $typeIdParts[1]));
                break;
        }

        return $this->resourceService->fetch($paymentType->setParentResource($this->heidelpay)->setId($typeId));
    }

    /**
     * Create the given payment type via api.
     *
     * @param BasePaymentType $paymentType
     *
     * @return BasePaymentType|AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function createPaymentType(BasePaymentType $paymentType): BasePaymentType
    {
        /** @var AbstractHeidelpayResource $paymentType */
        $paymentType->setParentResource($this->heidelpay);
        return $this->resourceService->create($paymentType);
    }

    /**
     * Fetch and return payment by given payment id.
     *
     * @param Payment|string $payment
     *
     * @return Payment
     *
     * @throws HeidelpayApiException
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function fetchPayment($payment): HeidelpayResourceInterface
    {
        $paymentObject = $payment;
        if (\is_string($payment)) {
            $paymentObject = new Payment($this->heidelpay);
            $paymentObject->setId($payment);
        }

        $this->resourceService->fetch($paymentObject);
        if (!$paymentObject instanceof Payment) {
            throw new HeidelpaySdkException(sprintf('Fetched object is not a payment object!'));
        }
        return $paymentObject;
    }

    //<editor-fold desc="Transactions">
    //<editor-fold desc="Shipment">

    /**
     * Creates a Shipment transaction for the given Payment object.
     *
     * @param Payment|string $payment
     *
     * @return Shipment Resulting Shipment object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function ship($payment): HeidelpayResourceInterface
    {
        $paymentObject = $payment;

        if (\is_string($payment)) {
            $paymentObject = $this->fetchPayment($payment);
        }

        if (!$paymentObject instanceof Payment) {
            throw new HeidelpaySdkException('Payment object is not set.');
        }

        $shipment = new Shipment();
        $paymentObject->addShipment($shipment);
        return $this->resourceService->create($shipment);
    }

    //</editor-fold>

    //<editor-fold desc="Authorize">

    /**
     * Perform an Authorization transaction and return the corresponding Authorization object.
     *
     * @param float $amount
     * @param string $currency
     * @param string|BasePaymentType $paymentType
     * @param string $returnUrl
     * @param Customer|null $customer
     * @param string|null $orderId
     *
     * @return Authorization Resulting Authorization object.
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function authorizeWithPaymentType(
        $amount,
        $currency,
        $paymentType,
        $returnUrl,
        $customer = null,
        $orderId = null
    ): Authorization {
        return $this->authorize($amount, $currency, $paymentType, $returnUrl, $customer, $orderId);
    }

    /**
     * Perform an authorization and return the corresponding Authorization object.
     *
     * @param $amount
     * @param $currency
     * @param Payment $payment
     * @param $returnUrl
     * @param string|null $customer
     * @param string|null $orderId
     *
     * @return Authorization Resulting Authorization object.
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function authorizeWithPayment(
        $amount,
        $currency,
        Payment $payment,
        $returnUrl = null,
        $customer = null,
        $orderId = null
    ): AbstractTransactionType {
        $authorization = (new Authorization($amount, $currency, $returnUrl))->setOrderId($orderId);
        $payment->setAuthorization($authorization)->setCustomer($customer);
        $this->resourceService->create($authorization);
        return $authorization;
    }

    /**
     * Perform an Authorization transaction and return the corresponding Authorization object.
     *
     * @param float  $amount
     * @param string $currency
     * @param $paymentType
     * @param string               $returnUrl
     * @param Customer|string|null $customer
     * @param string|null          $orderId
     *
     * @return Authorization Resulting Authorization object.
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function authorize($amount, $currency, $paymentType, $returnUrl, $customer = null, $orderId = null): AbstractTransactionType
    {
        $payment = $this->createPayment($paymentType, $customer);
        return $this->authorizeWithPayment($amount, $currency, $payment, $returnUrl, $customer, $orderId);
    }

    //</editor-fold>

    //<editor-fold desc="Charge">

    /**
     * Charge the given amount and currency on the given PaymentType resource.
     *
     * @param float                  $amount
     * @param string                 $currency
     * @param BasePaymentType|string $paymentType
     * @param string                 $returnUrl
     * @param Customer|string|null   $customer
     * @param string|null            $orderId
     *
     * @return Charge Resulting Charge object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function charge(
        $amount,
        $currency,
        $paymentType,
        $returnUrl,
        $customer = null,
        $orderId = null
    ): AbstractTransactionType {
        $payment = $this->createPayment($paymentType, $customer);
        $charge = new Charge($amount, $currency, $returnUrl);
        $charge->setParentResource($payment)->setPayment($payment);
        $charge->setOrderId($orderId);
        $payment->addCharge($charge);
        $this->resourceService->create($charge);

        return $charge;
    }

    /**
     * Charge the given amount on the payment with the given id.
     * Perform a full charge by leaving the amount null.
     *
     * @param string|Payment $payment
     * @param null           $amount
     *
     * @return Charge Resulting Charge object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function chargeAuthorization($payment, $amount = null): AbstractTransactionType
    {
        $paymentObject = $payment;

        if (\is_string($payment)) {
            $paymentObject = $this->fetchPayment($payment);
        }

        return $this->chargePayment($paymentObject, $amount);
    }

    /**
     * Charge the given amount on the given payment object with the given currency.
     *
     * @param Payment $payment
     * @param null    $amount
     * @param null    $currency
     *
     * @return Charge Resulting Charge object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function chargePayment(Payment $payment, $amount = null, $currency = null): AbstractTransactionType
    {
        $charge = new Charge($amount, $currency);
        $charge->setParentResource($payment)->setPayment($payment);
        $payment->addCharge($charge);
        $this->resourceService->create($charge);
        return $charge;
    }

    //</editor-fold>
    
    //</editor-fold>

    /**
     * Create a Payment object with the given properties.
     *
     * @param BasePaymentType|string $paymentType
     * @param Customer|string|null   $customer
     *
     * @return Payment The resulting Payment object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    private function createPayment($paymentType, $customer = null): HeidelpayResourceInterface
    {
        return (new Payment($this->heidelpay))->setPaymentType($paymentType)->setCustomer($customer);
    }
}
