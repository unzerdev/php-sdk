<?php
/**
 * This is the partial including generic scripts for the payment example index pages.
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
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/examples
 */
?>

    <script>
        $('.ui.accordion')
            .accordion()
        ;

        function handleResponseJson(response) {
            JSON.parse(response).forEach(function (item) {
                switch (item['result']) {
                    case 'success':
                        logSuccess(item['message']);
                        break;
                    case 'info':
                        logInfo(item['message']);
                        break;
                    case 'redirect':
                        window.location.href = item['redirectUrl'];
                        break;
                    default:
                        logError(item['message']);
                        break;
                }
            })
        }
    </script>
