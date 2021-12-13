<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Laminas\Diactoros\Request\Serializer;
use Laminas\Diactoros\ServerRequest;
use VonageTest\VonageTestCase;
use Vonage\SMS\Webhook\Factory;
use Vonage\SMS\Webhook\InboundSMS;

uses(VonageTestCase::class);


test('can create from form post server request', function () {
    $expected = getQueryStringFromRequest('inbound');
    $request = getServerRequest('inbound');
    $inboundSMS = Factory::createFromRequest($request);

    $this->assertSame($expected['msisdn'], $inboundSMS->getMsisdn());
    $this->assertSame($expected['msisdn'], $inboundSMS->getFrom());
    $this->assertSame($expected['to'], $inboundSMS->getTo());
    $this->assertSame($expected['messageId'], $inboundSMS->getMessageId());
    $this->assertSame($expected['text'], $inboundSMS->getText());
    $this->assertSame($expected['type'], $inboundSMS->getType());
    $this->assertSame($expected['keyword'], $inboundSMS->getKeyword());
    $this->assertSame($expected['message-timestamp'], $inboundSMS->getMessageTimestamp()->format('Y-m-d H:i:s'));
    $this->assertSame((int)$expected['timestamp'], $inboundSMS->getTimestamp());
    $this->assertSame($expected['nonce'], $inboundSMS->getNonce());
    $this->assertSame($expected['sig'], $inboundSMS->getSignature());
});

test('can create incoming binary message', function () {
    $expected = getQueryStringFromRequest('inbound-binary');
    $request = getServerRequest('inbound-binary');
    $inboundSMS = Factory::createFromRequest($request);

    $this->assertSame($expected['msisdn'], $inboundSMS->getMsisdn());
    $this->assertSame($expected['msisdn'], $inboundSMS->getFrom());
    $this->assertSame($expected['to'], $inboundSMS->getTo());
    $this->assertSame($expected['messageId'], $inboundSMS->getMessageId());
    $this->assertSame($expected['text'], $inboundSMS->getText());
    $this->assertSame($expected['type'], $inboundSMS->getType());
    $this->assertSame($expected['keyword'], $inboundSMS->getKeyword());
    $this->assertSame($expected['data'], $inboundSMS->getData());
    $this->assertSame($expected['udh'], $inboundSMS->getUdh());
});

test('can create from concat message form post server request', function () {
    $expected = getQueryStringFromRequest('inbound-long');
    $request = getServerRequest('inbound-long');
    $inboundSMS = Factory::createFromRequest($request);

    $this->assertSame($expected['msisdn'], $inboundSMS->getMsisdn());
    $this->assertSame($expected['msisdn'], $inboundSMS->getFrom());
    $this->assertSame($expected['to'], $inboundSMS->getTo());
    $this->assertSame($expected['messageId'], $inboundSMS->getMessageId());
    $this->assertSame($expected['text'], $inboundSMS->getText());
    $this->assertSame($expected['type'], $inboundSMS->getType());
    $this->assertSame($expected['keyword'], $inboundSMS->getKeyword());
    $this->assertSame($expected['message-timestamp'], $inboundSMS->getMessageTimestamp()->format('Y-m-d H:i:s'));
    $this->assertSame((bool)$expected['concat'], $inboundSMS->getConcat());
    $this->assertSame((int)$expected['concat-part'], $inboundSMS->getConcatPart());
    $this->assertSame($expected['concat-ref'], $inboundSMS->getConcatRef());
    $this->assertSame((int)$expected['concat-total'], $inboundSMS->getConcatTotal());
});

test('throw runtime exception when invalid request detected', function () {
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage("Invalid method for incoming webhook");
    $request = new ServerRequest([], [], '/', 'DELETE');

    Factory::createFromRequest($request);
});

