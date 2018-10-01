<?php
/**
 * Http adapters to be used by this api have to implement this interface.
 *
 * LICENSE
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/heidelpay-php-payment-api/
 *
 * @author  Simon Gabriel <simon.gabriel@heidelpay.com>
 *
 * @package  heidelpay/ngw_sdk/adapter
 */
namespace heidelpay\NmgPhpSdk\Adapter;

use heidelpay\NmgPhpSdk\Resources\AbstractHeidelpayResource;

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
     * @param AbstractHeidelpayResource $heidelpayResource
     * @param $httpMethod
     * @return string result json of the transaction
     */
    public function send($uri = null, AbstractHeidelpayResource $heidelpayResource = null, $httpMethod = self::REQUEST_POST): string;
}
