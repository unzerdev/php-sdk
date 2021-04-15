<?php
/*
 *  [DESCRIPTION]
 *
 *  Copyright (C) 2021 - today Unzer E-Com GmbH
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

namespace UnzerSDK\test\unit\Adapter;

use PHPUnit\Framework\TestCase;
use UnzerSDK\Resources\ExternalResources\ApplepaySession;

class ApplepaySessionTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $applepaySession = new ApplepaySession('merchantIdentifier', 'displayName', 'domainName');
        $expectedJson = '{"merchantIdentifier": "merchantIdentifier", "displayName": "displayName", "domainName": "domainName"}';

        $jsonSerialize = $applepaySession->jsonSerialize();
        $this->assertJsonStringEqualsJsonString($expectedJson, $jsonSerialize);
    }
}