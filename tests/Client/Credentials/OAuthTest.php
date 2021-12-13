<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Client\Credentials\OAuth;

uses(VonageTestCase::class);

test('as array', function () {
    $credentials = new OAuth($this->appToken, $this->appSecret, $this->clientToken, $this->clientSecret);
    $array = $credentials->asArray();

    $this->assertEquals($this->clientToken, $array['token']);
    $this->assertEquals($this->clientSecret, $array['token_secret']);
    $this->assertEquals($this->appToken, $array['consumer_key']);
    $this->assertEquals($this->appSecret, $array['consumer_secret']);
});

test('array access', function () {
    $credentials = new OAuth($this->appToken, $this->appSecret, $this->clientToken, $this->clientSecret);

    $this->assertEquals($this->clientToken, $credentials['token']);
    $this->assertEquals($this->clientSecret, $credentials['token_secret']);
    $this->assertEquals($this->appToken, $credentials['consumer_key']);
    $this->assertEquals($this->appSecret, $credentials['consumer_secret']);
});

test('properties', function () {
    $credentials = new OAuth($this->appToken, $this->appSecret, $this->clientToken, $this->clientSecret);

    $this->assertEquals($this->clientToken, $credentials->token);
    $this->assertEquals($this->clientSecret, $credentials->token_secret);
    $this->assertEquals($this->appToken, $credentials->consumer_key);
    $this->assertEquals($this->appSecret, $credentials->consumer_secret);
});
