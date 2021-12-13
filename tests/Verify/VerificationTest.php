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
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\Verify\Check;
use Vonage\Verify\Client as VerifyClient;
use Vonage\Verify\Verification;

uses(VonageTestCase::class);


/**
 * Create a basic verification object
 */
beforeEach(function () {
    $this->verification = @new Verification($this->number, $this->brand);
    $this->existing = new Verification('44a5279b27dd4a638d614d265ad57a77');
});

test('existing and new', function () {
    $this->assertTrue(@$this->verification->isDirty());
    $this->assertFalse(@$this->existing->isDirty());
});

test('construct data as object', function () {
    $this->assertEquals($this->number, @$this->verification->getNumber());
});

/**
 * @throws ClientException
 */
test('construct data as params', function () {
    $params = $this->verification->getRequestData(false);
    $this->assertEquals($this->number, @$params['number']);
    $this->assertEquals($this->brand, @$params['brand']);
});

test('construct data as array', function () {
    $this->assertEquals($this->number, @$this->verification['number']);
    $this->assertEquals($this->brand, @$this->verification['brand']);
});

/**
 *
 * @param $value
 * @param $setter
 * @param $param
 * @param null $normal
 *
 * @throws ClientException
 * @noinspection PhpUnusedParameterInspection
 */
test('can construct optional values', function ($value, $setter, $param, $normal = null) {
    if (is_null($normal)) {
        $normal = $value;
    }

    $verification = @new Verification('14845552121', 'brand', [
        $param => $normal
    ]);

    $params = $verification->getRequestData(false);

    $this->assertEquals($normal, $params[$param]);
    $this->assertEquals($normal, @$verification[$param]);
})->with('optionalValues');

/**
 *
 * @param $value
 * @param $setter
 * @param $param
 * @param null $normal
 *
 * @throws ClientException
 */
test('can set optional values', function ($value, $setter, $param, $normal = null) {
    if (is_null($normal)) {
        $normal = $value;
    }

    $this->verification->$setter($value);
    $params = @$this->verification->getRequestData(false);

    $this->assertEquals($normal, $params[$param]);
    $this->assertEquals($normal, @$this->verification[$param]);
})->with('optionalValues');

/**
 * Test that the request id can be accessed when a verification is created with it, or when a request is created.
 */
test('request id', function () {
    $this->assertEquals('44a5279b27dd4a638d614d265ad57a77', @$this->existing->getRequestId());

    @$this->verification->setResponse(getResponse('search'));

    $this->assertEquals('44a5279b27dd4a638d614d265ad57a77', @$this->verification->getRequestId());
});

/**
 * Verification provides object access to normalized data (dates as DateTime)
 *
 * @throws Exception
 */
test('search params as object', function () {
    @$this->existing->setResponse(getResponse('search'));

    $this->assertEquals('6cff3913', @$this->existing->getAccountId());
    $this->assertEquals('14845551212', @$this->existing->getNumber());
    $this->assertEquals('verify', @$this->existing->getSenderId());
    $this->assertEquals(new DateTime("2016-05-15 03:55:05"), @$this->existing->getSubmitted());
    $this->assertEquals(null, @$this->existing->getFinalized());
    $this->assertEquals(new DateTime("2016-05-15 03:55:05"), @$this->existing->getFirstEvent());
    $this->assertEquals(new DateTime("2016-05-15 03:57:12"), @$this->existing->getLastEvent());
    $this->assertEquals('0.10000000', @$this->existing->getPrice());
    $this->assertEquals('EUR', @$this->existing->getCurrency());
    $this->assertEquals(Verification::FAILED, @$this->existing->getStatus());

    @$checks = $this->existing->getChecks();

    $this->assertIsArray($checks);
    $this->assertCount(3, $checks);

    foreach ($checks as $index => $check) {
        $this->assertInstanceOf(Check::class, $check);
    }

    $this->assertEquals('123456', $checks[0]->getCode());
    $this->assertEquals('1234', $checks[1]->getCode());
    $this->assertEquals('1234', $checks[2]->getCode());
    $this->assertEquals(new DateTime('2016-05-15 03:58:11'), $checks[0]->getDate());
    $this->assertEquals(new DateTime('2016-05-15 03:55:50'), $checks[1]->getDate());
    $this->assertEquals(new DateTime('2016-05-15 03:59:18'), $checks[2]->getDate());
    $this->assertEquals(Check::INVALID, $checks[0]->getStatus());
    $this->assertEquals(Check::INVALID, $checks[1]->getStatus());
    $this->assertEquals(Check::INVALID, $checks[2]->getStatus());
    $this->assertEquals(null, $checks[0]->getIpAddress());
    $this->assertEquals(null, $checks[1]->getIpAddress());
    $this->assertEquals('8.8.4.4', $checks[2]->getIpAddress());
});

/**
 * Verification provides simple access to raw data when available.
 *
 *
 * @param $type
 *
 * @throws Exception
 */
test('response data as array', function ($type) {
    @$this->existing->setResponse(getResponse($type));
    $json = $this->existing->getResponseData();

    foreach ($json as $key => $value) {
        $this->assertEquals($value, @$this->existing[$key], "Could not access `$key` as a property.");
    }
})->with('dataResponses');

