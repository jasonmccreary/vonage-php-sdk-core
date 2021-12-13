<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\SMS\Message\WAPPush;


test('can create w a p message', function () {
    $data = (new WAPPush(
        '447700900000',
        '16105551212',
        'Check In Now!',
        'https://test.domain/check-in',
        300000
    ))->toArray();

    expect($data['to'])->toBe('447700900000');
    expect($data['from'])->toBe('16105551212');
    expect($data['title'])->toBe('Check In Now!');
    expect($data['url'])->toBe('https://test.domain/check-in');
    expect($data['validity'])->toBe(300000);
});
