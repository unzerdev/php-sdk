<?php
/**
 * This is the base class for all resource types managed by the api.
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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\Resources
 */
namespace heidelpayPHP\Resources;

use DateTime;
use heidelpayPHP\Adapter\HttpAdapterInterface;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Interfaces\HeidelpayParentInterface;
use heidelpayPHP\Services\ResourceNameService;
use heidelpayPHP\Services\ResourceService;
use heidelpayPHP\Services\ValueService;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;
use stdClass;
use function count;
use function is_array;
use function is_callable;
use function is_float;
use function is_object;

abstract class AbstractHeidelpayResource implements HeidelpayParentInterface
{
    /** @var string $id */
    protected $id;

    /** @var HeidelpayParentInterface */
    private $parentResource;

    /** @var DateTime */
    private $fetchedAt;

    /** @var array $specialParams */
    private $specialParams = [];

    /** @var array $additionalAttributes */
    protected $additionalAttributes = [];

    //<editor-fold desc="Getters/Setters">

    /**
     * Returns the API name of the resource.
     *
     * @return string
     */
    public static function getResourceName(): string
    {
        return ResourceNameService::getClassShortNameKebapCase(static::class);
    }

    /**
     * Returns the id of this resource.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * This setter must be public to enable fetching a resource by setting the id and then call fetch.
     *
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
     *
     * @throws RuntimeException
     */
    public function getParentResource(): HeidelpayParentInterface
    {
        if (!$this->parentResource instanceof HeidelpayParentInterface) {
            throw new RuntimeException('Parent resource reference is not set!');
        }
        return $this->parentResource;
    }

    /**
     * @return DateTime|null
     */
    public function getFetchedAt(): ?DateTime
    {
        return $this->fetchedAt;
    }

    /**
     * @param DateTime $fetchedAt
     *
     * @return self
     */
    public function setFetchedAt(DateTime $fetchedAt): self
    {
        $this->fetchedAt = $fetchedAt;
        return $this;
    }

    /**
     * Returns an array of additional params which can be added to the resource request.
     *
     * @return array
     */
    public function getSpecialParams(): array
    {
        return $this->specialParams;
    }

    /**
     * Sets the array of additional params which are to be added to the resource request.
     *
     * @param array $specialParams
     *
     * @return self
     */
    public function setSpecialParams(array $specialParams): self
    {
        $this->specialParams = $specialParams;
        return $this;
    }

    /**
     * @param array $additionalAttributes
     *
     * @return AbstractHeidelpayResource
     */
    protected function setAdditionalAttributes(array $additionalAttributes): AbstractHeidelpayResource
    {
        $this->additionalAttributes = $additionalAttributes;
        return $this;
    }

    /**
     * Adds the given value to the additionalAttributes array if it is not set yet.
     * Overwrites the given value if it already exists.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return AbstractHeidelpayResource
     */
    protected function setAdditionalAttribute(string $attribute, $value): AbstractHeidelpayResource
    {
        $this->additionalAttributes[$attribute] = $value;
        return $this;
    }

    /**
     * Returns the value of the given attribute or null if it is not set.
     *
     * @param string $attribute
     *
     * @return mixed
     */
    protected function getAdditionalAttribute(string $attribute)
    {
        return $this->additionalAttributes[$attribute] ?? null;
    }

    //</editor-fold>

    //<editor-fold desc="Helpers">

    /**
     * {@inheritDoc}
     */
    public function getHeidelpayObject(): Heidelpay
    {
        return $this->getParentResource()->getHeidelpayObject();
    }

    /**
     * Fetches the parent URI and combines it with the uri of the current resource.
     * If appendId is set the id of the current resource will be appended if it is set.
     * The flag appendId is always set for getUri of the parent resource.
     *
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function getUri($appendId = true): string
    {
        $uri = [rtrim($this->getParentResource()->getUri(), '/'), $this->getResourcePath()];
        if ($appendId) {
            if ($this->getId() !== null) {
                $uri[] = $this->getId();
            } elseif ($this->getExternalId() !== null) {
                $uri[] = $this->getExternalId();
            }
        }

        return implode('/', $uri);
    }

    /**
     * This method updates the properties of the resource.
     *
     * @param $object
     * @param stdClass $response
     */
    private static function updateValues($object, stdClass $response): void
    {
        foreach ($response as $key => $value) {
            // set empty string to null (workaround)
            $newValue = $value === '' ? null : $value;

            // handle nested object
            if (is_object($value)) {
                $getter = 'get' . ucfirst($key);
                if (is_callable([$object, $getter])) {
                    self::updateValues($object->$getter(), $newValue);
                } elseif ($key === 'processing') {
                    self::updateValues($object, $newValue);
                }
                continue;
            }

            // handle nested array
            if (is_array($value)) {
                $firstItem = reset($value);
                if (is_object($firstItem)) {
                    // Handled by the owning object since we do not know the type of the items here.
                    continue;
                }
            }

            // handle basic types
            self::setItemProperty($object, $key, $newValue);
        }
    }

    //</editor-fold>

