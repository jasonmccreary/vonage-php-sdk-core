<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\Container;
use Vonage\Client\Credentials\Keypair;
use Vonage\Client\Credentials\SignatureSecret;

uses(VonageTestCase::class);

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

    $this->assertSame($credential, $container->get($type));
    $this->assertSame($credential, $container[$type]);

    foreach ($this->types as $class) {
        if ($type === $class) {
            $this->assertTrue($container->has($class));
        } else {
            $this->assertFalse($container->has($class));
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
        $this->assertTrue($container->has($class));
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
