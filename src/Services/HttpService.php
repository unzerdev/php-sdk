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
namespace heidelpay\MgwPhpSdk\Services;

use heidelpay\MgwPhpSdk\Adapter\CurlAdapter;
use heidelpay\MgwPhpSdk\Adapter\HttpAdapterInterface;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Interfaces\DebugHandlerInterface;
use heidelpay\MgwPhpSdk\Resources\AbstractHeidelpayResource;

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
            throw new \RuntimeException('Transfer object is empty');
        }

        // perform request
        $this->initRequest($url, $resource, $httpMethod);
        $response = $this->getAdapter()->execute();
        $responseCode = $this->getAdapter()->getResponseCode();
        $this->getAdapter()->close();

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
        $httpAdapter->setUserAgent('HeidelpayPHP');
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
     * @param $info
     * @param $response
     *
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException
     */
    private function handleErrors($info, $response)
    {
        if ($response === null) {
            throw new HeidelpayApiException('The Request returned a null response!');
        }

        $responseArray = json_decode($response);
        if ($info >= 400 || isset($responseArray->errors)) {
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
     * @param $httpMethod
     * @param string $url
     * @param string $response
     *
     * @throws \RuntimeException
     */
    public function debugLog(AbstractHeidelpayResource $resource, $httpMethod, string $url, string $response)
    {
        $heidelpayObj = $resource->getHeidelpayObject();
        if ($heidelpayObj->isDebugMode()) {
            $debugHandler = $heidelpayObj->getDebugHandler();
            if ($debugHandler instanceof DebugHandlerInterface) {
                $resourceJson = $resource->jsonSerialize();
                $debugHandler->log('Http ' . strip_tags($httpMethod) . '-Request: ' . strip_tags($url));
                $debugHandler->log('Request: ' . strip_tags($resourceJson));
                $debugHandler->log('Response: ' . strip_tags(json_encode(json_decode($response))));
            }
        }
    }
}
