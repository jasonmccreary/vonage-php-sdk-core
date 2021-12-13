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
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Account\Client as AccountClient;
use Vonage\Account\PrefixPrice;
use Vonage\Client;
use Vonage\Client\Exception as ClientException;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\Client\Exception\Validation as ValidationException;
use Vonage\InvalidResponseException;
use Vonage\Network;
use VonageTest\Psr7AssertionTrait;

uses(VonageTestCase::class);
uses(Psr7AssertionTrait::class);


beforeEach(function () {
    $this->vonageClient = $this->prophesize(Client::class);
    $this->vonageClient->getRestUrl()->willReturn('https://rest.nexmo.com');
    $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

    $this->accountClient = new AccountClient();
    /** @noinspection PhpParamsInspection */
    $this->accountClient->setClient($this->vonageClient->reveal());
});

/**
 * @throws ClientException\Exception
 * @throws ClientExceptionInterface
 */
test('top up', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/account/top-up');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('POST');
        $this->assertRequestFormBodyContains('trx', 'ABC123', $request);

        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('empty'));

    $this->accountClient->topUp('ABC123');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 */
test('top up fails with4xx', function () {
    $this->expectException(RequestException::class);
    $this->expectExceptionMessage('authentication failed');

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/account/top-up');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('POST');
        $this->assertRequestFormBodyContains('trx', 'ABC123', $request);

        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('auth-failure', 401));

    $this->accountClient->topUp('ABC123');
});

/**
 * Handle when a proper error is returned from the top-up API
 * While this client library is building the response correctly, we need to
 * simulate a non-200 response
 *
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 */
test('top up fails due to bad request', function () {
    $this->expectException(RequestException::class);
    $this->expectExceptionMessage('Bad Request');

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/account/top-up');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('POST');
        $this->assertRequestFormBodyContains('trx', 'ABC123', $request);

        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('top-up-bad-request', 400));

    $this->accountClient->topUp('ABC123');
});

/**
 * Handle when a proper error is returned from the top-up API
 * While this client library is building the response correctly, we need to
 * simulate a non-200 response
 *
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 */
test('top up fails due to bad request returns500', function () {
    $this->expectException(ServerException::class);
    $this->expectExceptionMessage('Bad Request');

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/account/top-up');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('POST');
        $this->assertRequestFormBodyContains('trx', 'ABC123', $request);

        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('top-up-bad-request', 500));

    $this->accountClient->topUp('ABC123');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ServerException
 */
test('get balance', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/account/get-balance');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('GET');

        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('get-balance'));

    $this->accountClient->getBalance();
});

/**
 * Handle if the balance API returns a completely empty body
 * Not sure how this would happen in real life, but making sure we work
 *
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ServerException
 *
 * @author Chris Tankersley <chris.tankersley@vonage.com>
 */
test('get balance with no results', function () {
    $this->expectException(ServerException::class);
    $this->expectExceptionMessage('No results found');

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/account/get-balance');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('GET');

        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('empty'));

    $this->accountClient->getBalance();
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ServerException
 */
test('get config', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/account/settings');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('POST');

        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('get-config'));

    $this->accountClient->getConfig();
});

/**
 * Handle if the balance API returns a completely empty body
 * Not sure how this would happen in real life, but making sure we work
 *
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ServerException
 *
 * @author Chris Tankersley <chris.tankersley@vonage.com>
 */
