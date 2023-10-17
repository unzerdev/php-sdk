<?php
/**
 * This file contains the allowed commercial sector items.
 *
 * @link  https://dev.unzer.com/
 *
 * @package  UnzerSDK\Constants
 */

namespace UnzerSDK\Constants;

class BasketItemTypes
{
    public const GOODS    = 'goods';
    public const SHIPMENT = 'shipment';
    public const VOUCHER  = 'voucher';
    public const DIGITAL  = 'digital';

    public const ARRAY = [self::GOODS, self::SHIPMENT, self::DIGITAL, self::VOUCHER];
}
