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


test('as array', function () {
    $credentials = new Basic($this->key, $this->secret);
    $array = $credentials->asArray();

    expect($array['api_key'])->toEqual($this->key);
    expect($array['api_secret'])->toEqual($this->secret);
});

test('array access', function () {
    $credentials = new Basic($this->key, $this->secret);

    expect($credentials['api_key'])->toEqual($this->key);
    expect($credentials['api_secret'])->toEqual($this->secret);
});

test('properties', function () {
    $credentials = new Basic($this->key, $this->secret);

    expect($credentials->api_key)->toEqual($this->key);
    expect($credentials->api_secret)->toEqual($this->secret);
});
