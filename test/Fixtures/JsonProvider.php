<?php
/*
 *  Provide Json strings to for unit tests.
 *
 *  Copyright (C) 2023 - today Unzer E-Com GmbH
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
 *  @package  UnzerSDK
 *
 */

namespace UnzerSDK\test\Fixtures;

class JsonProvider
{
    private static string $baseDir = __DIR__ . '/jsonData/';

    /**
     * @throws \Exception
     */
    public static function getJsonFromFile(string $path): string
    {
        $filepath = self::$baseDir . $path;
        $filepath = str_replace(['/'], DIRECTORY_SEPARATOR, $filepath);

        if (file_exists($filepath)) {
            return file_get_contents($filepath);
        }

        throw new \Exception('File could not be read.');
    }
}
