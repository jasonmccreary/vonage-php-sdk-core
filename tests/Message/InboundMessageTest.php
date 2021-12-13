<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use VonageTest\VonageTestCase;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Message\InboundMessage;
use Vonage\Message\Message;

uses(VonageTestCase::class);


test('construction with id', function () {
    $message = new InboundMessage('test1234');
    $this->assertSame('test1234', $message->getMessageId());
});

/**
 * Inbound messages can be created from a PSR-7 server request.
 *
 */
test('can create with server request', function (ServerRequest $request) {
    $message = @new InboundMessage($request);

    /** @var array $requestData */
    $requestData = @$message->getRequestData();

    $originalData = $request->getQueryParams();

    if ('POST' === $request->getMethod()) {
        $originalData = $request->getParsedBody();

        $contentTypeHeader = $request->getHeader('Content-Type');

        if (array_key_exists(0, $contentTypeHeader) && 'application/json' === $contentTypeHeader[0]) {
            $originalData = json_decode((string)$request->getBody(), true);
        }
    }

    $this->assertCount(count($originalData), $requestData);

    foreach ($originalData as $key => $value) {
        $this->assertSame($value, $requestData[$key]);
    }
})->with('getRequests');

test('can check valid', function () {
    $request = getServerRequest();
    $message = @new InboundMessage($request);

    $this->assertTrue($message->isValid());

    $request = getServerRequest('http://example.com', 'GET', 'invalid');
    $message = @new InboundMessage($request);

    $this->assertFalse($message->isValid());
});

/**
 * Can access expected params via getters.
 *
 *
 * @param $request
 */
test('request object access', function ($request) {
    $message = @new InboundMessage($request);

    $this->assertEquals('14845552121', $message->getFrom());
    $this->assertEquals('16105553939', $message->getTo());
    $this->assertEquals('02000000DA7C52E7', $message->getMessageId());
    $this->assertEquals('Test this.', $message->getBody());
    $this->assertEquals('text', $message->getType());
})->with('getRequests');

/**
 * Can access raw params via array access.
 *
 *
 * @param $request
 */
test('request array access', function ($request) {
    $message = @new InboundMessage($request);

    $this->assertEquals('14845552121', @$message['msisdn']);
    $this->assertEquals('16105553939', @$message['to']);
    $this->assertEquals('02000000DA7C52E7', @$message['messageId']);
    $this->assertEquals('Test this.', @$message['text']);
    $this->assertEquals('text', @$message['type']);
})->with('getRequests');

/**
 * Can access expected params when populated from an API request.
 *
 *
 * @param $response
 */
test('response object access', function ($response) {
    $message = new InboundMessage('02000000DA7C52E7');
    @$message->setResponse($response);

    $this->assertEquals('14845552121', $message->getFrom());
    $this->assertEquals('16105553939', $message->getTo());
    $this->assertEquals('02000000DA7C52E7', $message->getMessageId());
    $this->assertEquals('Test this.', $message->getBody());
    $this->assertEquals('6cff3913', $message->getAccountId());
    $this->assertEquals('US-VIRTUAL-BANDWIDTH', $message->getNetwork());
})->with('getResponses');

/**
 * Can access raw params when populated from an API request.
 *
 *
 * @param $response
 */
test('response array access', function ($response) {
    $message = new InboundMessage('02000000DA7C52E7');
    @$message->setResponse($response);

    $this->assertEquals('14845552121', @$message['from']);
    $this->assertEquals('16105553939', @$message['to']);
    $this->assertEquals('02000000DA7C52E7', @$message['message-id']);
    $this->assertEquals('Test this.', @$message['body']);
    $this->assertEquals('MO', @$message['type']);
    $this->assertEquals('6cff3913', @$message['account-id']);
    $this->assertEquals('US-VIRTUAL-BANDWIDTH', @$message['network']);
})->with('getResponses');

/**
 * @throws ClientException
 */
test('can create reply', function () {
    $message = @new InboundMessage(getServerRequest());
    $reply = $message->createReply('this is a reply');

    $this->assertInstanceOf(Message::class, $reply);

    $params = $reply->getRequestData(false);

    $this->assertEquals('14845552121', $params['to']);
    $this->assertEquals('16105553939', $params['from']);
    $this->assertEquals('this is a reply', $params['text']);
});

// Datasets
/**
 * @return Response[]
 */
dataset('getResponses', [
    [getResponse('search-inbound')]
]);

/**
 * @return ServerRequest[]
 */
dataset('getRequests', [
    'post, application/json' => [
        getServerRequest(
            'https://ohyt2ctr9l0z.runscope.net/sms_post',
            'POST',
            'json',
            ['Content-Type' => 'application/json']
        )
    ],
    'post, form-encoded' => [
        getServerRequest(
            'https://ohyt2ctr9l0z.runscope.net/sms_post',
            'POST',
            'inbound'
        )
    ],
    'get, form-encoded' => [
        getServerRequest(
            'https://ohyt2ctr9l0z.runscope.net/sms_post',
            'GET',
            'inbound'
        )
    ],
]);

// Helpers
/**
     * @param string $url
     * @param string $method
     * @param string $type
     * @param array $headers
     */
function getServerRequest(
    $url = 'https://ohyt2ctr9l0z.runscope.net/sms_post',
    $method = 'GET',
    $type = 'inbound',
    $headers = []
): ServerRequest {
    $data = file_get_contents(__DIR__ . '/requests/' . $type . '.txt');
    $params = [];
    $parsed = null;

    parse_str($data, $params);

    if (strtoupper($method) === 'GET') {
        $query = $params;
        $body = 'php://memory';
    } else {
        $body = fopen(__DIR__ . '/requests/' . $type . '.txt', 'rb');
        $query = [];
        $parsed = $params;

        if (isset($headers['Content-Type']) && $headers['Content-Type'] === 'application/json') {
            $parsed = null;
        }
    }

    return new ServerRequest([], [], $url, $method, $body, $headers, [], $query, $parsed);
}

/**
     * Get the API response we'd expect for a call to the API.
     */
function getResponse(string $type = 'success'): Response
{
    return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'));
}
