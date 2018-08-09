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
namespace heidelpay\NmgPhpSdk;

use heidelpay\NmgPhpSdk\Exceptions\HeidelpayObjectMissingException;
use heidelpay\NmgPhpSdk\Exceptions\IdRequiredToFetchResourceException;

class HeidelpayResource implements HeidelpayResourceInterface
{
    /** @var string $id */
    private $id = '';

    /** @var Heidelpay $heidelpay */
    private $heidelpay;

    /**
     * HeidelpayResource constructor.
     * @param Heidelpay|null $heidelpay
     */
    public function __construct(Heidelpay $heidelpay = null)
    {
        $this->heidelpay = $heidelpay;
    }


    //<editor-fold desc="CRUD">
    public function create()
    {
        $heidelpay = $this->getHeidelpay();
    }

    public function update()
    {
        $heidelpay = $this->getHeidelpay();
    }

    public function delete()
    {
        $heidelpay = $this->getHeidelpay();

        if (empty($this->id)) {
            throw new IdRequiredToFetchResourceException();
        }
    }

    public function fetch()
    {
        $heidelpay = $this->getHeidelpay();

        if (empty($this->id)) {
            throw new IdRequiredToFetchResourceException();
        }
    }
    //</editor-fold>

    //<editor-fold desc="Getters/Setters">
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return HeidelpayResource
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Heidelpay
     */
    public function getHeidelpay()
    {
        if (!$this->heidelpay instanceof Heidelpay) {
            throw new HeidelpayObjectMissingException();
        }
        return $this->heidelpay;
    }

    /**
     * @param Heidelpay $heidelpay
     * @return HeidelpayResource
     */
    public function setHeidelpay($heidelpay)
    {
        $this->heidelpay = $heidelpay;
        return $this;
    }
    //</editor-fold>
}
