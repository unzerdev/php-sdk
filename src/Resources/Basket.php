<?php

namespace UnzerSDK\Resources;

use stdClass;
use UnzerSDK\Adapter\HttpAdapterInterface;
use UnzerSDK\Constants\ApiVersions;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use UnzerSDK\Resources\V2\BasketProperties as BasketV2Properties;

/**
 * This represents the basket resource.
 *
 * @link  https://docs.unzer.com/
 *
 */
class Basket extends AbstractUnzerResource
{
    use BasketV2Properties;

    public function __construct(
        string $orderId = '',
        float $totalValueGross = 0.0,
        string $currencyCode = 'EUR',
        array $basketItems = []
    ) {
        $this->orderId      = $orderId;
        $this->currencyCode = $currencyCode;
        $this->setTotalValueGross($totalValueGross);
        $this->setBasketItems($basketItems);
    }

    /**
     * {@inheritDoc}
     */
    public function expose(): array
    {
        $basketItemArray = [];

        /** @var BasketItem $basketItem */
        foreach ($this->getBasketItems() as $basketItem) {
            $basketItemArray[] = $basketItem->expose();
        }

        $returnArray = parent::expose();
        $returnArray['basketItems'] = $basketItemArray;

        return $returnArray;
    }

    /**
     * {@inheritDoc}
     */
    public function getApiVersion(): string
    {
        if (!empty($this->getTotalValueGross())) {
            return ApiVersions::V2;
        }
        return parent::getApiVersion();
    }

    /**
     * Returns the key of the last BasketItem in the Array.
     *
     * @return int|string|null
     */
    private function getKeyOfLastBasketItemAdded()
    {
        end($this->basketItems);
        return key($this->basketItems);
    }

    /**
     * {@inheritDoc}
     */
    protected function getResourcePath(string $httpMethod = HttpAdapterInterface::REQUEST_GET): string
    {
        return 'baskets';
    }

    /**
     * {@inheritDoc}
     */
    public function handleResponse(stdClass $response, string $method = HttpAdapterInterface::REQUEST_GET): void
    {
        parent::handleResponse($response, $method);

        if (isset($response->basketItems)) {
            $items = [];
            foreach ($response->basketItems as $basketItem) {
                $item = new BasketItem();
                $item->handleResponse($basketItem);
                $items[] = $item;
            }
            $this->setBasketItems($items);
        }
    }
}
