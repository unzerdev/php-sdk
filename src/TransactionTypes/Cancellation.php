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
namespace heidelpay\NmgPhpSdk\TransactionTypes;

use heidelpay\NmgPhpSdk\ReferenceException;

class Cancellation extends AbstractTransactionType
{
    /** @var float $amount */
    private $amount;

    /**
     * Authorization constructor.
     * @param float $amount
     */
    public function __construct($amount = null)
    {
        $this->setAmount($amount);

        parent::__construct(null);
    }

    /**
     * {@inheritDoc}
     */
    protected function getResourcePath()
    {
        return 'cancels';
    }

    /**
     * {@inheritDoc}
     */
    protected function handleResponse(\stdClass $response)
    {
        if ($this->id !== ($response->id ?? '')) {
            throw new ReferenceException();
        }

        if (isset($response->amount)) {
            $this->setAmount($response->amount);
        }

        parent::handleResponse($response);
    }

    //<editor-fold desc="Getters/Setters">
    /**
     * @return float|null
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return Cancellation
     */
    public function setAmount($amount): Cancellation
    {
        $this->amount = $amount;
        return $this;
    }
    //</editor-fold>
}
