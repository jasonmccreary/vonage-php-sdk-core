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
use Vonage\Network\Number\Response;

uses(VonageTestCase::class);

beforeEach(function () {
    $this->response = new Response($this->data);
});

test('methods match data', function () {
    expect($this->response->getId())->toEqual($this->data['request_id']);
    expect($this->response->getNumber())->toEqual($this->data['number']);
    expect($this->response->getPrice())->toEqual($this->data['request_price']);
    expect($this->response->getBalance())->toEqual($this->data['remaining_balance']);
    expect($this->response->getCallbackTotal())->toEqual($this->data['callback_total_parts']);
    expect($this->response->getStatus())->toEqual($this->data['status']);
});

/**
 *
 * @param $property
 */
test('cant get optional data before callback', function ($property) {
    $this->expectException(BadMethodCallException::class);

    $get = 'get' . $property;
    $this->response->$get();
})->with('getOptionalProperties');

/**
 *
 * @param $property
 */
test('cant has optional data before callback', function ($property) {
    $this->expectException(BadMethodCallException::class);

    $has = 'has' . $property;
    $this->response->$has();
})->with('getOptionalProperties');

/**
 * Test that any optional parameters are simply passed to the callback stack (when there is at least one), until the
 * value is found (or return the last callback's data).
 *
 *
 * @param $property
 */
test('optional data proxies callback', function ($property) {
    $has = 'has' . $property;
    $get = 'get' . $property;

    $callback = $this->getMockBuilder(Callback::class)
        ->disableOriginalConstructor()
        ->setMethods(['getId', $has, $get])
        ->getMock();

    //setup so the request will accept the callback
    $callback
        ->method('getId')
        ->willReturn($this->data['request_id']);

    $callback->expects(self::atLeastOnce())
        ->method($has)
        ->willReturnCallback(function () {
            static $called = false;
            if (!$called) {
                $called = true;
                return false;
            }

            return true;
        });

    $callback->expects(self::atLeastOnce())
        ->method($get)
        ->willReturnCallback(function () {
            static $called = false;
            if (!$called) {
                $called = true;
                return null;
            }

            return 'data';
        });

    $response = new Response($this->data, [$callback, $callback]);

    $this->assertTrue($response->$has());
    $this->assertEquals('data', $response->$get());
})->with('getOptionalProperties');

// Datasets
dataset('getOptionalProperties', [
    ['Type'],
    ['Network'],
    ['NetworkName'],
    ['Valid'],
    ['Ported'],
    ['Reachable'],
    ['Roaming'],
    ['RoamingCountry'],
    ['RoamingNetwork'],
]);
