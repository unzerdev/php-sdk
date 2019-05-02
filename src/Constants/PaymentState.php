<?php
/**
 * This file contains definitions of the payment states.
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
 * @package  heidelpayPHP/constants
 */
namespace heidelpayPHP\Constants;

use RuntimeException;

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
     * @throws RuntimeException A RuntimeException is thrown when the $stateCode is unknown.
     */
    public static function mapStateCodeToName($stateCode): string
    {
        switch ($stateCode) {
            case self::STATE_PENDING:
                $stateName =  self::STATE_NAME_PENDING;
                break;
            case self::STATE_COMPLETED:
                $stateName =  self::STATE_NAME_COMPLETED;
                break;
            case self::STATE_CANCELED:
                $stateName =  self::STATE_NAME_CANCELED;
                break;
            case self::STATE_PARTLY:
                $stateName =  self::STATE_NAME_PARTLY;
                break;
            case self::STATE_PAYMENT_REVIEW:
                $stateName =  self::STATE_NAME_PAYMENT_REVIEW;
                break;
            case self::STATE_CHARGEBACK:
                $stateName =  self::STATE_NAME_CHARGEBACK;
                break;
            default:
                throw new RuntimeException('Unknown payment state #' . $stateCode);
        }

        return $stateName;
    }

    /**
     * Returns the name of the state with the given code.
     *
     * @param string $stateName The name of the code.
     *
     * @return int The code of the payment state.
     *
     * @throws RuntimeException A RuntimeException is thrown when the $stateName is unknown.
     */
    public static function mapStateNameToCode($stateName): int
    {
        switch ($stateName) {
            case self::STATE_NAME_PENDING:
                $stateCode = self::STATE_PENDING;
                break;
            case self::STATE_NAME_COMPLETED:
                $stateCode = self::STATE_COMPLETED;
                break;
            case self::STATE_NAME_CANCELED:
                $stateCode = self::STATE_CANCELED;
                break;
            case self::STATE_NAME_PARTLY:
                $stateCode = self::STATE_PARTLY;
                break;
            case self::STATE_NAME_PAYMENT_REVIEW:
                $stateCode = self::STATE_PAYMENT_REVIEW;
                break;
            case self::STATE_NAME_CHARGEBACK:
                $stateCode = self::STATE_CHARGEBACK;
                break;
            default:
                throw new RuntimeException('Unknown payment state ' . $stateName);
        }

        return $stateCode;
    }
}
