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

    expect(@$this->message->getRequest())->toBe($request);

    $requestData = @$this->message->getRequestData();

    expect($requestData)->toEqual($data);
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

    expect(@$this->message->getResponse())->toBe($response);
    expect(@$this->message->getResponseData())->toEqual($data);
});

/**
 * For getting message data from API, can create a simple object with just an ID.
 *
 * @throws Exception
 */
test('can create with id', function () {
    expect((new Message('00000123'))->getMessageId())->toEqual('00000123');
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

    expect($message->isEncodingDetectionEnabled())->toBeFalse();

    $d = $message->getRequestData(false);

    expect($d['type'])->toEqual('text');
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

    expect($message->isEncodingDetectionEnabled())->toBeTrue();

    $d = $message->getRequestData(false);

    expect($encoding)->toEqual($d['type']);
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
