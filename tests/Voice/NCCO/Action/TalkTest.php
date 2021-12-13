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

    expect($action->jsonSerialize())->toBe($expected);
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

    expect($action->getLanguage())->toBe($expected['language']);
    expect($action->getLanguageStyle())->toBe(0);

    expect($action->toNCCOArray())->toBe($expected);
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

    expect($action->getLanguage())->toBe($expected['language']);
    expect($action->getLanguageStyle())->toBe((int) $expected['style']);

    expect($action->toNCCOArray())->toBe($expected);
});

test('factory sets language', function () {
    $expected = [
        'action' => 'talk',
        'text' => 'Hello',
        'language' => 'en-US',
        'style' => '0'
    ];

    $action = Talk::factory($expected['text'], $expected);

    expect($action->getLanguage())->toBe($expected['language']);
    expect($action->getLanguageStyle())->toBe(0);

    expect($action->toNCCOArray())->toBe($expected);
});

test('factory sets language and style', function () {
    $expected = [
        'action' => 'talk',
        'text' => 'Hello',
        'language' => 'en-US',
        'style' => '3'
    ];

    $action = Talk::factory($expected['text'], $expected);

    expect($action->getLanguage())->toBe($expected['language']);
    expect($action->getLanguageStyle())->toBe((int) $expected['style']);

    expect($action->toNCCOArray())->toBe($expected);
});
