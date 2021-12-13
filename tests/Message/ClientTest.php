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
use Vonage\Client\Exception as ClientException;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\Message\Client as MessageClient;
use Vonage\Message\InboundMessage;
use Vonage\Message\Message;
use Vonage\Message\Query;
use Vonage\Message\Shortcode\TwoFactor;
use Vonage\Message\Text;
use VonageTest\MessageAssertionTrait;
use VonageTest\Psr7AssertionTrait;

uses(Psr7AssertionTrait::class);
uses(MessageAssertionTrait::class);


/**
 * Create the Message API Client, and mock the Vonage Client
 */
beforeEach(function () {
    $this->vonageClient = $this->prophesize(Client::class);
    $this->vonageClient->getRestUrl()->willReturn('https://rest.nexmo.com');
    $this->messageClient = new MessageClient();

    /** @noinspection PhpParamsInspection */
    $this->messageClient->setClient($this->vonageClient->reveal());
});

/**
 * @throws ClientException\Exception
 * @throws ClientException\Request
 * @throws ServerException
 * @throws ClientExceptionInterface
 */
test('can use message', function () {
    $args = [
        'to' => '14845551212',
        'from' => '16105551212',
        'text' => 'Go To Gino\'s'
    ];

    $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
        $this->assertRequestJsonBodyContains('to', $args['to'], $request);
        $this->assertRequestJsonBodyContains('from', $args['from'], $request);
        $this->assertRequestJsonBodyContains('text', $args['text'], $request);
        return true;
    }))->willReturn(getResponse());

    $message = @$this->messageClient->send(new Text($args['to'], $args['from'], $args['text']));

    expect($message)->toBeInstanceOf(Text::class);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 * @throws ServerException
 */
test('throws request exception when invalid a p i response', function () {
    $this->expectException(ClientException\Request::class);
    $this->expectExceptionMessage('unexpected response from API');

    $args = [
        'to' => '14845551212',
        'from' => '16105551212',
        'text' => 'Go To Gino\'s'
    ];

    $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
        $this->assertRequestJsonBodyContains('to', $args['to'], $request);
        $this->assertRequestJsonBodyContains('from', $args['from'], $request);
        $this->assertRequestJsonBodyContains('text', $args['text'], $request);

        return true;
    }))->willReturn(getResponse('empty'));

    $this->messageClient->send(new Text($args['to'], $args['from'], $args['text']));
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 * @throws ServerException
 */
test('can use arguments', function () {
    $args = [
        'to' => '14845551212',
        'from' => '16105551212',
        'text' => 'Go To Gino\'s'
    ];

    $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
        $this->assertRequestJsonBodyContains('to', $args['to'], $request);
        $this->assertRequestJsonBodyContains('from', $args['from'], $request);
        $this->assertRequestJsonBodyContains('text', $args['text'], $request);
        return true;
    }))->willReturn(getResponse());

    @$message = $this->messageClient->send($args);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 * @throws ServerException
 */
