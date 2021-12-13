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

    expect($inboundSMS->getMsisdn())->toBe($expected['msisdn']);
    expect($inboundSMS->getFrom())->toBe($expected['msisdn']);
    expect($inboundSMS->getTo())->toBe($expected['to']);
    expect($inboundSMS->getMessageId())->toBe($expected['messageId']);
    expect($inboundSMS->getText())->toBe($expected['text']);
    expect($inboundSMS->getType())->toBe($expected['type']);
    expect($inboundSMS->getKeyword())->toBe($expected['keyword']);
    expect($inboundSMS->getMessageTimestamp()->format('Y-m-d H:i:s'))->toBe($expected['message-timestamp']);
    expect($inboundSMS->getTimestamp())->toBe((int)$expected['timestamp']);
    expect($inboundSMS->getNonce())->toBe($expected['nonce']);
    expect($inboundSMS->getSignature())->toBe($expected['sig']);
});

test('can create incoming binary message', function () {
    $expected = getQueryStringFromRequest('inbound-binary');
    $request = getServerRequest('inbound-binary');
    $inboundSMS = Factory::createFromRequest($request);

    expect($inboundSMS->getMsisdn())->toBe($expected['msisdn']);
    expect($inboundSMS->getFrom())->toBe($expected['msisdn']);
    expect($inboundSMS->getTo())->toBe($expected['to']);
    expect($inboundSMS->getMessageId())->toBe($expected['messageId']);
    expect($inboundSMS->getText())->toBe($expected['text']);
    expect($inboundSMS->getType())->toBe($expected['type']);
    expect($inboundSMS->getKeyword())->toBe($expected['keyword']);
    expect($inboundSMS->getData())->toBe($expected['data']);
    expect($inboundSMS->getUdh())->toBe($expected['udh']);
});

test('can create from concat message form post server request', function () {
    $expected = getQueryStringFromRequest('inbound-long');
    $request = getServerRequest('inbound-long');
    $inboundSMS = Factory::createFromRequest($request);

    expect($inboundSMS->getMsisdn())->toBe($expected['msisdn']);
    expect($inboundSMS->getFrom())->toBe($expected['msisdn']);
    expect($inboundSMS->getTo())->toBe($expected['to']);
    expect($inboundSMS->getMessageId())->toBe($expected['messageId']);
    expect($inboundSMS->getText())->toBe($expected['text']);
    expect($inboundSMS->getType())->toBe($expected['type']);
    expect($inboundSMS->getKeyword())->toBe($expected['keyword']);
    expect($inboundSMS->getMessageTimestamp()->format('Y-m-d H:i:s'))->toBe($expected['message-timestamp']);
    expect($inboundSMS->getConcat())->toBe((bool)$expected['concat']);
    expect($inboundSMS->getConcatPart())->toBe((int)$expected['concat-part']);
    expect($inboundSMS->getConcatRef())->toBe($expected['concat-ref']);
    expect($inboundSMS->getConcatTotal())->toBe((int)$expected['concat-total']);
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

    expect($inboundSMS->getMsisdn())->toBe($expected['msisdn']);
    expect($inboundSMS->getTo())->toBe($expected['to']);
    expect($inboundSMS->getMessageId())->toBe($expected['messageId']);
    expect($inboundSMS->getText())->toBe($expected['text']);
    expect($inboundSMS->getType())->toBe($expected['type']);
    expect($inboundSMS->getKeyword())->toBe($expected['keyword']);
    expect($inboundSMS->getMessageTimestamp()->format('Y-m-d H:i:s'))->toBe($expected['message-timestamp']);
    expect($inboundSMS->getTimestamp())->toBe((int)$expected['timestamp']);
    expect($inboundSMS->getNonce())->toBe($expected['nonce']);
    expect($inboundSMS->getSignature())->toBe($expected['sig']);
});

/**
 * @throws Exception
 */
test('can create from raw array', function () {
    $expected = getQueryStringFromRequest('inbound');
    $inboundSMS = new InboundSMS($expected);

    expect($inboundSMS->getMsisdn())->toBe($expected['msisdn']);
    expect($inboundSMS->getFrom())->toBe($expected['msisdn']);
    expect($inboundSMS->getTo())->toBe($expected['to']);
    expect($inboundSMS->getMessageId())->toBe($expected['messageId']);
    expect($inboundSMS->getText())->toBe($expected['text']);
    expect($inboundSMS->getType())->toBe($expected['type']);
    expect($inboundSMS->getKeyword())->toBe($expected['keyword']);
    expect($inboundSMS->getMessageTimestamp()->format('Y-m-d H:i:s'))->toBe($expected['message-timestamp']);
    expect($inboundSMS->getTimestamp())->toBe((int)$expected['timestamp']);
    expect($inboundSMS->getNonce())->toBe($expected['nonce']);
    expect($inboundSMS->getSignature())->toBe($expected['sig']);
});

test('can create from get with body server request', function () {
    $expected = getQueryStringFromRequest('inbound');
    $request = getServerRequest('inbound');
    $inboundSMS = Factory::createFromRequest($request);

    expect($inboundSMS->getMsisdn())->toBe($expected['msisdn']);
    expect($inboundSMS->getTo())->toBe($expected['to']);
    expect($inboundSMS->getMessageId())->toBe($expected['messageId']);
    expect($inboundSMS->getText())->toBe($expected['text']);
    expect($inboundSMS->getType())->toBe($expected['type']);
    expect($inboundSMS->getKeyword())->toBe($expected['keyword']);
    expect($inboundSMS->getMessageTimestamp()->format('Y-m-d H:i:s'))->toBe($expected['message-timestamp']);
    expect($inboundSMS->getTimestamp())->toBe((int)$expected['timestamp']);
    expect($inboundSMS->getNonce())->toBe($expected['nonce']);
    expect($inboundSMS->getSignature())->toBe($expected['sig']);
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
