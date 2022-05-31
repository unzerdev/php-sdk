<?php
/**
 * This trait adds geolocation to class.
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
 * @package  UnzerSDK\Traits
 */
namespace UnzerSDK\Traits;

use UnzerSDK\Resources\EmbeddedResources\GeoLocation;

trait HasGeoLocation
{
    /** @var GeoLocation $geoLocation */
    private $geoLocation;

    //<editor-fold desc="Setters/Getters">

    /**
     * @return GeoLocation
     */
    public function getGeoLocation(): GeoLocation
    {
        if (empty($this->geoLocation)) {
            $this->geoLocation = new GeoLocation();
        }
        return $this->geoLocation;
    }

    /**
     * @param GeoLocation $geoLocation
     *
     * @return $this
     */
    public function setGeoLocation(GeoLocation $geoLocation): self
    {
        $this->geoLocation = $geoLocation;
        return $this;
    }

    //</editor-fold>
}
