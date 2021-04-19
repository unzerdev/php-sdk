<?php
/**
 * This file contains the different domains that are allowed to be used for merchant validation.
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
 * @link  https://docs.unzer.com/
 *
 * @author  David Owusu <development@unzer.com>
 *
 * @package  UnzerSDK\Constants
 */
namespace UnzerSDK\Constants;

class ApplepayValidationDomains
{
    // URL list
    public const ALLOWED_VALIDATION_URLS = [
        'apple-pay-gateway.apple.com',
        'cn-apple-pay-gateway.apple.com',
        'apple-pay-gateway-nc-pod1.apple.com',
        'apple-pay-gateway-nc-pod2.apple.com',
        'apple-pay-gateway-nc-pod3.apple.com',
        'apple-pay-gateway-nc-pod4.apple.com',
        'apple-pay-gateway-nc-pod5.apple.com',
        'apple-pay-gateway-pr-pod1.apple.com',
        'apple-pay-gateway-pr-pod2.apple.com',
        'apple-pay-gateway-pr-pod3.apple.com',
        'apple-pay-gateway-pr-pod4.apple.com',
        'apple-pay-gateway-pr-pod5.apple.com',
        'cn-apple-pay-gateway-sh-pod1.apple.com',
        'cn-apple-pay-gateway-sh-pod2.apple.com',
        'cn-apple-pay-gateway-sh-pod3.apple.com',
        'cn-apple-pay-gateway-tj-pod1.apple.com',
        'cn-apple-pay-gateway-tj-pod2.apple.com',
        'cn-apple-pay-gateway-tj-pod3.apple.com',
        'apple-pay-gateway-cert.apple.com',
        'cn-apple-pay-gateway-cert.apple.com'
    ];
}
