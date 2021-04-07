<?php
/** @noinspection PhpComposerExtensionStubsInspection */
/**
 * This is a wrapper for the applepay http adapter (CURL).
 *
 * Copyright (C) 2021 - today Unzer E-Com GmbH
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
 * @link https://dev.unzer.com/
 *
 * @author David Owusu <development@unzer.com>
 *
 * @package  UnzerSDK\Adapter
 */
namespace UnzerSDK\Adapter;

use UnzerSDK\Services\EnvironmentService;
use UnzerSDK\Exceptions\UnzerApiException;
use function in_array;

class ApplepayAdapter
{
    private $request;

    /**
     * @param string          $merchantValidationURL
     * @param ApplepaySession $applePaySession
     * @param string          $merchantValidationCertificatePath
     * @param string          $merchantValidationCertificateKeyChainPath
     *
     * @return string|null
     *
     * @throws UnzerApiException
     */
    public function validateApplePayMerchant(
        string $merchantValidationURL,
        ApplepaySession $applePaySession,
        string $merchantValidationCertificatePath
    ): ?string {
        $payload = $applePaySession->jsonSerialize();
        $this->init($merchantValidationURL, $payload, $merchantValidationCertificatePath);
        try {
            return $this->execute();
        } catch (\Exception $exception) {
            return $exception;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function init($url, $payload, $sslCert): void
    {
        $timeout = EnvironmentService::getTimeout();
        $curlVerbose = EnvironmentService::isCurlVerbose();

        $this->request = curl_init();
        $this->setOption(CURLOPT_URL, $url);
        $this->setOption(CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $this->setOption(CURLOPT_POST, 1);
        $this->setOption(CURLOPT_DNS_USE_GLOBAL_CACHE, false);
        $this->setOption(CURLOPT_POSTFIELDS, $payload);
        $this->setOption(CURLOPT_SSLCERT, $sslCert);


        $this->setOption(CURLOPT_FAILONERROR, false);
        $this->setOption(CURLOPT_TIMEOUT, $timeout);
        $this->setOption(CURLOPT_CONNECTTIMEOUT, $timeout);
        $this->setOption(CURLOPT_HTTP200ALIASES, (array)400);
        $this->setOption(CURLOPT_RETURNTRANSFER, 1);
        $this->setOption(CURLOPT_SSL_VERIFYPEER, 1);
        $this->setOption(CURLOPT_SSL_VERIFYHOST, 2);
        $this->setOption(CURLOPT_VERBOSE, $curlVerbose);
        $this->setOption(CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
    }

    /**
     * {@inheritDoc}
     */
    public function execute(): ?string
    {
        $response = curl_exec($this->request);
        $error    = curl_error($this->request);
        $errorNo  = curl_errno($this->request);

        switch ($errorNo) {
            case 0:
                return $response;
                break;
            case CURLE_OPERATION_TIMEDOUT:
                $errorMessage = 'Timeout: The Payment API seems to be not available at the moment!';
                break;
            default:
                $errorMessage = $error . ' (curl_errno: '. $errorNo . ').';
                break;
        }
        throw new UnzerApiException($errorMessage);
    }

    /**
     * Sets curl option.
     *
     * @param $name
     * @param $value
     */
    private function setOption($name, $value): void
    {
        curl_setopt($this->request, $name, $value);
    }
}
