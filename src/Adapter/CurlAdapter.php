<?php
/**
 * By default this adapter will be used for communication however a custom adapter implementing the
 * HttpAdapterInterface can be used.
 *
 * Copyright (C) 2018 Heidelpay GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link http://dev.heidelpay.com/
 *
 * @author Simon Gabriel <development@heidelpay.com>
 *
 * @package heidelpay/mgw_sdk/tests/integration
 */
namespace heidelpay\MgwPhpSdk\Adapter;

use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Interfaces\DebugHandlerInterface;
use heidelpay\MgwPhpSdk\Resources\AbstractHeidelpayResource;

class CurlAdapter implements HttpAdapterInterface
{
    /**
     * send post request to payment server
     *
     * @param $uri string url of the target system
     * @param AbstractHeidelpayResource $heidelpayResource
     * @param string                    $httpMethod
     *
     * @return string
     *
     * @throws \RuntimeException
     * @throws HeidelpayApiException
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
            throw new \RuntimeException('Transfer object is empty');
        }

        $request = $this->initCurlRequest($uri, $heidelpayResource, $httpMethod);

        $response = curl_exec($request);
        $info = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);

        $heidelpayObj = $heidelpayResource->getHeidelpayObject();
        if ($heidelpayObj->isDebugMode() &&
            $heidelpayObj->getDebugHandler() instanceof DebugHandlerInterface) {
            $resourceJson = $heidelpayResource->jsonSerialize();
            $handler = $heidelpayObj->getDebugHandler();
            $handler->log('Curl ' . strip_tags($httpMethod) . '-Request: ' . strip_tags($uri));
            $handler->log('Request: ' . strip_tags($resourceJson));
            $handler->log('Response: ' . strip_tags(json_encode(json_decode($response))));
        }

        $this->handleErrors($info, $response);

        return $response;
    }

    /**
     * Handles error responses by throwing a HeidelpayApiException with the returned messages and error code.
     * Returns doing nothing if no error occurred.
     *
     * @param $info
     * @param $response
     *
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException
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
     * Creates and returns the curl request
     *
     * @param $uri
     * @param AbstractHeidelpayResource $heidelpayResource
     * @param $httpMethod
     *
     * @return mixed
     *
     * @throws \RuntimeException
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
        ));
        return $request;
    }
}
