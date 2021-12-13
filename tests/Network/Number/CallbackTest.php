<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Network\Number\Callback;

uses(VonageTestCase::class);


test('methods match data', function () {
    $this->assertEquals($this->data['request_id'], $this->callback->getId());
    $this->assertEquals($this->data['callback_total_parts'], $this->callback->getCallbackTotal());
    $this->assertEquals($this->data['callback_part'], $this->callback->getCallbackIndex());
    $this->assertEquals($this->data['number'], $this->callback->getNumber());
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
