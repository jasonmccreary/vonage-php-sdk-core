<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use RuntimeException;
use Vonage\Voice\NCCO\Action\Input;

uses(VonageTestCase::class);

test('speech settings generate correct n c c o', function () {
    $ncco = (new Input())
        ->setSpeechUUID('aaaaaaaa-bbbb-cccc-dddd-0123456789ab')
        ->setSpeechEndOnSilence(5)
        ->setSpeechLanguage('en-US')
        ->setSpeechContext(['foo', 'bar'])
        ->setSpeechStartTimeout(2)
        ->setSpeechMaxDuration(10)
        ->toNCCOArray();

    $this->assertSame(['aaaaaaaa-bbbb-cccc-dddd-0123456789ab'], $ncco['speech']->uuid);
    $this->assertSame(5, $ncco['speech']->endOnSilence);
    $this->assertSame('en-US', $ncco['speech']->language);
    $this->assertSame(['foo', 'bar'], $ncco['speech']->context);
    $this->assertSame(2, $ncco['speech']->startTimeout);
    $this->assertSame(10, $ncco['speech']->maxDuration);
});

test('speech settings are set in factory', function () {
    $action = Input::factory([
        'action' => 'input',
        'speech' => [
            'uuid' => ['aaaaaaaa-bbbb-cccc-dddd-0123456789ab'],
            'endOnSilence' => '5',
            'language' => 'en-US',
            'context' => ['foo', 'bar'],
            'startTimeout' => '2',
            'maxDuration' => '10'
        ]
    ]);

    $this->assertSame('aaaaaaaa-bbbb-cccc-dddd-0123456789ab', $action->getSpeechUUID());
    $this->assertSame(5, $action->getSpeechEndOnSilence());
    $this->assertSame('en-US', $action->getSpeechLanguage());
    $this->assertSame(['foo', 'bar'], $action->getSpeechContext());
    $this->assertSame(2, $action->getSpeechStartTimeout());
    $this->assertSame(10, $action->getSpeechMaxDuration());
});

test('d t m f settings generate correct n c c o', function () {
    $ncco = (new Input())
        ->setDtmfMaxDigits(2)
        ->setDtmfSubmitOnHash(true)
        ->setDtmfTimeout(5)
        ->toNCCOArray();

    $this->assertSame(2, $ncco['dtmf']->maxDigits);
    $this->assertSame('true', $ncco['dtmf']->submitOnHash);
    $this->assertSame(5, $ncco['dtmf']->timeOut);
});

test('d t m f settings are set in factory', function () {
    $action = Input::factory([
        'action' => 'input',
        'dtmf' => [
            'timeOut' => '2',
            'maxDigits' => '5',
            'submitOnHash' => 'false',
        ]
    ]);

    $this->assertSame(5, $action->getDtmfMaxDigits());
    $this->assertSame(2, $action->getDtmfTimeout());
    $this->assertFalse($action->getDtmfSubmitOnHash());
});

test('event u r l can be set in factory', function () {
    $data = [
        'action' => 'input',
        'eventUrl' => ['https://test.domain/events'],
        'eventMethod' => 'POST',
        'speech' => [],
    ];

    $action = Input::factory($data);
    $ncco = $action->toNCCOArray();

    $this->assertSame($data['eventUrl'], $ncco['eventUrl']);
    $this->assertSame($data['eventMethod'], $ncco['eventMethod']);
    $this->assertSame($data['eventUrl'][0], $action->getEventWebhook()->getUrl());
    $this->assertSame($data['eventMethod'], $action->getEventWebhook()->getMethod());
});

test('event method defaults to post when not supplied', function () {
    $data = [
        'action' => 'input',
        'eventUrl' => ['https://test.domain/events'],
        'dtmf' => []
    ];

    $action = Input::factory($data);
    $ncco = $action->toNCCOArray();

    $this->assertSame($data['eventUrl'], $ncco['eventUrl']);
    $this->assertSame('POST', $ncco['eventMethod']);
    $this->assertSame($data['eventUrl'][0], $action->getEventWebhook()->getUrl());
    $this->assertSame('POST', $action->getEventWebhook()->getMethod());
});

test('j s o n serialization looks correct', function () {
    $this->assertEquals([
        'action' => 'input',
        'dtmf' => (object)[]
    ], (new Input())->setEnableDtmf(true)->jsonSerialize());
});

test('throws runtime exception if no input defined', function () {
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Input NCCO action must have either speech or DTMF enabled');

    (new Input())->toNCCOArray();
});
