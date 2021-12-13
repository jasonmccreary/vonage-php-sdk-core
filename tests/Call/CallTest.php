<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Laminas\Diactoros\Response;
use VonageTest\VonageTestCase;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Call\Call;
use Vonage\Call\Dtmf;
use Vonage\Call\Endpoint;
use Vonage\Call\Stream;
use Vonage\Call\Talk;
use Vonage\Call\Transfer;
use Vonage\Call\Webhook;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\Conversations\Conversation;
use VonageTest\Psr7AssertionTrait;

uses(VonageTestCase::class);
uses(Psr7AssertionTrait::class);


beforeEach(function () {
    $this->id = '3fd4d839-493e-4485-b2a5-ace527aacff3';
    $this->class = Call::class;

    $this->entity = @new Call('3fd4d839-493e-4485-b2a5-ace527aacff3');
    $this->new = @new Call();

    $this->vonageClient = $this->prophesize('Vonage\Client');
    $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

    /** @noinspection PhpParamsInspection */
    $this->entity->setClient($this->vonageClient->reveal());
    /** @noinspection PhpParamsInspection */
    $this->new->setClient($this->vonageClient->reveal());
});

/**
 * Entities should be constructable with an ID.
 */
test('construct with id', function () {
    $class = $this->class;
    $entity = @new $class('3fd4d839-493e-4485-b2a5-ace527aacff3');

    expect($entity->getId())->toBe('3fd4d839-493e-4485-b2a5-ace527aacff3');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException
 * @throws RequestException
 * @throws ServerException
 */
test('get makes request', function () {
    // @todo Remove deprecated tests
    $class = $this->class;
    $id = $this->id;
    $response = getResponse('call');

    $entity = @new $class($id);
    $entity->setClient($this->vonageClient->reveal());

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'GET', $request);
        return true;
    }))->willReturn($response);

    @$entity->get();

    @assertEntityMatchesResponse($entity, $response);
});

/**
 * @param $payload
 * @param $expectedHttpCode
 * @param $expectedResponse
 *
 * @throws ClientExceptionInterface
 * @throws ClientException
 * @throws RequestException
 * @throws ServerException
 */
test('put makes request', function ($payload, $expectedHttpCode, $expectedResponse) {
    $id = $this->id;
    $expected = json_decode(json_encode($payload), true);

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $expected) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'PUT', $request);

        $request->getBody()->rewind();
        $body = json_decode($request->getBody()->getContents(), true);
        $request->getBody()->rewind();

        expect($body)->toEqual($expected);

        return true;
    }))->willReturn(getResponse($expectedResponse, $expectedHttpCode));

    @$this->entity->put($payload);
})->with('putCall');

/**
 * @throws ClientExceptionInterface
 * @throws ClientException
 * @throws RequestException
 * @throws ServerException
 */
test('lazy load', function () {
    // @todo Remove deprecated tests
    $id = $this->id;
    $response = getResponse('call');

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'GET', $request);
        return true;
    }))->willReturn($response);

    $return = @$this->entity->getStatus();
    expect($return)->toBe('completed');

    @assertEntityMatchesResponse($this->entity, $response);
});

test('stream', function () {
    // @todo Remove deprecated tests
    @$stream = $this->entity->stream;

    expect($stream)->toBeInstanceOf(Stream::class);
    expect($stream->getId())->toBe($this->entity->getId());

    expect(@$this->entity->stream)->toBe($stream);
    expect(@$this->entity->stream())->toBe($stream);

    @$this->entity->stream->setUrl('http://example.com');

    $response = new Response(fopen(__DIR__ . '/responses/stream.json', 'rb'), 200);

    $id = $this->entity->getId();

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/stream', 'PUT', $request);
        return true;
    }))->willReturn($response)->shouldBeCalled();

    @$this->entity->stream($stream);
});

