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
use Vonage\Client\Exception as ClientException;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Numbers\Client as NumbersClient;
use Vonage\Numbers\Filter\AvailableNumbers;
use Vonage\Numbers\Number;
use VonageTest\Psr7AssertionTrait;

uses(Psr7AssertionTrait::class);


beforeEach(function () {
    $this->vonageClient = $this->prophesize('Vonage\Client');
    $this->vonageClient->getRestUrl()->willReturn('https://rest.nexmo.com');

    /** @noinspection PhpParamsInspection */
    $this->numberClient = (new NumbersClient())->setClient($this->vonageClient->reveal());
});

/**
 *
 * @param $payload
 * @param $id
 * @param $expectedId
 * @param $lookup
 *
 * @throws ClientException\Exception
 * @throws RequestException
 * @throws ClientExceptionInterface
 */
test('update number', function ($payload, $id, $expectedId, $lookup) {
    //based on the id provided, may need to look up the number first
    if ($lookup) {
        if (1415550100 === (int)$id || is_null($id)) {
            $first = getResponse('single');
        } else {
            $first = getResponse('single-update');
        }

        $second = getResponse('post');
        $third = getResponse('single');
    } else {
        $first = getResponse('post');
        $second = getResponse('single');
        $third = null;
    }

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($expectedId) {
        if ($request->getUri()->getPath() === '/account/numbers') {
            //just getting the number first / last
            return true;
        }

        expect($request->getUri()->getPath())->toEqual('/number/update');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('POST');

        $this->assertRequestFormBodyContains('country', 'US', $request);
        $this->assertRequestFormBodyContains('msisdn', $expectedId, $request);

        $this->assertRequestFormBodyContains('moHttpUrl', 'https://example.com/new_message', $request);
        $this->assertRequestFormBodyContains('voiceCallbackType', 'vxml', $request);
        $this->assertRequestFormBodyContains('voiceCallbackValue', 'https://example.com/new_voice', $request);
        $this->assertRequestFormBodyContains('voiceStatusCallbackUrl', 'https://example.com/new_status', $request);

        return true;
    }))->willReturn($first, $second, $third);

    if (isset($id)) {
        $number = @$this->numberClient->update($payload, $id);
    } else {
        $number = @$this->numberClient->update($payload);
    }

    expect($number)->toBeInstanceOf(Number::class);
    if ($payload instanceof Number) {
        expect($number)->toBe($payload);
    }
})->with('updateNumber');

/**
 *
 * @param $payload
 * @param $id
 *
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Server
 * @throws RequestException
 */
test('get number', function ($payload, $id) {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
        expect($request->getUri()->getPath())->toEqual('/account/numbers');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('GET');
        $this->assertRequestQueryContains('pattern', $id, $request);
        return true;
    }))->willReturn(getResponse('single'));

    $number = @$this->numberClient->get($payload);

    expect($number)->toBeInstanceOf(Number::class);

    if ($payload instanceof Number) {
        expect($number)->toBe($payload);
    }

    expect($number->getId())->toBe($id);
})->with('numbers');

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Server
 * @throws RequestException
 */
test('list numbers', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/account/numbers');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('GET');
        return true;
    }))->willReturn(getResponse('list'));

    $numbers = $this->numberClient->search();

    expect($numbers)->toBeArray();
    expect($numbers[0])->toBeInstanceOf(Number::class);
    expect($numbers[1])->toBeInstanceOf(Number::class);
    expect($numbers[0]->getId())->toBe('14155550100');
    expect($numbers[1]->getId())->toBe('14155550101');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Server
 * @throws RequestException
 */
test('search available passes through whitelisted options', function () {
    $options = [
        'pattern' => '1',
        'search_pattern' => '2',
        'features' => 'SMS,VOICE',
        'size' => '100',
        'index' => '19'
    ];

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($options) {
        expect($request->getUri()->getPath())->toEqual('/number/search');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('GET');

        // Things that are whitelisted should be shown
        foreach ($options as $name => $value) {
            $this->assertRequestQueryContains($name, $value, $request);
        }

        return true;
    }))->willReturn(getResponse('available-numbers'));

    @$this->numberClient->searchAvailable('US', $options);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Server
 * @throws RequestException
 */
test('search available accepts filter interface options', function () {
    $options = new AvailableNumbers([
        'pattern' => '1',
        'search_pattern' => 2,
        'features' => 'SMS,VOICE',
        'size' => 100,
        'index' => 19
    ]);

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/number/search');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('GET');

        return true;
    }))->willReturn(getResponse('available-numbers'));

    @$this->numberClient->searchAvailable('US', $options);
});

/**
 * Make sure that unknown parameters fail validation
 *
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Server
 * @throws RequestException
 */
