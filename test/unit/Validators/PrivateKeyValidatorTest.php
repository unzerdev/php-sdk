<?php
/**
 * This class defines unit tests to verify functionality of the private key validator.
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
namespace heidelpayPHP\test\unit\Resources;

use heidelpayPHP\test\BaseUnitTest;
use heidelpayPHP\Validators\PrivateKeyValidator;
use PHPUnit\Framework\Exception;

class PrivateKeyValidatorTest extends BaseUnitTest
{
    /**
     * Verify validate method behaves as expected.
     *
     * @test
     * @dataProvider validateShouldReturnTrueIfPrivateKeyHasCorrectFormatDP
     *
     * @param string $key
     * @param bool   $expectedResult
     *
     * @throws Exception
     */
    public function validateShouldReturnTrueIfPrivateKeyHasCorrectFormat($key, $expectedResult)
    {
        $this->assertEquals($expectedResult, PrivateKeyValidator::validate($key));
    }

    /**
     * Data provider for above test.
     *
     * @return array
     */
    public function validateShouldReturnTrueIfPrivateKeyHasCorrectFormatDP(): array
    {
        return [
            'valid sandbox' => ['s-priv-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n', true],
            'valid production' => ['p-priv-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n', true],
            'invalid public' => ['s-pub-2a10ifVINFAjpQJ9qW8jBe5OJPBx6Gxa', false],
            'invalid wrong format #1' => ['spriv-2a10an6aJK0Jg7sMdpu9gK7ih8pCccze', false],
            'invalid empty' => ['', false],
            'invalid null' => [null, false],
            'invalid missing postfix' => ['s-priv-', false],
            'invalid missing type' => ['s--2a10an6aJK0Jg7sMdpu9gK7ih8pCccze', false],
            'invalid wrong type' => ['s-foo-2a10an6aJK0Jg7sMdpu9gK7ih8pCccze', false]
        ];
    }
}
