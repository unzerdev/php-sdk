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

    //<editor-fold desc="Payment Type">
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
    //</editor-fold>

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
}
