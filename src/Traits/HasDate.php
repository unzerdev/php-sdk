<?php
/**
 * This trait adds the date property to a resource class.
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

use DateTime;
use Exception;

trait HasDate
{
    /** @var DateTime $date */
    private $date;

    //<editor-fold desc="Getters/Setters">

    /**
     * This returns the date of the Transaction as string.
     *
     * @return string|null
     */
    public function getDate(): ?string
    {
        $date = $this->date;
        return $date ? $date->format('Y-m-d H:i:s') : null;
    }

    /**
     * @param string $date
     *
     * @return $this
     *
     * @throws Exception
     */
    public function setDate(string $date): self
    {
        $this->date = new DateTime($date);
        return $this;
    }

    //</editor-fold>
}
