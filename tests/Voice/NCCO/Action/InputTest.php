<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Vonage\Voice\NCCO\Action\Input;

test('speech settings generate correct n c c o', function () {
    $ncco = (new Input())
        ->setSpeechUUID('aaaaaaaa-bbbb-cccc-dddd-0123456789ab')
        ->setSpeechEndOnSilence(5)
        ->setSpeechLanguage('en-US')
        ->setSpeechContext(['foo', 'bar'])
        ->setSpeechStartTimeout(2)
        ->setSpeechMaxDuration(10)
        ->toNCCOArray();

    expect($ncco['speech']->uuid)->toBe(['aaaaaaaa-bbbb-cccc-dddd-0123456789ab']);
    expect($ncco['speech']->endOnSilence)->toBe(5);
    expect($ncco['speech']->language)->toBe('en-US');
    expect($ncco['speech']->context)->toBe(['foo', 'bar']);
    expect($ncco['speech']->startTimeout)->toBe(2);
    expect($ncco['speech']->maxDuration)->toBe(10);
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

    expect($action->getSpeechUUID())->toBe('aaaaaaaa-bbbb-cccc-dddd-0123456789ab');
    expect($action->getSpeechEndOnSilence())->toBe(5);
    expect($action->getSpeechLanguage())->toBe('en-US');
    expect($action->getSpeechContext())->toBe(['foo', 'bar']);
    expect($action->getSpeechStartTimeout())->toBe(2);
    expect($action->getSpeechMaxDuration())->toBe(10);
});

test('d t m f settings generate correct n c c o', function () {
    $ncco = (new Input())
        ->setDtmfMaxDigits(2)
        ->setDtmfSubmitOnHash(true)
        ->setDtmfTimeout(5)
        ->toNCCOArray();

    expect($ncco['dtmf']->maxDigits)->toBe(2);
    expect($ncco['dtmf']->submitOnHash)->toBe('true');
    expect($ncco['dtmf']->timeOut)->toBe(5);
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

    expect($action->getDtmfMaxDigits())->toBe(5);
    expect($action->getDtmfTimeout())->toBe(2);
    expect($action->getDtmfSubmitOnHash())->toBeFalse();
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

    expect($ncco['eventUrl'])->toBe($data['eventUrl']);
    expect($ncco['eventMethod'])->toBe($data['eventMethod']);
    expect($action->getEventWebhook()->getUrl())->toBe($data['eventUrl'][0]);
    expect($action->getEventWebhook()->getMethod())->toBe($data['eventMethod']);
});

test('event method defaults to post when not supplied', function () {
    $data = [
        'action' => 'input',
        'eventUrl' => ['https://test.domain/events'],
        'dtmf' => []
    ];

    $action = Input::factory($data);
    $ncco = $action->toNCCOArray();

    expect($ncco['eventUrl'])->toBe($data['eventUrl']);
    expect($ncco['eventMethod'])->toBe('POST');
    expect($action->getEventWebhook()->getUrl())->toBe($data['eventUrl'][0]);
    expect($action->getEventWebhook()->getMethod())->toBe('POST');
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
