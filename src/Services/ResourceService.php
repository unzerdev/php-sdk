<?php
/**
 * This service provides for all methods to manage resources with the api.
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/ngw_sdk/services
 */
namespace heidelpay\NmgPhpSdk\Services;

use heidelpay\NmgPhpSdk\Adapter\HttpAdapterInterface;
use heidelpay\NmgPhpSdk\Exceptions\IdRequiredToFetchResourceException;
use heidelpay\NmgPhpSdk\Interfaces\HeidelpayResourceInterface;
use heidelpay\NmgPhpSdk\Resources\AbstractHeidelpayResource;

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
     * @return AbstractHeidelpayResource|null
     */
    public function delete(AbstractHeidelpayResource $resource)
    {
        if ($resource->getId() === null) {
            throw new IdRequiredToFetchResourceException();
        }

        $this->send($resource, HttpAdapterInterface::REQUEST_DELETE);

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
