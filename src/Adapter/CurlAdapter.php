<?php
/**
 * By default this adapter will be used for communication however a custom adapter implementing the
 * HttpAdapterInterface can be used.
 *
 * @license
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
 * @copyright Copyright © 2018 Heidelpay GmbH
 *
 * @link  http://dev.heidelpay.com/heidelpay-php-payment-api/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/adapter
 */
namespace heidelpay\MgwPhpSdk\Adapter;

use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Resources\AbstractHeidelpayResource;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;

class CurlAdapter implements HttpAdapterInterface
{
    /**
     * send post request to payment server
     *
     * @param $uri string url of the target system
     * @param AbstractHeidelpayResource $heidelpayResource
     * @param string $httpMethod
     * @return string
     * @throws \RuntimeException
     */
    public function send(
        $uri = null,
        AbstractHeidelpayResource $heidelpayResource = null,
        $httpMethod = HttpAdapterInterface::REQUEST_POST
    ): string {
        if (!\extension_loaded('curl')) {
            throw new \RuntimeException('Connection error php-curl not installed');
        }

        if (null === $heidelpayResource) {
            throw new \RuntimeException('Transfer object is null');
        }

        $request = $this->initCurlRequest($uri, $heidelpayResource, $httpMethod);

        if (Heidelpay::DEBUG_MODE) {
            echo 'Curl ' . $httpMethod . '-Request: ' . $uri . "\n";
        }

        $response = curl_exec($request);
        $info = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);

        $this->handleErrors($info, $response);

        return $response;
    }

    /**
     * @param $info
     * @param $response
     */
    private function handleErrors($info, $response)
    {
        $responseArray = json_decode($response);
        if ($info >= 400 || isset($responseArray->errors)) {
            $merchantMessage = $customerMessage = $code = '';

            if (isset($responseArray->errors[0])) {
                $errors = $responseArray->errors[0];
                $merchantMessage = $errors->merchantMessage ?? '';
                $customerMessage = $errors->customerMessage ?? '';
                $code = $errors->code ?? '';
            }

            throw new HeidelpayApiException($merchantMessage, $customerMessage, $code);
        }
    }

    /**
     * @param $uri
     * @param AbstractHeidelpayResource $heidelpayResource
     * @param $httpMethod
     *
     * @return mixed
     */
    private function initCurlRequest($uri, AbstractHeidelpayResource $heidelpayResource, $httpMethod)
    {
        $request = curl_init($uri);
        curl_setopt($request, CURLOPT_HEADER, 0);
        curl_setopt($request, CURLOPT_FAILONERROR, false);
        curl_setopt($request, CURLOPT_TIMEOUT, 60);
        curl_setopt($request, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($request, CURLOPT_HTTP200ALIASES, (array)400);
        curl_setopt($request, CURLOPT_CUSTOMREQUEST, $httpMethod);
        curl_setopt($request, CURLOPT_POSTFIELDS, $heidelpayResource->jsonSerialize());
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($request, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($request, CURLOPT_SSLVERSION, 6);       // CURL_SSLVERSION_TLSv1_2
        curl_setopt($request, CURLOPT_USERAGENT, 'HeidelpayPHP');
        curl_setopt($request, CURLOPT_HTTPHEADER, array(
            'Authorization: ' . 'Basic ' . base64_encode($heidelpayResource->getHeidelpayObject()->getKey() . ':'), // basic auth with key as user and empty password
            'Content-Type: application/json',
            'SDK-VERSION: ' . Heidelpay::SDK_VERSION
//            'CUSTOMER-LANGUAGE: en_US', // heidelpay constructor // header object?
//            'CHECKOUT-ID: checkout-5aba2fad0ab154.88150279', // heidelpay constructor
//            'SHOP-SYSTEM: Shopware - 5.2.2', // heidelpay constructor
//            'EXTENSION: heidelpay/magento-cd-edition - 1.5.3' // heidelpay constructor
        ));
        return $request;
    }

}
