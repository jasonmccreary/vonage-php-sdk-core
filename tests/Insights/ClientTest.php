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
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\Insights\Advanced;
use Vonage\Insights\AdvancedCnam;
use Vonage\Insights\Basic;
use Vonage\Insights\Client as InsightsClient;
use Vonage\Insights\Standard;
use Vonage\Insights\StandardCnam;
use VonageTest\Psr7AssertionTrait;

uses(VonageTestCase::class);
uses(Psr7AssertionTrait::class);


beforeEach(function () {
    $this->vonageClient = $this->prophesize(Client::class);
    $this->vonageClient->getApiUrl()->willReturn('http://api.nexmo.com');

    $this->insightsClient = new InsightsClient();
    /** @noinspection PhpParamsInspection */
    $this->insightsClient->setClient($this->vonageClient->reveal());
});

test('standard cnam', function () {
    checkInsightsRequestCnam('standardCnam', '/ni/standard/json', StandardCnam::class);
});

test('advanced cnam', function () {
    checkInsightsRequestCnam('advancedCnam', '/ni/advanced/json', AdvancedCnam::class);
});

/**
 * @throws Client\Exception\Exception
 * @throws RequestException
 * @throws ServerException
 * @throws ClientExceptionInterface
 */
test('advanced async', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/ni/advanced/async/json');
        expect($request->getUri()->getHost())->toEqual('api.nexmo.com');
        expect($request->getMethod())->toEqual('GET');
        $this->assertRequestQueryContains("number", "14155550100", $request);
        $this->assertRequestQueryContains("callback", "example.com/hook", $request);

        return true;
    }))->willReturn(getResponse('advancedAsync'));

    $this->insightsClient->advancedAsync('14155550100', 'example.com/hook');
});

test('basic', function () {
    checkInsightsRequest('basic', '/ni/basic/json', Basic::class);
});

test('standard', function () {
    checkInsightsRequest('standard', '/ni/standard/json', Standard::class);
});

test('advanced', function () {
    checkInsightsRequest('advanced', '/ni/advanced/json', Advanced::class);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws RequestException
 * @throws ServerException
 */
test('error', function () {
    $this->expectException(RequestException::class);

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        return true;
    }))->willReturn(getResponse('error'));

    $this->insightsClient->basic('14155550100');
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws RequestException
 * @throws ServerException
 */
test('client exception', function () {
    $this->expectException(RequestException::class);

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        return true;
    }))->willReturn(getResponse('error', 401));

    $this->insightsClient->basic('14155550100');
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws RequestException
 * @throws ServerException
 */
test('server exception', function () {
    $this->expectException(ServerException::class);

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        return true;
    }))->willReturn(getResponse('error', 502));

    $this->insightsClient->basic('14155550100');
});

// Helpers
function checkInsightsRequest($methodToCall, $expectedPath, $expectedClass): void
{
    test()->vonageClient->send(Argument::that(function (RequestInterface $request) use ($expectedPath) {
        expect($request->getUri()->getPath())->toEqual($expectedPath);
        expect($request->getUri()->getHost())->toEqual('api.nexmo.com');
        expect($request->getMethod())->toEqual('GET');

        test()->assertRequestQueryContains("number", "14155550100", $request);
        return true;
    }))->willReturn(test()->getResponse($methodToCall));

    $insightsStandard = @test()->insightsClient->$methodToCall('14155550100');
    expect($insightsStandard)->toBeInstanceOf($expectedClass);
    expect($insightsStandard->getNationalFormatNumber())->toEqual('(415) 555-0100');
}

function checkInsightsRequestCnam($methodToCall, $expectedPath, $expectedClass): void
{
    test()->vonageClient->send(Argument::that(function (RequestInterface $request) use ($expectedPath) {
        expect($request->getUri()->getPath())->toEqual($expectedPath);
        expect($request->getUri()->getHost())->toEqual('api.nexmo.com');
        expect($request->getMethod())->toEqual('GET');

        test()->assertRequestQueryContains("number", "14155550100", $request);
        test()->assertRequestQueryContains("cnam", "true", $request);
        return true;
    }))->willReturn(test()->getResponse($methodToCall));

    $insightsStandard = @test()->insightsClient->$methodToCall('14155550100');
    expect($insightsStandard)->toBeInstanceOf($expectedClass);
    expect($insightsStandard->getNationalFormatNumber())->toEqual('(415) 555-0100');
}

/**
     * Get the API response we'd expect for a call to the API.
     */
function getResponse(string $type = 'success', int $status = 200): Response
{
    return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
}