test('unknown parameter value for search throws exception', function () {
    $this->expectException(RequestException::class);
    $this->expectExceptionMessage("Unknown option: 'foo'");

    @$this->numberClient->searchAvailable('US', ['foo' => 'bar']);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Server
 * @throws RequestException
 */
test('search available returns number list', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/number/search');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('GET');

        return true;
    }))->willReturn(getResponse('available-numbers'));

    $numbers = $this->numberClient->searchAvailable('US');

    expect($numbers)->toBeArray();
    expect($numbers[0])->toBeInstanceOf(Number::class);
    expect($numbers[1])->toBeInstanceOf(Number::class);
    expect($numbers[0]->getId())->toBe('14155550100');
    expect($numbers[1]->getId())->toBe('14155550101');
});

/**
 * A search can return an empty set `[]` result when no numbers are found
 *
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Server
 * @throws RequestException
 */
test('search available returns empty number list', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/number/search');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('GET');

        return true;
    }))->willReturn(getResponse('empty'));

    $numbers = @$this->numberClient->searchAvailable('US');

    expect($numbers)->toBeArray();
    expect($numbers)->toBeEmpty();
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Server
 * @throws RequestException
 */
test('search owned errors on unknown search parameters', function () {
    $this->expectException(ClientException\Request::class);
    $this->expectExceptionMessage("Unknown option: 'foo'");

    @$this->numberClient->searchOwned('1415550100', ['foo' => 'bar']);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Server
 * @throws RequestException
 */
test('search owned passes in allowed additional parameters', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/account/numbers');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('GET');
        $this->assertRequestQueryContains('index', '1', $request);
        $this->assertRequestQueryContains('size', '100', $request);
        $this->assertRequestQueryContains('search_pattern', '0', $request);
        $this->assertRequestQueryContains('has_application', 'false', $request);
        $this->assertRequestQueryContains('pattern', '1415550100', $request);

        return true;
    }))->willReturn(getResponse('single'));

    @$this->numberClient->searchOwned('1415550100', [
        'index' => 1,
        'size' => '100',
        'search_pattern' => 0,
        'has_application' => false,
        'country' => 'GB',
    ]);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Server
 * @throws RequestException
 */
test('search owned returns single number', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/account/numbers');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('GET');

        return true;
    }))->willReturn(getResponse('single'));

    $numbers = $this->numberClient->searchOwned('1415550100');

    expect($numbers)->toBeArray();
    expect($numbers[0])->toBeInstanceOf(Number::class);
    expect($numbers[0]->getId())->toBe('1415550100');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 */
test('purchase number with number object', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/number/buy');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('POST');

        return true;
    }))->willReturn(getResponse('post'));

    $number = new Number('1415550100', 'US');
    $this->numberClient->purchase($number);

    // There's nothing to assert here as we don't do anything with the response.
    // If there's no exception thrown, everything is fine!
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 */
test('purchase number with number and country', function () {
    // When providing a number string, the first thing that happens is a GET request to fetch number details
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        return $request->getUri()->getPath() === '/account/numbers';
    }))->willReturn(getResponse('single'));

    // Then we purchase the number
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        if ($request->getUri()->getPath() === '/number/buy') {
            expect($request->getUri()->getPath())->toEqual('/number/buy');
            expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
            expect($request->getMethod())->toEqual('POST');
            return true;
        }
        return false;
    }))->willReturn(getResponse('post'));
    @$this->numberClient->purchase('1415550100', 'US');

    // There's nothing to assert here as we don't do anything with the response.
    // If there's no exception thrown, everything is fine!
});

/**
 *
 * @param $number
 * @param $country
 * @param $responseFile
 * @param $expectedHttpCode
 * @param $expectedException
 * @param $expectedExceptionMessage
 *
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 */
test('purchase number errors', function (
    $number,
    $country,
    $responseFile,
    $expectedHttpCode,
    $expectedException,
    $expectedExceptionMessage
) {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/number/buy');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('POST');
        return true;
    }))->willReturn(getResponse($responseFile, $expectedHttpCode));

    $this->expectException($expectedException);
    $this->expectExceptionMessage($expectedExceptionMessage);

    $num = new Number($number, $country);
    @$this->numberClient->purchase($num);
})->with('purchaseNumberErrorProvider');

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Server
 * @throws RequestException
 */
test('cancel number with number object', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/number/cancel');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('POST');

        return true;
    }))->willReturn(getResponse('cancel'));

    $number = new Number('1415550100', 'US');
    @$this->numberClient->cancel($number);

    // There's nothing to assert here as we don't do anything with the response.
    // If there's no exception thrown, everything is fine!
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Server
 * @throws RequestException
 */
test('cancel number with number string', function () {
    // When providing a number string, the first thing that happens is a GET request to fetch number details
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        return $request->getUri()->getPath() === '/account/numbers';
    }))->willReturn(getResponse('single'));

