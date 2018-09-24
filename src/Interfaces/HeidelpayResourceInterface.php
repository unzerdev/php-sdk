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

use JsonSerializable;

interface HeidelpayResourceInterface extends JsonSerializable
{
    /**
     * Return the id of the resource.
     *
     * @return string|null
     */
    public function getId();

    /**
     * Create the resource on the api.
     *
     * @return $this
     */
    public function create(): HeidelpayResourceInterface;

    /**
     * Update the resource on the api.
     *
     * @return $this
     */
    public function update(): HeidelpayResourceInterface;

    /**
     * Delete the resource on the api.
     */
    public function delete();

    /**
     * Fetch the resource from the api (id must be set).
     *
     * @return $this
     */
    public function fetch(): HeidelpayResourceInterface;
}
