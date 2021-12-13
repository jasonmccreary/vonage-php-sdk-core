<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Voice\Message\Message;

uses(VonageTestCase::class);

beforeEach(function () {
    $this->message = new Message($this->text, $this->to, $this->from);
});

test('constructor params', function () {
    $params = $this->message->getParams();

    $this->assertArrayHasKey('text', $params);
    $this->assertArrayHasKey('to', $params);
    $this->assertArrayHasKey('from', $params);
    expect($params['text'])->toEqual($this->text);
    expect($params['to'])->toEqual($this->to);
    expect($params['from'])->toEqual($this->from);
});

test('from is optional', function () {
    $message = new Message($this->text, $this->to);
    $params = $message->getParams();

    $this->assertArrayNotHasKey('from', $params);
});

test('callback', function () {
    $this->message->setCallback('http://example.com');
    $params = $this->message->getParams();

    $this->assertArrayHasKey('callback', $params);
    expect($params['callback'])->toEqual('http://example.com');
    $this->assertArrayNotHasKey('callback_method', $params);

    $this->message->setCallback('http://example.com', 'POST');
    $params = $this->message->getParams();

    $this->assertArrayHasKey('callback', $params);
    expect($params['callback'])->toEqual('http://example.com');
    $this->assertArrayHasKey('callback_method', $params);
    expect($params['callback_method'])->toEqual('POST');

    $this->message->setCallback('http://example.com');
    $params = $this->message->getParams();

    $this->assertArrayHasKey('callback', $params);
    expect($params['callback'])->toEqual('http://example.com');
    $this->assertArrayNotHasKey('callback_method', $params);
});

test('machine', function () {
    $this->message->setMachineDetection();
    $params = $this->message->getParams();

    $this->assertArrayHasKey('machine_detection', $params);
    $this->assertArrayNotHasKey('machine_timeout', $params);
    expect($params['machine_detection'])->toEqual('hangup');

    $this->message->setMachineDetection(true, 100);
    $params = $this->message->getParams();

    $this->assertArrayHasKey('machine_detection', $params);
    $this->assertArrayHasKey('machine_timeout', $params);
    expect($params['machine_detection'])->toEqual('hangup');
    expect($params['machine_timeout'])->toEqual(100);

    $this->message->setMachineDetection(false);
    $params = $this->message->getParams();

    $this->assertArrayHasKey('machine_detection', $params);
    $this->assertArrayNotHasKey('machine_timeout', $params);
    expect($params['machine_detection'])->toEqual('true');
});

/**
 *
 * @param $setter
 * @param $param
 * @param $values
 */
test('optional params', function ($setter, $param, $values) {
    //check no default value
    $params = $this->message->getParams();
    $this->assertArrayNotHasKey($param, $params);

    //test values
    foreach ($values as $value => $expected) {
        $this->message->$setter($value);
        $params = $this->message->getParams();

        $this->assertArrayHasKey($param, $params);
        expect($params[$param])->toEqual($expected);
    }
})->with('optionalParams');

// Datasets
/**
 * @return array[]
 */
dataset('optionalParams', [
    ['setLanguage', 'lg', ['test' => 'test']],
    ['setVoice', 'voice', ['test' => 'test']],
    ['setRepeat', 'repeat', [2 => 2]],
]);
