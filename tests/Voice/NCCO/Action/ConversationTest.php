<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Voice\NCCO\Action\Conversation;
use Vonage\Voice\Webhook;

uses(VonageTestCase::class);

test('simple setup', function () {
    $this->assertSame([
        'action' => 'conversation',
        'name' => 'my-conversation'
    ], (new Conversation('my-conversation'))->toNCCOArray());
});

test('can set music on hold', function () {
    $action = new Conversation('my-conversation');
    $action->setMusicOnHoldUrl('https://test.domain/hold.mp3');
    $data = $action->toNCCOArray();

    expect($data['musicOnHoldUrl'])->toBe(['https://test.domain/hold.mp3']);

    $secondAction = Conversation::factory('my-conversation', ['musicOnHoldUrl' => 'https://test.domain/hold2.mp3']);
    $newData = $secondAction->toNCCOArray();

    expect($newData['musicOnHoldUrl'])->toBe(['https://test.domain/hold2.mp3']);
});

test('can add individual speakers', function () {
    $uuid = '6a4d6af0-55a6-4667-be90-8614e4c8e83c';

    $this->assertSame([$uuid], (new Conversation('my-conversation'))
        ->addCanSpeak($uuid)
        ->toNCCOArray()['canSpeak']);
});

test('can add individual listeners', function () {
    $uuid = '6a4d6af0-55a6-4667-be90-8614e4c8e83c';

    $this->assertSame([$uuid], (new Conversation('my-conversation'))
        ->addCanHear($uuid)
        ->toNCCOArray()['canHear']);
});

test('json serializes to correct structure', function () {
    $this->assertSame([
        'action' => 'conversation',
        'name' => 'my-conversation',
        'startOnEnter' => 'true',
        'endOnExit' => 'false',
        'record' => 'false',
    ], (new Conversation('my-conversation'))
        ->setStartOnEnter(true)
        ->setEndOnExit(false)
        ->setRecord(false)
        ->jsonSerialize());
});

test('can set record event url', function () {
    $data = (new Conversation('my-conversation'))
        ->setRecord(true)
        ->setEventWebhook(new Webhook('https://test.domain/events'))
        ->toNCCOArray();

    expect($data['eventUrl'])->toBe(['https://test.domain/events']);
    expect($data['eventMethod'])->toBe('POST');
});

test('webhook set in factory', function () {
    $expected = [
        'action' => 'conversation',
        'name' => 'my-conversation',
        'eventUrl' => ['https://test.domain/events'],
        'eventMethod' => 'GET',
    ];

    $action = Conversation::factory($expected['name'], $expected);

    expect($action->getEventWebhook())->toBeInstanceOf(Webhook::class);
    expect($action->getEventWebhook()->getUrl())->toBe($expected['eventUrl'][0]);
    expect($action->getEventWebhook()->getMethod())->toBe($expected['eventMethod']);
});

test('webhook set in factory without method', function () {
    $expected = [
        'action' => 'conversation',
        'name' => 'my-conversation',
        'eventUrl' => ['https://test.domain/events'],
    ];

    $action = Conversation::factory($expected['name'], $expected);

    expect($action->getEventWebhook())->toBeInstanceOf(Webhook::class);
    expect($action->getEventWebhook()->getUrl())->toBe($expected['eventUrl'][0]);
    expect($action->getEventWebhook()->getMethod())->toBe('POST');
});

test('webhook set in factory with string event url', function () {
    $expected = [
        'action' => 'conversation',
        'name' => 'my-conversation',
        'eventUrl' => 'https://test.domain/events',
    ];

    $action = Conversation::factory($expected['name'], $expected);

    expect($action->getEventWebhook())->toBeInstanceOf(Webhook::class);
    expect($action->getEventWebhook()->getUrl())->toBe($expected['eventUrl']);
    expect($action->getEventWebhook()->getMethod())->toBe('POST');
});
