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

use heidelpay\NmgPhpSdk\AbstractHeidelpayResource;
use heidelpay\NmgPhpSdk\ReferenceException;

class Cancellation extends AbstractHeidelpayResource
{
    private $amount = 0.0;

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
    protected function handleCreateResponse(\stdClass $response)
    {
        if ($this->id !== ($response->id ?? '')) {
            throw new ReferenceException();
        }

        if (isset($response->amount)) {
            $this->setAmount($response->amount);
        }
    }

    //<editor-fold desc="Getters/Setters">
    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return Cancellation
     */
    public function setAmount(float $amount): Cancellation
    {
        $this->amount = $amount;
        return $this;
    }
    //</editor-fold>
}
