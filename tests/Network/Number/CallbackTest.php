<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Vonage\Network\Number\Callback;

test('methods match data', function () {
    expect($this->callback->getId())->toEqual($this->data['request_id']);
    expect($this->callback->getCallbackTotal())->toEqual($this->data['callback_total_parts']);
    expect($this->callback->getCallbackIndex())->toEqual($this->data['callback_part']);
    expect($this->callback->getNumber())->toEqual($this->data['number']);
});

/**
 *
 * @param $key
 * @param $value
 * @param $method
 * @param $expected
 */
test('optional data', function ($key, $value, $method, $expected) {
    $has = 'has' . $method;
    $get = 'get' . $method;

    $this->assertFalse($this->callback->$has());
    $this->assertNull($this->callback->$get());

    $callback = new Callback(array_merge($this->data, [$key => $value]));

    $this->assertTrue($callback->$has());
    $this->assertEquals($expected, $callback->$get());
})->with('optionalData');

// Datasets
dataset('optionalData', [
    ['number_type', 'unknown', 'Type', 'unknown'],
    ['carrier_network_code', 'CODE', 'Network', 'CODE'],
    ['carrier_network_name', 'NAME', 'NetworkName', 'NAME'],
    ['valid', 'unknown', 'Valid', 'unknown'],
    ['ported', 'unknown', 'Ported', 'unknown'],
    ['reachable', 'unknown', 'Reachable', 'unknown'],
    ['roaming', 'unknown', 'Roaming', 'unknown'],
    ['roaming_country_code', 'CODE', 'RoamingCountry', 'CODE'],
    ['roaming_network_code', 'CODE', 'RoamingNetwork', 'CODE'],
]);

// Helpers
function setup(): void
{
    test()->callback = new Callback(test()->data);
}
