<?php
/**
 * This trait adds the invoiceId property to a class.
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/traits
 */
namespace heidelpayPHP\Traits;

trait HasInvoiceId
{
    /** @var string $invoiceId */
    protected $invoiceId;

    //<editor-fold desc="Getters/Setters">

    /**
     * @return string|null
     */
    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    /**
     * @param string|null $invoiceId
     *
     * @return self
     */
    public function setInvoiceId($invoiceId): self
    {
        $this->invoiceId = $invoiceId;
        return $this;
    }

    //</editor-fold>
}
