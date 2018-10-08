<?php
/**
 * This represents the ideal payment type.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/payment_types
 */
namespace heidelpay\MgwPhpSdk\Resources\PaymentTypes;

class Ideal extends BasePaymentType
{
    /** @var string $bankName */
    protected $bankName;

    /**
     * GiroPay constructor.
     */
    public function __construct()
    {
        $this->setChargeable(true);

        parent::__construct();
    }

    //<editor-fold desc="Getter/Setter">
    /**
     * @return string
     */
    public function getBankName(): string
    {
        return $this->bankName;
    }

    /**
     * @param string $bankName
     * @return Ideal
     */
    public function setBankName(string $bankName): Ideal
    {
        $this->bankName = $bankName;
        return $this;
    }
    //</editor-fold>
}