test('sent message has response', function () {
    $response = getResponse();
    @$this->vonageClient->send(Argument::type(RequestInterface::class))->willReturn($response);
    $message = $this->messageClient->send(new Text('14845551212', '16105551212', 'Not Pats?'));

    expect(@$message->getResponse())->toBe($response);

    $this->vonageClient->send(@$message->getRequest())->shouldHaveBeenCalled();
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ServerException
 */
test('throw request exception', function () {
    $response = getResponse('fail');
    $this->vonageClient->send(Argument::type(RequestInterface::class))->willReturn($response);
    $message = new Text('14845551212', '16105551212', 'Not Pats?');

    try {
        $this->messageClient->send($message);

        self::fail('did not throw exception');
    } catch (ClientException\Request $e) {
        expect($e->getEntity())->toBe($message);
        expect($e->getCode())->toEqual('2');
        expect($e->getMessage())->toEqual('Missing from param');
    }
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('throw server exception', function () {
    $response = getResponse('fail-server');
    $this->vonageClient->send(Argument::type(RequestInterface::class))->willReturn($response);
    $message = new Text('14845551212', '16105551212', 'Not Pats?');

    try {
        $this->messageClient->send($message);

        self::fail('did not throw exception');
    } catch (ServerException $e) {
        expect($e->getCode())->toEqual('5');
        expect($e->getMessage())->toEqual('Server Error');
    }
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 */
test('throw concurrent requests exception', function () {
    try {
        $message = new Message('02000000D912945A');
        $response = getResponse('empty', 429);

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestQueryContains('id', '02000000D912945A', $request);
            return true;
        }))->willReturn($response);

        $this->messageClient->search($message);

        self::fail('did not throw exception');
    } catch (ClientException\Request $e) {
        expect($e->getCode())->toEqual('429');
        expect($e->getMessage())->toEqual('too many concurrent requests');
    }
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('can get message with message object', function () {
    $message = new Message('02000000D912945A');
    $response = getResponse('get-outbound');

    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);

        return true;
    }))->willReturn($response);

    $messages = $this->messageClient->get($message);

    // The response was already read, so have to rewind
    $response->getBody()->rewind();
    $body = json_decode($response->getBody()->getContents(), true);

    expect($messages)->toHaveCount($body['count']);
    expect($messages[0]->getMessageId())->toBe($body['items'][0]['message-id']);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('can get inbound message', function () {
    $message = new Message('0B00000053FFB40F');
    $response = getResponse('get-inbound');

    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestQueryContains('ids', ['0B00000053FFB40F'], $request);

        return true;
    }))->willReturn($response);

    $messages = $this->messageClient->get($message);

    // The response was already read, so need to rewind
    $response->getBody()->rewind();
    $body = json_decode($response->getBody()->getContents(), true);

    expect($messages)->toHaveCount($body['count']);
    expect($messages[0]->getMessageId())->toBe($body['items'][0]['message-id']);
    expect($messages[0])->toBeInstanceOf(InboundMessage::class);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('get throws exception on bad message type', function () {
    $this->expectException(ClientException\Request::class);
    $this->expectExceptionMessage('unexpected response from API');

    $message = new Message('0B00000053FFB40F');
    $response = getResponse('get-invalid-type');

    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestQueryContains('ids', ['0B00000053FFB40F'], $request);
        return true;
    }))->willReturn($response);

    $this->messageClient->get($message);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('get returns empty array with no results', function () {
    $message = new Message('02000000D912945A');
    $response = getResponse('get-no-results');

    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);

        return true;
    }))->willReturn($response);

    $messages = $this->messageClient->get($message);

    expect($messages)->toHaveCount(0);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('can get message with string i d', function () {
    $messageID = '02000000D912945A';
    $response = getResponse('get-outbound');

    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);

        return true;
    }))->willReturn($response);

    $messages = $this->messageClient->get($messageID);

    // The response was already read, so need to rewind
    $response->getBody()->rewind();
    $body = json_decode($response->getBody()->getContents(), true);

    expect($messages)->toHaveCount($body['count']);
    expect($messages[0]->getMessageId())->toBe($body['items'][0]['message-id']);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('can get message with array of i ds', function () {
    $messageIDs = ['02000000D912945A'];
    $response = getResponse('get-outbound');

    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);

        return true;
    }))->willReturn($response);

    $messages = $this->messageClient->get($messageIDs);

    // The response was already read, so need to rewind
    $response->getBody()->rewind();
    $body = json_decode($response->getBody()->getContents(), true);

    expect($messages)->toHaveCount($body['count']);
    expect($messages[0]->getMessageId())->toBe($body['items'][0]['message-id']);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('can get message with query', function () {
    $query = new Query(new DateTime('2016-05-19'), '14845551212');
    $response = getResponse('get-outbound');

    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestQueryContains('date', '2016-05-19', $request);
        $this->assertRequestQueryContains('to', '14845551212', $request);

        return true;
    }))->willReturn($response);

    $messages = $this->messageClient->get($query);

    // The response was already read, so need to rewind
    $response->getBody()->rewind();
    $body = json_decode($response->getBody()->getContents(), true);

    expect($messages)->toHaveCount($body['count']);
    expect($messages[0]->getMessageId())->toBe($body['items'][0]['message-id']);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('get throws exception when not200 but has error label', function () {
    $this->expectException(ClientException\Request::class);
    $this->expectExceptionMessage('authentication failed');

    $message = new Message('02000000D912945A');
    $response = getResponse('auth-failure', 401);

    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);

        return true;
    }))->willReturn($response);

    $this->messageClient->get($message);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('get throws exception when not200 and has no code', function () {
    $this->expectException(ClientException\Request::class);
    $this->expectExceptionMessage('error status from API');

    $message = new Message('02000000D912945A');
    $response = getResponse('empty', 500);

    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);

        return true;
    }))->willReturn($response);

    $this->messageClient->get($message);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('get throws exception when invalid response returned', function () {
    $this->expectException(ClientException\Request::class);
    $this->expectExceptionMessage('unexpected response from API');

    $message = new Message('02000000D912945A');
    $response = getResponse('empty');

    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);

        return true;
    }))->willReturn($response);

    $this->messageClient->get($message);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('get throws invalid argument exception with bad query', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage(
        'query must be an instance of Query, MessageInterface, string ID, or array of IDs.'
    );

    $message = new stdClass();
    $message->ids = ['02000000D912945A'];
    $response = getResponse('empty');

    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestQueryContains('ids', ['02000000D912945A'], $request);

        return true;
    }))->willReturn($response);

    $this->messageClient->get($message);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 * @throws Exception
 */
