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

uses(Psr7AssertionTrait::class);


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

    expect($response)->toHaveCount(1);
    expect($sentData->getTo())->toBe($args['to']);
    expect($sentData->getMessageId())->toBe('0A0000000123ABCD1');
    expect($sentData->getMessagePrice())->toBe("0.03330000");
    expect($sentData->getNetwork())->toBe("12345");
    expect($sentData->getRemainingBalance())->toBe("3.14159265");
    expect($sentData->getAccountRef())->toBe("customer1234");
    expect($sentData->getClientRef())->toBe("my-personal-reference");
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

    expect($response)->toHaveCount(1);
    expect($sentData->getTo())->toBe($args['to']);
    expect($sentData->getMessageId())->toBe('0A0000000123ABCD1');
    expect($sentData->getMessagePrice())->toBe("0.03330000");
    expect($sentData->getNetwork())->toBe("12345");
    expect($sentData->getRemainingBalance())->toBe("3.14159265");
    expect($sentData->getStatus())->toBe(0);
    expect($end - $start)->toBeGreaterThanOrEqual(2);
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

    expect($response)->toHaveCount(1);
    expect($sentData->getTo())->toBe($args['to']);
    expect($sentData->getMessageId())->toBe('0A0000000123ABCD1');
    expect($sentData->getMessagePrice())->toBe("0.03330000");
    expect($sentData->getNetwork())->toBe("12345");
    expect($sentData->getRemainingBalance())->toBe("3.14159265");
    expect($sentData->getStatus())->toBe(0);
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

    expect($response)->toHaveCount(1);
    expect($sentData->getTo())->toBe($args['to']);
    expect($sentData->getMessageId())->toBe('0A0000000123ABCD1');
    expect($sentData->getMessagePrice())->toBe("0.03330000");
    expect($sentData->getNetwork())->toBe("12345");
    expect($sentData->getRemainingBalance())->toBe("3.14159265");
    expect($sentData->getStatus())->toBe(0);
    expect($end - $start)->toBeGreaterThanOrEqual(2);
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

    expect($response)->toHaveCount((int)$rawData['message-count']);

    foreach ($response as $key => $sentData) {
        expect($sentData->getTo())->toBe($rawData['messages'][$key]['to']);
        expect($sentData->getMessageId())->toBe($rawData['messages'][$key]['message-id']);
        expect($sentData->getMessagePrice())->toBe($rawData['messages'][$key]['message-price']);
        expect($sentData->getNetwork())->toBe($rawData['messages'][$key]['network']);
        expect($sentData->getRemainingBalance())->toBe($rawData['messages'][$key]['remaining-balance']);
        expect($sentData->getStatus())->toBe((int)$rawData['messages'][$key]['status']);
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

    expect($sentData->getTo())->toBe('447700900000');
    expect($sentData->getMessageId())->toBe('0A0000000123ABCD1');
    expect($sentData->getMessagePrice())->toBe("0.03330000");
    expect($sentData->getNetwork())->toBe("12345");
    expect($sentData->getRemainingBalance())->toBe("3.14159265");
    expect($sentData->getStatus())->toBe(0);
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

    expect($response)->toHaveCount(1);
    expect($sentData->getTo())->toBe('447700900000');
    expect($sentData->getMessageId())->toBe('0A0000000123ABCD1');
    expect($sentData->getMessagePrice())->toBe("0.03330000");
    expect($sentData->getNetwork())->toBe("12345");
    expect($sentData->getRemainingBalance())->toBe("3.14159265");
    expect($sentData->getStatus())->toBe(0);
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
