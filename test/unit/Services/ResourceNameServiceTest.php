<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the resource name service.
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
 * @package  UnzerSDK\test\unit
 */
namespace UnzerSDK\test\unit\Services;

use UnzerSDK\Services\ResourceNameService;
use UnzerSDK\test\BasePaymentTest;

class ResourceNameServiceTest extends BasePaymentTest
{
    /**
     * Verify getting the short name of a class.
     *
     * @test
     *
     * @dataProvider classShortNameTestDP
     *
     * @param string $className
     * @param string $expected
     */
    public function shouldReturnTheCorrectShortName($className, $expected): void
    {
        $this->assertEquals($expected, ResourceNameService::getClassShortNameKebapCase($className));
    }

    /**
     * @return array
     */
    public function classShortNameTestDP(): array
    {
        return [
            'normal class name' => ['className' => 'Path\\To\\Test\\Class', 'expected' => 'class'],
            'camel case class' => ['className' => 'Path\\To\\Test\\CamelCaseClass', 'expected' => 'camel-case-class'],
            'upper case class' => ['className' => 'Path\\To\\Test\\CCC', 'expected' => 'ccc']
        ];
    }
}
