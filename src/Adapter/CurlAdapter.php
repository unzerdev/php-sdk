<?php
/**
 * Standard curl adapter
 *
 * You can use this adapter for your project or you can
 * create one based on a standard library like zend-http
 * or guzzlehttp.
 *
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/heidelpay-php-payment-api/
 *
 * @author  Jens Richter
 *
 * @package  Heidelpay
 * @subpackage PhpPaymentApi
 * @category PhpPaymentApi
 */
namespace heidelpay\NmgPhpSdk\Adapter;

use heidelpay\NmgPhpSdk\AbstractHeidelpayResource;
use heidelpay\NmgPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\NmgPhpSdk\Parameters;

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

        if (Parameters::DEBUG) {
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
        if ($info >= 400) {
            $responseArray = json_decode($response);
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
            'SDK-VERSION: ' . Parameters::VERSION
//            'CUSTOMER-LANGUAGE: en_US', // heidelpay constructor // header object?
//            'CHECKOUT-ID: checkout-5aba2fad0ab154.88150279', // heidelpay constructor
//            'SHOP-SYSTEM: Shopware - 5.2.2', // heidelpay constructor
//            'EXTENSION: heidelpay/magento-cd-edition - 1.5.3' // heidelpay constructor
        ));
        return $request;
    }

}
