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
 * @package  heidelpay/mgw_sdk/services
 */
namespace heidelpayPHP\Services;

use heidelpayPHP\Adapter\CurlAdapter;
use heidelpayPHP\Adapter\HttpAdapterInterface;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Interfaces\DebugHandlerInterface;
use heidelpayPHP\Resources\AbstractHeidelpayResource;

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
     * @throws \RuntimeException
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
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function send(
        $uri = null,
        AbstractHeidelpayResource $resource = null,
        $httpMethod = HttpAdapterInterface::REQUEST_GET
    ): string {
        $url = Heidelpay::BASE_URL . Heidelpay::API_VERSION . $uri;

        if (null === $resource) {
            throw new \RuntimeException('Transfer object is empty!');
        }

        // perform request
        $this->initRequest($url, $resource, $httpMethod);
        $httpAdapter  = $this->getAdapter();
        $response     = $httpAdapter->execute();
        $responseCode = $httpAdapter->getResponseCode();
        $httpAdapter->close();

        // handle response
        $this->debugLog($resource, $httpMethod, $url, $response);
        $this->handleErrors($responseCode, $response);

        return $response;
    }

    /**
     * Initializes and returns the http request object.
     *
     * @param $uri
     * @param AbstractHeidelpayResource $heidelpayResource
     * @param $httpMethod
     *
     * @throws \RuntimeException
     */
    private function initRequest($uri, AbstractHeidelpayResource $heidelpayResource, $httpMethod)
    {
        $httpAdapter = $this->getAdapter();
        $httpAdapter->init($uri, $heidelpayResource->jsonSerialize(), $httpMethod);
        $httpAdapter->setUserAgent(Heidelpay::SDK_TYPE);
        $httpAdapter->setHeaders(
            [
                'Authorization' => 'Basic ' . base64_encode($heidelpayResource->getHeidelpayObject()->getKey() . ':'),
                'Content-Type'  => 'application/json',
                'SDK-VERSION'   => Heidelpay::SDK_VERSION
            ]
        );
    }

    /**
     * Handles error responses by throwing a HeidelpayApiException with the returned messages and error code.
     * Returns doing nothing if no error occurred.
     *
     * @param string      $responseCode
     * @param string|null $response
     *
     * @throws \heidelpayPHP\Exceptions\HeidelpayApiException
     */
    private function handleErrors($responseCode, $response)
    {
        if ($response === null) {
            throw new HeidelpayApiException('The Request returned a null response!');
        }

        $responseArray = json_decode($response);
        if ($responseCode >= 400 || isset($responseArray->errors)) {
            $merchantMessage = $customerMessage = $code = null;
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
     * @param AbstractHeidelpayResource $resource
     * @param string                    $httpMethod
     * @param string                    $url
     * @param string|null               $response
     *
     * @throws \RuntimeException
     */
    public function debugLog(AbstractHeidelpayResource $resource, $httpMethod, string $url, $response)
    {
        $heidelpayObj = $resource->getHeidelpayObject();
        if ($heidelpayObj->isDebugMode()) {
            $debugHandler = $heidelpayObj->getDebugHandler();
            if ($debugHandler instanceof DebugHandlerInterface) {
                $resourceJson = $resource->jsonSerialize();
                $debugHandler->log('Http ' . $httpMethod . '-Request: ' . $url);
                $debugHandler->log('Request: ' . $resourceJson);
                $debugHandler->log('Response: ' . $response);
            }
        }
    }
}
