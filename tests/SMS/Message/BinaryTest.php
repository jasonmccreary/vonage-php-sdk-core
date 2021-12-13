<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\SMS\Message\Binary;


test('can create binary message', function () {
    $data = (new Binary(
        '447700900000',
        '16105551212',
        'EA0601AE02056A0045C60C037761702E6F7A656B692E6875000801034F7A656B69000101',
        '0605040B8423F0'
    ))->toArray();

    expect($data['to'])->toBe('447700900000');
    expect($data['from'])->toBe('16105551212');
    expect($data['body'])->toBe('EA0601AE02056A0045C60C037761702E6F7A656B692E6875000801034F7A656B69000101');
    expect($data['udh'])->toBe('0605040B8423F0');
});

test('can create binary message with protocol i d', function () {
    $data = (new Binary(
        '447700900000',
        '16105551212',
        'EA0601AE02056A0045C60C037761702E6F7A656B692E6875000801034F7A656B69000101',
        '0605040B8423F0',
        45
    ))->toArray();

    expect($data['to'])->toBe('447700900000');
    expect($data['from'])->toBe('16105551212');
    expect($data['body'])->toBe('EA0601AE02056A0045C60C037761702E6F7A656B692E6875000801034F7A656B69000101');
    expect($data['udh'])->toBe('0605040B8423F0');
    expect($data['protocol-id'])->toBe(45);
});
