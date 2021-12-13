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
use Vonage\SMS\Webhook\DeliveryReceipt;
use Vonage\SMS\Webhook\Factory;

test('can create from get server request', function () {
    $expected = getQueryStringFromRequest('dlr-get');
    $request = getServerRequest('dlr-get');
    $dlr = Factory::createFromRequest($request);

    expect($dlr->getMsisdn())->toBe($expected['msisdn']);
    expect($dlr->getErrCode())->toBe((int)$expected['err-code']);
    expect($dlr->getMessageId())->toBe($expected['messageId']);
    expect($dlr->getNetworkCode())->toBe($expected['network-code']);
    expect($dlr->getPrice())->toBe($expected['price']);
    expect($dlr->getScts())->toBe($expected['scts']);
    expect($dlr->getStatus())->toBe($expected['status']);
    expect($dlr->getTo())->toBe($expected['to']);
    expect($dlr->getApiKey())->toBe($expected['api-key']);
    expect($dlr->getMessageTimestamp()->format('Y-m-d H:i:s'))->toBe($expected['message-timestamp']);
});

test('can create from j s o n post server request', function () {
    $expected = getBodyFromRequest('dlr-post-json');
    $request = getServerRequest('dlr-post-json');
    $dlr = Factory::createFromRequest($request);

    expect($dlr->getMsisdn())->toBe($expected['msisdn']);
    expect($dlr->getErrCode())->toBe((int)$expected['err-code']);
    expect($dlr->getMessageId())->toBe($expected['messageId']);
    expect($dlr->getNetworkCode())->toBe($expected['network-code']);
    expect($dlr->getPrice())->toBe($expected['price']);
    expect($dlr->getScts())->toBe($expected['scts']);
    expect($dlr->getStatus())->toBe($expected['status']);
    expect($dlr->getTo())->toBe($expected['to']);
    expect($dlr->getApiKey())->toBe($expected['api-key']);
    expect($dlr->getMessageTimestamp()->format('Y-m-d H:i:s'))->toBe($expected['message-timestamp']);
});

test('can create from form post server request', function () {
    $expected = getBodyFromRequest('dlr-post', false);
    $request = getServerRequest('dlr-post');
    $dlr = Factory::createFromRequest($request);

    expect($dlr->getMsisdn())->toBe($expected['msisdn']);
    expect($dlr->getErrCode())->toBe((int)$expected['err-code']);
    expect($dlr->getMessageId())->toBe($expected['messageId']);
    expect($dlr->getNetworkCode())->toBe($expected['network-code']);
    expect($dlr->getPrice())->toBe($expected['price']);
    expect($dlr->getScts())->toBe($expected['scts']);
    expect($dlr->getStatus())->toBe($expected['status']);
    expect($dlr->getTo())->toBe($expected['to']);
    expect($dlr->getApiKey())->toBe($expected['api-key']);
    expect($dlr->getMessageTimestamp()->format('Y-m-d H:i:s'))->toBe($expected['message-timestamp']);
});

/**
 * @throws Exception
 */
test('can create from raw array', function () {
    $expected = getQueryStringFromRequest('dlr-get');
    $dlr = new DeliveryReceipt($expected);

    expect($dlr->getMsisdn())->toBe($expected['msisdn']);
    expect($dlr->getErrCode())->toBe((int)$expected['err-code']);
    expect($dlr->getMessageId())->toBe($expected['messageId']);
    expect($dlr->getNetworkCode())->toBe($expected['network-code']);
    expect($dlr->getPrice())->toBe($expected['price']);
    expect($dlr->getScts())->toBe($expected['scts']);
    expect($dlr->getStatus())->toBe($expected['status']);
    expect($dlr->getTo())->toBe($expected['to']);
    expect($dlr->getApiKey())->toBe($expected['api-key']);
    expect($dlr->getMessageTimestamp()->format('Y-m-d H:i:s'))->toBe($expected['message-timestamp']);
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
