<?php
/**
 * This represents the metadata resource.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
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
 * @link  https://docs.unzer.com/
 *
 * @package  UnzerSDK\Resources
 */
namespace UnzerSDK\Resources;

use UnzerSDK\Adapter\HttpAdapterInterface;
use function count;
use function in_array;
use function is_callable;
use stdClass;

class Metadata extends AbstractUnzerResource
{
    private $metadata = [];

    protected $shopType;
    protected $shopVersion;

    //<editor-fold desc="Setters/Getters">

    /**
     * @return string|null
     */
    public function getShopType(): ?string
    {
        return $this->shopType;
    }

    /**
     * @param string $shopType
     *
     * @return Metadata
     */
    public function setShopType(string $shopType): Metadata
    {
        $this->shopType = $shopType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getShopVersion(): ?string
    {
        return $this->shopVersion;
    }

    /**
     * @param string $shopVersion
     *
     * @return Metadata
     */
    public function setShopVersion(string $shopVersion): Metadata
    {
        $this->shopVersion = $shopVersion;
        return $this;
    }

    /**
     * Magic setter
     *
     * @param string $name
     * @param string $value
     *
     * @return Metadata
     */
    public function addMetadata($name, $value): Metadata
    {
        if (!in_array(strtolower($name), ['sdkversion', 'sdktype', 'shoptype', 'shopversion'])) {
            $this->metadata[$name] = $value;
        }

        return $this;
    }

    /**
     * Getter function for custom criterion fields.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getMetadata($name)
    {
        return $this->metadata[$name] ?? null;
    }

    //</editor-fold>>

    //<editor-fold desc="Overridable Methods">

    /**
     * Add the dynamically set meta data.
     * {@inheritDoc}
     */
    public function expose()
    {
        $array_merge = array_merge((array)parent::expose(), $this->metadata);
        return count($array_merge) > 0 ? $array_merge : new stdClass();
    }

    /**
     * Add custom properties (i. e. properties without setter) to the metadata array.
     * {@inheritDoc}
     */
    public function handleResponse(stdClass $response, $method = HttpAdapterInterface::REQUEST_GET): void
    {
        parent::handleResponse($response, $method);

        foreach ($response as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (!is_callable([$this, $setter])) {
                $this->addMetadata($key, $value);
            }
        }
    }

    //</editor-fold>
}
