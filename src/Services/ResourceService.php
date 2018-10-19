<?php
/**
 * This service provides for all methods to manage resources with the api.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/services
 */
namespace heidelpay\MgwPhpSdk\Services;

use heidelpay\MgwPhpSdk\Adapter\HttpAdapterInterface;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException;
use heidelpay\MgwPhpSdk\Interfaces\HeidelpayResourceInterface;
use heidelpay\MgwPhpSdk\Resources\AbstractHeidelpayResource;

class ResourceService
{
    //<editor-fold desc="CRUD operations">

    /**
     * Create the resource on the api.
     *
     * @param AbstractHeidelpayResource $resource
     *
     * @return AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function create(AbstractHeidelpayResource $resource): HeidelpayResourceInterface
    {
        $method = HttpAdapterInterface::REQUEST_POST;
        $response = $this->send($resource, $method);

        $isError = isset($response->isError) && $response->isError;
        if ($isError) {
            return $resource;
        }

        $resource->setId($response->id);

        $resource->handleResponse($response, $method);
        return $resource;
    }

    /**
     * Update the resource on the api.
     *
     * @param AbstractHeidelpayResource $resource
     *
     * @return AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function update(AbstractHeidelpayResource $resource): HeidelpayResourceInterface
    {
        $method = HttpAdapterInterface::REQUEST_PUT;
        $response = $this->send($resource, $method);

        $isError = isset($response->isError) && $response->isError;
        if ($isError) {
            return $resource;
        }

        $resource->handleResponse($response, $method);
        return $resource;
    }

    /**
     * @param AbstractHeidelpayResource $resource
     *
     * @return null
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function delete(AbstractHeidelpayResource $resource)
    {
        $this->send($resource, HttpAdapterInterface::REQUEST_DELETE);

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $resource = null;

        return $resource;
    }

    /**
     * Fetch the resource from the api (id must be set).
     *
     * @param AbstractHeidelpayResource $resource
     *
     * @return AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function fetch(AbstractHeidelpayResource $resource): HeidelpayResourceInterface
    {
        $method = HttpAdapterInterface::REQUEST_GET;
        $response = $this->send($resource, $method);
        $resource->setFetchedAt(new \DateTime('now'));
        $resource->handleResponse($response, $method);
        return $resource;
    }

    //</editor-fold>

    /**
     * Send request to API.
     *
     * @param AbstractHeidelpayResource $resource
     * @param string                    $httpMethod
     *
     * @return \stdClass
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function send(AbstractHeidelpayResource $resource, $httpMethod = HttpAdapterInterface::REQUEST_GET): \stdClass
    {
        $responseJson = $resource->getHeidelpayObject()->send($resource->getUri(), $resource, $httpMethod);
        return json_decode($responseJson);
    }

    /**
     * @param $transaction
     * @param $pattern
     *
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public function getResourceId($transaction, $pattern)
    {
        $matches = [];
        preg_match('~\/([s|p]{1}-' . $pattern . '-[\d]+)~', $transaction->url, $matches);

        if (\count($matches) < 2) {
            throw new \RuntimeException('Id not found!');
        }

        return $matches[1];
    }

    /**
     * Fetches the Resource if necessary.
     *
     * @param AbstractHeidelpayResource $resource
     *
     * @return AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function getResource(AbstractHeidelpayResource $resource): AbstractHeidelpayResource
    {
        if ($resource->getFetchedAt() === null && $resource->getId() !== null) {
            $this->fetch($resource);
        }
        return $resource;
    }
}
