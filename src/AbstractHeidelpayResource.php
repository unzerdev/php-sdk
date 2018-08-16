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
namespace heidelpay\NmgPhpSdk;

use heidelpay\NmgPhpSdk\Adapter\HttpAdapterInterface;
use heidelpay\NmgPhpSdk\Exceptions\HeidelpayObjectMissingException;
use heidelpay\NmgPhpSdk\Exceptions\IdRequiredToFetchResourceException;

abstract class AbstractHeidelpayResource implements HeidelpayResourceInterface, HeidelpayParentInterface
{
    /** @var string $id */
    protected $id = '';

    /** @var HeidelpayParentInterface */
    private $parentResource;

    /**
     * @param HeidelpayParentInterface $parent
     * @param string $id
     */
    public function __construct(HeidelpayParentInterface $parent, $id = '')
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
        $this->setId($response->id);

        $this->handleResponse($response);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function update(): HeidelpayResourceInterface
    {
//        $this->send(HttpAdapterInterface::REQUEST_PUT);

        // todo: update resource

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function delete()
    {
        if (empty($this->id)) {
            throw new IdRequiredToFetchResourceException();
        }

//        $this->send(HttpAdapterInterface::REQUEST_DELETE);

        // todo: What to do here?
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(): HeidelpayResourceInterface
    {
        if (empty($this->id)) {
            throw new IdRequiredToFetchResourceException();
        }

//        $this->send(HttpAdapterInterface::REQUEST_GET);

        // todo: update resource

        return $this;
    }
    //</editor-fold>

    //<editor-fold desc="Getters/Setters">
    /**
     * {@inheritDoc}
     */
    public function getId(): string
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
     * @return AbstractHeidelpayResource
     */
    public function setParentResource($parentResource): AbstractHeidelpayResource
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
                    unset($properties[$property]);//$properties[$property] = '';
                } else {
                    $properties[$property] = (string)$value;
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
        if (!empty($this->getId())) {
            $uri[] = $this->getId();
        }

        $uri[] = '';

        return implode('/', $uri);
    }

    /**
     * Return the payment object stored in heidelpay object.
     *
     * @return Payment|null
     */
    public function getPayment()
    {
        return $this->getHeidelpayObject()->getPayment();
    }

    /**
     * Return class short name.
     *
     * @return string
     */
    protected static function getClassShortName(): string
    {
        $classNameParts = explode('\\', static::class);
        return end($classNameParts);
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
     * This method is called to handle the response from a called crud message.
     * Override it to handle the data correctly.
     *
     * @param \stdClass $response
     */
    protected function handleResponse(\stdClass $response)
    {
        // I do nothing with the data
    }
    //</editor-fold>
}
