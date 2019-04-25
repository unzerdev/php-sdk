<?php
/** @noinspection PhpComposerExtensionStubsInspection */
/**
 * This is a wrapper for the default http adapter (CURL).
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
 * @link http://dev.heidelpay.com/
 *
 * @author Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/adapter
 */
namespace heidelpayPHP\Adapter;

use function extension_loaded;
use RuntimeException;

class CurlAdapter implements HttpAdapterInterface
{
    private $request;

    /**
     * CurlAdapter constructor.
     *
     * @throws RuntimeException
     */
    public function __construct()
    {
        if (!extension_loaded('curl')) {
            throw new RuntimeException('Connection error php-curl not installed');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function init($url, $payload = null, $httpMethod = HttpAdapterInterface::REQUEST_GET)
    {
        $this->request = curl_init($url);
        $this->setOption(CURLOPT_HEADER, 0);
        $this->setOption(CURLOPT_FAILONERROR, false);
        $this->setOption(CURLOPT_TIMEOUT, 60);
        $this->setOption(CURLOPT_CONNECTTIMEOUT, 60);
        $this->setOption(CURLOPT_HTTP200ALIASES, (array)400);
        $this->setOption(CURLOPT_CUSTOMREQUEST, $httpMethod);
        $this->setOption(CURLOPT_RETURNTRANSFER, 1);
        $this->setOption(CURLOPT_SSL_VERIFYPEER, 1);
        $this->setOption(CURLOPT_SSL_VERIFYHOST, 2);
        $this->setOption(CURLOPT_SSLVERSION, 6);       // CURL_SSLVERSION_TLSv1_2

        if (in_array($httpMethod, [HttpAdapterInterface::REQUEST_POST, HttpAdapterInterface::REQUEST_PUT], true)) {
            $this->setOption(CURLOPT_POSTFIELDS, $payload);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        return curl_exec($this->request);
    }

    /**
     * {@inheritDoc}
     */
    public function getResponseCode(): string
    {
        return curl_getinfo($this->request, CURLINFO_HTTP_CODE);
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        curl_close($this->request);
    }

    /**
     * {@inheritDoc}
     */
    public function setHeaders(array $headers)
    {
        array_walk($headers, static function (&$value, $key) {
            $value = $key . ': ' . $value;
        });

        $this->setOption(CURLOPT_HTTPHEADER, $headers);
    }

    /**
     * {@inheritDoc}
     */
    public function setUserAgent($userAgent)
    {
        $this->setOption(CURLOPT_USERAGENT, 'HeidelpayPHP');
    }

    /**
     * Sets curl option.
     *
     * @param $name
     * @param $value
     */
    private function setOption($name, $value)
    {
        curl_setopt($this->request, $name, $value);
    }
}
