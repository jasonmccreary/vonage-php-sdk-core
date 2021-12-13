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
use Vonage\Client;
use Vonage\Client\Exception as ClientException;
use Vonage\Conversations\Collection;
use Vonage\Conversations\Conversation;

beforeEach(function () {
    $this->vonageClient = $this->prophesize(Client::class);
    $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

    $this->collection = new Collection();
    /** @noinspection PhpParamsInspection */
    $this->collection->setClient($this->vonageClient->reveal());
});

/**
 * Getting an entity from the collection should not fetch it if we use the array interface.
 *
 *
 * @param $payload
 * @param $id
 */
test('array is lazy', function ($payload, $id) {
    $this->vonageClient->send(Argument::any())->willReturn(getResponse('conversation'));

    $conversation = $this->collection[$payload];
    expect($conversation)->toBeInstanceOf(Conversation::class);

    $this->vonageClient->send(Argument::any())->shouldNotHaveBeenCalled();
    expect($conversation->getId())->toEqual($id);

    if ($payload instanceof Conversation) {
        expect($conversation)->toBe($payload);
    }

    // Once we call get() the rest of the data should be populated
    $conversation->get();
    $this->vonageClient->send(Argument::any())->shouldHaveBeenCalled();
})->with('getConversation');

/**
 * Using `get()` should fetch the conversation data. Will accept both a string id and an object.
 * Must return the same object if that's the input.
 *
 *
 * @param $payload
 * @param $id
 *
 * @throws ClientException\Exception
 * @throws ClientException\Request
 * @throws ClientException\Server
 * @throws ClientExceptionInterface
 */
test('get is not lazy', function ($payload, $id) {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
        $this->assertRequestUrl('api.nexmo.com', '/beta/conversations/' . $id, 'GET', $request);
        return true;
    }))->willReturn(getResponse('conversation'))->shouldBeCalled();

    $conversation = $this->collection->get($payload);

    if ($payload instanceof Conversation) {
        expect($conversation)->toBe($payload);
    }
})->with('getConversation');

/**
 *
 * @param $payload
 * @param $method
 */
test('create post conversation', function ($payload, $method) {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
        $this->assertRequestUrl('api.nexmo.com', '/beta/conversations', 'POST', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('conversation', 200));

    $conversation = $this->collection->$method($payload);

    expect($conversation)->toBeInstanceOf(Conversation::class);
    expect($conversation->getId())->toEqual('CON-aaaaaaaa-bbbb-cccc-dddd-0123456789ab');
})->with('postConversation');

/**
 *
 * @param $payload
 * @param $method
 */
test('create post conversation error from v api', function ($payload, $method) {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
        $this->assertRequestUrl('api.nexmo.com', '/beta/conversations', 'POST', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('error_stitch', 400));

    try {
        $this->collection->$method($payload);

        self::fail('Expected to throw request exception');
    } catch (ClientException\Request $e) {
        expect($e->getMessage())->toEqual('the token was rejected');
    }
})->with('postConversation');

/**
 *
 * @param $payload
 * @param $method
 */
test('create post call error from proxy', function ($payload, $method) {
    self::markTestSkipped();

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/conversation', 'POST', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('error_proxy', 400));

    try {
        $this->collection->$method($payload);

        self::fail('Expected to throw request exception');
    } catch (ClientException\Request $e) {
        expect($e->getMessage())->toEqual('Unsupported Media Type');
    }
})->with('postConversation');

/**
 *
 * @param $payload
 * @param $method
 */
test('create post call error unknown format', function ($payload, $method) {
    self::markTestSkipped();

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/conversation', 'POST', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('error_unknown_format', 400));

    try {
        $this->collection->$method($payload);

        self::fail('Expected to throw request exception');
    } catch (ClientException\Request $e) {
        expect($e->getMessage())->toEqual("Unexpected error");
    }
})->with('postConversation');

// Datasets
/**
 * Getting a conversation can use an object or an ID.
 */
dataset('getConversation', [
    ['3fd4d839-493e-4485-b2a5-ace527aacff3', '3fd4d839-493e-4485-b2a5-ace527aacff3'],
    [new Conversation('3fd4d839-493e-4485-b2a5-ace527aacff3'), '3fd4d839-493e-4485-b2a5-ace527aacff3']
]);

/**
 * Creating a conversation can take a Call object or a simple array.
 */
dataset('postConversation', function () {
    $raw = [
        'name' => 'demo',
        'display_name' => 'Demo Name'
    ];

    $conversation = new Conversation();
    $conversation->setName('demo')
        ->setDisplayName('Demo Name');

    return [
        [clone $conversation, 'create'],
        [clone $conversation, 'post'],
        [$raw, 'create'],
        [$raw, 'post'],
    ];
});

// Helpers
/**
     * Get the API response we'd expect for a call to the API.
     */
function getResponse(string $type = 'success', int $status = 200): Response
{
    return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
}
