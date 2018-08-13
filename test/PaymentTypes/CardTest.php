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
namespace heidelpay\NmgPhpSdk\test\PaymentTypes;

use heidelpay\NmgPhpSdk\Constants\SupportedLocale;
use heidelpay\NmgPhpSdk\Heidelpay;
use heidelpay\NmgPhpSdk\HeidelpayResourceInterface;
use heidelpay\NmgPhpSdk\PaymentTypes\Card;
use PHPUnit\Framework\TestCase;

class CardTest extends TestCase
{
    /** @var Heidelpay $heidelpay */
    private $heidelpay;

    protected function setUp()
    {
        $this->heidelpay = new Heidelpay('thisKey', SupportedLocale::GERMAN_GERMAN);
    }

    /**
     * @test
     */
    public function createCardType()
    {
        $card = new Card ('4111111111111111', '03/20');
        $card->setCvc('123');
        $this->assertEmpty($card->getId());
        $card = $this->heidelpay->createPaymentType($card);
        /** @var HeidelpayResourceInterface $card */
        $this->assertNotEmpty($card->getId());
    }

}
