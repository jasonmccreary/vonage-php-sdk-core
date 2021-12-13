<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Network\Number\Request;

uses(VonageTestCase::class);


test('null values not present', function () {
    $request = new Request('14443332121', 'http://example.com');
    $params = $request->getParams();

    $this->assertCount(2, $params);
    $this->assertArrayHasKey('number', $params);
    $this->assertArrayHasKey('callback', $params);
});

test('number matches params', function () {
    $request = new Request('14443332121', 'http://example.com');
    $params = $request->getParams();

    $this->assertArrayHasKey('number', $params);
    $this->assertEquals('14443332121', $params['number']);
});

test('callback matches params', function () {
    $request = new Request('14443332121', 'http://example.com');
    $params = $request->getParams();

    $this->assertArrayHasKey('callback', $params);
    $this->assertEquals('http://example.com', $params['callback']);
});

test('features matches params', function () {
    $request = new Request(
        '14443332121',
        'http://example.com',
        [Request::FEATURE_CARRIER, Request::FEATURE_PORTED]
    );
    $params = $request->getParams();

    $this->assertArrayHasKey('features', $params);
    $this->assertIsString($params['features']);

    $array = explode(',', $params['features']);

    $this->assertCount(2, $array);
    $this->assertContains(Request::FEATURE_CARRIER, $array);
    $this->assertContains(Request::FEATURE_PORTED, $array);
});

test('callback timeout matches params', function () {
    $request = new Request(
        '14443332121',
        'http://example.com',
        [],
        100
    );
    $params = $request->getParams();

    $this->assertArrayHasKey('callback_timeout', $params);
    $this->assertEquals(100, $params['callback_timeout']);
});

test('callback method matches params', function () {
    $request = new Request(
        '14443332121',
        'http://example.com',
        [],
        null,
        'POST'
    );
    $params = $request->getParams();

    $this->assertArrayHasKey('callback_method', $params);
    $this->assertEquals('POST', $params['callback_method']);
});

test('ref matches params', function () {
    $request = new Request(
        '14443332121',
        'http://example.com',
        [],
        null,
        null,
        'ref'
    );
    $params = $request->getParams();

    $this->assertArrayHasKey('client_ref', $params);
    $this->assertEquals('ref', $params['client_ref']);
});
