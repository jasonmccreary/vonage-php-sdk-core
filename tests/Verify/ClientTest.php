<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use InvalidArgumentException;
use Laminas\Diactoros\Response;
use VonageTest\VonageTestCase;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Client;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\Verify\Client as VerifyClient;
use Vonage\Verify\Request;
use Vonage\Verify\RequestPSD2;
use Vonage\Verify\Verification;
use VonageTest\Psr7AssertionTrait;

uses(VonageTestCase::class);
uses(Psr7AssertionTrait::class);

use function array_unshift;
use function call_user_func_array;
use function fopen;
use function serialize;

/**
 * Create the Message API Client, and mock the Vonage Client
 */
beforeEach(function () {
    $this->vonageClient = $this->prophesize(Client::class);
    $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

    $this->client = new VerifyClient();

    /** @noinspection PhpParamsInspection */
    $this->client->setClient($this->vonageClient->reveal());
});

/**
 *
 * @param $method
 * @param $response
 * @param $construct
 * @param array $args
 */
test('client sets self', function ($method, $response, $construct, $args = []) {
    /** @var mixed $client */
    $client = $this->prophesize(Client::class);
    $client->send(Argument::cetera())->willReturn(getResponse($response));
    $client->getApiUrl()->willReturn('http://api.nexmo.com');

    $this->client->setClient($client->reveal());

    $mock = @$this->getMockBuilder(Verification::class)
        ->setConstructorArgs($construct)
        ->setMethods(['setClient'])
        ->getMock();

    $mock->expects(self::once())->method('setClient')->with($this->client);

    array_unshift($args, $mock);
    @call_user_func_array([$this->client, $method], $args);
})->with('getApiMethods');

test('unserialize accepts object', function () {
    $mock = @$this->getMockBuilder(Verification::class)
        ->setConstructorArgs(['14845551212', 'Test Verify'])
        ->setMethods(['setClient'])
        ->getMock();

    $mock->expects(self::once())->method('setClient')->with($this->client);

    @$this->client->unserialize($mock);
});

/**
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws ServerException
 * @throws ClientExceptionInterface
 */
test('unserialize sets client', function () {
    $verification = @new Verification('14845551212', 'Test Verify');
    @$verification->setResponse(getResponse('start'));

    $string = serialize($verification);
    $object = @$this->client->unserialize($string);

    $this->assertInstanceOf(Verification::class, $object);

    $search = setupClientForSearch('search');
    @$object->sync();

    $this->assertSame($search, @$object->getResponse());
});

