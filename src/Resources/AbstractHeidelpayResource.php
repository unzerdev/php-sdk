<?php
/**
 * This is the base class for all resource types managed by the api.
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
 * @package  heidelpay/mgw_sdk/resources
 */
namespace heidelpay\MgwPhpSdk\Resources;

use heidelpay\MgwPhpSdk\Exceptions\HeidelpayObjectMissingException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Interfaces\HeidelpayParentInterface;
use heidelpay\MgwPhpSdk\Interfaces\HeidelpayResourceInterface;

abstract class AbstractHeidelpayResource implements HeidelpayResourceInterface, HeidelpayParentInterface
{
    /** @var string $id */
    protected $id;

    /** @var HeidelpayParentInterface */
    private $parentResource;

    /**
     * @param HeidelpayParentInterface $parent
     * @param string $id
     *
     * todo: wird das noch gebraucht?
     */
    public function __construct($parent = null, $id = null)
    {
        $this->parentResource = $parent;
        $this->id = $id;
    }

    //<editor-fold desc="Getters/Setters">
    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param HeidelpayParentInterface $parentResource
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
    //</editor-fold>

    //<editor-fold desc="Serialization">
    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
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
         * @var string $key
         * @var HeidelpayResourceInterface $linkedResource
         */
        foreach ($this->getLinkedResources() as $key=>$linkedResource) {
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

    /**
     * {@inheritDoc}
     */
    public function getHeidelpayObject(): Heidelpay
    {
        $heidelpayObject = $this->parentResource->getHeidelpayObject();

        if (!$heidelpayObject instanceof Heidelpay) {
            throw new HeidelpayObjectMissingException();
        }

        return $heidelpayObject;
    }

    /**
     * {@inheritDoc}
     */
    public function getUri(): string
    {
        // remove trailing slash and explode
        $uri = [rtrim($this->parentResource->getUri(), '/'), $this->getResourcePath()];
        if ($this->getId() !== null) {
            $uri[] = $this->getId();
        }

        $uri[] = '';

        return implode('/', $uri);
    }

    //<editor-fold desc="Optional Methods">
    /**
     * Return the resources which should be referenced by Id within the resource section of the resource data.
     * Override this to define the linked resources.
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
        return strtolower(self::getClassShortName());
    }

    /**
     * This method is called to handle the response from a crud command.
     * Override it to handle the data correctly.
     *
     * @param \stdClass $response
     */
    public function handleResponse(\stdClass $response)
    {
        $this->updateValues($this, $response);
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
                }
            } else if (\is_callable([$object, $setter])) {
                $object->$setter($newValue);
            }
        }
    }
    //</editor-fold>

    //<editor-fold desc="Private helper">
    /**
     * Return class short name.
     *
     * @return string
     */
    protected static function getClassShortName(): string
    {
        $classNameParts = explode('\\', static::class);
        return strtolower(end($classNameParts));
    }
    //</editor-fold>
}
