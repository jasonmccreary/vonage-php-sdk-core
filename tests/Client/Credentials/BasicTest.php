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

uses(VonageTestCase::class);

test('as array', function () {
    $credentials = new Basic($this->key, $this->secret);
    $array = $credentials->asArray();

    $this->assertEquals($this->key, $array['api_key']);
    $this->assertEquals($this->secret, $array['api_secret']);
});

test('array access', function () {
    $credentials = new Basic($this->key, $this->secret);

    $this->assertEquals($this->key, $credentials['api_key']);
    $this->assertEquals($this->secret, $credentials['api_secret']);
});

test('properties', function () {
    $credentials = new Basic($this->key, $this->secret);

    $this->assertEquals($this->key, $credentials->api_key);
    $this->assertEquals($this->secret, $credentials->api_secret);
});
