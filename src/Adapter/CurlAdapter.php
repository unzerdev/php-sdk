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

use HeidelpayPHP\lib\TransferableObject;

class CurlAdapter implements HttpAdapterInterface
{
    /**
     * send post request to payment server
     *
     * @param $uri string url of the target system
     * @param TransferableObject $transferObject
     * @param string $httpMethod
     * @return string
     * @throws \RuntimeException
     */
    public function send(
        $uri = null,
        TransferableObject $transferObject = null,
        $httpMethod = HttpAdapterInterface::REQUEST_POST
    ) {
        if (!extension_loaded('curl')) {
            throw new \RuntimeException('Connection error php-curl not installed');
        }

        if (null === $transferObject) {
            throw new \RuntimeException('Transfer object is null');
        }

        $request = curl_init();
        $data = $transferObject->toJson();

        curl_setopt($request, CURLOPT_URL, $uri);
        curl_setopt($request, CURLOPT_HEADER, 0);
        curl_setopt($request, CURLOPT_FAILONERROR, true);
        curl_setopt($request, CURLOPT_TIMEOUT, 60);
        curl_setopt($request, CURLOPT_CONNECTTIMEOUT, 60);

        switch ($httpMethod) {
            case HttpAdapterInterface::REQUEST_POST:
                curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
            case HttpAdapterInterface::REQUEST_DELETE:
                curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case HttpAdapterInterface::REQUEST_PUT:
                curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'UPDATE');
                break;
            case HttpAdapterInterface::REQUEST_GET: // intended fall through
            default:
                curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'GET');
                break;
        }

//        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_POSTFIELDS, $data);

        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($request, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($request, CURLOPT_SSLVERSION, 6);       // CURL_SSLVERSION_TLSv1_2
        curl_setopt($request, CURLOPT_USERAGENT, 'HeidelpayPHP');

        curl_setopt($request, CURLOPT_HTTPHEADER, array(
            'CUSTOMER-LANGUAGE: en_US', // heidelpay constructor // header object?
            'CHECKOUT-ID: checkout-5aba2fad0ab154.88150279', // heidelpay constructor
            'HEIDELPAY-API-KEY: p-sec-' . $transferObject->getSecret(),
            'SDK-VERSION: PHP - 1.4.1', // heidelpay constructor
            'SHOP-SYSTEM: Shopware - 5.2.2', // heidelpay constructor
            'EXTENSION: heidelpay/magento-cd-edition - 1.5.3' // heidelpay constructor
        ));

        $response = curl_exec($request);
//        $error = curl_error($request);
//        $info = curl_getinfo($request, CURLINFO_HTTP_CODE);

        curl_close($request);

//        if ($error !== null && !empty($error)) {
//            $errorCode =
//                (is_array($info) && array_key_exists('CURLINFO_HTTP_CODE', $info))
//                    ? $info['CURLINFO_HTTP_CODE']
//                    : 'DEF';
//
//            $result = array(
//                'PROCESSING_RESULT' => 'NOK',
//                'PROCESSING_RETURN' => 'Connection error http status ' . $error,
//                'PROCESSING_RETURN_CODE' => 'CON.ERR.' . $errorCode
//            );
//        }
//
//        if (empty($error) && is_string($response)) {
//            parse_str($response, $result);
//        }

        return $response;
    }
}
