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
 *
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
use heidelpay\MgwPhpSdk\Exceptions\IdRequiredToFetchResourceException;
use heidelpay\MgwPhpSdk\Interfaces\HeidelpayResourceInterface;
use heidelpay\MgwPhpSdk\Resources\AbstractHeidelpayResource;

class ResourceService
{
    //<editor-fold desc="CRUD operations">
    /**
     * Create the resource on the api.
     *
     * @param AbstractHeidelpayResource $resource
     * @return AbstractHeidelpayResource
     */
    public function create(AbstractHeidelpayResource $resource): HeidelpayResourceInterface
    {
        $response = $this->send($resource, HttpAdapterInterface::REQUEST_POST);

        $isError = isset($response->isError) && $response->isError;
        if ($isError) {
            return $resource;
        }

        $resource->setId($response->id);

        $resource->handleResponse($response);
        return $resource;
    }

    /**
     * Update the resource on the api.
     *
     * @param AbstractHeidelpayResource $resource
     * @return AbstractHeidelpayResource
     */
    public function update(AbstractHeidelpayResource $resource): HeidelpayResourceInterface
    {
        $response = $this->send($resource, HttpAdapterInterface::REQUEST_PUT);

        $isError = isset($response->isError) && $response->isError;
        if ($isError) {
            return $resource;
        }

        $resource->handleResponse($response);
        return $resource;
    }

    /**
     * @param AbstractHeidelpayResource $resource
     * @return null
     * @throws HeidelpayApiException
     */
    public function delete(AbstractHeidelpayResource $resource)
    {
        if ($resource->getId() === null) {
            throw new IdRequiredToFetchResourceException();
        }

        $this->send($resource, HttpAdapterInterface::REQUEST_DELETE);

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $resource = null;

        return $resource;
    }

    /**
     * Fetch the resource from the api (id must be set).
     *
     * @param AbstractHeidelpayResource $resource
     * @return AbstractHeidelpayResource
     */
    public function fetch(AbstractHeidelpayResource $resource): HeidelpayResourceInterface
    {
        if ($resource->getId() === null) {
            throw new IdRequiredToFetchResourceException();
        }

        $response = $this->send($resource, HttpAdapterInterface::REQUEST_GET);
        $resource->handleResponse($response);
        return $resource;
    }
    //</editor-fold>

    /**
     * @param AbstractHeidelpayResource $resource
     * @param string $httpMethod
     * @return \stdClass
     */
    public function send(AbstractHeidelpayResource $resource, $httpMethod = HttpAdapterInterface::REQUEST_GET): \stdClass
    {
        $responseJson = $resource->getHeidelpayObject()->send($resource->getUri(), $resource, $httpMethod);
        return json_decode($responseJson);
    }
}