test('get config blank response', function () {
    $this->expectException(ServerException::class);
    $this->expectExceptionMessage('Response was empty');

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/account/settings');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('POST');

        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('empty'));

    $this->accountClient->getConfig();
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ServerException
 */
test('update config', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/account/settings');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('POST');
        $this->assertRequestFormBodyContains('moCallBackUrl', 'https://example.com/other', $request);

        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('get-config'));

    $this->accountClient->updateConfig([
        "sms_callback_url" => "https://example.com/other",
        "dr_callback_url" => "https://example.com/receipt",
    ]);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ServerException
 */
test('update config throws non200', function () {
    $this->expectException(RequestException::class);
    $this->expectExceptionMessage('authentication failed');

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/account/settings');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('POST');
        $this->assertRequestFormBodyContains('moCallBackUrl', 'https://example.com/other', $request);

        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('auth-failure', 401));

    $this->accountClient->updateConfig(["sms_callback_url" => "https://example.com/other"]);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ServerException
 */
test('update config returns blank response', function () {
    $this->expectException(ServerException::class);
    $this->expectExceptionMessage('Response was empty');

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/account/settings');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('POST');
        $this->assertRequestFormBodyContains('moCallBackUrl', 'https://example.com/other', $request);

        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('empty', 200));

    $this->accountClient->updateConfig(["sms_callback_url" => "https://example.com/other"]);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws RequestException
 * @throws ServerException
 */
test('get sms pricing', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/account/get-pricing/outbound/sms');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('GET');
        $this->assertRequestQueryContains('country', 'US', $request);

        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('smsprice-us'));

    $smsPrice = $this->accountClient->getSmsPrice('US');

    expect(@$smsPrice['networks']['311310'])->toBeInstanceOf(Network::class);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws RequestException
 * @throws ServerException
 */
test('get sms pricing returns empty set', function () {
    $this->expectException(ServerException::class);
    $this->expectExceptionMessage('No results found');

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/account/get-pricing/outbound/sms');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('GET');
        $this->assertRequestQueryContains('country', 'XX', $request);

        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('empty'));

    $this->accountClient->getSmsPrice('XX');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws RequestException
 * @throws ServerException
 */
test('get voice pricing', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/account/get-pricing/outbound/voice');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('GET');
        $this->assertRequestQueryContains('country', 'US', $request);

        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('voiceprice-us'));

    $voicePrice = $this->accountClient->getVoicePrice('US');

    expect(@$voicePrice['networks']['311310'])->toBeInstanceOf(Network::class);
});

test('get prefix pricing', function () {
    $first = getResponse('prefix-pricing');
    $noResults = getResponse('prefix-pricing-no-results');

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        static $hasRun = false;

        expect($request->getUri()->getPath())->toEqual('/account/get-prefix-pricing/outbound');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('GET');
        $this->assertRequestQueryContains('prefix', '263', $request);

        if ($hasRun) {
            $this->assertRequestQueryContains('page_index', '2', $request);
        }

        $hasRun = true;
        return true;
    }))->shouldBeCalledTimes(2)->willReturn($first, $noResults);

    $prefixPrice = $this->accountClient->getPrefixPricing('263');
    expect(@$prefixPrice[0])->toBeInstanceOf(PrefixPrice::class);
    expect(@$prefixPrice[0]['networks']['64804'])->toBeInstanceOf(Network::class);
});

test('get prefix pricing no results', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/account/get-prefix-pricing/outbound');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('GET');
        $this->assertRequestQueryContains('prefix', '263', $request);

        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('prefix-pricing-no-results'));

    $prefixPrice = $this->accountClient->getPrefixPricing('263');
    expect($prefixPrice)->toBeEmpty();
});

test('get prefix pricing generates4xx error', function () {
    $this->expectException(RequestException::class);
    $this->expectExceptionMessage('authentication failed');

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/account/get-prefix-pricing/outbound');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('GET');
        $this->assertRequestQueryContains('prefix', '263', $request);

        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('auth-failure', 401));

    $this->accountClient->getPrefixPricing('263');
});

