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

    expect($action->getFrom())->toBe('15553216547');
    expect($action->getMachineDetection())->toBe(Connect::MACHINE_CONTINUE);
    expect($action->getEventType())->toBe(Connect::EVENT_TYPE_SYNCHRONOUS);
    expect($action->getLimit())->toBe(6000);
    expect($action->getRingbackTone())->toBe('https://test.domain/ringback.mp3');
    expect($action->getTimeout())->toBe(10);
    expect($action->getEventWebhook())->toBe($webhook);
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

    expect($ncco['from'])->toBe('15553216547');
    expect($ncco['machineDetection'])->toBe(Connect::MACHINE_CONTINUE);
    expect($ncco['eventType'])->toBe(Connect::EVENT_TYPE_SYNCHRONOUS);
    expect($ncco['limit'])->toBe(6000);
    expect($ncco['ringbackTone'])->toBe('https://test.domain/ringback.mp3');
    expect($ncco['timeout'])->toBe(10);
    expect($ncco['eventUrl'])->toBe(['https://test.domain/events']);
    expect($ncco['eventMethod'])->toBe('POST');
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

    expect($ncco['from'])->toBe('15553216547');
    expect($ncco['machineDetection'])->toBe(Connect::MACHINE_CONTINUE);
    expect($ncco['eventType'])->toBe(Connect::EVENT_TYPE_SYNCHRONOUS);
    expect($ncco['limit'])->toBe(6000);
    expect($ncco['ringbackTone'])->toBe('https://test.domain/ringback.mp3');
    expect($ncco['timeout'])->toBe(10);
    expect($ncco['eventUrl'])->toBe(['https://test.domain/events']);
    expect($ncco['eventMethod'])->toBe('POST');
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
