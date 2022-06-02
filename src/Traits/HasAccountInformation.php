<?php
/*
 *  Trait containing a property set of transaction regarding bank account information.
 *
 *  Copyright (C) 2022 - today Unzer E-Com GmbH
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 *  @link  https://docs.unzer.com/
 *
 *  @author  David Owusu <development@unzer.com>
 *
 *  @package  UnzerSDK
 *
 */

namespace UnzerSDK\Traits;

trait HasAccountInformation
{
    /** @var string $iban */
    private $iban;

    /** @var string bic */
    private $bic;

    /** @var string $holder */
    private $holder;

    /** @var string $descriptor */
    private $descriptor;

    /**
     * Returns the IBAN of the account the customer needs to transfer the amount to.
     * E. g. invoice, prepayment, etc.
     *
     * @return string|null
     */
    public function getIban(): ?string
    {
        return $this->iban;
    }

    /**
     * @param string $iban
     *
     * @return self
     */
    protected function setIban(string $iban): self
    {
        $this->iban = $iban;
        return $this;
    }

    /**
     * Returns the BIC of the account the customer needs to transfer the amount to.
     * E. g. invoice, prepayment, etc.
     *
     * @return string|null
     */
    public function getBic(): ?string
    {
        return $this->bic;
    }

    /**
     * @param string $bic
     *
     * @return self
     */
    protected function setBic(string $bic): self
    {
        $this->bic = $bic;
        return $this;
    }

    /**
     * Returns the holder of the account the customer needs to transfer the amount to.
     * E. g. invoice, prepayment, etc.
     *
     * @return string|null
     */
    public function getHolder(): ?string
    {
        return $this->holder;
    }

    /**
     * @param string $holder
     *
     * @return self
     */
    protected function setHolder(string $holder): self
    {
        $this->holder = $holder;
        return $this;
    }

    /**
     * Returns the Descriptor the customer needs to use when transferring the amount.
     * E. g. invoice, prepayment, etc.
     *
     * @return string|null
     */
    public function getDescriptor(): ?string
    {
        return $this->descriptor;
    }

    /**
     * @param string $descriptor
     *
     * @return self
     */
    protected function setDescriptor(string $descriptor): self
    {
        $this->descriptor = $descriptor;
        return $this;
    }
}
