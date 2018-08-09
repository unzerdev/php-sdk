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

interface HeidelpayResourceInterface
{
    /**
     * Create the resource on the api.
     *
     * @return $this
     */
    public function create();

    /**
     * Update the resource on the api.
     *
     * @return mixed
     */
    public function update();

    /**
     * Delete the resource on the api.
     *
     * @return mixed
     */
    public function delete();

    /**
     * Fetch the resource from the api (id must be set).
     *
     * @return mixed
     */
    public function fetch();
}
