<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use RuntimeException;
use Vonage\Client;
use Vonage\Client\Factory\MapFactory;

uses(VonageTestCase::class);

beforeEach(function () {
    $this->client = new Client(new Client\Credentials\Basic('key', 'secret'));

    $this->factory = new MapFactory([
        'test' => TestDouble::class
    ], $this->client);
});

test('client injection', function () {
    $api = $this->factory->getApi('test');
    $this->assertSame($this->client, $api->client);
});

test('cache', function () {
    $api = $this->factory->getApi('test');
    $cache = $this->factory->getApi('test');

    $this->assertSame($api, $cache);
});

test('class map', function () {
    $this->assertTrue($this->factory->hasApi('test'));
    $this->assertFalse($this->factory->hasApi('not'));

    $api = $this->factory->getApi('test');
    $this->assertInstanceOf(TestDouble::class, $api);

    $this->expectException(RuntimeException::class);
    $this->factory->getApi('not');
});

test('make creates new instance', function () {
    $first = $this->factory->make('test');
    $second = $this->factory->make('test');

    $this->assertNotSame($first, $second);
    $this->assertInstanceOf(TestDouble::class, $first);
    $this->assertInstanceOf(TestDouble::class, $second);
});

test('make does not use cache', function () {
    $cached = $this->factory->get('test');
    $new = $this->factory->make('test');
    $secondCached = $this->factory->get('test');

    $this->assertNotSame($cached, $new);
    $this->assertNotSame($secondCached, $new);
    $this->assertSame($cached, $secondCached);
});
