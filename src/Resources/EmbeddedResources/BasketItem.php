<?php

namespace UnzerSDK\Resources\EmbeddedResources;

use UnzerSDK\Resources\AbstractUnzerResource;
use UnzerSDK\Resources\V2\BasketItemProperties as BasketV2ItemProperties;

/**
 * This trait adds amount properties to a class.
 *
 * @link  https://docs.unzer.com/
 *
 */
class BasketItem extends AbstractUnzerResource
{
    use BasketV2ItemProperties;
}
