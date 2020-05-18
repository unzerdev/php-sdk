<?php
/**
 * This file contains definitions of common response codes.
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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\Constants
 */
namespace heidelpayPHP\Constants;

class ApiResponseCodes
{
    // Status codes
    const API_SUCCESS_REQUEST_PROCESSED_IN_TEST_MODE            = 'API.000.100.112';
    const API_SUCCESS_CHARGED_AMOUNT_HIGHER_THAN_EXPECTED       = 'API.100.550.340';
    const API_SUCCESS_CHARGED_AMOUNT_LOWER_THAN_EXPECTED        = 'API.100.550.341';

    const CORE_TRANSACTION_PENDING                              = 'COR.000.200.000';

    // Errors codes
    const API_ERROR_GENERAL                                     = 'API.000.000.999';
    const API_ERROR_PAYMENT_NOT_FOUND                           = 'API.310.100.003';
    const API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED           = 'API.320.000.004';
    const API_ERROR_TRANSACTION_CHARGE_NOT_ALLOWED              = 'API.330.000.004';
    const API_ERROR_TRANSACTION_CANCEL_NOT_ALLOWED              = 'API.340.000.004';
    const API_ERROR_TRANSACTION_SHIP_NOT_ALLOWED                = 'API.360.000.004';
    const API_ERROR_SHIPPING_REQUIRES_INVOICE_ID                = 'API.360.100.025';
    const API_ERROR_CUSTOMER_ID_REQUIRED                        = 'API.320.100.008';
    const API_ERROR_ORDER_ID_ALREADY_IN_USE                     = 'API.320.200.138';
    const API_ERROR_RESOURCE_DOES_NOT_BELONG_TO_MERCHANT        = 'API.320.200.145';
    const API_ERROR_CHARGED_AMOUNT_HIGHER_THAN_EXPECTED         = 'API.330.100.007';
    const API_ERROR_IVF_REQUIRES_CUSTOMER                       = 'API.330.100.008';
    const API_ERROR_IVF_REQUIRES_BASKET                         = 'API.330.100.023';
    const API_ERROR_ADDRESSES_DO_NOT_MATCH                      = 'API.330.100.106';
    const API_ERROR_CURRENCY_IS_NOT_SUPPORTED                   = 'API.330.100.202';
    /**
     * API_ERROR_AUTHORIZE_ALREADY_CANCELLED
     *
     * @deprecated since 1.2.3.0
     * @see ApiResponseCodes::API_ERROR_ALREADY_CANCELLED
     */
    const API_ERROR_AUTHORIZE_ALREADY_CANCELLED                 = 'API.340.100.014';
    const API_ERROR_ALREADY_CANCELLED                           = 'API.340.100.014';
    /**
     * API_ERROR_CHARGE_ALREADY_CHARGED_BACK
     *
     * @deprecated since 1.2.3.0
     * @see ApiResponseCodes::API_ERROR_ALREADY_CHARGED_BACK
     */
    const API_ERROR_CHARGE_ALREADY_CHARGED_BACK                 = 'API.340.100.015';
    const API_ERROR_ALREADY_CHARGED_BACK                        = 'API.340.100.015';
    const API_ERROR_ALREADY_CHARGED                             = 'API.340.100.018';
    const API_ERROR_CANCEL_REASON_CODE_IS_MISSING               = 'API.340.100.024';
    const API_ERROR_AMOUNT_IS_MISSING                           = 'API.340.200.130';
    const API_ERROR_CUSTOMER_DOES_NOT_EXIST                     = 'API.410.100.100';
    const API_ERROR_CUSTOMER_ID_ALREADY_EXISTS                  = 'API.410.200.010';
    const API_ERROR_ADDRESS_NAME_TO_LONG                        = 'API.410.200.031';
    const API_ERROR_CUSTOMER_CAN_NOT_BE_FOUND                   = 'API.500.100.100';
    const API_ERROR_REQUEST_DATA_IS_INVALID                     = 'API.500.300.999';
    const API_ERROR_RECURRING_PAYMENT_NOT_SUPPORTED             = 'API.500.550.004';
    const API_ERROR_WEBHOOK_EVENT_ALREADY_REGISTERED            = 'API.510.310.009';
    const API_ERROR_WEBHOOK_CAN_NOT_BE_FOUND                    = 'API.510.310.008';
    const API_ERROR_BASKET_ITEM_IMAGE_INVALID_URL               = 'API.600.630.004';
    /**
     * API_ERROR_BASKET_ITEM_IMAGE_INVALID_EXTENSION
     *
     * @deprecated since 1.2.5.0 Will be removed in next major version.
     */
    const API_ERROR_BASKET_ITEM_IMAGE_INVALID_EXTENSION         = 'API.600.630.005';
    const API_ERROR_ACTIVATE_RECURRING_VIA_TRANSACTION          = 'API.640.550.005';
    const API_ERROR_RECURRING_ALREADY_ACTIVE                    = 'API.640.550.006';
    const API_ERROR_INVALID_KEY                                 = 'API.710.000.002';
    const API_ERROR_INSUFFICIENT_PERMISSION                     = 'API.710.000.005';
    const API_ERROR_WRONG_AUTHENTICATION_METHOD                 = 'API.710.000.007';
    const API_ERROR_FIELD_IS_MISSING                            = 'API.710.200.100';

    const CORE_ERROR_INVALID_OR_MISSING_LOGIN                   = 'COR.100.300.600';
    const CORE_ERROR_INSURANCE_ALREADY_ACTIVATED                = 'COR.700.400.800';

    const SDM_ERROR_CURRENT_INSURANCE_EVENT                     = 'SDM.CURRENT_INSURANCE_EVENT';
    const SDM_ERROR_LIMIT_EXCEEDED                              = 'SDM.LIMIT_EXCEEDED';
    const SDM_ERROR_NEGATIVE_TRAIT_FOUND                        = 'SDM.NEGATIVE_TRAIT_FOUND';
    const SDM_ERROR_INCREASED_RISK                              = 'SDM.INCREASED_RISK';
    const SDM_ERROR_DATA_FORMAT_ERROR                           = 'SDM.DATA_FORMAT_ERROR';
}
