<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Account\Balance;

uses(VonageTestCase::class);
use Vonage\Client\Exception\Exception as ClientException;

beforeEach(function () {
    $this->balance = new Balance('12.99', false);
});

test('object access', function () {
    expect($this->balance->getBalance())->toEqual("12.99");
    expect($this->balance->getAutoReload())->toEqual(false);
});

test('array access', function () {
    expect(@$this->balance['balance'])->toEqual("12.99");
    expect(@$this->balance['auto_reload'])->toEqual(false);
});

test('json serialize', function () {
    $data = $this->balance->jsonSerialize();

    expect($data['balance'])->toBe('12.99');
    expect($data['auto_reload'])->toBeFalse();
});

test('json unserialize', function () {
    $data = ['value' => '5.00', 'autoReload' => false];

    $balance = new Balance('1.99', true);
    $balance->fromArray($data);

    expect(@$balance['balance'])->toBe($data['value']);
    expect(@$balance['auto_reload'])->toBe($data['autoReload']);
});

test('acts like array', function () {
    expect(@$this->balance['balance'])->toBe('12.99');
    expect(@isset($this->balance['balance']))->toBeTrue();
});

test('cannot remove array key', function () {
    $this->expectException(ClientException::class);
    $this->expectExceptionMessage('Balance is read only');

    unset($this->balance['balance']);
});

test('cannot directly set array key', function () {
    $this->expectException(ClientException::class);
    $this->expectExceptionMessage('Balance is read only');

    $this->balance['balance'] = '5.00';
});

test('make sure data is publicly visible', function () {
    expect(@$this->balance->data['balance'])->toBe('12.99');
});
