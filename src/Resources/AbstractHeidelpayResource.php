<?php
/**
 * This is the base class for all resource types managed by the api.
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/resources
 */
namespace heidelpay\MgwPhpSdk\Resources;

use heidelpay\MgwPhpSdk\Adapter\HttpAdapterInterface;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Interfaces\HeidelpayParentInterface;
use heidelpay\MgwPhpSdk\Services\ResourceNameService;
use heidelpay\MgwPhpSdk\Services\ResourceService;

abstract class AbstractHeidelpayResource implements HeidelpayParentInterface
{
    /** @var string $id */
    protected $id;

    /** @var HeidelpayParentInterface */
    private $parentResource;

    /** @var \DateTime */
    private $fetchedAt;

    /**
     * @param HeidelpayParentInterface $parent
     * @param string                   $resourceId
     */
    public function __construct($parent = null, $resourceId = null)
    {
        $this->parentResource = $parent;
        $this->id = $resourceId;
    }

    //<editor-fold desc="Getters/Setters">

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        $resourceId = $this->id;
        if (null === $resourceId) {
            $resourceId = $this->getExternalId();
        }
        return $resourceId;
    }

    /**
     * @param int $resourceId
     *
     * @return $this
     */
    public function setId($resourceId): self
    {
        $this->id = $resourceId;
        return $this;
    }

    /**
     * @param HeidelpayParentInterface $parentResource
     *
     * @return $this
     */
    public function setParentResource($parentResource): self
    {
        $this->parentResource = $parentResource;
        return $this;
    }

    /**
     * @return HeidelpayParentInterface
     */
    public function getParentResource(): HeidelpayParentInterface
    {
        return $this->parentResource;
    }

    /**
     * @return \DateTime|null
     */
    public function getFetchedAt()
    {
        return $this->fetchedAt;
    }

    /**
     * @param \DateTime $fetchedAt
     *
     * @return $this
     */
    public function setFetchedAt(\DateTime $fetchedAt): self
    {
        $this->fetchedAt = $fetchedAt;
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="Helpers">

    /**
     * {@inheritDoc}
     */
    public function getHeidelpayObject(): Heidelpay
    {
        $heidelpayObject = $this->parentResource->getHeidelpayObject();

        if (!$heidelpayObject instanceof Heidelpay) {
            throw new \RuntimeException('Heidelpay object reference is not set!');
        }

        return $heidelpayObject;
    }

    /**
     * {@inheritDoc}
     */
    public function getUri($appendId = true): string
    {
        // remove trailing slash and explode
        $uri = [rtrim($this->parentResource->getUri(), '/'), $this->getResourcePath()];
        if ($appendId && $this->getId() !== null) {
            $uri[] = $this->getId();
        }

        $uri[] = '';

        return implode('/', $uri);
    }

    /**
     * This method updates the properties of the resource.
     *
     * @param $object
     * @param \stdClass $response
     */
    private function updateValues($object, \stdClass $response)
    {
        foreach ($response as $key => $value) {
            $newValue = $value ?: null;
            $setter = 'set' . ucfirst($key);
            $getter = 'get' . ucfirst($key);
            if (\is_object($value)) {
                if (\is_callable([$object, $getter])) {
                    $this->updateValues($object->$getter(), $newValue);
                } elseif ('processing' === $key) {
                    $this->updateValues($object, $newValue);
                }
            } elseif (\is_callable([$object, $setter])) {
                $object->$setter($newValue);
            }
        }
    }

    //</editor-fold>

    //<editor-fold desc="Resource service facade">

    /**
     * @return ResourceService
     *
     * @throws \RuntimeException
     */
    private function getResourceService(): ResourceService
    {
        return $this->getHeidelpayObject()->getResourceService();
    }

    /**
     * Fetches the Resource if necessary.
     *
     * @param AbstractHeidelpayResource $resource
     *
     * @return AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function getResource(AbstractHeidelpayResource $resource): AbstractHeidelpayResource
    {
        return $this->getResourceService()->getResource($resource);
    }

    /**
     * Fetch the given resource object.
     *
     * @param AbstractHeidelpayResource $resource
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function fetchResource(AbstractHeidelpayResource $resource)
    {
        $this->getResourceService()->fetch($resource);
    }

    /**
     * @param string $url
     * @param string $typePattern
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getResourceIdFromUrl($url, $typePattern): string
    {
        return $this->getResourceService()->getResourceIdFromUrl($url, $typePattern);
    }

    //</editor-fold>

    //<editor-fold desc="Serialization">

    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return json_encode($this->expose(), JSON_FORCE_OBJECT);
    }

    /**
     * Creates an array containing all properties to be exposed to the heidelpay api as resource parameters.
     *
     * @return array
     */
    public function expose(): array
    {
        // Add resources properties
        $properties = get_object_vars($this);
        foreach ($properties as $property => $value) {
            try {
                $reflection = new \ReflectionProperty(static::class, $property);
                if (($property === 'id' && empty($value)) || !$reflection->isProtected()) {
                    unset($properties[$property]);
                    continue;
                }

                if ($value === null) {
                    unset($properties[$property]);
                } else {
                    if ($value instanceof self) {
                        $newValue = $value->expose();
                    } else {
                        $newValue = (string)$value;
                    }
                    $properties[$property] = $newValue;
                }
            } catch (\ReflectionException $e) {
                unset($properties[$property]);
            }
        }
        //---------------------

        // Add linked resources if any
        $resources = [];
        /**
         * @var string                    $key
         * @var AbstractHeidelpayResource $linkedResource
         */
        foreach ($this->getLinkedResources() as $key => $linkedResource) {
            $resources[$key . 'Id'] = $linkedResource ? $linkedResource->getId() : '';
        }

        if (\count($resources) > 0) {
            ksort($resources);
            $properties['resources'] = $resources;
        }
        //---------------------

        ksort($properties);
        return $properties;
    }

    //</editor-fold>

    //<editor-fold desc="Overridable Methods">

    /**
     * Return the resources which should be referenced by Id within the resource section of the resource data.
     * Override this to define the linked resources.
     *
     * @return array
     */
    public function getLinkedResources(): array
    {
        return [];
    }

    /**
     * This returns the path of this resource within the parent resource.
     * Override this if the path does not match the class name.
     *
     * @return null
     */
    protected function getResourcePath()
    {
        return ResourceNameService::getClassShortNameKebapCase(static::class);
    }

    /**
     * This method is called to handle the response from a crud command.
     * Override it to handle the data correctly.
     *
     * @param \stdClass $response
     * @param string    $method
     */
    public function handleResponse(\stdClass $response, $method = HttpAdapterInterface::REQUEST_GET)
    {
        $this->updateValues($this, $response);
    }

    /**
     * Returns the externalId of a resource if the resource supports to be loaded by it.
     * Override this in the resource class.
     *
     * @return string|null
     */
    public function getExternalId()
    {
        return null;
    }

    //</editor-fold>
}
