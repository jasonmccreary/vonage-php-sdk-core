<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Voice\Endpoint\Websocket;

uses(VonageTestCase::class);

test('sets u r l at creation', function () {
    $this->assertSame($this->uri, (new Websocket($this->uri))->getId());
});

test('can add header', function () {
    $endpoint = (new Websocket($this->uri))->addHeader('key', 'value');

    $this->assertSame($this->uri, $endpoint->getId());
    $this->assertSame(['key' => 'value'], $endpoint->getHeaders());
});

test('factory creates websocket endpoint', function () {
    $this->assertSame($this->uri, (Websocket::factory($this->uri))->getId());
});

test('factory creates additional options', function () {
    $endpoint = Websocket::factory($this->uri, [
        'headers' => ['key' => 'value'],
        'content-type' => Websocket::TYPE_16000
    ]);

    $this->assertSame($this->uri, $endpoint->getId());
    $this->assertSame(['key' => 'value'], $endpoint->getHeaders());
    $this->assertSame(Websocket::TYPE_16000, $endpoint->getContentType());
});

test('to array has correct structure', function () {
    $this->assertSame([
        'type' => 'websocket',
        'uri' => $this->uri,
        'content-type' => Websocket::TYPE_8000
    ], (new Websocket($this->uri))->toArray());
});

test('to array adds headers', function () {
    $headers = ['key' => 'value'];

    $this->assertSame([
        'type' => 'websocket',
        'uri' => $this->uri,
        'content-type' => Websocket::TYPE_8000,
        'headers' => $headers,
    ], (new Websocket($this->uri))->setHeaders($headers)->toArray());
});

test('serializes to j s o n correctly', function () {
    $this->assertSame([
        'type' => 'websocket',
        'uri' => $this->uri,
        'content-type' => Websocket::TYPE_8000
    ], (new Websocket($this->uri))->jsonSerialize());
});