    //<editor-fold desc="Resource service facade">

    /**
     * @return ResourceService
     *
     * @throws RuntimeException
     */
    private function getResourceService(): ResourceService
    {
        return $this->getHeidelpayObject()->getResourceService();
    }

    /**
     * Fetches the Resource if it has not been fetched yet and the id is set.
     *
     * @param AbstractHeidelpayResource $resource
     *
     * @return AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    protected function getResource(AbstractHeidelpayResource $resource): AbstractHeidelpayResource
    {
        return $this->getResourceService()->getResource($resource);
    }

    /**
     * Fetch the given resource object.
     *
     * @param AbstractHeidelpayResource $resource
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    protected function fetchResource(AbstractHeidelpayResource $resource): void
    {
        $this->getResourceService()->fetchResource($resource);
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
        return json_encode($this->expose(), JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * Creates an array containing all properties to be exposed to the heidelpay api as resource parameters.
     *
     * @return array|stdClass
     */
    public function expose()
    {
        // Add resources properties
        $properties = get_object_vars($this);
        foreach ($properties as $property => $value) {
            if (self::propertyShouldBeSkipped($property, $value)) {
                unset($properties[$property]);
                continue;
            }

            // expose child objects if possible
            if ($value instanceof self) {
                $value = $value->expose();
            }

            // reduce floats to 4 decimal places and update the property in object
            if (is_float($value)) {
                $value = ValueService::limitFloats($value);
                self::setItemProperty($this, $property, $value);
            }

            // handle additional values
            if ($property === 'additionalAttributes') {
                if (!is_array($value) || empty($value)) {
                    unset($properties[$property]);
                    continue;
                }
                $value = $this->exposeAdditionalAttributes($value);
            }

            $properties[$property] = $value;
        }
        //---------------------

        // Add linked resources if any
        $resources = [];
        /**
         * @var string                    $attributeName
         * @var AbstractHeidelpayResource $linkedResource
         */
        foreach ($this->getLinkedResources() as $attributeName => $linkedResource) {
            $resources[$attributeName . 'Id'] = $linkedResource ? $linkedResource->getId() : '';
        }

        if (count($resources) > 0) {
            ksort($resources);
            $properties['resources'] = $resources;
        }
        //---------------------

        // Add special params if any
        foreach ($this->getSpecialParams() as $attributeName => $specialParam) {
            $properties[$attributeName] = $specialParam;
        }
        //---------------------

        ksort($properties);
        return count($properties) > 0 ? $properties : new stdClass();
    }

    /**
     * @param $value
     *
     * @return array
     */
    private function exposeAdditionalAttributes($value): array
    {
        foreach ($value as $attributeName => $attributeValue) {
            $attributeValue        = ValueService::limitFloats($attributeValue);
            $value[$attributeName] = $attributeValue;
            $this->setAdditionalAttribute($attributeName, $attributeValue);
        }
        return $value;
    }

    /**
     * Returns true if the given property should be skipped.
     *
     * @param $property
     * @param $value
     *
     * @return bool
     */
    private static function propertyShouldBeSkipped($property, $value): bool
    {
        $skipProperty = false;

        try {
            $reflection = new ReflectionProperty(static::class, $property);
            if ($value === null ||                          // do not send properties that are set to null
                ($property === 'id' && empty($value)) ||    // do not send id property if it is empty
                !$reflection->isProtected()) {              // only send protected properties
                $skipProperty = true;
            }
        } /** @noinspection BadExceptionsProcessingInspection */
        catch (ReflectionException $e) {
            $skipProperty = true;
        }

        return $skipProperty;
    }

    /**
     * Can not be moved to service since setters and getters are most likely private.
     *
     * @param $item
     * @param $key
     * @param $value
     */
    private static function setItemProperty($item, $key, $value): void
    {
        $setter = 'set' . ucfirst($key);
        if (!is_callable([$item, $setter])) {
            $setter = 'add' . ucfirst($key);
        }
        if (is_callable([$item, $setter])) {
            $item->$setter($value);
        }
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
     * @return string
     */
    protected function getResourcePath(): string
    {
        return self::getResourceName();
    }

    /**
     * This method is called to handle the response from a crud command.
     * Override it to handle the data correctly.
     *
     * @param stdClass $response
     * @param string   $method
     * @noinspection PhpUnusedParameterInspection
     */
    public function handleResponse(stdClass $response, $method = HttpAdapterInterface::REQUEST_GET): void
    {
        self::updateValues($this, $response);

        // Todo: Workaround to be removed when API sends TraceID in processing-group
        if (
            isset($response->resources->traceId) &&
            is_callable([$this, 'setTraceId']) &&
            is_callable([$this, 'getTraceId']) &&
            $this->getTraceId() === null
        ) {
            $this->setTraceId($response->resources->traceId);
        }
        // Todo: Workaround end
    }

    /**
     * Returns the externalId of a resource if the resource supports to be loaded by it.
     * Override this in the resource class.
     *
     * @return string|null
     */
    public function getExternalId(): ?string
    {
        return null;
    }

    //</editor-fold>
}