test('can search by message', function () {
    $message = new Message('02000000D912945A');
    $response = getResponse('search-outbound');

    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestQueryContains('id', '02000000D912945A', $request);

        return true;
    }))->willReturn($response);

    $searchedMessage = $this->messageClient->search($message);

    $response->getBody()->rewind();
    $successData = json_decode($response->getBody()->getContents(), true);

    expect($searchedMessage->getMessageId())->toEqual($successData['message-id']);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('can search by single outbound id', function () {
    $response = getResponse('search-outbound');

    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestQueryContains('id', '02000000D912945A', $request);

        return true;
    }))->willReturn($response);

    $message = $this->messageClient->search('02000000D912945A');

    expect($message)->toBeInstanceOf(Message::class);
    expect(@$message->getResponse())->toBe($response);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('can search by single inbound id', function () {
    $response = getResponse('search-inbound');

    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestQueryContains('id', '02000000DA7C52E7', $request);

        return true;
    }))->willReturn($response);

    $message = $this->messageClient->search('02000000DA7C52E7');

    expect($message)->toBeInstanceOf(InboundMessage::class);
    expect(@$message->getResponse())->toBe($response);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('search throws exception on empty search set', function () {
    $this->expectException(ClientException\Request::class);
    $this->expectExceptionMessage('no message found for `02000000DA7C52E7`');
    $response = getResponse('search-empty');

    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestQueryContains('id', '02000000DA7C52E7', $request);

        return true;
    }))->willReturn($response);

    $this->messageClient->search('02000000DA7C52E7');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('search throw exception on non200', function () {
    $this->expectException(ClientException\Request::class);
    $this->expectExceptionMessage('authentication failed');

    $message = new Message('02000000D912945A');
    $response = getResponse('auth-failure', 401);

    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestQueryContains('id', '02000000D912945A', $request);

        return true;
    }))->willReturn($response);

    $this->messageClient->search($message);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('search throw exception on invalid type', function () {
    $this->expectException(ClientException\Request::class);
    $this->expectExceptionMessage('unexpected response from API');

    $message = new Message('02000000D912945A');
    $response = getResponse('search-invalid-type');

    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestQueryContains('id', '02000000D912945A', $request);

        return true;
    }))->willReturn($response);

    $this->messageClient->search($message);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('search throws generic exception on non200', function () {
    $this->expectException(ClientException\Request::class);
    $this->expectExceptionMessage('error status from API');

    $message = new Message('02000000D912945A');
    $response = getResponse('empty', 500);

    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestQueryContains('id', '02000000D912945A', $request);

        return true;
    }))->willReturn($response);

    $this->messageClient->search($message);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('throws exception when search result mismatches query', function () {
    $this->expectException(ClientException\Exception::class);
    $this->expectExceptionMessage('searched for message with type `Vonage\Message\Message` ' .
        'but message of type `Vonage\Message\InboundMessage`');

    $message = new Message('02000000D912945A');
    $response = getResponse('search-inbound');

    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestQueryContains('id', '02000000D912945A', $request);

        return true;
    }))->willReturn($response);

    $this->messageClient->search($message);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 * @throws ServerException
 */
test('rate limit retries', function () {
    $start = microtime(true);
    $rate = getResponse('ratelimit');
    $rate2 = getResponse('ratelimit');
    $success = getResponse('success');

    $args = [
        'to' => '14845551345',
        'from' => '1105551334',
        'text' => 'test message'
    ];

    $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
        $this->assertRequestJsonBodyContains('to', $args['to'], $request);
        $this->assertRequestJsonBodyContains('from', $args['from'], $request);
        $this->assertRequestJsonBodyContains('text', $args['text'], $request);

        return true;
    }))->willReturn($rate, $rate2, $success);

    $message = $this->messageClient->send(new Text($args['to'], $args['from'], $args['text']));
    $end = microtime(true);

    expect(@$message->getResponse())->toEqual($success);
    expect($end - $start)->toBeGreaterThanOrEqual(2);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 * @throws ServerException
 * @throws Exception
 */
