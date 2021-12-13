<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ResponseFactory;
use VonageTest\VonageTestCase;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\Client\Exception\ThrottleException;
use Vonage\SMS\ExceptionErrorHandler;


/**
 * @throws RequestException
 * @throws ThrottleException
 * @throws ServerException
 */
test('test429 throws throttle exception', function () {
    $this->expectException(ThrottleException::class);
    $this->expectExceptionMessage('Too many concurrent requests');

    $response = (new ResponseFactory())
        ->createResponse(429);

    $handler = new ExceptionErrorHandler();
    $handler($response, new Request());
});

/**
 * @throws RequestException
 * @throws ServerException
 * @throws ThrottleException
 */
test('generic error throws request exception', function () {
    $this->expectException(RequestException::class);
    $this->expectExceptionMessage('This is a generic error being thrown');
    $this->expectExceptionCode(499);

    $response = new JsonResponse([
        'error-code' => 499,
        'error-code-label' => 'This is a generic error being thrown'
    ]);

    $handler = new ExceptionErrorHandler();
    $handler($response, new Request());
});
