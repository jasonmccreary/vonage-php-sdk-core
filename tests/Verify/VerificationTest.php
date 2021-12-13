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



/**
 * Create a basic verification object
 */
beforeEach(function () {
    $this->verification = @new Verification($this->number, $this->brand);
    $this->existing = new Verification('44a5279b27dd4a638d614d265ad57a77');
});

test('existing and new', function () {
    expect(@$this->verification->isDirty())->toBeTrue();
    expect(@$this->existing->isDirty())->toBeFalse();
});

test('construct data as object', function () {
    expect(@$this->verification->getNumber())->toEqual($this->number);
});

/**
 * @throws ClientException
 */
test('construct data as params', function () {
    $params = $this->verification->getRequestData(false);
    expect(@$params['number'])->toEqual($this->number);
    expect(@$params['brand'])->toEqual($this->brand);
});

test('construct data as array', function () {
    expect(@$this->verification['number'])->toEqual($this->number);
    expect(@$this->verification['brand'])->toEqual($this->brand);
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

    expect($params[$param])->toEqual($normal);
    expect(@$verification[$param])->toEqual($normal);
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

    expect($params[$param])->toEqual($normal);
    expect(@$this->verification[$param])->toEqual($normal);
})->with('optionalValues');

/**
 * Test that the request id can be accessed when a verification is created with it, or when a request is created.
 */
test('request id', function () {
    expect(@$this->existing->getRequestId())->toEqual('44a5279b27dd4a638d614d265ad57a77');

    @$this->verification->setResponse(getResponse('search'));

    expect(@$this->verification->getRequestId())->toEqual('44a5279b27dd4a638d614d265ad57a77');
});

/**
 * Verification provides object access to normalized data (dates as DateTime)
 *
 * @throws Exception
 */
test('search params as object', function () {
    @$this->existing->setResponse(getResponse('search'));

    expect(@$this->existing->getAccountId())->toEqual('6cff3913');
    expect(@$this->existing->getNumber())->toEqual('14845551212');
    expect(@$this->existing->getSenderId())->toEqual('verify');
    expect(@$this->existing->getSubmitted())->toEqual(new DateTime("2016-05-15 03:55:05"));
    expect(@$this->existing->getFinalized())->toEqual(null);
    expect(@$this->existing->getFirstEvent())->toEqual(new DateTime("2016-05-15 03:55:05"));
    expect(@$this->existing->getLastEvent())->toEqual(new DateTime("2016-05-15 03:57:12"));
    expect(@$this->existing->getPrice())->toEqual('0.10000000');
    expect(@$this->existing->getCurrency())->toEqual('EUR');
    expect(@$this->existing->getStatus())->toEqual(Verification::FAILED);

    @$checks = $this->existing->getChecks();

    expect($checks)->toBeArray();
    expect($checks)->toHaveCount(3);

    foreach ($checks as $index => $check) {
        expect($check)->toBeInstanceOf(Check::class);
    }

    expect($checks[0]->getCode())->toEqual('123456');
    expect($checks[1]->getCode())->toEqual('1234');
    expect($checks[2]->getCode())->toEqual('1234');
    expect($checks[0]->getDate())->toEqual(new DateTime('2016-05-15 03:58:11'));
    expect($checks[1]->getDate())->toEqual(new DateTime('2016-05-15 03:55:50'));
    expect($checks[2]->getDate())->toEqual(new DateTime('2016-05-15 03:59:18'));
    expect($checks[0]->getStatus())->toEqual(Check::INVALID);
    expect($checks[1]->getStatus())->toEqual(Check::INVALID);
    expect($checks[2]->getStatus())->toEqual(Check::INVALID);
    expect($checks[0]->getIpAddress())->toEqual(null);
    expect($checks[1]->getIpAddress())->toEqual(null);
    expect($checks[2]->getIpAddress())->toEqual('8.8.4.4');
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

    @expect($this->existing->check('4321'))->toBeFalse();
    @expect($this->existing->check('1234'))->toBeTrue();
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

    @expect($this->existing->check('4321'))->toBeFalse();
    @expect($this->existing->check('1234'))->toBeTrue();
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

    expect($unserialized)->toBeInstanceOf(get_class($this->existing));
    expect(@$unserialized->getAccountId())->toEqual(@$this->existing->getAccountId());
    expect(@$unserialized->getStatus())->toEqual(@$this->existing->getStatus());
    expect(@$unserialized->getResponseData())->toEqual(@$this->existing->getResponseData());
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