// Then we get a POST request to cancel
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        if ($request->getUri()->getPath() === '/number/cancel') {
            expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
            expect($request->getMethod())->toEqual('POST');

            return true;
        }
        return false;
    }))->willReturn(getResponse('cancel'));

    @$this->numberClient->cancel('1415550100');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Server
 * @throws RequestException
 */
test('cancel number with number and country string', function () {
    // When providing a number string, the first thing that happens is a GET request to fetch number details
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        return $request->getUri()->getPath() === '/account/numbers';
    }))->willReturn(getResponse('single'));

    // Then we get a POST request to cancel
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        if ($request->getUri()->getPath() === '/number/cancel') {
            expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
            expect($request->getMethod())->toEqual('POST');

            return true;
        }
        return false;
    }))->willReturn(getResponse('cancel'));

    @$this->numberClient->cancel('1415550100', 'US');
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Server
 * @throws RequestException
 */
test('cancel number error', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/number/cancel');
        expect($request->getUri()->getHost())->toEqual('rest.nexmo.com');
        expect($request->getMethod())->toEqual('POST');

        return true;
    }))->willReturn(getResponse('method-failed', 420));

    $this->expectException(ClientException\Request::class);
    $this->expectExceptionMessage('method failed');

    $num = new Number('1415550100', 'US');
    @$this->numberClient->cancel($num);
});

/**
 * Make sure that integer values that fail validation throw properly
 *
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Server
 * @throws RequestException
 */
test('invalid integer value for search throws exception', function () {
    $this->expectException(RequestException::class);
    $this->expectExceptionMessage("Invalid value: 'size' must be an integer");

    @$this->numberClient->searchOwned(null, ['size' => 'bob']);
});

/**
 * Make sure that boolean values that fail validation throw properly
 *
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Server
 * @throws RequestException
 */
test('invalid boolean value for search throws exception', function () {
    $this->expectException(RequestException::class);
    $this->expectExceptionMessage("Invalid value: 'has_application' must be a boolean value");

    @$this->numberClient->searchOwned(null, ['has_application' => 'bob']);
});

// Datasets
/**
 * @return array[]
 */
dataset('updateNumber', function () {

    $raw = $rawId = [
        'moHttpUrl' => 'https://example.com/new_message',
        'voiceCallbackType' => 'vxml',
        'voiceCallbackValue' => 'https://example.com/new_voice',
        'voiceStatusCallbackUrl' => 'https://example.com/new_status'
    ];

    $rawId['country'] = 'US';
    $rawId['msisdn'] = '1415550100';

    $number = new Number('1415550100');
    $number->setWebhook(Number::WEBHOOK_MESSAGE, 'https://example.com/new_message');
    $number->setWebhook(Number::WEBHOOK_VOICE_STATUS, 'https://example.com/new_status');
    $number->setVoiceDestination('https://example.com/new_voice');

    $noLookup = new Number('1415550100', 'US');
    $noLookup->setWebhook(Number::WEBHOOK_MESSAGE, 'https://example.com/new_message');
    $noLookup->setWebhook(Number::WEBHOOK_VOICE_STATUS, 'https://example.com/new_status');
    $noLookup->setVoiceDestination('https://example.com/new_voice');

    $fresh = new Number('1415550100', 'US');
    $fresh->setWebhook(Number::WEBHOOK_MESSAGE, 'https://example.com/new_message');
    $fresh->setWebhook(Number::WEBHOOK_VOICE_STATUS, 'https://example.com/new_status');
    $fresh->setVoiceDestination('https://example.com/new_voice');

    return [
        [$raw, '1415550100', '1415550100', true],
        [$rawId, null, '1415550100', false],
        [clone $number, null, '1415550100', true],
        [clone $number, '1415550100', '1415550100', true],
        [clone $noLookup, null, '1415550100', false],
        [clone $fresh, '1415550100', '1415550100', true],
    ];
});

dataset('numbers', [
    ['1415550100', '1415550100'],
    [new Number('1415550100'), '1415550100'],
]);

dataset('purchaseNumberErrorProvider', function () {
    $r = [];

    $r['mismatched number/country'] = [
        '14155510100',
        'GB',
        'method-failed',
        420,
        ClientException\Request::class,
        'method failed'
    ];

    $r['user already owns number'] = [
        '14155510100',
        'GB',
        'method-failed',
        420,
        ClientException\Request::class,
        'method failed'
    ];

    $r['someone else owns the number'] = [
        '14155510100',
        'GB',
        'method-failed',
        420,
        ClientException\Request::class,
        'method failed'
    ];

    return $r;
});

// Helpers
/**
     * Get the API response we'd expect for a call to the API.
     */
function getResponse(string $type = 'success', int $status = 200): Response
{
    return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
}
