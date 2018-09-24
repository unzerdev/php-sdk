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
namespace heidelpay\NmgPhpSdk\Interfaces;

use heidelpay\NmgPhpSdk\Resources\Customer;
use heidelpay\NmgPhpSdk\Exceptions\IllegalTransactionTypeException;
use heidelpay\NmgPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\NmgPhpSdk\Resources\TransactionTypes\Charge;

interface PaymentTypeInterface
{
    /**
     * @param null $amount
     * @param null $currency
     * @param string $returnUrl
     * @param Customer|null $customer
     * @return Charge
     */
    public function charge($amount, $currency, $returnUrl, $customer = null): Charge;

    /**
     * @param float $amount
     * @param string $currency
     * @param string $returnUrl
     * @return Authorization
     * @throws IllegalTransactionTypeException
     */
    public function authorize($amount, $currency, $returnUrl): Authorization;

    /**
     * @return bool
     */
    public function isChargeable(): bool;

    /**
     * @return bool
     */
    public function isAuthorizable(): bool;
}
