<?php
/**
 * This is the failure page for the example payments.
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

include 'assets/partials/_resultPage_php.php';
?>

<!DOCTYPE html>
<html>

    <?php include 'assets/partials/_resultPage_html.php'; ?>

    <body>
        <div class="ui container messages">
            <div class="ui red info message">
                <div class="header">Failure</div>
                <p>There has been an error completing the payment.</p>
                <?php
                    echo renderPaymentDetails($payment);
                ?>
            </div>
            <a href="<?php echo EXAMPLE_URL; ?>">go back</a>
        </div>
    </body>

</html>
