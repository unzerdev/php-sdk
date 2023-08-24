<?php

/**
 * This trait adds the short id and unique id property to a class.
 *
 * Copyright (C) 2021 - today Unzer E-Com GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link  https://docs.unzer.com/
 *
 * @package  UnzerSDK\Traits
 */

namespace UnzerSDK\Traits;

use UnzerSDK\Resources\EmbeddedResources\CardTransactionData;

trait HasRecurrenceType
{
    /**
     * @return string|null
     */
    public function getRecurrenceType(): ?string
    {
        $cardTransactionData = $this->getCardTransactionData();
        if ($cardTransactionData instanceof CardTransactionData) {
            return $cardTransactionData->getRecurrenceType();
        }

        return $this->getAdditionalTransactionData()->card['recurrenceType'] ?? null;
    }

    /**
     * @param string $recurrenceType Recurrence type used for recurring payment.
     *
     * @return $this
     */
    public function setRecurrenceType(string $recurrenceType): self
    {
        $card = $this->getCardTransactionData() ?? new CardTransactionData();
        $card->setRecurrenceType($recurrenceType);
        $this->addAdditionalTransactionData('card', $card);

        return $this;
    }
}
