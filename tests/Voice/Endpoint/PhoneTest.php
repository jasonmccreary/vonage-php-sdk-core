<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Voice\Endpoint\Phone;


test('default endpoint is created properly', function () {
    $endpoint = new Phone($this->number);

    expect($endpoint->getId())->toBe($this->number);
    expect($endpoint->getDtmfAnswer())->toBeNull();
    expect($endpoint->getRingbackTone())->toBeNull();
    expect($endpoint->getUrl())->toBeNull();
});

test('factory creates phone endpoint', function () {
    $endpoint = Phone::factory($this->number, [
        'dtmfAnswer' => $this->dtmfAnswer,
        'onAnswer' => [
            'url' => $this->url,
            'ringbackTone' => $this->ringbackTone
        ]
    ]);

    expect($endpoint->getId())->toBe($this->number);
    expect($endpoint->getUrl())->toBe($this->url);
    expect($endpoint->getRingbackTone())->toBe($this->ringbackTone);
});

test('factory handles legacy ringback argument', function () {
    $endpoint = Phone::factory($this->number, [
        'dtmfAnswer' => $this->dtmfAnswer,
        'onAnswer' => [
            'url' => $this->url,
            'ringback' => $this->ringbackTone
        ]
    ]);

    expect($endpoint->getId())->toBe($this->number);
    expect($endpoint->getUrl())->toBe($this->url);
    expect($endpoint->getRingbackTone())->toBe($this->ringbackTone);
});

test('to array has correct structure', function () {
    $expected = [
        'type' => $this->type,
        'number' => $this->number
    ];

    expect((new Phone($this->number))->toArray())->toBe($expected);
});

test('ringback not returned if u r l not set', function () {
    $expected = [
        'type' => $this->type,
        'number' => $this->number
    ];

    $this->assertSame(
        $expected,
        (new Phone($this->number))->setRingbackTone($this->ringbackTone)->toArray()
    );
});

test('ringback is returned if u r l is set', function () {
    $expected = [
        'type' => $this->type,
        'number' => $this->number,
        'onAnswer' => [
            'url' => $this->url,
            'ringbackTone' => $this->ringbackTone
        ]
    ];

    $this->assertSame(
        $expected,
        (new Phone($this->number))
            ->setRingbackTone($this->ringbackTone)
            ->setUrl($this->url)->toArray()
    );
});

test('serializes to j s o n correctly', function () {
    $expected = [
        'type' => $this->type,
        'number' => $this->number,
        'dtmfAnswer' => $this->dtmfAnswer
    ];

    $endpoint = new Phone($this->number);
    $endpoint->setDtmfAnswer($this->dtmfAnswer);

    expect($endpoint->jsonSerialize())->toBe($expected);
});
