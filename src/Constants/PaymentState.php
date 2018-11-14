<?php
/**
 * This file contains definitions of the payment states.
 *
 * Copyright (C) 2018 Heidelpay GmbH
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
 * @package  heidelpay/mgw_sdk/constants
 */
namespace heidelpay\MgwPhpSdk\Constants;

class PaymentState
{
    const STATE_PENDING = 0;
    const STATE_COMPLETED = 1;
    const STATE_CANCELED = 2;
    const STATE_PARTLY = 3;
    const STATE_PAYMENT_REVIEW = 4;
    const STATE_CHARGEBACK = 5;

    const STATE_NAME_PENDING = 'pending';
    const STATE_NAME_COMPLETED = 'completed';
    const STATE_NAME_CANCELED = 'canceled';
    const STATE_NAME_PARTLY = 'partly';
    const STATE_NAME_PAYMENT_REVIEW = 'payment review';
    const STATE_NAME_CHARGEBACK = 'chargeback';

    /**
     * Returns the name of the state with the given code.
     *
     * @param int $stateCode The code of the payment state.
     *
     * @return string The name of the code.
     *
     * @throws \RuntimeException A \RuntimeException is thrown when the $stateCode is unknown.
     */
    public static function mapStateCodeToName($stateCode)
    {
        switch ($stateCode) {
            case self::STATE_PENDING:
                return self::STATE_NAME_PENDING;
                break;
            case self::STATE_COMPLETED:
                return self::STATE_NAME_COMPLETED;
                break;
            case self::STATE_CANCELED:
                return self::STATE_NAME_CANCELED;
                break;
            case self::STATE_PARTLY:
                return self::STATE_NAME_PARTLY;
                break;
            case self::STATE_PAYMENT_REVIEW:
                return self::STATE_NAME_PAYMENT_REVIEW;
                break;
            case self::STATE_CHARGEBACK:
                return self::STATE_NAME_CHARGEBACK;
                break;
            default:
                throw new \RuntimeException('Unknown payment state #' . $stateCode);
        }
    }

    /**
     * Returns the name of the state with the given code.
     *
     * @param string $stateName The name of the code.
     *
     * @return int The code of the payment state.
     *
     * @throws \RuntimeException A \RuntimeException is thrown when the $stateName is unknown.
     */
    public static function mapStateNameToCode($stateName)
    {
        switch ($stateName) {
            case self::STATE_NAME_PENDING:
                return self::STATE_PENDING;
                break;
            case self::STATE_NAME_COMPLETED:
                return self::STATE_COMPLETED;
                break;
            case self::STATE_NAME_CANCELED:
                return self::STATE_CANCELED;
                break;
            case self::STATE_NAME_PARTLY:
                return self::STATE_PARTLY;
                break;
            case self::STATE_NAME_PAYMENT_REVIEW:
                return self::STATE_PAYMENT_REVIEW;
                break;
            case self::STATE_NAME_CHARGEBACK:
                return self::STATE_CHARGEBACK;
                break;
            default:
                throw new \RuntimeException('Unknown payment state ' . $stateName);
        }
    }
}
