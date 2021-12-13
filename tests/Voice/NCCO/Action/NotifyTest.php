<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Voice\NCCO\Action\Notify;
use Vonage\Voice\Webhook;

uses(VonageTestCase::class);

test('can set additional information', function () {
    $webhook = new Webhook('https://test.domain/events');
    $action = (new Notify(['foo' => 'bar'], $webhook))->setEventWebhook($webhook);

    expect($action->getPayload())->toBe(['foo' => 'bar']);
    expect($action->getEventWebhook())->toBe($webhook);
});

test('can generate from factory', function () {
    $data = [
        'action' => 'notify',
        'payload' => ['foo' => 'bar'],
        'eventUrl' => 'https://test.domain/events',
    ];

    $action = Notify::factory(['foo' => 'bar'], $data);

    expect($action->getPayload())->toBe(['foo' => 'bar']);
    expect($action->getEventWebhook()->getUrl())->toBe('https://test.domain/events');
    expect($action->getEventWebhook()->getMethod())->toBe('POST');
});

test('generates correct n c c o array', function () {
    $webhook = new Webhook('https://test.domain/events');

    $action = new Notify(['foo' => 'bar'], $webhook);
    $action->setEventWebhook($webhook);

    $ncco = $action->toNCCOArray();

    expect($ncco['action'])->toBe('notify');
    expect($ncco['payload'])->toBe(['foo' => 'bar']);
    expect($ncco['eventUrl'])->toBe(['https://test.domain/events']);
    expect($ncco['eventMethod'])->toBe('POST');
});

test('j s o n serializes to correct structure', function () {
    $webhook = new Webhook('https://test.domain/events');
    $ncco = (new Notify(['foo' => 'bar'], $webhook))->setEventWebhook($webhook)->jsonSerialize();

    expect($ncco['action'])->toBe('notify');
    expect($ncco['payload'])->toBe(['foo' => 'bar']);
    expect($ncco['eventUrl'])->toBe(['https://test.domain/events']);
    expect($ncco['eventMethod'])->toBe('POST');
});

test('can add to payload', function () {
    $webhook = new Webhook('https://test.domain/events');
    $action = (new Notify(['foo' => 'bar'], $webhook))->addToPayload('baz', 'biff');

    expect($action->getPayload())->toBe(['foo' => 'bar', 'baz' => 'biff']);
});

test('throws exception when missing event u r l', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Must supply at least an eventUrl for Notify NCCO');

    Notify::factory(['foo' => 'bar'], []);
});
