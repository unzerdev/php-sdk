<?php
/**
 * This trait adds the trace id to a class.
 * It can be sent to the support when a problem occurs.
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

trait HasTraceId
{
    /** @var string $traceId */
    private $traceId;

    //<editor-fold desc="Getters/Setters">

    /**
     * @return string|null
     */
    public function getTraceId(): ?string
    {
        return $this->traceId;
    }

    /**
     * @param string $traceId
     *
     * @return $this
     */
    protected function setTraceId(string $traceId): self
    {
        $this->traceId = $traceId;
        return $this;
    }

    //</editor-fold>
}