/**
 *
 * @param $method
 * @param $proxy
 * @param null $code
 * @param null $ip
 */
test('methods proxy client', function ($method, $proxy, $code = null, $ip = null) {
    /** @var mixed $client */
    $client = $this->prophesize(VerifyClient::class);

    if (!is_null($ip)) {
        $prediction = $client->$proxy($this->existing, $code, $ip);
    } elseif (!is_null($code)) {
        $prediction = $client->$proxy($this->existing, $code, Argument::cetera());
    } else {
        $prediction = $client->$proxy($this->existing);
    }

    $prediction->shouldBeCalled()->willReturn($this->existing);

    @$this->existing->setClient($client->reveal());

    if (!is_null($ip)) {
        @$this->existing->$method($code, $ip);
    } elseif (!is_null($code)) {
        @$this->existing->$method($code);
    } else {
        @$this->existing->$method();
    }
})->with('getClientProxyMethods');

/**
 * @throws ClientException
 * @throws RequestException
 * @throws ClientExceptionInterface
 * @throws ServerException
 */
test('check returns bool for invalid code', function () {
    /** @var mixed $client */
    $client = $this->prophesize(VerifyClient::class);
    $client->check($this->existing, '1234', Argument::cetera())->willReturn($this->existing);
    $client->check($this->existing, '4321', Argument::cetera())->willThrow(new RequestException('dummy', 16));

    @$this->existing->setClient($client->reveal());

    @$this->assertFalse($this->existing->check('4321'));
    @$this->assertTrue($this->existing->check('1234'));
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException
 * @throws RequestException
 * @throws ServerException
 */
test('check returns bool for too many attempts', function () {
    /** @var mixed $client */
    $client = $this->prophesize(VerifyClient::class);
    $client->check($this->existing, '1234', Argument::cetera())->willReturn($this->existing);
    $client->check($this->existing, '4321', Argument::cetera())->willThrow(new RequestException('dummy', 17));

    @$this->existing->setClient($client->reveal());

    @$this->assertFalse($this->existing->check('4321'));
    @$this->assertTrue($this->existing->check('1234'));
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException
 * @throws RequestException
 * @throws ServerException
 */
test('exception for check fail', function () {
    /** @var mixed $client */
    $client = $this->prophesize(VerifyClient::class);
    $client->check($this->existing, '1234', Argument::cetera())->willReturn($this->existing);
    $client->check($this->existing, '4321', Argument::cetera())->willThrow(new RequestException('dummy', 6));

    @$this->existing->setClient($client->reveal());

    $this->expectException(RequestException::class);
    @$this->existing->check('4321');
});

/**
 *
 * @param $response
 *
 * @throws Exception
 */
test('serialize', function ($response) {
    @$this->existing->setResponse($response);
    @$this->existing->getResponse()->getBody()->rewind();
    @$this->existing->getResponse()->getBody()->getContents();

    $serialized = serialize($this->existing);
    $unserialized = unserialize($serialized, [Verification::class]);

    $this->assertInstanceOf(get_class($this->existing), $unserialized);
    $this->assertEquals(@$this->existing->getAccountId(), @$unserialized->getAccountId());
    $this->assertEquals(@$this->existing->getStatus(), @$unserialized->getStatus());
    $this->assertEquals(@$this->existing->getResponseData(), @$unserialized->getResponseData());
})->with('getSerializeResponses');

/**
 *
 * @param $method
 * @param $proxy
 * @param null $code
 * @param null $ip
 * @noinspection PhpUnusedParameterInspection
 */
test('missing client exception', function ($method, $proxy, $code = null, $ip = null) {
    $this->expectException('RuntimeException');

    if (!is_null($ip)) {
        @$this->existing->$method($code, $ip);
    } elseif (!is_null($code)) {
        @$this->existing->$method($code);
    } else {
        @$this->existing->$method();
    }
})->with('getClientProxyMethods');

// Datasets
/**
 * @return string[]
 */
dataset('optionalValues', [
    ['us', 'setCountry', 'country'],
    ['16105551212', 'setSenderId', 'sender_id'],
    ['6', 'setCodeLength', 'code_length'],
    ['en-us', 'setLanguage', 'lg'],
    ['landline', 'setRequireType', 'require_type'],
    ['400', 'setPinExpiry', 'pin_expiry'],
    ['200', 'setWaitTime', 'next_event_wait'],
]);

/**
 * @return string[]
 */
dataset('dataResponses', [
    ['search'],
    ['start']
]);

/**
 * @return Response[]
 */
dataset('getSerializeResponses', [
    [getResponse('search')],
    [getResponse('start')],
]);

/**
 * @return string[]
 */
dataset('getClientProxyMethods', [
    ['cancel', 'cancel'],
    ['trigger', 'trigger'],
    ['sync', 'search'],
    ['check', 'check', '1234'],
    ['check', 'check', '1234', '192.168.1.1'],
]);

// Helpers
/**
     * Get the API response we'd expect for a call to the API. Verify API currently returns 200 all the time, so only
     * change between success / fail is body of the message.
     */
function getResponse(string $type = 'success'): Response
{
    return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'));
}
