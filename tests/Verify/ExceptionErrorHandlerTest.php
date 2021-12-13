<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

use Laminas\Diactoros\Request;
use VonageTest\VonageTestCase;
use Vonage\Entity\Psr7Trait;
use Laminas\Diactoros\Response;
use Vonage\Client\Exception\Request as ExceptionRequest;
use Vonage\Verify\ExceptionErrorHandler;

uses(Psr7Trait::class);

test('server exception throw on error', function () {
    $this->expectException(ExceptionRequest::class);

    $handler = new ExceptionErrorHandler();
    $handler->__invoke(getResponse('start-error'), new Request());
});

test('no exception throw on valid response', function () {
    $handler = new ExceptionErrorHandler();
    expect($handler->__invoke(getResponse('start'), new Request()))->toBeNull();
});

// Helpers
/**
     * Get the API response we'd expect for a call to the API. Verify API currently returns 200 all the time, so only
     * change between success / fail is body of the message.
     */
function getResponse(string $type = 'success'): Response
{
    return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'));
}