test('rate limit retries with default', function () {
    $rate = getResponse('ratelimit-notime');
    $rate2 = getResponse('ratelimit-notime'); // Have to duplicate to avoid rewind issues
    $success = getResponse('success');

    $args = [
        'to' => '14845551345',
        'from' => '1105551334',
        'text' => 'test message'
    ];

    $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
        $this->assertRequestJsonBodyContains('to', $args['to'], $request);
        $this->assertRequestJsonBodyContains('from', $args['from'], $request);
        $this->assertRequestJsonBodyContains('text', $args['text'], $request);

        return true;
    }))->willReturn($rate, $rate2, $success);

    $message = $this->messageClient->send(new Text($args['to'], $args['from'], $args['text']));

    $success->getBody()->rewind();
    $successData = json_decode($success->getBody()->getContents(), true);

    expect($message->getMessageId())->toEqual($successData['messages'][0]['message-id']);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 * @throws ServerException
 */
test('a p i rate limit retries', function () {
    $start = microtime(true);
    $rate = getResponse('mt-limit');
    $rate2 = getResponse('mt-limit');
    $success = getResponse('success');

    $args = [
        'to' => '14845551345',
        'from' => '1105551334',
        'text' => 'test message'
    ];

    $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
        $this->assertRequestJsonBodyContains('to', $args['to'], $request);
        $this->assertRequestJsonBodyContains('from', $args['from'], $request);
        $this->assertRequestJsonBodyContains('text', $args['text'], $request);

        return true;
    }))->willReturn($rate, $rate2, $success);

    $message = $this->messageClient->send(new Text($args['to'], $args['from'], $args['text']));
    $end = microtime(true);

    expect(@$message->getResponse())->toEqual($success);
    expect($end - $start)->toBeGreaterThanOrEqual(2);
});

/**
 *
 * @param $date
 * @param $to
 * @param $responseFile
 * @param $expectedResponse
 * @param $expectedHttpCode
 * @param $expectedException
 *
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('can search rejections', function (
    $date,
    $to,
    $responseFile,
    $expectedResponse,
    $expectedHttpCode,
    $expectedException
) {
    $query = new Query($date, $to);

    $apiResponse = getResponse($responseFile, $expectedHttpCode);

    $this->vonageClient->send(Argument::that(function (Request $request) use ($to, $date) {
        $this->assertRequestQueryContains('to', $to, $request);
        $this->assertRequestQueryContains('date', $date->format('Y-m-d'), $request);

        return true;
    }))->willReturn($apiResponse);

// If we're expecting this to throw an exception, listen for it in advance
    if ($expectedException !== null) {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedResponse);
    }

    // Make the request and assert that our responses match
    $rejectionsResponse = $this->messageClient->searchRejections($query);

    $this->assertListOfMessagesEqual($expectedResponse, $rejectionsResponse);
})->with('searchRejectionsProvider');

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('shortcode with object', function () {
    $message = new TwoFactor('14155550100', ['link' => 'https://example.com'], ['status-report-req' => 1]);

    $this->vonageClient->send(Argument::that(function (Request $request) {
        $this->assertRequestJsonBodyContains('to', '14155550100', $request);
        $this->assertRequestJsonBodyContains('link', 'https://example.com', $request);
        $this->assertRequestJsonBodyContains('status-report-req', 1, $request);

        return true;
    }))->willReturn(getResponse('success-2fa'));

    $response = $this->messageClient->sendShortcode($message);

    $this->assertEquals([
        'message-count' => '1',
        'messages' => [
            [
                'status' => '0',
                'message-id' => '00000123',
                'to' => '14155550100',
                'client-ref' => 'client-ref',
                'remaining-balance' => '1.10',
                'message-price' => '0.05',
                'network' => '23410'
            ]
        ]
    ], $response);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('shortcode error', function () {
    $args = [
        'to' => '14155550100',
        'custom' => ['link' => 'https://example.com'],
        'options' => ['status-report-req' => 1],
        'type' => '2fa'
    ];

    $this->vonageClient->send(Argument::that(function (Request $request) {
        return true;
    }))->willReturn(getResponse('error-2fa'));

    $this->expectException(ClientException\Request::class);
    $this->expectExceptionMessage('Invalid Account for Campaign');

    $this->messageClient->sendShortcode($args);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 */
