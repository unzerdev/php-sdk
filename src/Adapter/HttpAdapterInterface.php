<?php
/**
 * Http adapter interface
 *
 * Http adapters to be used by this api should implement this interface.
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/heidelpay-php-payment-api/
 *
 * @author  Simon Gabriel <simon.gabriel@heidelpay.com>
 *
 * @package  Heidelpay
 * @subpackage PhpPaymentApi
 * @category PhpPaymentApi
 */
namespace heidelpay\NmgPhpSdk\Adapter;

use HeidelpayPHP\lib\TransferableObject;

/**
 * Http adapters to be used by this api should implement this interface.
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/heidelpay-php-payment-api/
 *
 * @author  Simon Gabriel
 *
 * @package  Heidelpay
 * @subpackage PhpPaymentApi
 * @category PhpPaymentApi
 */
interface HttpAdapterInterface
{
    const REQUEST_POST = 'POST';
    const REQUEST_DELETE = 'DELETE';
    const REQUEST_PUT = 'PUT';
    const REQUEST_GET = 'GET';

    /**
     * send post request to payment server
     *
     * @param $uri string url of the target system
     *
     * @param TransferableObject $transferObject
     * @param $httpMethod
     * @return string result json of the transaction
     */
    public function send($uri = null, TransferableObject $transferObject = null, $httpMethod = self::REQUEST_POST);
}
