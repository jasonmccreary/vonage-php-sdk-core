<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use InvalidArgumentException;
use VonageTest\VonageTestCase;
use Vonage\SMS\Message\SMS;

uses(VonageTestCase::class);

test('can set unicode type', function () {
    $sms = (new SMS('447700900000', '16105551212', 'Test Message'));
    $this->assertSame('unicode', $sms->getType());
    $sms->setType('text');
    $this->assertSame('text', $sms->getType());
});

test('can set unicode type in constructor', function () {
    $sms = (new SMS('447700900000', '16105551212', 'Test Message', 'text'));
    $this->assertSame('text', $sms->getType());
});

test('delivery callback can be set', function () {
    $sms = (new SMS('447700900000', '16105551212', 'Test Message'))
        ->setDeliveryReceiptCallback('https://test.domain/webhooks/dlr');

    $this->assertSame('https://test.domain/webhooks/dlr', $sms->getDeliveryReceiptCallback());
    $this->assertTrue($sms->getRequestDeliveryReceipt());

    $data = $sms->toArray();

    $this->assertSame('https://test.domain/webhooks/dlr', $data['callback']);
    $this->assertSame(1, $data['status-report-req']);
});

test('message class can be set', function () {
    $sms = (new SMS('447700900000', '16105551212', 'Test Message'))
        ->setMessageClass(0);

    $this->assertSame(0, $sms->getMessageClass());

    $data = $sms->toArray();

    $this->assertSame(0, $data['message-class']);
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

    $this->assertSame(40000, $sms->getTtl());

    $data = $sms->toArray();

    $this->assertSame(40000, $data['ttl']);
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

    $this->assertSame($expected, $sms->toArray());
    $this->assertSame($expected['entity-id'], $sms->getEntityId());
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

    $this->assertSame($expected, $sms->toArray());
    $this->assertSame($expected['content-id'], $sms->getContentId());
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

    $this->assertSame($expected, $sms->toArray());
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

    $this->assertSame($expected, $sms->toArray());
});
