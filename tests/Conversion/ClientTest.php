<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Laminas\Diactoros\Response;
use PHPUnit\Framework\MockObject\MockObject;
use VonageTest\VonageTestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\Conversion\Client as ConversionClient;
use VonageTest\Psr7AssertionTrait;



beforeEach(function () {
    $this->vonageClient = $this->getMockBuilder('Vonage\Client')
        ->disableOriginalConstructor()
        ->setMethods(['send', 'getApiUrl'])
        ->getMock();
    $this->vonageClient->method('getApiUrl')->willReturn('https://api.nexmo.com');

    $this->apiResource = new APIResource();
    $this->apiResource
        ->setBaseUri('/conversions/')
        ->setClient($this->vonageClient);

    $this->conversionClient = new ConversionClient($this->apiResource);
    $this->conversionClient->setClient($this->vonageClient);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException
 * @throws RequestException
 * @throws ServerException
 */
test('sms with timestamp', function () {
    $this->vonageClient->method('send')->willReturnCallback(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/conversions/sms');
        expect($request->getUri()->getHost())->toEqual('api.nexmo.com');
        expect($request->getMethod())->toEqual('POST');
        $this->assertRequestQueryContains('message-id', 'ABC123', $request);
        $this->assertRequestQueryContains('delivered', '1', $request);
        $this->assertRequestQueryContains('timestamp', '123456', $request);

        return getResponse();
    });

    $this->conversionClient->sms('ABC123', true, '123456');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException
 * @throws RequestException
 * @throws ServerException
 */
test('sms without timestamp', function () {
    $this->vonageClient->method('send')->willReturnCallback(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/conversions/sms');
        expect($request->getUri()->getHost())->toEqual('api.nexmo.com');
        expect($request->getMethod())->toEqual('POST');
        $this->assertRequestQueryContains('message-id', 'ABC123', $request);
        $this->assertRequestQueryContains('delivered', '1', $request);
        $this->assertRequestQueryNotContains('timestamp', $request);

        return getResponse();
    });

    $this->conversionClient->sms('ABC123', true);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException
 * @throws RequestException
 * @throws ServerException
 */
test('voice with timestamp', function () {
    $this->vonageClient->method('send')->willReturnCallback(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/conversions/voice');
        expect($request->getUri()->getHost())->toEqual('api.nexmo.com');
        expect($request->getMethod())->toEqual('POST');
        $this->assertRequestQueryContains('message-id', 'ABC123', $request);
        $this->assertRequestQueryContains('delivered', '1', $request);
        $this->assertRequestQueryContains('timestamp', '123456', $request);

        return getResponse();
    });

    $this->conversionClient->voice('ABC123', true, '123456');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException
 * @throws RequestException
 * @throws ServerException
 */
test('voice without timestamp', function () {
    $this->vonageClient->method('send')->willReturnCallback(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/conversions/voice');
        expect($request->getUri()->getHost())->toEqual('api.nexmo.com');
        expect($request->getMethod())->toEqual('POST');
        $this->assertRequestQueryContains('message-id', 'ABC123', $request);
        $this->assertRequestQueryContains('delivered', '1', $request);
        $this->assertRequestQueryNotContains('timestamp', $request);

        return getResponse();
    });

    $this->conversionClient->voice('ABC123', true);
});

// Helpers
/**
     * Get the API response we'd expect for a call to the API.
     */
function getResponse(): Response
{
    return new Response(fopen('data://text/plain,', 'rb'), 200);
}