test('get prefix pricing generates5xx error', function () {
    $this->expectException(ServerException::class);
    $this->expectExceptionMessage('unknown error');

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/account/get-prefix-pricing/outbound');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('GET');
        $this->assertRequestQueryContains('prefix', '263', $request);

        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('prefix-pricing-server-failure', 500));

    $this->accountClient->getPrefixPricing('263');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws InvalidResponseException
 */
test('list secrets', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/accounts/abcd1234/secrets');
        expect($request->getUri()->getHost())->toEqual('api.nexmo.com');
        expect($request->getMethod())->toEqual('GET');
        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('secret-management/list'));

    @$this->accountClient->listSecrets('abcd1234');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws InvalidResponseException
 */
test('list secrets server error', function () {
    $this->expectException(ClientException\Server::class);

    $this->vonageClient->send(
        Argument::any()
    )->willReturn(getGenericResponse('500', 500));

    @$this->accountClient->listSecrets('abcd1234');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws InvalidResponseException
 */
test('list secrets request error', function () {
    $this->expectException(ClientException\Request::class);

    $this->vonageClient->send(
        Argument::any()
    )->willReturn(getGenericResponse('401', 401));

    @$this->accountClient->listSecrets('abcd1234');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws InvalidResponseException
 */
test('get secret', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        $this->assertEquals(
            '/accounts/abcd1234/secrets/ad6dc56f-07b5-46e1-a527-85530e625800',
            $request->getUri()->getPath()
        );
        expect($request->getUri()->getHost())->toEqual('api.nexmo.com');
        expect($request->getMethod())->toEqual('GET');
        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('secret-management/get'));

    @$this->accountClient->getSecret('abcd1234', 'ad6dc56f-07b5-46e1-a527-85530e625800');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws InvalidResponseException
 */
test('get secrets server error', function () {
    $this->expectException(ClientException\Server::class);

    $this->vonageClient->send(
        Argument::any()
    )->willReturn(getGenericResponse('500', 500));

    @$this->accountClient->getSecret('abcd1234', 'ad6dc56f-07b5-46e1-a527-85530e625800');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws InvalidResponseException
 */
test('get secrets request error', function () {
    $this->expectException(ClientException\Request::class);

    $this->vonageClient->send(
        Argument::any()
    )->willReturn(getGenericResponse('401', 401));

    @$this->accountClient->getSecret('abcd1234', 'ad6dc56f-07b5-46e1-a527-85530e625800');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws InvalidResponseException
 * @throws RequestException
 * @throws ValidationException
 */
test('create secret', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/accounts/abcd1234/secrets');
        expect($request->getUri()->getHost())->toEqual('api.nexmo.com');
        expect($request->getMethod())->toEqual('POST');
        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('secret-management/create'));

    @$this->accountClient->createSecret('abcd1234', 'example-4PI-secret');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws InvalidResponseException
 * @throws RequestException
 * @throws ValidationException
 */
test('create secrets server error', function () {
    $this->expectException(ClientException\Server::class);

    $this->vonageClient->send(
        Argument::any()
    )->willReturn(getGenericResponse('500', 500));

    @$this->accountClient->createSecret('abcd1234', 'example-4PI-secret');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws InvalidResponseException
 * @throws RequestException
 * @throws ValidationException
 */
test('create secrets request error', function () {
    $this->expectException(ClientException\Request::class);

    $this->vonageClient->send(Argument::any())->willReturn(getGenericResponse('401', 401));

    @$this->accountClient->createSecret('abcd1234', 'example-4PI-secret');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws InvalidResponseException
 * @throws RequestException
 */
test('create secrets validation error', function () {
    try {
        $this->vonageClient->send(Argument::any())
            ->willReturn(getResponse('secret-management/create-validation', 400));
        @$this->accountClient->createSecret('abcd1234', 'example-4PI-secret');
    } catch (ValidationException $e) {
        $this->assertEquals(
            'Bad Request: The request failed due to validation errors. ' .
                'See https://developer.nexmo.com/api-errors/account/secret-management#validation ' .
                'for more information',
            $e->getMessage()
        );
        $this->assertEquals(
            [
                [
                    'name' => 'secret',
                    'reason' => 'Does not meet complexity requirements'
                ]
            ],
            $e->getValidationErrors()
        );
    }
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 */
test('delete secret', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        $this->assertEquals(
            '/accounts/abcd1234/secrets/ad6dc56f-07b5-46e1-a527-85530e625800',
            $request->getUri()->getPath()
        );
        expect($request->getUri()->getHost())->toEqual('api.nexmo.com');
        expect($request->getMethod())->toEqual('DELETE');
        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('secret-management/delete'));

    @$this->accountClient->deleteSecret('abcd1234', 'ad6dc56f-07b5-46e1-a527-85530e625800');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 */
test('delete secrets server error', function () {
    $this->expectException(ClientException\Server::class);
    $this->vonageClient->send(Argument::any())->willReturn(getGenericResponse('500', 500));
    @$this->accountClient->deleteSecret('abcd1234', 'ad6dc56f-07b5-46e1-a527-85530e625800');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 */
test('delete secrets request error', function () {
    $this->expectException(ClientException\Request::class);
    $this->vonageClient->send(Argument::any())->willReturn(getGenericResponse('401', 401));
    @$this->accountClient->deleteSecret('abcd1234', 'ad6dc56f-07b5-46e1-a527-85530e625800');
});

// Helpers
/**
     * Get the API response we'd expect for a call to the API.
     */
function getResponse(string $type = 'success', int $status = 200): Response
{
    return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
}

function getGenericResponse(string $type = 'success', int $status = 200): Response
{
    return new Response(fopen(__DIR__ . '/../responses/general/' . $type . '.json', 'rb'), $status);
}
