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
    expect($api->client)->toBe($this->client);
});

test('cache', function () {
    $api = $this->factory->getApi('test');
    $cache = $this->factory->getApi('test');

    expect($cache)->toBe($api);
});

test('class map', function () {
    expect($this->factory->hasApi('test'))->toBeTrue();
    expect($this->factory->hasApi('not'))->toBeFalse();

    $api = $this->factory->getApi('test');
    expect($api)->toBeInstanceOf(TestDouble::class);

    $this->expectException(RuntimeException::class);
    $this->factory->getApi('not');
});

test('make creates new instance', function () {
    $first = $this->factory->make('test');
    $second = $this->factory->make('test');

    $this->assertNotSame($first, $second);
    expect($first)->toBeInstanceOf(TestDouble::class);
    expect($second)->toBeInstanceOf(TestDouble::class);
});

test('make does not use cache', function () {
    $cached = $this->factory->get('test');
    $new = $this->factory->make('test');
    $secondCached = $this->factory->get('test');

    $this->assertNotSame($cached, $new);
    $this->assertNotSame($secondCached, $new);
    expect($secondCached)->toBe($cached);
});
