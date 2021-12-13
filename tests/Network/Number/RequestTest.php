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



test('null values not present', function () {
    $request = new Request('14443332121', 'http://example.com');
    $params = $request->getParams();

    expect($params)->toHaveCount(2);
    $this->assertArrayHasKey('number', $params);
    $this->assertArrayHasKey('callback', $params);
});

test('number matches params', function () {
    $request = new Request('14443332121', 'http://example.com');
    $params = $request->getParams();

    $this->assertArrayHasKey('number', $params);
    expect($params['number'])->toEqual('14443332121');
});

test('callback matches params', function () {
    $request = new Request('14443332121', 'http://example.com');
    $params = $request->getParams();

    $this->assertArrayHasKey('callback', $params);
    expect($params['callback'])->toEqual('http://example.com');
});

test('features matches params', function () {
    $request = new Request(
        '14443332121',
        'http://example.com',
        [Request::FEATURE_CARRIER, Request::FEATURE_PORTED]
    );
    $params = $request->getParams();

    $this->assertArrayHasKey('features', $params);
    expect($params['features'])->toBeString();

    $array = explode(',', $params['features']);

    expect($array)->toHaveCount(2);
    expect($array)->toContain(Request::FEATURE_CARRIER);
    expect($array)->toContain(Request::FEATURE_PORTED);
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
    expect($params['callback_timeout'])->toEqual(100);
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
    expect($params['callback_method'])->toEqual('POST');
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
    expect($params['client_ref'])->toEqual('ref');
});
