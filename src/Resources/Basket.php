<?php

namespace UnzerSDK\Resources;

use stdClass;
use UnzerSDK\Adapter\HttpAdapterInterface;
use UnzerSDK\Apis\PaymentApiConfigBearerAuth;
use UnzerSDK\Constants\ApiVersions;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use UnzerSDK\Services\IdService;

/**
 * This represents the basket resource.
 *
 * @link  https://docs.unzer.com/
 *
 */
class Basket extends AbstractUnzerResource
{
    /** @var float $totalValueGross */
    protected $totalValueGross = 0.0;

    /** @var string $currencyCode */
    protected $currencyCode;

    /** @var string $orderId */
    protected $orderId = '';

    /** @var string $note */
    protected $note;

    /** @var BasketItem[] $basketItems */
    private $basketItems;

    /**
     * @return float
     */
    public function getTotalValueGross(): float
    {
        return $this->totalValueGross;
    }

    /**
     * @param float $totalValueGross
     *
     * @return Basket
     */
    public function setTotalValueGross(float $totalValueGross): Basket
    {
        $this->totalValueGross = $totalValueGross;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    /**
     * @param string $currencyCode
     *
     * @return Basket
     */
    public function setCurrencyCode(string $currencyCode): Basket
    {
        $this->currencyCode = $currencyCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getItemCount(): int
    {
        return count($this->basketItems);
    }

    /**
     * @return string|null
     */
    public function getNote(): ?string
    {
        return $this->note;
    }

    /**
     * @param string|null $note
     *
     * @return Basket
     */
    public function setNote(?string $note): Basket
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     *
     * @return Basket
     */
    public function setOrderId(string $orderId): Basket
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * @return array
     */
    public function getBasketItems(): array
    {
        return $this->basketItems;
    }

    /**
     * @param array $basketItems
     *
     * @return Basket
     */
    public function setBasketItems(array $basketItems): Basket
    {
        $this->basketItems = [];

        foreach ($basketItems as $basketItem) {
            $this->addBasketItem($basketItem);
        }

        return $this;
    }

    /**
     * Adds the given BasketItem to the Basket.
     *
     * @param BasketItem $basketItem
     *
     * @return Basket
     */
    public function addBasketItem(BasketItem $basketItem): Basket
    {
        $this->basketItems[] = $basketItem;
        if ($basketItem->getBasketItemReferenceId() === null) {
            $basketItem->setBasketItemReferenceId((string)$this->getKeyOfLastBasketItemAdded());
        }
        return $this;
    }

    /**
     * @param int $index
     *
     * @return BasketItem|null
     */
    public function getBasketItemByIndex(int $index): ?BasketItem
    {
        return $this->basketItems[$index] ?? null;
    }

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
     * Baskets are created on the v3 endpoint. Existing baskets with a non-UUID id
     * (created before v3) are read and updated on the v2 endpoint.
     *
     * {@inheritDoc}
     */
    public function getApiVersion(): string
    {
        $basketId = $this->getId();
        if ($basketId !== null && $basketId !== '' && !IdService::isUUIDResource($basketId)) {
            return ApiVersions::V2;
        }
        return ApiVersions::V3;
    }

    /**
     * {@inheritDoc}
     */
    public function getApiConfig(): string
    {
        if ($this->getApiVersion() === ApiVersions::V3) {
            return PaymentApiConfigBearerAuth::class;
        }
        return parent::getApiConfig();
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
