<?php
/**
 * This class verifies function of the instalment plan resources.
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpayPHP/test/unit
 */
namespace heidelpayPHP\test\unit\Resources;

use heidelpayPHP\Resources\InstalmentPlans;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class InstalmentPlanTest extends TestCase
{
    /**
     * Verify the functionalities of the instalment plan resources.
     *
     * @test
     * @dataProvider verifyQueryStringDP
     *
     * @param float  $amount
     * @param string $currency
     * @param float  $effectiveInterest
     *
     * @throws Exception
     * @throws RuntimeException
     *
     * todo add missing parameter
     */
    public function verifyQueryString($amount, $currency, $effectiveInterest)
    {
        $plans = new InstalmentPlans($amount, $currency, $effectiveInterest);
        $this->assertEquals("plans?amount={$amount}&currency={$currency}&effectiveInterest={$effectiveInterest}", $plans->getResourcePath());
    }

    //<editor-fold desc="Data Providers">

    /**
     * @return array
     */
    public function verifyQueryStringDP(): array
    {
        return [
            [100, 'EUR', 4.99],
            [123.45, 'USD', 1.23]
        ];
    }

    //</editor-fold>
}
