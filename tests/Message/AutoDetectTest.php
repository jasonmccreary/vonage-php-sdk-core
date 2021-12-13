<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Message\AutoDetect;


/**
 * When creating a message, it should not auto-detect encoding by default
 */
test('auto detect enabled by default', function () {
    $message = new AutoDetect('to', 'from', 'Example Message');

    expect($message->isEncodingDetectionEnabled())->toBeTrue();
});
