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
use Vonage\User\Collection;
use Vonage\User\User;

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
    $this->vonageClient->send(Argument::any())->willReturn(getResponse('user'));

    $user = $this->collection[$payload];

    expect($user)->toBeInstanceOf(User::class);
    $this->vonageClient->send(Argument::any())->shouldNotHaveBeenCalled();
    expect($user->getId())->toEqual($id);

    if ($payload instanceof User) {
        expect($user)->toBe($payload);
    }

    // Once we call get() the rest of the data should be populated
    $user->get();
    $this->vonageClient->send(Argument::any())->shouldHaveBeenCalled();
})->with('getUser');

/**
 * Using `get()` should fetch the user data. Will accept both a string id and an object. Must return the same object
 * if that's the input.
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
        $this->assertRequestUrl('api.nexmo.com', '/beta/users/' . $id, 'GET', $request);
        return true;
    }))->willReturn(getResponse('user'))->shouldBeCalled();

    $user = $this->collection->get($payload);

    if ($payload instanceof User) {
        expect($user)->toBe($payload);
    }
})->with('getUser');

test('can fetch all users', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        $this->assertRequestUrl('api.nexmo.com', '/beta/users', 'GET', $request);

        return true;
    }))->willReturn(getResponse('multiple_users'))->shouldBeCalled();

    $users = $this->collection->fetch();

    expect($users)->toHaveCount(3);
    expect($users[0])->toBeInstanceOf(User::class);
    expect($users[1])->toBeInstanceOf(User::class);
    expect($users[2])->toBeInstanceOf(User::class);
});

/**
 *
 * @param $payload
 * @param $method
 */
test('create post conversation', function ($payload, $method) {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
        $this->assertRequestUrl('api.nexmo.com', '/beta/users', 'POST', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('user', 200));

    $user = $this->collection->$method($payload);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->getId())->toEqual('USR-aaaaaaaa-bbbb-cccc-dddd-0123456789ab');
})->with('postUser');

/**
 *
 * @param $payload
 * @param $method
 */
test('create post user error from stitch', function ($payload, $method) {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
        $this->assertRequestUrl('api.nexmo.com', '/beta/users', 'POST', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('error_stitch', 400));

    try {
        $this->collection->$method($payload);

        self::fail('Expected to throw request exception');
    } catch (ClientException\Request $e) {
        expect($e->getMessage())->toEqual('the token was rejected');
    }
})->with('postUser');

/**
 *
 * @param $payload
 * @param $method
 */
test('create post user error from proxy', function ($payload, $method) {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
        $this->assertRequestUrl('api.nexmo.com', '/beta/users', 'POST', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('error_proxy', 400));

    try {
        $this->collection->$method($payload);

        self::fail('Expected to throw request exception');
    } catch (ClientException\Request $e) {
        expect($e->getMessage())->toEqual('Unsupported Media Type');
    }
})->with('postUser');

/**
 *
 * @param $payload
 * @param $method
 */
test('create post call error unknown format', function ($payload, $method) {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
        $this->assertRequestUrl('api.nexmo.com', '/beta/users', 'POST', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);
        return true;
    }))->willReturn(getResponse('error_unknown_format', 400));

    try {
        $this->collection->$method($payload);

        self::fail('Expected to throw request exception');
    } catch (ClientException\Request $e) {
        expect($e->getMessage())->toEqual("Unexpected error");
    }
})->with('postUser');

// Datasets
/**
 * Getting a user can use an object or an ID.
 */
dataset('getUser', [
    ['3fd4d839-493e-4485-b2a5-ace527aacff3', '3fd4d839-493e-4485-b2a5-ace527aacff3'],
    [new User('3fd4d839-493e-4485-b2a5-ace527aacff3'), '3fd4d839-493e-4485-b2a5-ace527aacff3']
]);

/**
 * Creating a user can take a Call object or a simple array.
 */
dataset('postUser', function () {
    $raw = [
        'name' => 'demo',
    ];

    $user = new User();
    $user->setName('demo');

    return [
        [clone $user, 'create'],
        [clone $user, 'post'],
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