test('serialize matches entity', function () {
    $verification = @new Verification('14845551212', 'Test Verify');
    @$verification->setResponse(getResponse('start'));

    $string = serialize($verification);

    $this->assertSame($string, @$this->client->serialize($verification));
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws ServerException
 *
 * @deprecated
 */
test('can start verification with verification object', function () {
    $success = setupClientForStart('start');

    $verification = @new Verification('14845551212', 'Test Verify');
    @$this->client->start($verification);

    $this->assertSame($success, @$verification->getResponse());
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws ServerException
 */
test('can start verification', function () {
    $success = setupClientForStart('start');

    $verification = new Request('14845551212', 'Test Verify');
    $verification = @$this->client->start($verification);

    $this->assertSame($success, @$verification->getResponse());
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws ServerException
 */
test('can start p s d2 verification', function () {
    $this->vonageClient->send(
        Argument::that(
            function (RequestInterface $request) {
                $this->assertRequestJsonBodyContains('number', '14845551212', $request);
                $this->assertRequestJsonBodyContains('payee', 'Test Verify', $request);
                $this->assertRequestJsonBodyContains('amount', '5.25', $request);
                $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/psd2/json', $request);

                return true;
            }
        )
    )->willReturn(getResponse('start'))
        ->shouldBeCalledTimes(1);

    $request = new RequestPSD2('14845551212', 'Test Verify', '5.25');
    $response = @$this->client->requestPSD2($request);

    $this->assertSame('0', $response['status']);
    $this->assertSame('44a5279b27dd4a638d614d265ad57a77', $response['request_id']);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws ServerException
 */
test('can start p s d2 verification with workflow i d', function () {
    $this->vonageClient->send(
        Argument::that(
            function (RequestInterface $request) {
                $this->assertRequestJsonBodyContains('number', '14845551212', $request);
                $this->assertRequestJsonBodyContains('payee', 'Test Verify', $request);
                $this->assertRequestJsonBodyContains('amount', '5.25', $request);
                $this->assertRequestJsonBodyContains('workflow_id', 5, $request);
                $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/psd2/json', $request);

                return true;
            }
        )
    )->willReturn(getResponse('start'))
        ->shouldBeCalledTimes(1);

    $request = new RequestPSD2('14845551212', 'Test Verify', '5.25', 5);
    $response = @$this->client->requestPSD2($request);

    $this->assertSame('0', $response['status']);
    $this->assertSame('44a5279b27dd4a638d614d265ad57a77', $response['request_id']);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws ServerException
 */
test('can start array', function () {
    $response = setupClientForStart('start');

    @$verification = $this->client->start(
        [
            'number' => '14845551212',
            'brand' => 'Test Verify'
        ]
    );

    $this->assertSame($response, @$verification->getResponse());
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws ServerException
 */
test('start throws exception', function () {
    $response = setupClientForStart('start-error');

    try {
        @$this->client->start(
            [
                'number' => '14845551212',
                'brand' => 'Test Verify'
            ]
        );

        self::fail('did not throw exception');
    } catch (Client\Exception\Request $e) {
        $this->assertEquals('2', $e->getCode());
        $this->assertEquals(
            'Your request is incomplete and missing the mandatory parameter: brand',
            $e->getMessage()
        );
        $this->assertSame($response, @$e->getEntity()->getResponse());
    }
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 */
test('start throws server exception', function () {
    $response = setupClientForStart('server-error');

    try {
        @$this->client->start(
            [
                'number' => '14845551212',
                'brand' => 'Test Verify'
            ]
        );

        self::fail('did not throw exception');
    } catch (ServerException $e) {
        $this->assertEquals('5', $e->getCode());
        $this->assertEquals('Server Error', $e->getMessage());
        $this->assertSame($response, @$e->getEntity()->getResponse());
    }
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws ServerException
 */
test('can search verification', function () {
    $response = setupClientForSearch('search');

    $verification = new Verification('44a5279b27dd4a638d614d265ad57a77');
    @$this->client->search($verification);

    $this->assertSame($response, @$verification->getResponse());
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws ServerException
 */
test('can search id', function () {
    $response = setupClientForSearch('search');

    $verification = @$this->client->search('44a5279b27dd4a638d614d265ad57a77');

    $this->assertSame($response, @$verification->getResponse());
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws ServerException
 */
test('search throws exception', function () {
    $response = setupClientForSearch('search-error');

    try {
        @$this->client->search('44a5279b27dd4a638d614d265ad57a77');

        self::fail('did not throw exception');
    } catch (Client\Exception\Request $e) {
        $this->assertEquals('101', $e->getCode());
        $this->assertEquals('No response found', $e->getMessage());
        $this->assertSame($response, @$e->getEntity()->getResponse());
    }
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 */
test('search throws server exception', function () {
    $response = setupClientForSearch('server-error');

    try {
        @$this->client->search('44a5279b27dd4a638d614d265ad57a77');

        self::fail('did not throw exception');
    } catch (ServerException $e) {
        $this->assertEquals('5', $e->getCode());
        $this->assertEquals('Server Error', $e->getMessage());
        $this->assertSame($response, @$e->getEntity()->getResponse());
    }
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws ServerException
 */
test('search replaces response', function () {
    $old = getResponse('start');
    $verification = @new Verification('14845551212', 'Test Verify');
    @$verification->setResponse($old);

    $response = setupClientForSearch('search');
    @$this->client->search($verification);

    $this->assertSame($response, @$verification->getResponse());
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws ServerException
 */
test('can cancel verification', function () {
    $response = setupClientForControl('cancel', 'cancel');

    $verification = new Verification('44a5279b27dd4a638d614d265ad57a77');
    $result = @$this->client->cancel($verification);

    $this->assertSame($verification, $result);
    $this->assertSame($response, @$verification->getResponse());
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws ServerException
 */
test('can cancel id', function () {
    $response = setupClientForControl('cancel', 'cancel');

    $verification = @$this->client->cancel('44a5279b27dd4a638d614d265ad57a77');

    $this->assertSame($response, @$verification->getResponse());
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws ServerException
 */
test('cancel throws client exception', function () {
    $response = setupClientForControl('cancel-error', 'cancel');

    try {
        @$this->client->cancel('44a5279b27dd4a638d614d265ad57a77');

        self::fail('did not throw exception');
    } catch (Client\Exception\Request $e) {
        $this->assertEquals('19', $e->getCode());
        $this->assertEquals(
            "Verification request  ['c1878c7451f94c1992d52797df57658e'] can't " .
            "be cancelled now. Too many attempts to re-deliver have already been made.",
            $e->getMessage()
        );
        $this->assertSame($response, @$e->getEntity()->getResponse());
    }
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 */
test('cancel throws server exception', function () {
    $response = setupClientForControl('server-error', 'cancel');

    try {
        @$this->client->cancel('44a5279b27dd4a638d614d265ad57a77');

        self::fail('did not throw exception');
    } catch (ServerException $e) {
        $this->assertEquals('5', $e->getCode());
        $this->assertEquals('Server Error', $e->getMessage());
        $this->assertSame($response, @$e->getEntity()->getResponse());
    }
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws ServerException
 */
test('can trigger id', function () {
    $response = setupClientForControl('trigger', 'trigger_next_event');

    $verification = @$this->client->trigger('44a5279b27dd4a638d614d265ad57a77');

    $this->assertSame($response, @$verification->getResponse());
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws ServerException
 */
test('can trigger verification', function () {
    $response = setupClientForControl('trigger', 'trigger_next_event');

    $verification = new Verification('44a5279b27dd4a638d614d265ad57a77');
    $result = @$this->client->trigger($verification);

    $this->assertSame($verification, $result);
    $this->assertSame($response, @$verification->getResponse());
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws ServerException
 */
test('trigger throws client exception', function () {
    $response = setupClientForControl('trigger-error', 'trigger_next_event');

    try {
        @$this->client->trigger('44a5279b27dd4a638d614d265ad57a77');

        self::fail('did not throw exception');
    } catch (Client\Exception\Request $e) {
        $this->assertEquals('6', $e->getCode());
        $this->assertEquals(
            "The requestId '44a5279b27dd4a638d614d265ad57a77' does " .
            "not exist or its no longer active.",
            $e->getMessage()
        );
        $this->assertSame($response, @$e->getEntity()->getResponse());
    }
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 */
test('trigger throws server exception', function () {
    $response = setupClientForControl('server-error', 'trigger_next_event');

    try {
        @$this->client->trigger('44a5279b27dd4a638d614d265ad57a77');

        self::fail('did not throw exception');
    } catch (ServerException $e) {
        $this->assertEquals('5', $e->getCode());
        $this->assertEquals('Server Error', $e->getMessage());
        $this->assertSame($response, @$e->getEntity()->getResponse());
    }
});

/**
 *
 * @param $method
 * @param $cmd
 */
test('control not replace response', function ($method, $cmd) {
    $response = getResponse('search');
    $verification = new Verification('44a5279b27dd4a638d614d265ad57a77');
    @$verification->setResponse($response);

    setupClientForControl($method, $cmd);
    @$this->client->$method($verification);

    $this->assertSame($response, @$verification->getResponse());
})->with('getControlCommands');

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws ServerException
 */
test('can check verification', function () {
    $response = setupClientForCheck('check', '1234');
    $verification = new Verification('44a5279b27dd4a638d614d265ad57a77');

    @$this->client->check($verification, '1234');

    $this->assertSame($response, @$verification->getResponse());
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws ServerException
 */
test('can check id', function () {
    $response = setupClientForCheck('check', '1234');
    $verification = @$this->client->check('44a5279b27dd4a638d614d265ad57a77', '1234');

    $this->assertSame($response, @$verification->getResponse());
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws ServerException
 */
test('check throws client exception', function () {
    $response = setupClientForCheck('check-error', '1234');

    try {
        @$this->client->check('44a5279b27dd4a638d614d265ad57a77', '1234');

        self::fail('did not throw exception');
    } catch (Client\Exception\Request $e) {
        $this->assertEquals('16', $e->getCode());
        $this->assertEquals('The code provided does not match the expected value', $e->getMessage());
        $this->assertSame($response, @$e->getEntity()->getResponse());
    }
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 */
test('check throws server exception', function () {
    $response = setupClientForCheck('server-error', '1234');

    try {
        @$this->client->check('44a5279b27dd4a638d614d265ad57a77', '1234');

        self::fail('did not throw exception');
    } catch (ServerException $e) {
        $this->assertEquals('5', $e->getCode());
        $this->assertEquals('Server Error', $e->getMessage());
        $this->assertSame($response, @$e->getEntity()->getResponse());
    }
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws ServerException
 */
test('check not replace response', function () {
    $old = getResponse('search');
    $verification = new Verification('44a5279b27dd4a638d614d265ad57a77');
    @$verification->setResponse($old);

    setupClientForCheck('check', '1234');

    @$this->client->check($verification, '1234');
    $this->assertSame($old, @$verification->getResponse());
});

// Datasets
/**
 * @return array[]
 */
dataset('getApiMethods', [
    ['start', 'start', ['14845551212', 'Test Verify']],
    ['cancel', 'cancel', ['44a5279b27dd4a638d614d265ad57a77']],
    ['trigger', 'trigger', ['44a5279b27dd4a638d614d265ad57a77']],
    ['search', 'search', ['44a5279b27dd4a638d614d265ad57a77']],
    ['check', 'check', ['44a5279b27dd4a638d614d265ad57a77'], ['1234']],
]);

/**
 * @return string[]
 */
dataset('getControlCommands', [
    ['cancel', 'cancel'],
    ['trigger', 'trigger_next_event']
]);

// Helpers
/**
     * @param $response
     */
function setupClientForStart($response): Response
{
    $response = test()->getResponse($response);
    test()->vonageClient->send(
        Argument::that(
            function (RequestInterface $request) {
                test()->assertRequestJsonBodyContains('number', '14845551212', $request);
                test()->assertRequestJsonBodyContains('brand', 'Test Verify', $request);
                test()->assertRequestMatchesUrl('https://api.nexmo.com/verify/json', $request);

                return true;
            }
        )
    )->willReturn($response)
        ->shouldBeCalledTimes(1);

    return $response;
}

/**
     * @param $response
     */
function setupClientForSearch($response): Response
{
    $response = test()->getResponse($response);
    test()->vonageClient->send(
        Argument::that(
            function (RequestInterface $request) {
                test()->assertRequestJsonBodyContains('request_id', '44a5279b27dd4a638d614d265ad57a77', $request);
                test()->assertRequestMatchesUrl('https://api.nexmo.com/verify/search/json', $request);

                return true;
            }
        )
    )->willReturn($response)
        ->shouldBeCalledTimes(1);

    return $response;
}

/**
     * @param $response
     * @param $cmd
     */
function setupClientForControl($response, $cmd): Response
{
    $response = test()->getResponse($response);
    test()->vonageClient->send(
        Argument::that(
            function (RequestInterface $request) use ($cmd) {
                test()->assertRequestJsonBodyContains('request_id', '44a5279b27dd4a638d614d265ad57a77', $request);
                test()->assertRequestJsonBodyContains('cmd', $cmd, $request);
                test()->assertRequestMatchesUrl('https://api.nexmo.com/verify/control/json', $request);

                return true;
            }
        )
    )->willReturn($response)
        ->shouldBeCalledTimes(1);

    return $response;
}

/**
     * @param $response
     * @param $code
     */
function setupClientForCheck($response, $code, ?string $ip = null): Response
{
    $response = test()->getResponse($response);

    test()->vonageClient->send(
        Argument::that(
            function (RequestInterface $request) use ($code, $ip) {
                test()->assertRequestJsonBodyContains('request_id', '44a5279b27dd4a638d614d265ad57a77', $request);
                test()->assertRequestJsonBodyContains('code', $code, $request);

                if ($ip) {
                    test()->assertRequestJsonBodyContains('ip_address', $ip, $request);
                }

                test()->assertRequestMatchesUrl('https://api.nexmo.com/verify/check/json', $request);
                return true;
            }
        )
    )->willReturn($response)
        ->shouldBeCalledTimes(1);

    return $response;
}

/**
     * Get the API response we'd expect for a call to the API. Verify API currently returns 200 all the time, so only
     * change between success / fail is body of the message.
     */
function getResponse(string $type = 'success'): Response
{
    return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'));
}
