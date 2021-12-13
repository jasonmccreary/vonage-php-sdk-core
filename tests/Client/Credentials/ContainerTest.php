<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\Container;
use Vonage\Client\Credentials\Keypair;
use Vonage\Client\Credentials\SignatureSecret;

beforeEach(function () {
    $this->basic = new Basic('key', 'secret');
    $this->secret = new SignatureSecret('key', 'secret');
    $this->keypair = new Keypair('key', 'app');
});

/**
 *
 * @param $credential
 * @param $type
 */
test('basic', function ($credential, $type) {
    $container = new Container($credential);

    expect($container->get($type))->toBe($credential);
    expect($container[$type])->toBe($credential);

    foreach ($this->types as $class) {
        if ($type === $class) {
            expect($container->has($class))->toBeTrue();
        } else {
            expect($container->has($class))->toBeFalse();
        }
    }
})->with('credentials');

/**
 *
 * @param $credential
 */
test('only one type', function ($credential) {
    $this->expectException('RuntimeException');

    new Container($credential, clone $credential);
})->with('credentials');

test('multiple', function () {
    $container = new Container($this->basic, $this->secret, $this->keypair);

    foreach ($this->types as $class) {
        expect($container->has($class))->toBeTrue();
    }
});

// Datasets
/**
 * @return array[]
 */
dataset('credentials', [
    [new Basic('key', 'secret'), Basic::class],
    [new SignatureSecret('key', 'secret'), SignatureSecret::class],
    [new Keypair('key', 'app'), Keypair::class]
]);
