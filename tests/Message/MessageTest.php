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
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Message\Message;
use Vonage\Message\Text;

uses(VonageTestCase::class);


beforeEach(function () {
    $this->message = new Message($this->to, $this->from, [
        'text' => $this->text
    ]);
});

afterEach(function () {
    $this->message = null;
});

/**
 * @throws ClientException
 */
test('request sets data', function () {
    $data = ['test' => 'test'];
    $request = new Request('http://example.com?' . http_build_query($data));
    @$this->message->setRequest($request);

    $this->assertSame($request, @$this->message->getRequest());

    $requestData = @$this->message->getRequestData();

    $this->assertEquals($data, $requestData);
});

/**
 * @throws Exception
 */
test('response sets data', function () {
    $data = ['test' => 'test'];
    $response = new Response();
    $response->getBody()->write(json_encode($data));
    $response->getBody()->rewind();

    @$this->message->setResponse($response);

    $this->assertSame($response, @$this->message->getResponse());
    $this->assertEquals($data, @$this->message->getResponseData());
});

/**
 * For getting message data from API, can create a simple object with just an ID.
 *
 * @throws Exception
 */
test('can create with id', function () {
    $this->assertEquals('00000123', (new Message('00000123'))->getMessageId());
});

/**
 * When creating a message, it should not auto-detect encoding by default
 *
 *
 * @param $msg
 *
 * @throws ClientException
 */
test('does not autodetect by default', function ($msg) {
    $message = new Text('to', 'from', $msg);

    $this->assertFalse($message->isEncodingDetectionEnabled());

    $d = $message->getRequestData(false);

    $this->assertEquals('text', $d['type']);
})->with('messageEncodingProvider');

/**
 * When creating a message, it should not auto-detect encoding by default
 *
 *
 * @param $msg
 * @param $encoding
 *
 * @throws ClientException
 */
test('does autodetect when enabled', function ($msg, $encoding) {
    $message = new Text('to', 'from', $msg);
    $message->enableEncodingDetection();

    $this->assertTrue($message->isEncodingDetectionEnabled());

    $d = $message->getRequestData(false);

    $this->assertEquals($d['type'], $encoding);
})->with('messageEncodingProvider');

// Datasets
dataset('messageEncodingProvider', [
    'text' => ['Hello World', 'text'],
    'emoji' => ['Testing 💪', 'unicode'],
    'kanji' => ['漢字', 'unicode']
]);

// Helpers
/**
     * Get the API response we'd expect for a call to the API. Message API currently returns 200 all the time, so only
     * change between success / fail is body of the message.
     */
function getResponse(string $type = 'success'): Response
{
    return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'));
}
