<?php
/**
 * Description
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/${Package}
 */
namespace heidelpay\NmgPhpSdk\Service;

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
