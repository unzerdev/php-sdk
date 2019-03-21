<?php
/**
 * This class defines unit tests to verify functionality of Ideal payment type.
 *
 * Copyright (C) 2018 heidelpay GmbH
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
 * @package  heidelpayPHP/test/unit
 */
namespace heidelpayPHP\test\unit\Resources\PaymentTypes;

use heidelpayPHP\Resources\PaymentTypes\Ideal;
use heidelpayPHP\test\BaseUnitTest;
use PHPUnit\Framework\Exception;

class IdealTest extends BaseUnitTest
{
    /**
     * Verify the bic can be set and read.
     *
     * @test
     *
     * @throws Exception
     */
    public function bicShouldBeRW()
    {
        $ideal = new Ideal();
        $this->assertNull($ideal->getBic());
        $ideal->setBic('RABONL2U');
        $this->assertEquals('RABONL2U', $ideal->getBic());
    }
}
