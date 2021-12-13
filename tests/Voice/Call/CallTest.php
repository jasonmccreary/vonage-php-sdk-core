<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Voice\Call\Call;

uses(VonageTestCase::class);

beforeEach(function () {
    $this->call = new Call($this->url, $this->to, $this->from);
});

test('construct params', function () {
    $params = $this->call->getParams();

    $this->assertArrayHasKey('to', $params);
    $this->assertArrayHasKey('from', $params);
    $this->assertArrayHasKey('answer_url', $params);
    expect($params['to'])->toEqual($this->to);
    expect($params['from'])->toEqual($this->from);
    expect($params['answer_url'])->toEqual($this->url);
});

test('from optional', function () {
    $this->assertArrayNotHasKey('from', (new Call($this->url, $this->to))->getParams());
});

test('machine', function () {
    $this->call->setMachineDetection();
    $params = $this->call->getParams();

    $this->assertArrayHasKey('machine_detection', $params);
    $this->assertArrayNotHasKey('machine_timeout', $params);
    expect($params['machine_detection'])->toEqual('hangup');

    $this->call->setMachineDetection(true, 100);
    $params = $this->call->getParams();

    $this->assertArrayHasKey('machine_detection', $params);
    $this->assertArrayHasKey('machine_timeout', $params);
    expect($params['machine_detection'])->toEqual('hangup');
    expect($params['machine_timeout'])->toEqual(100);

    $this->call->setMachineDetection(false);
    $params = $this->call->getParams();

    $this->assertArrayHasKey('machine_detection', $params);
    $this->assertArrayNotHasKey('machine_timeout', $params);
    expect($params['machine_detection'])->toEqual('true');
});

test('callback', function (string $method, string $param, string $param_method) {
    $this->call->$method('http://example.com');
    $params = $this->call->getParams();

    $this->assertArrayHasKey($param, $params);
    expect($params[$param])->toEqual('http://example.com');
    $this->assertArrayNotHasKey($param_method, $params);

    $this->call->$method('http://example.com', 'POST');
    $params = $this->call->getParams();

    $this->assertArrayHasKey($param, $params);
    expect($params[$param])->toEqual('http://example.com');
    $this->assertArrayHasKey($param_method, $params);
    expect($params[$param_method])->toEqual('POST');

    $this->call->$method('http://example.com');
    $params = $this->call->getParams();

    $this->assertArrayHasKey($param, $params);
    expect($params[$param])->toEqual('http://example.com');
    $this->assertArrayNotHasKey($param_method, $params);
})->with('getCallbacks');

// Datasets
/**
 * @return string[]
 */
dataset('getCallbacks', [
    ['setAnswer', 'answer_url', 'answer_method'],
    ['setError', 'error_url', 'error_method'],
    ['setStatus', 'status_url', 'status_method']
]);
