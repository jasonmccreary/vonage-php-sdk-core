<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Voice\Endpoint\EndpointInterface;
use Vonage\Voice\Endpoint\Phone;
use Vonage\Voice\NCCO\Action\Connect;
use Vonage\Voice\Webhook;

uses(VonageTestCase::class);

beforeEach(function () {
    $this->endpoint = new Phone('15551231234');
});

test('simple setup', function () {
    $this->assertSame([
        'action' => 'connect',
        'endpoint' => [
            [
                'type' => 'phone',
                'number' => '15551231234'
            ]
        ]
    ], (new Connect($this->endpoint))->toNCCOArray());
});

test('can set additional information', function () {
    $webhook = new Webhook('https://test.domain/events');
    $action = (new Connect($this->endpoint))
        ->setFrom('15553216547')
        ->setMachineDetection(Connect::MACHINE_CONTINUE)
        ->setEventType(Connect::EVENT_TYPE_SYNCHRONOUS)
        ->setLimit(6000)
        ->setRingbackTone('https://test.domain/ringback.mp3')
        ->setTimeout(10)
        ->setEventWebhook($webhook);

    $this->assertSame('15553216547', $action->getFrom());
    $this->assertSame(Connect::MACHINE_CONTINUE, $action->getMachineDetection());
    $this->assertSame(Connect::EVENT_TYPE_SYNCHRONOUS, $action->getEventType());
    $this->assertSame(6000, $action->getLimit());
    $this->assertSame('https://test.domain/ringback.mp3', $action->getRingbackTone());
    $this->assertSame(10, $action->getTimeout());
    $this->assertSame($webhook, $action->getEventWebhook());
});

test('generates correct n c c o array', function () {
    $webhook = new Webhook('https://test.domain/events');
    $ncco = (new Connect($this->endpoint))
        ->setFrom('15553216547')
        ->setMachineDetection(Connect::MACHINE_CONTINUE)
        ->setEventType(Connect::EVENT_TYPE_SYNCHRONOUS)
        ->setLimit(6000)
        ->setRingbackTone('https://test.domain/ringback.mp3')
        ->setTimeout(10)
        ->setEventWebhook($webhook)
        ->toNCCOArray();

    $this->assertSame('15553216547', $ncco['from']);
    $this->assertSame(Connect::MACHINE_CONTINUE, $ncco['machineDetection']);
    $this->assertSame(Connect::EVENT_TYPE_SYNCHRONOUS, $ncco['eventType']);
    $this->assertSame(6000, $ncco['limit']);
    $this->assertSame('https://test.domain/ringback.mp3', $ncco['ringbackTone']);
    $this->assertSame(10, $ncco['timeout']);
    $this->assertSame(['https://test.domain/events'], $ncco['eventUrl']);
    $this->assertSame('POST', $ncco['eventMethod']);
});

test('j s o n serializes to correct structure', function () {
    $webhook = new Webhook('https://test.domain/events');
    $ncco = (new Connect($this->endpoint))
        ->setFrom('15553216547')
        ->setMachineDetection(Connect::MACHINE_CONTINUE)
        ->setEventType(Connect::EVENT_TYPE_SYNCHRONOUS)
        ->setLimit(6000)
        ->setRingbackTone('https://test.domain/ringback.mp3')
        ->setTimeout(10)
        ->setEventWebhook($webhook)
        ->jsonSerialize();

    $this->assertSame('15553216547', $ncco['from']);
    $this->assertSame(Connect::MACHINE_CONTINUE, $ncco['machineDetection']);
    $this->assertSame(Connect::EVENT_TYPE_SYNCHRONOUS, $ncco['eventType']);
    $this->assertSame(6000, $ncco['limit']);
    $this->assertSame('https://test.domain/ringback.mp3', $ncco['ringbackTone']);
    $this->assertSame(10, $ncco['timeout']);
    $this->assertSame(['https://test.domain/events'], $ncco['eventUrl']);
    $this->assertSame('POST', $ncco['eventMethod']);
});

test('invalid machine detection throws exception', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Unknown machine detection type');

    (new Connect($this->endpoint))->setMachineDetection('foo');
});

test('invalid event type throw exception', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Unknown event type for Connection action');

    (new Connect($this->endpoint))->setEventType('foo');
});