test('s talk', function () {
    // @todo Remove deprecated tests
    @$talk = $this->entity->talk;

    expect($talk)->toBeInstanceOf(Talk::class);
    expect($talk->getId())->toBe($this->entity->getId());

    expect(@$this->entity->talk)->toBe($talk);
    expect(@$this->entity->talk())->toBe($talk);

    @$this->entity->talk->setText('Boom!');

    $response = new Response(fopen(__DIR__ . '/responses/talk.json', 'rb'), 200);

    $id = $this->entity->getId();

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/talk', 'PUT', $request);
        return true;
    }))->willReturn($response)->shouldBeCalled();

    @$this->entity->talk($talk);
});

test('s dtmf', function () {
    // @todo Remove deprecated tests
    $dtmf = @$this->entity->dtmf;

    expect($dtmf)->toBeInstanceOf(Dtmf::class);
    expect($dtmf->getId())->toBe($this->entity->getId());

    expect(@$this->entity->dtmf)->toBe($dtmf);
    expect(@$this->entity->dtmf())->toBe($dtmf);

    @$this->entity->dtmf->setDigits(1234);

    $response = new Response(fopen(__DIR__ . '/responses/dtmf.json', 'rb'), 200);

    $id = $this->entity->getId();

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/dtmf', 'PUT', $request);
        return true;
    }))->willReturn($response)->shouldBeCalled();

    @$this->entity->dtmf($dtmf);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException
 * @throws RequestException
 * @throws ServerException
 */
