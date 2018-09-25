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
}
