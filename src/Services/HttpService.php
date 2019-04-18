<?php
/**
 * This service provides for functionalities concerning http transactions.
 *
 * Copyright (C) 2018 heidelpay GmbH
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/services
 */
namespace heidelpayPHP\Services;

use heidelpayPHP\Adapter\CurlAdapter;
use heidelpayPHP\Adapter\HttpAdapterInterface;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\AbstractHeidelpayResource;
use RuntimeException;

class HttpService
{
    /** @var HttpAdapterInterface $httpAdapter */
    private $httpAdapter;

    /**
     * Returns the currently set HttpAdapter.
     * If it is not set it will create a CurlRequest by default and return it.
     *
     * @return HttpAdapterInterface
     *
     * @throws RuntimeException
     */
    public function getAdapter(): HttpAdapterInterface
    {
        if (!$this->httpAdapter instanceof HttpAdapterInterface) {
            $this->httpAdapter = new CurlAdapter();
        }
        return $this->httpAdapter;
    }

    /**
     * @param HttpAdapterInterface $httpAdapter
     *
     * @return HttpService
     */
    public function setHttpAdapter(HttpAdapterInterface $httpAdapter): HttpService
    {
        $this->httpAdapter = $httpAdapter;
        return $this;
    }

    /**
     * send post request to payment server
     *
     * @param $uri string url of the target system
     * @param AbstractHeidelpayResource $resource
     * @param string                    $httpMethod
     *
     * @return string
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function send(
        $uri = null,
        AbstractHeidelpayResource $resource = null,
        $httpMethod = HttpAdapterInterface::REQUEST_GET
    ): string {
        $url = Heidelpay::BASE_URL . Heidelpay::API_VERSION . $uri;

        if (!$resource instanceof AbstractHeidelpayResource) {
            throw new RuntimeException('Transfer object is empty!');
        }
        $heidelpayObj = $resource->getHeidelpayObject();

        // perform request
        $payload = $resource->jsonSerialize();
        $this->initRequest($heidelpayObj, $url, $payload, $httpMethod);
        $httpAdapter  = $this->getAdapter();
        $response     = $httpAdapter->execute();
        $responseCode = $httpAdapter->getResponseCode();
        $httpAdapter->close();

        // handle response
        try {
            $this->handleErrors($responseCode, $response);
        } finally {
            $this->debugLog($heidelpayObj, $payload, $responseCode, $httpMethod, $url, $response);
        }

        return $response;
    }

    /**
     * Initializes and returns the http request object.
     *
     * @param Heidelpay $heidelpay
     * @param string    $uri
     * @param string    $payload
     * @param string    $httpMethod
     *
     * @throws RuntimeException
     */
    private function initRequest(Heidelpay $heidelpay, $uri, $payload, $httpMethod)
    {
        $httpAdapter = $this->getAdapter();
        $httpAdapter->init($uri, $payload, $httpMethod);
        $httpAdapter->setUserAgent(Heidelpay::SDK_TYPE);

        // Set HTTP-headers
        $locale      = $heidelpay->getLocale();
        $key         = $heidelpay->getKey();
        $httpHeaders = [
            'Authorization' => 'Basic ' . base64_encode($key . ':'),
            'Content-Type'  => 'application/json',
            'SDK-VERSION'   => Heidelpay::SDK_VERSION
        ];
        /** @noinspection IsEmptyFunctionUsageInspection */
        if (!empty($locale)) {
            $httpHeaders['Accept-Language'] = $locale;
        }
        $httpAdapter->setHeaders($httpHeaders);
    }

    /**
     * Handles error responses by throwing a HeidelpayApiException with the returned messages and error code.
     * Returns doing nothing if no error occurred.
     *
     * @param string      $responseCode
     * @param string|null $response
     *
     * @throws HeidelpayApiException
     */
    private function handleErrors($responseCode, $response)
    {
        if ($response === null) {
            throw new HeidelpayApiException('The Request returned a null response!');
        }

        $responseArray = json_decode($response);
        if ($responseCode >= 400 || isset($responseArray->errors)) {
            $code            = null;
            $customerMessage = $code;
            $merchantMessage = $customerMessage;
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
     * @param Heidelpay   $heidelpayObj
     * @param string      $payload
     * @param int         $responseCode
     * @param string      $httpMethod
     * @param string      $url
     * @param string|null $response
     */
    public function debugLog(Heidelpay $heidelpayObj, $payload, $responseCode, $httpMethod, string $url, $response)
    {
        $heidelpayObj->debugLog($httpMethod . ': ' . $url);
        $writingOperations = [HttpAdapterInterface::REQUEST_POST, HttpAdapterInterface::REQUEST_PUT];
        if (in_array($httpMethod, $writingOperations, true)) {
            $heidelpayObj->debugLog('Request: ' . $payload);
        }
        $heidelpayObj->debugLog('Response: (' . $responseCode . ') ' . json_encode(json_decode($response)));
    }
}