test('shortcode with array', function () {
    $args = [
        'to' => '14155550100',
        'custom' => ['link' => 'https://example.com'],
        'options' => ['status-report-req' => 1],
        'type' => '2fa'
    ];

    $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
        $this->assertRequestJsonBodyContains('to', $args['to'], $request);
        $this->assertRequestJsonBodyContains('link', $args['custom']['link'], $request);
        $this->assertRequestJsonBodyContains('status-report-req', $args['options']['status-report-req'], $request);

        return true;
    }))->willReturn(getResponse('success-2fa'));

    $response = $this->messageClient->sendShortcode($args);

    $this->assertEquals([
        'message-count' => '1',
        'messages' => [
            [
                'status' => '0',
                'message-id' => '00000123',
                'to' => '14155550100',
                'client-ref' => 'client-ref',
                'remaining-balance' => '1.10',
                'message-price' => '0.05',
                'network' => '23410'
            ]
        ]
    ], $response);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 * @throws ServerException
 */
test('create message throws exception on bad data', function () {
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('message must implement `Vonage\Message\MessageInterface` or be an array`');

    /** @noinspection PhpParamsInspection */
    @$this->messageClient->send('Bob');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 * @throws ServerException
 */
test('create message throws exception on missing data', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('missing expected key `from`');

    @$this->messageClient->send(['to' => '15555555555']);
});

test('magic method is called properly', function () {
    $args = [
        'to' => '14845551212',
        'from' => '16105551212',
        'text' => 'Go To Gino\'s'
    ];

    $this->vonageClient->send(Argument::that(function (Request $request) use ($args) {
        $this->assertRequestJsonBodyContains('to', $args['to'], $request);
        $this->assertRequestJsonBodyContains('from', $args['from'], $request);
        $this->assertRequestJsonBodyContains('text', $args['text'], $request);

        return true;
    }))->willReturn(getResponse());

    $message = $this->messageClient->sendText($args['to'], $args['from'], $args['text']);
    expect($message)->toBeInstanceOf(Text::class);
});

test('create message throws exception on non send method', function () {
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('failsendText` is not a valid method on `Vonage\Message\Client`');

    /** @noinspection PhpUndefinedMethodInspection */
    $this->messageClient->failsendText('14845551212', '16105551212', 'Test');
});

test('create message throws exception on non send method take two', function () {
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('failText` is not a valid method on `Vonage\Message\Client`');

    /** @noinspection PhpUndefinedMethodInspection */
    $this->messageClient->failText('14845551212', '16105551212', 'Test');
});

test('create message throws exception on invalid message type', function () {
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('sendGarbage` is not a valid method on `Vonage\Message\Client`');

    /** @noinspection PhpUndefinedMethodInspection */
    $this->messageClient->sendGarbage('14845551212', '16105551212', 'Test');
});

// Datasets
dataset('searchRejectionsProvider', function () {
    $r = [];

    $r['no rejections found'] = [new DateTime(), '123456', 'search-rejections-empty', [], 200, null];

    // Build up our expected message object
    $message = new Message('0C0000005BA0B864');
    @$message->setResponse(getResponse('search-rejections'));
    $message->setIndex(0);
    $inboundMessage = new InboundMessage('0C0000005BA0B864');
    @$inboundMessage->setResponse(getResponse('search-rejections-inbound'));
    $inboundMessage->setIndex(0);

    $r['rejection found'] = [new DateTime(), '123456', 'search-rejections', [$message], 200, null];
    $r['inbound rejection found'] = [
        new DateTime(),
        '123456',
        'search-rejections-inbound',
        [$inboundMessage],
        200,
        null
    ];

    $r['error-code provided (validation)'] = [
        new DateTime(),
        '123456',
        'search-rejections-error-provided-validation',
        'Validation error: You forgot to do something',
        400,
        ClientException\Request::class
    ];

    $r['error-code provided (server error)'] = [
        new DateTime(),
        '123456',
        'search-rejections-error-provided-server-error',
        'Gremlins! There are gremlins in the system!',
        500,
        ClientException\Request::class
    ];

    $r['error-code not provided'] = [
        new DateTime(),
        '123456',
        'empty',
        'error status from API',
        500,
        ClientException\Request::class
    ];

    $r['missing items key in response on 200'] = [
        new DateTime(),
        '123456',
        'empty',
        'unexpected response from API',
        200,
        ClientException\Exception::class
    ];

    $r['invalid message type in response'] = [
        new DateTime(),
        '123456',
        'search-rejections-invalid-type',
        'unexpected response from API',
        200,
        ClientException\Request::class
    ];

    return $r;
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