test('can create from j s o n post server request', function () {
    $expected = getBodyFromRequest('json');
    $request = getServerRequest('json');
    $inboundSMS = Factory::createFromRequest($request);

    $this->assertSame($expected['msisdn'], $inboundSMS->getMsisdn());
    $this->assertSame($expected['to'], $inboundSMS->getTo());
    $this->assertSame($expected['messageId'], $inboundSMS->getMessageId());
    $this->assertSame($expected['text'], $inboundSMS->getText());
    $this->assertSame($expected['type'], $inboundSMS->getType());
    $this->assertSame($expected['keyword'], $inboundSMS->getKeyword());
    $this->assertSame($expected['message-timestamp'], $inboundSMS->getMessageTimestamp()->format('Y-m-d H:i:s'));
    $this->assertSame((int)$expected['timestamp'], $inboundSMS->getTimestamp());
    $this->assertSame($expected['nonce'], $inboundSMS->getNonce());
    $this->assertSame($expected['sig'], $inboundSMS->getSignature());
});

/**
 * @throws Exception
 */
test('can create from raw array', function () {
    $expected = getQueryStringFromRequest('inbound');
    $inboundSMS = new InboundSMS($expected);

    $this->assertSame($expected['msisdn'], $inboundSMS->getMsisdn());
    $this->assertSame($expected['msisdn'], $inboundSMS->getFrom());
    $this->assertSame($expected['to'], $inboundSMS->getTo());
    $this->assertSame($expected['messageId'], $inboundSMS->getMessageId());
    $this->assertSame($expected['text'], $inboundSMS->getText());
    $this->assertSame($expected['type'], $inboundSMS->getType());
    $this->assertSame($expected['keyword'], $inboundSMS->getKeyword());
    $this->assertSame($expected['message-timestamp'], $inboundSMS->getMessageTimestamp()->format('Y-m-d H:i:s'));
    $this->assertSame((int)$expected['timestamp'], $inboundSMS->getTimestamp());
    $this->assertSame($expected['nonce'], $inboundSMS->getNonce());
    $this->assertSame($expected['sig'], $inboundSMS->getSignature());
});

test('can create from get with body server request', function () {
    $expected = getQueryStringFromRequest('inbound');
    $request = getServerRequest('inbound');
    $inboundSMS = Factory::createFromRequest($request);

    $this->assertSame($expected['msisdn'], $inboundSMS->getMsisdn());
    $this->assertSame($expected['to'], $inboundSMS->getTo());
    $this->assertSame($expected['messageId'], $inboundSMS->getMessageId());
    $this->assertSame($expected['text'], $inboundSMS->getText());
    $this->assertSame($expected['type'], $inboundSMS->getType());
    $this->assertSame($expected['keyword'], $inboundSMS->getKeyword());
    $this->assertSame($expected['message-timestamp'], $inboundSMS->getMessageTimestamp()->format('Y-m-d H:i:s'));
    $this->assertSame((int)$expected['timestamp'], $inboundSMS->getTimestamp());
    $this->assertSame($expected['nonce'], $inboundSMS->getNonce());
    $this->assertSame($expected['sig'], $inboundSMS->getSignature());
});

/**
 * @throws Exception
 */
test('throws exception with invalid request', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Incoming SMS missing required data `msisdn`');

    $request = getServerRequest('invalid')->getQueryParams();

    new InboundSMS($request);
});

// Helpers
function getQueryStringFromRequest(string $requestName)
{
    $text = file_get_contents(__DIR__ . '/../requests/' . $requestName . '.txt');
    $request = Serializer::fromString($text);

    parse_str($request->getUri()->getQuery(), $query);

    return $query;
}

function getBodyFromRequest(string $requestName)
{
    $text = file_get_contents(__DIR__ . '/../requests/' . $requestName . '.txt');
    $request = Serializer::fromString($text);

    return json_decode($request->getBody()->getContents(), true);
}

function getServerRequest(string $requestName): ServerRequest
{
    $text = file_get_contents(__DIR__ . '/../requests/' . $requestName . '.txt');
    $request = Serializer::fromString($text);

    parse_str($request->getUri()->getQuery(), $query);

    return new ServerRequest(
        [],
        [],
        $request->getHeader('Host')[0],
        $request->getMethod(),
        $request->getBody(),
        $request->getHeaders(),
        [],
        $query
    );
}
