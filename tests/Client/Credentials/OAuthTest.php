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


test('as array', function () {
    $credentials = new OAuth($this->appToken, $this->appSecret, $this->clientToken, $this->clientSecret);
    $array = $credentials->asArray();

    expect($array['token'])->toEqual($this->clientToken);
    expect($array['token_secret'])->toEqual($this->clientSecret);
    expect($array['consumer_key'])->toEqual($this->appToken);
    expect($array['consumer_secret'])->toEqual($this->appSecret);
});

test('array access', function () {
    $credentials = new OAuth($this->appToken, $this->appSecret, $this->clientToken, $this->clientSecret);

    expect($credentials['token'])->toEqual($this->clientToken);
    expect($credentials['token_secret'])->toEqual($this->clientSecret);
    expect($credentials['consumer_key'])->toEqual($this->appToken);
    expect($credentials['consumer_secret'])->toEqual($this->appSecret);
});

test('properties', function () {
    $credentials = new OAuth($this->appToken, $this->appSecret, $this->clientToken, $this->clientSecret);

    expect($credentials->token)->toEqual($this->clientToken);
    expect($credentials->token_secret)->toEqual($this->clientSecret);
    expect($credentials->consumer_key)->toEqual($this->appToken);
    expect($credentials->consumer_secret)->toEqual($this->appSecret);
});
