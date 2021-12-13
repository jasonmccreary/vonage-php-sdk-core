<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Voice\Endpoint\App;
use Vonage\Voice\Endpoint\EndpointFactory;
use Vonage\Voice\Endpoint\Phone;
use Vonage\Voice\Endpoint\SIP;
use Vonage\Voice\Endpoint\VBC;
use Vonage\Voice\Endpoint\Websocket;

uses(VonageTestCase::class);

test('can create app endpoint', function () {
    $this->assertInstanceOf(App::class, (new EndpointFactory())->create([
        'type' => 'app',
        'user' => 'username',
    ]));
});

test('can create phone endpoint', function () {
    $data = [
        'type' => 'phone',
        'number' => '15551231234',
        'onAnswer' => [
            'url' => 'https://test.domain/answerNCCO.json',
            'ringbackTone' => 'https://test.domain/ringback.mp3'
        ]
    ];
    $factory = new EndpointFactory();
    $endpoint = $factory->create($data);

    $this->assertInstanceOf(Phone::class, $endpoint);
    $this->assertSame($data, $endpoint->toArray());
});

test('can create s i p endpoint', function () {
    $data = [
        'type' => 'sip',
        'uri' => 'sip:rebekka@sip.example.com',
    ];
    $endpoint = (new EndpointFactory())->create($data);

    $this->assertInstanceOf(SIP::class, $endpoint);
    $this->assertSame($data['uri'], $endpoint->getId());
});

test('can create v b c endpoint', function () {
    $data = [
        'type' => 'vbc',
        'extension' => '123',
    ];
    $endpoint = (new EndpointFactory())->create($data);

    $this->assertInstanceOf(VBC::class, $endpoint);
    $this->assertSame($data, $endpoint->toArray());
});

test('can create websocket endpoint', function () {
    $data = [
        'type' => 'websocket',
        'uri' => 'https://testdomain.com/websocket',
        'content-type' => 'audio/116;rate=8000',
        'headers' => ['key' => 'value'],
    ];
    $endpoint = (new EndpointFactory())->create($data);

    $this->assertInstanceOf(Websocket::class, $endpoint);
    $this->assertSame($data, $endpoint->toArray());
});

test('throws exception on unknown endpoint', function () {
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Unknown endpoint type');

    (new EndpointFactory())
        ->create([
            'type' => 'foo',
            'user' => 'username',
        ]);
});
