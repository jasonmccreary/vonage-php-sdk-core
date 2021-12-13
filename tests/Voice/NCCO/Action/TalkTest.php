<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Voice\NCCO\Action\Talk;

uses(VonageTestCase::class);

test('simple setup', function () {
    $this->assertSame([
        'action' => 'talk',
        'text' => 'Hello',
    ], (new Talk('Hello'))->jsonSerialize());
});

test('json serialize looks correct', function () {
    $expected = [
        'action' => 'talk',
        'text' => 'Hello',
        'bargeIn' => 'false',
        'level' => '0',
        'loop' => '1',
        'voiceName' => 'kimberly'
    ];

    $action = new Talk('Hello');
    $action->setBargeIn(false);
    $action->setLevel(0);
    $action->setLoop(1);
    @$action->setVoiceName('kimberly');

    $this->assertSame($expected, $action->jsonSerialize());
});

test('can set language', function () {
    $expected = [
        'action' => 'talk',
        'text' => 'Hello',
        'language' => 'en-US',
        'style' => '0'
    ];

    $action = new Talk($expected['text']);
    $action->setLanguage($expected['language']);

    $this->assertSame($expected['language'], $action->getLanguage());
    $this->assertSame(0, $action->getLanguageStyle());

    $this->assertSame($expected, $action->toNCCOArray());
});

test('can set language style', function () {
    $expected = [
        'action' => 'talk',
        'text' => 'Hello',
        'language' => 'en-US',
        'style' => '3'
    ];

    $action = new Talk($expected['text']);
    $action->setLanguage($expected['language'], (int) $expected['style']);

    $this->assertSame($expected['language'], $action->getLanguage());
    $this->assertSame((int) $expected['style'], $action->getLanguageStyle());

    $this->assertSame($expected, $action->toNCCOArray());
});

test('factory sets language', function () {
    $expected = [
        'action' => 'talk',
        'text' => 'Hello',
        'language' => 'en-US',
        'style' => '0'
    ];

    $action = Talk::factory($expected['text'], $expected);

    $this->assertSame($expected['language'], $action->getLanguage());
    $this->assertSame(0, $action->getLanguageStyle());

    $this->assertSame($expected, $action->toNCCOArray());
});

test('factory sets language and style', function () {
    $expected = [
        'action' => 'talk',
        'text' => 'Hello',
        'language' => 'en-US',
        'style' => '3'
    ];

    $action = Talk::factory($expected['text'], $expected);

    $this->assertSame($expected['language'], $action->getLanguage());
    $this->assertSame((int) $expected['style'], $action->getLanguageStyle());

    $this->assertSame($expected, $action->toNCCOArray());
});
