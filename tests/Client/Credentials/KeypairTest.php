<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

use Vonage\Client\Credentials\Keypair;

beforeEach(function () {
    $this->key = file_get_contents(__DIR__ . '/test.key');
});

test('as array', function () {
    $credentials = new Keypair($this->key, $this->application);

    $array = $credentials->asArray();
    expect($array['key'])->toEqual($this->key);
    expect($array['application'])->toEqual($this->application);
});

test('array access', function () {
    $credentials = new Keypair($this->key, $this->application);

    expect($credentials['key'])->toEqual($this->key);
    expect($credentials['application'])->toEqual($this->application);
});

test('properties', function () {
    $credentials = new Keypair($this->key, $this->application);

    expect($credentials->__get('key'))->toEqual($this->key);
    expect($credentials->application)->toEqual($this->application);
});

test('default j w t', function () {
    $credentials = new Keypair($this->key, $this->application);

    //could use the JWT object, but hope to remove as a dependency
    $jwt = (string)$credentials->generateJwt()->toString();

    [$header, $payload] = decodeJWT($jwt);

    $this->assertArrayHasKey('typ', $header);
    $this->assertArrayHasKey('alg', $header);
    expect($header['typ'])->toEqual('JWT');
    expect($header['alg'])->toEqual('RS256');
    $this->assertArrayHasKey('application_id', $payload);
    $this->assertArrayHasKey('jti', $payload);
    expect($payload['application_id'])->toEqual($this->application);
});

test('additional claims', function () {
    $credentials = new Keypair($this->key, $this->application);

    $claims = [
        'arbitrary' => [
            'nested' => [
                'data' => "something"
            ]
        ],
        'nbf' => 900
    ];

    $jwt = $credentials->generateJwt($claims);
    [, $payload] = decodeJWT($jwt->toString());

    $this->assertArrayHasKey('arbitrary', $payload);
    expect($payload['arbitrary'])->toEqual($claims['arbitrary']);
    $this->assertArrayHasKey('nbf', $payload);
    expect($payload['nbf'])->toEqual(900);
});

/**
 * @link https://github.com/Vonage/vonage-php-sdk-core/issues/276
 */
test('example conversation j w t works', function () {
    $credentials = new Keypair($this->key, $this->application);
    $claims = [
        'exp' => strtotime(date('Y-m-d', strtotime('+24 Hours'))),
        'sub' => 'apg-cs',
        'acl' => [
            'paths' => [
                '/*/users/**' => (object) [],
                '/*/conversations/**' => (object) [],
                '/*/sessions/**' => (object) [],
                '/*/devices/**' => (object) [],
                '/*/image/**' => (object) [],
                '/*/media/**' => (object) [],
                '/*/applications/**' => (object) [],
                '/*/push/**' => (object) [],
                '/*/knocking/**' => (object) [],
                '/*/legs/**' => (object) [],
            ]
        ],
    ];

    $jwt = $credentials->generateJwt($claims);
    [, $payload] = decodeJWT($jwt->toString());

    $this->assertArrayHasKey('exp', $payload);
    expect($payload['exp'])->toEqual($claims['exp']);
    expect($payload['sub'])->toEqual($claims['sub']);
});

// Helpers
/**
     * @param $jwt
     */
function decodeJWT($jwt): array
{
    $parts = explode('.', $jwt);

    expect($parts)->toHaveCount(3);

    $header = json_decode(base64_decode($parts[0]), true);
    $payload = json_decode(base64_decode($parts[1]), true);
    $sig = $parts[2];

    return [
        $header,
        $payload,
        $sig
    ];
}
