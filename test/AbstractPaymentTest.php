<?php
/**
 * Description
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/${Package}
 */
namespace heidelpay\NmgPhpSdk\test;

use heidelpay\NmgPhpSdk\Constants\SupportedLocale;
use heidelpay\NmgPhpSdk\Heidelpay;
use PHPUnit\Framework\TestCase;

class AbstractPaymentTest extends TestCase
{
    /** @var Heidelpay $heidelpay */
    protected $heidelpay;

    const PRIVATE_KEY = 's-priv-6S59Dt6Q9mJYj8X5qpcxSpA3XLXUw4Zf';
    const PUBLIC_KEY = 's-pub-uM8yNmBNcs1GGdwAL4ytebYA4HErD22H';

    protected function setUp()
    {
        $this->heidelpay = new Heidelpay(self::PUBLIC_KEY, SupportedLocale::GERMAN_GERMAN);
    }
}
