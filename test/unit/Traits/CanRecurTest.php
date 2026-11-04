<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the CanRecur trait.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\test\unit\Traits;

use UnzerSDK\Unzer;
use UnzerSDK\Resources\Recurring;
use UnzerSDK\test\BasePaymentTest;
use RuntimeException;
use stdClass;

class CanRecurTest extends BasePaymentTest
{
    /**
     * Verify setters and getters.
     *
     * @test
     */
    public function gettersAndSettersShouldWorkProperly(): void
    {
        $dummy = new TraitDummyCanRecur();
        $this->assertFalse($dummy->isRecurring());
        $response = new stdClass();
        $response->recurring = true;
        $dummy->handleResponse($response);
        $this->assertTrue($dummy->isRecurring());
    }

}
