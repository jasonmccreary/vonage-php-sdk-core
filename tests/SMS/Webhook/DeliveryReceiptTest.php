<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Exception;
use InvalidArgumentException;
use Laminas\Diactoros\Request\Serializer;
use Laminas\Diactoros\ServerRequest;
use VonageTest\VonageTestCase;
use Vonage\SMS\Webhook\DeliveryReceipt;
use Vonage\SMS\Webhook\Factory;

uses(VonageTestCase::class);

use function file_get_contents;
use function json_decode;
use function parse_str;

test('can create from get server request', function () {
    $expected = getQueryStringFromRequest('dlr-get');
    $request = getServerRequest('dlr-get');
    $dlr = Factory::createFromRequest($request);

    $this->assertSame($expected['msisdn'], $dlr->getMsisdn());
    $this->assertSame((int)$expected['err-code'], $dlr->getErrCode());
    $this->assertSame($expected['messageId'], $dlr->getMessageId());
    $this->assertSame($expected['network-code'], $dlr->getNetworkCode());
    $this->assertSame($expected['price'], $dlr->getPrice());
    $this->assertSame($expected['scts'], $dlr->getScts());
    $this->assertSame($expected['status'], $dlr->getStatus());
    $this->assertSame($expected['to'], $dlr->getTo());
    $this->assertSame($expected['api-key'], $dlr->getApiKey());
    $this->assertSame($expected['message-timestamp'], $dlr->getMessageTimestamp()->format('Y-m-d H:i:s'));
});

test('can create from j s o n post server request', function () {
    $expected = getBodyFromRequest('dlr-post-json');
    $request = getServerRequest('dlr-post-json');
    $dlr = Factory::createFromRequest($request);

    $this->assertSame($expected['msisdn'], $dlr->getMsisdn());
    $this->assertSame((int)$expected['err-code'], $dlr->getErrCode());
    $this->assertSame($expected['messageId'], $dlr->getMessageId());
    $this->assertSame($expected['network-code'], $dlr->getNetworkCode());
    $this->assertSame($expected['price'], $dlr->getPrice());
    $this->assertSame($expected['scts'], $dlr->getScts());
    $this->assertSame($expected['status'], $dlr->getStatus());
    $this->assertSame($expected['to'], $dlr->getTo());
    $this->assertSame($expected['api-key'], $dlr->getApiKey());
    $this->assertSame($expected['message-timestamp'], $dlr->getMessageTimestamp()->format('Y-m-d H:i:s'));
});

test('can create from form post server request', function () {
    $expected = getBodyFromRequest('dlr-post', false);
    $request = getServerRequest('dlr-post');
    $dlr = Factory::createFromRequest($request);

    $this->assertSame($expected['msisdn'], $dlr->getMsisdn());
    $this->assertSame((int)$expected['err-code'], $dlr->getErrCode());
    $this->assertSame($expected['messageId'], $dlr->getMessageId());
    $this->assertSame($expected['network-code'], $dlr->getNetworkCode());
    $this->assertSame($expected['price'], $dlr->getPrice());
    $this->assertSame($expected['scts'], $dlr->getScts());
    $this->assertSame($expected['status'], $dlr->getStatus());
    $this->assertSame($expected['to'], $dlr->getTo());
    $this->assertSame($expected['api-key'], $dlr->getApiKey());
    $this->assertSame($expected['message-timestamp'], $dlr->getMessageTimestamp()->format('Y-m-d H:i:s'));
});

/**
 * @throws Exception
 */
test('can create from raw array', function () {
    $expected = getQueryStringFromRequest('dlr-get');
    $dlr = new DeliveryReceipt($expected);

    $this->assertSame($expected['msisdn'], $dlr->getMsisdn());
    $this->assertSame((int)$expected['err-code'], $dlr->getErrCode());
    $this->assertSame($expected['messageId'], $dlr->getMessageId());
    $this->assertSame($expected['network-code'], $dlr->getNetworkCode());
    $this->assertSame($expected['price'], $dlr->getPrice());
    $this->assertSame($expected['scts'], $dlr->getScts());
    $this->assertSame($expected['status'], $dlr->getStatus());
    $this->assertSame($expected['to'], $dlr->getTo());
    $this->assertSame($expected['api-key'], $dlr->getApiKey());
    $this->assertSame($expected['message-timestamp'], $dlr->getMessageTimestamp()->format('Y-m-d H:i:s'));
});

/**
 * @throws Exception
 */
test('throws exception with invalid request', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Delivery Receipt missing required data `err-code`');

    $request = getServerRequest('invalid')->getQueryParams();

    new DeliveryReceipt($request);
});

// Helpers
function getQueryStringFromRequest(string $requestName)
{
    $text = file_get_contents(__DIR__ . '/../requests/' . $requestName . '.txt');
    $request = Serializer::fromString($text);

    parse_str($request->getUri()->getQuery(), $query);

    return $query;
}

/**
     * @param bool $json
     */
function getBodyFromRequest(string $requestName, $json = true)
{
    $text = file_get_contents(__DIR__ . '/../requests/' . $requestName . '.txt');
    $request = Serializer::fromString($text);

    if ($json) {
        return json_decode($request->getBody()->getContents(), true);
    }

    parse_str($request->getBody()->getContents(), $params);

    return $params;
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
