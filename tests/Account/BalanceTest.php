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
    $this->assertEquals("12.99", $this->balance->getBalance());
    $this->assertEquals(false, $this->balance->getAutoReload());
});

test('array access', function () {
    $this->assertEquals("12.99", @$this->balance['balance']);
    $this->assertEquals(false, @$this->balance['auto_reload']);
});

test('json serialize', function () {
    $data = $this->balance->jsonSerialize();

    $this->assertSame('12.99', $data['balance']);
    $this->assertFalse($data['auto_reload']);
});

test('json unserialize', function () {
    $data = ['value' => '5.00', 'autoReload' => false];

    $balance = new Balance('1.99', true);
    $balance->fromArray($data);

    $this->assertSame($data['value'], @$balance['balance']);
    $this->assertSame($data['autoReload'], @$balance['auto_reload']);
});

test('acts like array', function () {
    $this->assertSame('12.99', @$this->balance['balance']);
    $this->assertTrue(@isset($this->balance['balance']));
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
    $this->assertSame('12.99', @$this->balance->data['balance']);
});
