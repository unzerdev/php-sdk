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

namespace heidelpay\NmgPhpSdk\test\Fixtures;

trait CustomerFixtureTrait
{
    protected static $customerW = [
        'birthday' => '2018-08-12',
        'firstname' => 'Max',
        'lastname' => 'Mustermann',
        'company' => 'Musterfirma',
        'state' => 'Bremen',
        'street1' => 'Märchenstraße 3',
        'street2' => 'Hinterhaus',
        'zip' => '12345',
        'city' => 'Pusemuckel',
        'country' => 'Schweiz',
        'email' => 'max@mustermann.de',
        'id' => 'c-123456'
    ];

    protected static $customerB = [
        'birthday' => '2000-01-11',
        'firstname' => 'Linda',
        'lastname' => 'Heideich',
        'company' => 'heidelpay GmbH',
        'street1' => 'Vangerowstr. 18',
        'street2' => 'am Neckar',
        'state' => 'Baden-Würtemberg',
        'zip' => '69115',
        'city' => 'Heidelberg',
        'country' => 'Deutschland',
        'email' => 'lh@heidelpay.de',
        'id' => 'c-654321'
    ];

    protected static $customerWithoutId = [
        'birthday' => '2000-01-11',
        'firstname' => 'Linda',
        'lastname' => 'Heideich',
        'company' => 'heidelpay GmbH',
        'street1' => 'Vangerowstr. 18',
        'street2' => 'am Neckar',
        'state' => 'Baden-Würtemberg',
        'zip' => '69115',
        'city' => 'Heidelberg',
        'country' => 'Deutschland',
        'email' => 'lh@heidelpay.de'
    ];
}
