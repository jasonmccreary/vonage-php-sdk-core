<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use VonageTest\VonageTestCase;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\SMS\Client as SMSClient;
use Vonage\SMS\ExceptionErrorHandler;
use Vonage\SMS\Message\SMS;
use VonageTest\Psr7AssertionTrait;

uses(VonageTestCase::class);
uses(Psr7AssertionTrait::class);

use function fopen;
use function json_decode;
use function str_repeat;

beforeEach(function () {
    $this->vonageClient = $this->prophesize(Client::class);
    $this->vonageClient->getRestUrl()->willReturn('https://rest.nexmo.com');
    /** @noinspection PhpParamsInspection */
    $this->api = (new APIResource())
        ->setCollectionName('messages')
        ->setIsHAL(false)
        ->setErrorsOn200(true)
        ->setClient($this->vonageClient->reveal())
        ->setExceptionErrorHandler(new ExceptionErrorHandler())
        ->setBaseUrl('https://rest.nexmo.com');
    $this->smsClient = new SMSClient($this->api);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can send s m s', function () {
    $args = [
        'to' => '447700900000',
        'from' => '16105551212',
        'text' => "Go To Gino's",
        'account-ref' => 'customer1234',
        'client-ref' => 'my-personal-reference'
    ];

    $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
        $this->assertRequestJsonBodyContains('to', $args['to'], $request);
        $this->assertRequestJsonBodyContains('from', $args['from'], $request);
        $this->assertRequestJsonBodyContains('text', $args['text'], $request);
        $this->assertRequestJsonBodyContains('account-ref', $args['account-ref'], $request);
        $this->assertRequestJsonBodyContains('client-ref', $args['client-ref'], $request);

        return true;
    }))->willReturn(getResponse('send-success'));

    $message = (new SMS($args['to'], $args['from'], $args['text']))
        ->setClientRef($args['client-ref'])
        ->setAccountRef($args['account-ref']);
    $response = $this->smsClient->send($message);
    $sentData = $response->current();

    $this->assertCount(1, $response);
    $this->assertSame($args['to'], $sentData->getTo());
    $this->assertSame('0A0000000123ABCD1', $sentData->getMessageId());
    $this->assertSame("0.03330000", $sentData->getMessagePrice());
    $this->assertSame("12345", $sentData->getNetwork());
    $this->assertSame("3.14159265", $sentData->getRemainingBalance());
    $this->assertSame("customer1234", $sentData->getAccountRef());
    $this->assertSame("my-personal-reference", $sentData->getClientRef());
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('handles empty response', function () {
    $this->expectException(Client\Exception\Request::class);
    $this->expectExceptionMessage('unexpected response from API');

    $this->vonageClient
        ->send(Argument::type(RequestInterface::class))
        ->willReturn(getResponse('empty'));

    $this->smsClient->send(new SMS('14845551212', '16105551212', "Go To Gino's"));
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can parse errors and throw exception', function () {
    $this->expectException(Client\Exception\Request::class);
    $this->expectExceptionMessage('Missing from param');

    $this->vonageClient
        ->send(Argument::type(RequestInterface::class))
        ->willReturn(getResponse('fail'));

    $this->smsClient->send(new SMS('14845551212', '16105551212', "Go To Gino's"));
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can parse server errors and throw exception', function () {
    $this->expectException(ServerException::class);
    $this->expectExceptionMessage('Server Error');

    $this->vonageClient
        ->send(Argument::type(RequestInterface::class))
        ->willReturn(getResponse('fail-server'));

    $this->smsClient->send(new SMS('14845551212', '16105551212', "Go To Gino's"));
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can handle rate limit requests', function () {
    $start = microtime(true);
    $rate = getResponse('ratelimit');
    $rate2 = getResponse('ratelimit');
    $success = getResponse('send-success');
    $args = [
        'to' => '447700900000',
        'from' => '1105551334',
        'text' => 'test message'
    ];

    $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
        $this->assertRequestJsonBodyContains('to', $args['to'], $request);
        $this->assertRequestJsonBodyContains('from', $args['from'], $request);
        $this->assertRequestJsonBodyContains('text', $args['text'], $request);

        return true;
    }))->willReturn($rate, $rate2, $success);

    $response = $this->smsClient->send(new SMS($args['to'], $args['from'], $args['text']));
    $sentData = $response->current();
    $end = microtime(true);

    $this->assertCount(1, $response);
    $this->assertSame($args['to'], $sentData->getTo());
    $this->assertSame('0A0000000123ABCD1', $sentData->getMessageId());
    $this->assertSame("0.03330000", $sentData->getMessagePrice());
    $this->assertSame("12345", $sentData->getNetwork());
    $this->assertSame("3.14159265", $sentData->getRemainingBalance());
    $this->assertSame(0, $sentData->getStatus());
    $this->assertGreaterThanOrEqual(2, $end - $start);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can handle rate limit requests with no declared timeout', function () {
    $rate = getResponse('ratelimit-notime');
    $rate2 = getResponse('ratelimit-notime');
    $success = getResponse('send-success');

    $args = [
        'to' => '447700900000',
        'from' => '1105551334',
        'text' => 'test message'
    ];

    $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
        $this->assertRequestJsonBodyContains('to', $args['to'], $request);
        $this->assertRequestJsonBodyContains('from', $args['from'], $request);
        $this->assertRequestJsonBodyContains('text', $args['text'], $request);

        return true;
    }))->willReturn($rate, $rate2, $success);

    $response = $this->smsClient->send(new SMS($args['to'], $args['from'], $args['text']));
    $sentData = $response->current();

    $this->assertCount(1, $response);
    $this->assertSame($args['to'], $sentData->getTo());
    $this->assertSame('0A0000000123ABCD1', $sentData->getMessageId());
    $this->assertSame("0.03330000", $sentData->getMessagePrice());
    $this->assertSame("12345", $sentData->getNetwork());
    $this->assertSame("3.14159265", $sentData->getRemainingBalance());
    $this->assertSame(0, $sentData->getStatus());
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can handle a p i rate limit requests', function () {
    $start = microtime(true);
    $rate = getResponse('mt-limit');
    $rate2 = getResponse('mt-limit');
    $success = getResponse('send-success');
    $args = [
        'to' => '447700900000',
        'from' => '1105551334',
        'text' => 'test message'
    ];

    $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
        $this->assertRequestJsonBodyContains('to', $args['to'], $request);
        $this->assertRequestJsonBodyContains('from', $args['from'], $request);
        $this->assertRequestJsonBodyContains('text', $args['text'], $request);

        return true;
    }))->willReturn($rate, $rate2, $success);

    $response = $this->smsClient->send(new SMS($args['to'], $args['from'], $args['text']));
    $sentData = $response->current();
    $end = microtime(true);

    $this->assertCount(1, $response);
    $this->assertSame($args['to'], $sentData->getTo());
    $this->assertSame('0A0000000123ABCD1', $sentData->getMessageId());
    $this->assertSame("0.03330000", $sentData->getMessagePrice());
    $this->assertSame("12345", $sentData->getNetwork());
    $this->assertSame("3.14159265", $sentData->getRemainingBalance());
    $this->assertSame(0, $sentData->getStatus());
    $this->assertGreaterThanOrEqual(2, $end - $start);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can understand multi message responses', function () {
    $args = [
        'to' => '447700900000',
        'from' => '16105551212',
        'text' => str_repeat('This is an incredibly large SMS message', 5)
    ];

    $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
        $this->assertRequestJsonBodyContains('to', $args['to'], $request);
        $this->assertRequestJsonBodyContains('from', $args['from'], $request);
        $this->assertRequestJsonBodyContains('text', $args['text'], $request);

        return true;
    }))->willReturn(getResponse('multi'));

    $response = $this->smsClient->send((new SMS($args['to'], $args['from'], $args['text'])));
    $rawData = json_decode(getResponse('multi')->getBody()->getContents(), true);

    $this->assertCount((int)$rawData['message-count'], $response);

    foreach ($response as $key => $sentData) {
        $this->assertSame($rawData['messages'][$key]['to'], $sentData->getTo());
        $this->assertSame($rawData['messages'][$key]['message-id'], $sentData->getMessageId());
        $this->assertSame($rawData['messages'][$key]['message-price'], $sentData->getMessagePrice());
        $this->assertSame($rawData['messages'][$key]['network'], $sentData->getNetwork());
        $this->assertSame($rawData['messages'][$key]['remaining-balance'], $sentData->getRemainingBalance());
        $this->assertSame((int)$rawData['messages'][$key]['status'], $sentData->getStatus());
    }
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can send2 f a message', function () {
    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestJsonBodyContains('to', '447700900000', $request);
        $this->assertRequestJsonBodyContains('pin', 1245, $request);

        return true;
    }))->willReturn(getResponse('send-success'));

    $sentData = $this->smsClient->sendTwoFactor('447700900000', 1245);

    $this->assertSame('447700900000', $sentData->getTo());
    $this->assertSame('0A0000000123ABCD1', $sentData->getMessageId());
    $this->assertSame("0.03330000", $sentData->getMessagePrice());
    $this->assertSame("12345", $sentData->getNetwork());
    $this->assertSame("3.14159265", $sentData->getRemainingBalance());
    $this->assertSame(0, $sentData->getStatus());
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can handle missing shortcode on2 f a', function () {
    $this->expectException(Client\Exception\Request::class);
    $this->expectExceptionMessage('Invalid Account for Campaign');
    $this->expectExceptionCode(101);

    $this->vonageClient
        ->send(Argument::type(RequestInterface::class))
        ->willReturn(getResponse('fail-shortcode'));
    $this->smsClient->sendTwoFactor('447700900000', 1245);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can send alert', function () {
    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestJsonBodyContains('to', '447700900000', $request);
        $this->assertRequestJsonBodyContains('key', 'value', $request);

        return true;
    }))->willReturn(getResponse('send-success'));

    $response = $this->smsClient->sendAlert('447700900000', ['key' => 'value']);
    $sentData = $response->current();

    $this->assertCount(1, $response);
    $this->assertSame('447700900000', $sentData->getTo());
    $this->assertSame('0A0000000123ABCD1', $sentData->getMessageId());
    $this->assertSame("0.03330000", $sentData->getMessagePrice());
    $this->assertSame("12345", $sentData->getNetwork());
    $this->assertSame("3.14159265", $sentData->getRemainingBalance());
    $this->assertSame(0, $sentData->getStatus());
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can handle missing alert setup', function () {
    $this->expectException(Client\Exception\Request::class);
    $this->expectExceptionMessage('Invalid Account for Campaign');
    $this->expectExceptionCode(101);

    $this->vonageClient
        ->send(Argument::type(RequestInterface::class))
        ->willReturn(getResponse('fail-shortcode'));
    $this->smsClient->sendAlert('447700900000', ['key' => 'value']);
});

// Helpers
/**
     * Get the API response we'd expect for a call to the API. Message API currently returns 200 all the time, so only
     * change between success / fail is body of the message.
     */
function getResponse(string $type = 'success', int $status = 200): Response
{
    return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
}
