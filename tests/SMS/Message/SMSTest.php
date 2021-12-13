<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Vonage\SMS\Message\SMS;

test('can set unicode type', function () {
    $sms = (new SMS('447700900000', '16105551212', 'Test Message'));
    expect($sms->getType())->toBe('unicode');
    $sms->setType('text');
    expect($sms->getType())->toBe('text');
});

test('can set unicode type in constructor', function () {
    $sms = (new SMS('447700900000', '16105551212', 'Test Message', 'text'));
    expect($sms->getType())->toBe('text');
});

test('delivery callback can be set', function () {
    $sms = (new SMS('447700900000', '16105551212', 'Test Message'))
        ->setDeliveryReceiptCallback('https://test.domain/webhooks/dlr');

    expect($sms->getDeliveryReceiptCallback())->toBe('https://test.domain/webhooks/dlr');
    expect($sms->getRequestDeliveryReceipt())->toBeTrue();

    $data = $sms->toArray();

    expect($data['callback'])->toBe('https://test.domain/webhooks/dlr');
    expect($data['status-report-req'])->toBe(1);
});

test('message class can be set', function () {
    $sms = (new SMS('447700900000', '16105551212', 'Test Message'))
        ->setMessageClass(0);

    expect($sms->getMessageClass())->toBe(0);

    $data = $sms->toArray();

    expect($data['message-class'])->toBe(0);
});

test('invalid message class cannot be set', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Message Class must be 0-3');

    (new SMS('447700900000', '16105551212', 'Test Message'))
        ->setMessageClass(10);
});

test('t t l can be set', function () {
    $sms = (new SMS('447700900000', '16105551212', 'Test Message'))
        ->setTtl(40000);

    expect($sms->getTtl())->toBe(40000);

    $data = $sms->toArray();

    expect($data['ttl'])->toBe(40000);
});

test('cannot set invalid t t l', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('SMS TTL must be in the range of 20000-604800000 milliseconds');

    (new SMS('447700900000', '16105551212', 'Test Message'))
        ->setTtl(2);
});

test('cannot set too long ofa client ref', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Client Ref can be no more than 40 characters');

    (new SMS('447700900000', '16105551212', 'Test Message'))
        ->setClientRef('This is a really long client ref and should throw an exception');
});

test('can set entity id', function () {
    $sms = new SMS('447700900000', '16105551212', 'Test Message');
    $sms->setEntityId('abcd');

    $expected = [
        'text' => 'Test Message',
        'entity-id' => 'abcd',
        'to' => '447700900000',
        'from' => '16105551212',
        'type' => 'unicode',
        'ttl' => 259200000,
        'status-report-req' => 1,
    ];

    expect($sms->toArray())->toBe($expected);
    expect($sms->getEntityId())->toBe($expected['entity-id']);
});

test('can set content id', function () {
    $sms = new SMS('447700900000', '16105551212', 'Test Message');
    $sms->setContentId('1234');

    $expected = [
        'text' => 'Test Message',
        'content-id' => '1234',
        'to' => '447700900000',
        'from' => '16105551212',
        'type' => 'unicode',
        'ttl' => 259200000,
        'status-report-req' => 1,
    ];

    expect($sms->toArray())->toBe($expected);
    expect($sms->getContentId())->toBe($expected['content-id']);
});

test('d l t info appears in request', function () {
    $sms = new SMS('447700900000', '16105551212', 'Test Message');
    $sms->enableDLT('abcd', '1234');

    $expected = [
        'text' => 'Test Message',
        'entity-id' => 'abcd',
        'content-id' => '1234',
        'to' => '447700900000',
        'from' => '16105551212',
        'type' => 'unicode',
        'ttl' => 259200000,
        'status-report-req' => 1,
    ];

    expect($sms->toArray())->toBe($expected);
});

test('d l t info does not appears when not set', function () {
    $sms = new SMS('447700900000', '16105551212', 'Test Message');

    $expected = [
        'text' => 'Test Message',
        'to' => '447700900000',
        'from' => '16105551212',
        'type' => 'unicode',
        'ttl' => 259200000,
        'status-report-req' => 1,
    ];

    expect($sms->toArray())->toBe($expected);
});
