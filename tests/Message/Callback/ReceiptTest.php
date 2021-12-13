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

    expect($date)->toEqual(new DateTime('12/30/2014 12:25'));
});

test('sent timestamp', function () {
    $date = $this->receipt->getSent();

    expect($date)->toEqual(new DateTime('7/23/2014 03:41:03'));
});

test('simple values', function () {
    expect($this->receipt->getErrorCode())->toEqual($this->data['err-code']);
    expect($this->receipt->getId())->toEqual($this->data['messageId']);
    expect($this->receipt->getNetwork())->toEqual($this->data['network-code']);
    expect($this->receipt->getPrice())->toEqual($this->data['price']);
    expect($this->receipt->getStatus())->toEqual($this->data['status']);
    expect($this->receipt->getReceiptFrom())->toEqual($this->data['msisdn']);
    expect($this->receipt->getTo())->toEqual($this->data['msisdn']);
    expect($this->receipt->getReceiptTo())->toEqual($this->data['to']);
    expect($this->receipt->getFrom())->toEqual($this->data['to']);
});

test('client ref default', function () {
    expect($this->receipt->getClientRef())->toBeNull();
});

test('client ref', function () {
    $receipt = new Receipt(array_merge(['client-ref' => 'test'], $this->data));
    expect($receipt->getClientRef())->toEqual('test');
});
