<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Laminas\Diactoros\Response;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Call\Event;
use Vonage\Call\Stream;
use Vonage\Client;
use VonageTest\Psr7AssertionTrait;

uses(Psr7AssertionTrait::class);


beforeEach(function () {
    $this->id = '3fd4d839-493e-4485-b2a5-ace527aacff3';
    $this->class = Stream::class;

    $this->entity = @new Stream('3fd4d839-493e-4485-b2a5-ace527aacff3');
    $this->new = @new Stream();

    $this->vonageClient = $this->prophesize(Client::class);
    $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

    /** @noinspection PhpParamsInspection */
    $this->entity->setClient($this->vonageClient->reveal());

    /** @noinspection PhpParamsInspection */
    $this->new->setClient($this->vonageClient->reveal());
});

test('has id', function () {
    expect($this->entity->getId())->toBe($this->id);
});

test('set url', function () {
    $url = 'http://example.com';
    $this->entity->setUrl($url);
    $data = $this->entity->jsonSerialize();

    expect($data['stream_url'])->toBe([$url]);
});

test('set url array', function () {
    $url = ['http://example.com', 'http://backup.example.com'];
    $this->entity->setUrl($url);
    $data = $this->entity->jsonSerialize();

    expect($data['stream_url'])->toBe($url);
});

test('set loop', function () {
    $loop = 10;
    $this->entity->setLoop($loop);
    $data = $this->entity->jsonSerialize();

    expect($data['loop'])->toBe($loop);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws Client\Exception\Server
 */
test('put makes request', function () {
    $this->entity->setUrl('http://example.com');
    $this->entity->setLoop(10);

    $callId = $this->id;
    $stream = $this->entity;

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($callId, $stream) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callId . '/stream', 'PUT', $request);
        $expected = json_decode(json_encode($stream), true);

        $request->getBody()->rewind();
        $body = json_decode($request->getBody()->getContents(), true);
        $request->getBody()->rewind();

        expect($body)->toEqual($expected);

        return true;
    }))->willReturn(getResponse('stream', 200));

    $event = @$this->entity->put();

    expect($event)->toBeInstanceOf(Event::class);
    expect($event['uuid'])->toBe('ssf61863-4a51-ef6b-11e1-w6edebcf93bb');
    expect($event['message'])->toBe('Stream started');
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws Client\Exception\Server
 */
test('put can replace', function () {
    $stream = @new Stream();
    $stream->setUrl('http://example.com');
    $stream->setLoop(10);

    $callId = $this->id;

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($callId, $stream) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callId . '/stream', 'PUT', $request);
        $expected = json_decode(json_encode($stream), true);

        $request->getBody()->rewind();
        $body = json_decode($request->getBody()->getContents(), true);
        $request->getBody()->rewind();

        expect($body)->toEqual($expected);

        return true;
    }))->willReturn(getResponse('stream', 200));

    $event = @$this->entity->put($stream);

    expect($event)->toBeInstanceOf(Event::class);
    expect($event['uuid'])->toBe('ssf61863-4a51-ef6b-11e1-w6edebcf93bb');
    expect($event['message'])->toBe('Stream started');
});

/**
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws Client\Exception\Server
 * @throws ClientExceptionInterface
 */
test('invoke proxies put with argument', function () {
    $object = $this->entity;

    $this->vonageClient->send(Argument::any())->willReturn(getResponse('stream', 200));
    $test = $object();

    expect($test)->toBe($this->entity);

    $this->vonageClient->send(Argument::any())->shouldNotHaveBeenCalled();

    $stream = @new Stream();
    $stream->setUrl('http://example.com');

    $event = @$object($stream);

    expect($event)->toBeInstanceOf(Event::class);
    expect($event['uuid'])->toBe('ssf61863-4a51-ef6b-11e1-w6edebcf93bb');
    expect($event['message'])->toBe('Stream started');

    $this->vonageClient->send(Argument::any())->shouldHaveBeenCalled();
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws Client\Exception\Server
 */
test('delete makes request', function () {
    $this->entity;
    $this->entity;

    $callId = $this->id;

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($callId) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callId . '/stream', 'DELETE', $request);
        return true;
    }))->willReturn(getResponse('stream-delete', 200));

    $event = @$this->entity->delete();

    expect($event)->toBeInstanceOf(Event::class);
    expect($event['uuid'])->toBe('ssf61863-4a51-ef6b-11e1-w6edebcf93bb');
    expect($event['message'])->toBe('Stream stopped');
});

// Helpers
/**
     * Get the API response we'd expect for a call to the API.
     */
function getResponse(string $type = 'success', int $status = 200): Response
{
    return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
}
