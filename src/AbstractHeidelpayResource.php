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
     * @param $id
     */
    public function __construct(HeidelpayParentInterface $parent, $id = 0)
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
//        $this->send(HttpAdapterInterface::REQUEST_POST);

        // todo: update resource

        $this->setId('dummy_id'); // todo: remove this when sending is implemented

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
    public function setParentResource($parentResource)
    {
        $this->parentResource = $parentResource;
        return $this;
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
        return json_encode($this->expose());
    }

    /**
     * Creates an array containing all properties to be exposed to the heidelpay api as resource parameters.
     *
     * @return array
     */
    public function expose()
    {
        $properties = get_object_vars($this);

        foreach ($properties as $property => $value) {
            try {
                $reflection = new \ReflectionProperty(static::class, $property);
                if (!$reflection->isProtected()) {
                    unset($properties[$property]);
                    continue;
                }

                if ($value === null) {
                    $properties[$property] = '';
                }
            } catch (\ReflectionException $e) {
                unset($properties[$property]);
            }
        }

        ksort($properties);
        return $properties;
    }
    //</editor-fold>

    /**
     * @param string $httpMethod
     * @throws \RuntimeException
     */
    public function send($httpMethod = HttpAdapterInterface::REQUEST_GET)
    {
        $responseJson = $this->getHeidelpayObject()->send(
            $this->getUri(),
            $this,
            $httpMethod
        );
//        $this->fromJson($responseJson);
    }

    /**
     * {@inheritDoc}
     */
    public function getHeidelpayObject()
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
    public function getUri()
    {
        // remove trailing slash and explode
        $uri = [rtrim($this->parentResource->getUri(), '/'), strtolower(self::getClassShortName())];
        if (!empty($this->getId())) {
            $uri[] = $this->getId();
        }

        $uri[] = '';

        return implode('/', $uri);
    }

    /**
     * Return class short name.
     *
     * @return string
     */
    public static function getClassShortName()
    {
        $classNameParts = explode('\\', static::class);
        return end($classNameParts);
    }
}
