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

namespace heidelpay\NmgPhpSdk\Traits;

use heidelpay\NmgPhpSdk\HeidelpayParentInterface;
use heidelpay\NmgPhpSdk\TransactionTypes\Cancellation;

trait HasCancellationsTrait
{
    /** @var array $cancellations */
    private $cancellations = [];


    //<editor-fold desc="Getters/Setters">
    /**
     * @return array
     */
    public function getCancellations(): array
    {
        return $this->cancellations;
    }

    /**
     * @param array $cancellations
     * @return self
     */
    public function setCancellations(array $cancellations): self
    {
        $this->cancellations = $cancellations;
        return $this;
    }

    /**
     * @param Cancellation $cancellation
     */
    public function addCancellation(Cancellation $cancellation)
    {
        if ($this instanceof HeidelpayParentInterface) {
            $cancellation->setParentResource($this);
        }
        $this->cancellations[] = $cancellation;
    }
    //</editor-fold>
}
