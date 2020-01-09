<?php
/**
 * This trait adds the state properties to a resource class.
 *
 * Copyright (C) 2019 heidelpay GmbH
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
 * @package  heidelpayPHP\Traits
 */
namespace heidelpayPHP\Traits;

trait HasStates
{
    /** @var bool $isError */
    private $isError = false;

    /** @var bool $isSuccess */
    private $isSuccess = false;

    /** @var bool $isPending */
    private $isPending = false;

    //<editor-fold desc="Getters/Setters">

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return $this->isError;
    }

    /**
     * @param bool $isError
     *
     * @return self
     */
    protected function setIsError(bool $isError): self
    {
        $this->isError = $isError;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    /**
     * @param bool $isSuccess
     *
     * @return self
     */
    protected function setIsSuccess(bool $isSuccess): self
    {
        $this->isSuccess = $isSuccess;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->isPending;
    }

    /**
     * @param bool $isPending
     *
     * @return self
     */
    protected function setIsPending(bool $isPending): self
    {
        $this->isPending = $isPending;
        return $this;
    }

    //</editor-fold>
}
