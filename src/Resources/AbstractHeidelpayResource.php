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
namespace heidelpay\NmgPhpSdk\Resources;

use heidelpay\NmgPhpSdk\Adapter\HttpAdapterInterface;
use heidelpay\NmgPhpSdk\Exceptions\HeidelpayObjectMissingException;
use heidelpay\NmgPhpSdk\Exceptions\IdRequiredToFetchResourceException;
use heidelpay\NmgPhpSdk\Heidelpay;
use heidelpay\NmgPhpSdk\Interfaces\HeidelpayParentInterface;
use heidelpay\NmgPhpSdk\Interfaces\HeidelpayResourceInterface;

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

    //<editor-fold desc="CRUD">

    /**
     * {@inheritDoc}
     */
    public function create(): HeidelpayResourceInterface
    {
        $response = $this->send(HttpAdapterInterface::REQUEST_POST);

        $isError = isset($response->isError) && $response->isError;
        if ($isError) {
            return $this;
        }

        $this->setId($response->id);

        $this->handleResponse($response);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function update(): HeidelpayResourceInterface
    {
        $response = $this->send(HttpAdapterInterface::REQUEST_PUT);

        $isError = isset($response->isError) && $response->isError;
        if ($isError) {
            return $this;
        }

        $this->handleResponse($response);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function delete()
    {
        if ($this->id === null) {
            throw new IdRequiredToFetchResourceException();
        }

        $response = $this->send(HttpAdapterInterface::REQUEST_DELETE);

        $isError = isset($response->isError) && $response->isError;
        if ($isError) {
            return $this;
        }

        $this->handleResponse($response);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(): HeidelpayResourceInterface
    {
        if ($this->id === null) {
            throw new IdRequiredToFetchResourceException();
        }

        $response = $this->send(HttpAdapterInterface::REQUEST_GET);
        $this->handleResponse($response);
        return $this;
    }
    //</editor-fold>

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
     * @return AbstractHeidelpayResource
     */
    public function setId($id): AbstractHeidelpayResource
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
     * @param string $httpMethod
     * @throws \RuntimeException
     * @return \stdClass
     */
    public function send($httpMethod = HttpAdapterInterface::REQUEST_GET): \stdClass
    {
        $responseJson = $this->getHeidelpayObject()->send(
            $this->getUri(),
            $this,
            $httpMethod
        );
        return json_decode($responseJson);
    }

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
    protected function handleResponse(\stdClass $response)
    {
        $this->updateValues($this, $response);
    }

    /**
     * @param $object
     * @param \stdClass $response
     */
    private function updateValues($object, \stdClass $response)
    {
        foreach ($response as $key => $value) {
            $setter = 'set' . ucfirst($key);
            $getter = 'get' . ucfirst($key);
            if (\is_object($value)) {
                if (\is_callable([$object, $getter])) {
                    $this->updateValues($object->$getter(), $value);
                }
            } else if (\is_callable([$object, $setter])) {
                $object->$setter($value);
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