test('to is set', function () {
    // @todo split into discrete tests, use trait as can be useful elsewhere for consistency
    @$this->new->setTo('14845551212');
    $this->assertSame('14845551212', (string)$this->new->getTo());
    expect($this->new->getTo()->getId())->toBe('14845551212');
    expect($this->new->getTo()->getType())->toBe('phone');

    $data = $this->new->jsonSerialize();

    $this->assertArrayHasKey('to', $data);
    expect($data['to'])->toBeArray();
    $this->assertArrayHasKey('number', $data['to'][0]);
    $this->assertArrayHasKey('type', $data['to'][0]);
    expect($data['to'][0]['number'])->toEqual('14845551212');
    expect($data['to'][0]['type'])->toEqual('phone');

    $this->new->setTo(@new Endpoint('14845551212'));
    $this->assertSame('14845551212', (string)$this->new->getTo());
    expect($this->new->getTo()->getId())->toBe('14845551212');
    expect($this->new->getTo()->getType())->toBe('phone');

    $data = $this->new->jsonSerialize();

    $this->assertArrayHasKey('to', $data);
    expect($data['to'])->toBeArray();
    $this->assertArrayHasKey('number', $data['to'][0]);
    $this->assertArrayHasKey('type', $data['to'][0]);
    expect($data['to'][0]['number'])->toEqual('14845551212');
    expect($data['to'][0]['type'])->toEqual('phone');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException
 * @throws RequestException
 * @throws ServerException
 */
test('from is set', function () {
    @$this->new->setFrom('14845551212');
    $this->assertSame('14845551212', (string)$this->new->getFrom());
    expect($this->new->getFrom()->getId())->toBe('14845551212');
    expect($this->new->getFrom()->getType())->toBe('phone');

    $data = $this->new->jsonSerialize();

    $this->assertArrayHasKey('from', $data);
    $this->assertArrayHasKey('number', $data['from']);
    $this->assertArrayHasKey('type', $data['from']);
    expect($data['from']['number'])->toEqual('14845551212');
    expect($data['from']['type'])->toEqual('phone');

    $this->new->setFrom(@new Endpoint('14845551212'));
    $this->assertSame('14845551212', (string)$this->new->getFrom());
    expect($this->new->getFrom()->getId())->toBe('14845551212');
    expect($this->new->getFrom()->getType())->toBe('phone');

    $data = $this->new->jsonSerialize();

    $this->assertArrayHasKey('from', $data);
    $this->assertArrayHasKey('number', $data['from']);
    $this->assertArrayHasKey('type', $data['from']);
    expect($data['from']['number'])->toEqual('14845551212');
    expect($data['from']['type'])->toEqual('phone');
});

test('webhooks', function () {
    @$this->entity->setWebhook(Call::WEBHOOK_ANSWER, 'http://example.com');

    $data = $this->entity->jsonSerialize();
    $this->assertArrayHasKey('answer_url', $data[0]);
    expect($data[0]['answer_url'])->toHaveCount(1);
    expect($data[0]['answer_url'][0])->toEqual('http://example.com');

    $this->entity->setWebhook(@new Webhook(Call::WEBHOOK_ANSWER, 'http://example.com'));

    $data = $this->entity->jsonSerialize();
    $this->assertArrayHasKey('answer_url', $data[0]);
    expect($data[0]['answer_url'])->toHaveCount(1);
    expect($data[0]['answer_url'][0])->toEqual('http://example.com');

    $this->entity->setWebhook(
        @new Webhook(Call::WEBHOOK_ANSWER, ['http://example.com', 'http://example.com/test'])
    );

    $data = $this->entity->jsonSerialize();
    $this->assertArrayHasKey('answer_url', $data[0]);
    expect($data[0]['answer_url'])->toHaveCount(2);
    expect($data[0]['answer_url'][0])->toEqual('http://example.com');
    expect($data[0]['answer_url'][1])->toEqual('http://example.com/test');

    $this->entity->setWebhook(@new Webhook(Call::WEBHOOK_ANSWER, 'http://example.com', 'POST'));

    $data = $this->entity->jsonSerialize();
    $this->assertArrayHasKey('answer_method', $data[0]);
    expect($data[0]['answer_method'])->toEqual('POST');
});

test('timers', function () {
    $this->entity->setTimer(Call::TIMER_LENGTH, 10);
    $data = $this->entity->jsonSerialize();

    $this->assertArrayHasKey('length_timer', $data);
    expect($data['length_timer'])->toEqual(10);
});

test('timeouts', function () {
    $this->entity->setTimeout(Call::TIMEOUT_MACHINE, 10);
    $data = $this->entity->jsonSerialize();

    $this->assertArrayHasKey('machine_timeout', $data);
    expect($data['machine_timeout'])->toEqual(10);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException
 * @throws RequestException
 * @throws ServerException
 */
test('hydrate', function () {
    $data = json_decode(file_get_contents(__DIR__ . '/responses/call.json'), true);
    $this->entity->jsonUnserialize($data);

    @assertEntityMatchesData($this->entity, $data);
});

// Datasets
/**
 * Can update the call with an object or a raw array.
 */
dataset('putCall', function () {
    $transfer = [
        'action' => 'transfer',
        'destination' => [
            'type' => 'ncco',
            'url' => ['http://example.com']
        ]
    ];

    return [
        [$transfer, 200, 'updated'],
        [@new Transfer('http://example.com'), 200, 'updated'],
        [@new Transfer('http://example.com'), 204, 'empty']
    ];
});

// Helpers
/**
     * Use a Response object as the data source.
     *
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
function assertEntityMatchesResponse(Call $entity, Response $response): void
{
    $response->getBody()->rewind();
    $json = $response->getBody()->getContents();
    $data = json_decode($json, true);

    test()->assertEntityMatchesData($entity, $data);
}

/**
     * Assert that the given response data is accessible via the object. This is the real work done by the hydration
     * test; however, it's also needed to test that API calls - $entity->get(), $entity->post() - actually set the
     * response data without coupling to the internal methods.
     *
     * @param $data
     *
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
function assertEntityMatchesData(Call $entity, $data): void
{
    expect($entity->getId())->toBe($data['uuid']);

    expect($entity->getTo()->getType())->toEqual($data['to']['type']);
    expect($entity->getFrom()->getType())->toEqual($data['from']['type']);

    expect($entity->getTo()->getId())->toEqual($data['to']['number']);
    expect($entity->getFrom()->getId())->toEqual($data['from']['number']);

    expect($entity->getTo()->getNumber())->toEqual($data['to']['number']);
    expect($entity->getFrom()->getNumber())->toEqual($data['from']['number']);

    expect($entity->getStatus())->toEqual($data['status']);
    expect($entity->getDirection())->toEqual($data['direction']);

    expect($entity->getConversation())->toBeInstanceOf(Conversation::class);
    expect($entity->getConversation()->getId())->toEqual($data['conversation_uuid']);
}

/**
     * Get the API response we'd expect for a call to the API.
     */
function getResponse(string $type = 'success', int $status = 200): Response
{
    return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
}
