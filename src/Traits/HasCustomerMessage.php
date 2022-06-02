<?php
/**
 * This trait adds the message properties to a resource class.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
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

use UnzerSDK\Resources\EmbeddedResources\Message;

trait HasCustomerMessage
{
    /** @var Message $message */
    private $message;

    //<editor-fold desc="Getters/Setters">

    /**
     * @return Message
     */
    public function getMessage(): Message
    {
        if (!$this->message instanceof Message) {
            $this->message = new Message();
        }

        return $this->message;
    }

    //</editor-fold>
}
