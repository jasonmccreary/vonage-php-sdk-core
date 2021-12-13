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


test('sets u r l at creation', function () {
    expect((new Websocket($this->uri))->getId())->toBe($this->uri);
});

test('can add header', function () {
    $endpoint = (new Websocket($this->uri))->addHeader('key', 'value');

    expect($endpoint->getId())->toBe($this->uri);
    expect($endpoint->getHeaders())->toBe(['key' => 'value']);
});

test('factory creates websocket endpoint', function () {
    $this->assertSame($this->uri, (Websocket::factory($this->uri))->getId());
});

test('factory creates additional options', function () {
    $endpoint = Websocket::factory($this->uri, [
        'headers' => ['key' => 'value'],
        'content-type' => Websocket::TYPE_16000
    ]);

    expect($endpoint->getId())->toBe($this->uri);
    expect($endpoint->getHeaders())->toBe(['key' => 'value']);
    expect($endpoint->getContentType())->toBe(Websocket::TYPE_16000);
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
