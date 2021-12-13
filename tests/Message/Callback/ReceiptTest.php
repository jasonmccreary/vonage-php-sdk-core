<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Message\Callback\Receipt;

uses(VonageTestCase::class);


beforeEach(function () {
    $this->receipt = new Receipt($this->data);
});

test('service center timestamp', function () {
    $date = $this->receipt->getTimestamp();

    $this->assertEquals(new DateTime('12/30/2014 12:25'), $date);
});

test('sent timestamp', function () {
    $date = $this->receipt->getSent();

    $this->assertEquals(new DateTime('7/23/2014 03:41:03'), $date);
});

test('simple values', function () {
    $this->assertEquals($this->data['err-code'], $this->receipt->getErrorCode());
    $this->assertEquals($this->data['messageId'], $this->receipt->getId());
    $this->assertEquals($this->data['network-code'], $this->receipt->getNetwork());
    $this->assertEquals($this->data['price'], $this->receipt->getPrice());
    $this->assertEquals($this->data['status'], $this->receipt->getStatus());
    $this->assertEquals($this->data['msisdn'], $this->receipt->getReceiptFrom());
    $this->assertEquals($this->data['msisdn'], $this->receipt->getTo());
    $this->assertEquals($this->data['to'], $this->receipt->getReceiptTo());
    $this->assertEquals($this->data['to'], $this->receipt->getFrom());
});

test('client ref default', function () {
    $this->assertNull($this->receipt->getClientRef());
});

test('client ref', function () {
    $receipt = new Receipt(array_merge(['client-ref' => 'test'], $this->data));
    $this->assertEquals('test', $receipt->getClientRef());
});
