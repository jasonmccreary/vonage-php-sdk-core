<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Client;
use Vonage\Client\APIResource;

uses(VonageTestCase::class);

test('overriding base url uses client api url', function () {
    /** @var mixed $mockClient */
    $mockClient = $this->prophesize(Client::class);
    $mockClient->getApiUrl()->willReturn('https://test.domain');

    $resource = new APIResource();
    $resource->setClient($mockClient->reveal());

    $this->assertSame('https://test.domain', $resource->getBaseUrl());
});

test('overriding base url manually works', function () {
    $resource = new APIResource();
    $resource->setBaseUrl('https://test.domain');

    $this->assertSame('https://test.domain', $resource->getBaseUrl());
});

test('not overriding base u r l returns blank', function () {
    $resource = new APIResource();
    $this->assertSame('', $resource->getBaseUrl());
});
