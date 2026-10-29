<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the HttpService.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\test\unit\Services;

use RuntimeException;
use UnzerSDK\Adapter\CurlAdapter;
use UnzerSDK\Services\EnvironmentService;
use UnzerSDK\Services\HttpService;
use UnzerSDK\test\BasePaymentTest;
use UnzerSDK\test\unit\DummyResource;
use UnzerSDK\Unzer;

class HttpServiceTest extends BasePaymentTest
{
    /**
     * Verify getAdapter will return a CurlAdapter if none has been set.
     *
     * @test
     */
    public function getAdapterShouldReturnDefaultAdapterIfNonHasBeenSet(): void
    {
        $httpService = new HttpService();
        $this->assertInstanceOf(CurlAdapter::class, $httpService->getAdapter());
    }

    /**
     * Verify getAdapter will return custom adapter if it has been set.
     *
     * @test
     */
    public function getAdapterShouldReturnCustomAdapterIfItHasBeenSet(): void
    {
        $dummyAdapter = new DummyAdapter();
        $httpService = (new HttpService())->setHttpAdapter($dummyAdapter);
        $this->assertSame($dummyAdapter, $httpService->getAdapter());
    }

    /**
     * Verify an environment service can be injected.
     *
     * @test
     */
    public function environmentServiceShouldBeInjectable(): void
    {
        $envService = new EnvironmentService();
        $httpService = new HttpService();
        $this->assertNotSame($envService, $httpService->getEnvironmentService());
        $httpService->setEnvironmentService($envService);
        $this->assertSame($envService, $httpService->getEnvironmentService());
    }

    /**
     * Verify 'CLIENTIP' header only set when a clientIp is defined in the Unzer object.
     *
     * @test
     *
     * @dataProvider clientIpHeaderShouldBeSetProperlyDP
     *
     * @param       $clientIp
     * @param mixed $isHeaderExpected
     */
    public function clientIpHeaderShouldBeSetProperly($clientIp, $isHeaderExpected): void
    {
        $unzer = new Unzer('s-priv-MyTestKey');
        $unzer->setClientIp($clientIp);

        $composeHttpHeaders = $unzer->getHttpService()->composeHttpHeaders($unzer);
        $this->assertEquals($isHeaderExpected, isset($composeHttpHeaders['CLIENTIP']));
        if ($isHeaderExpected) {
            $this->assertEquals($clientIp, $composeHttpHeaders['CLIENTIP']);
        }
    }

    //<editor-fold desc="DataProviders">

    /**
     * Returns test data for method public function languageShouldOnlyBeSetIfSpecificallyDefined.
     */
    public function clientIpHeaderShouldBeSetProperlyDP(): array
    {
        return [
            'valid ipv4' => ['111.222.333.444', true],
            'valid ipv6' => ['684D:1111:222:3333:4444:5555:6:7', true],
            'valid ipv6 (dual)' => ['2001:db8:3333:4444:5555:6666:1.2.3.4', true],
            'empty string' => ['', false],
            'null' => [null, false]
        ];
    }

    //</editor-fold>
}
