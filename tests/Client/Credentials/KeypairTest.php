<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

use VonageTest\VonageTestCase;
use Vonage\Client\Credentials\Keypair;

uses(VonageTestCase::class);


beforeEach(function () {
    $this->key = file_get_contents(__DIR__ . '/test.key');
});

test('as array', function () {
    $credentials = new Keypair($this->key, $this->application);

    $array = $credentials->asArray();
    $this->assertEquals($this->key, $array['key']);
    $this->assertEquals($this->application, $array['application']);
});

test('array access', function () {
    $credentials = new Keypair($this->key, $this->application);

    $this->assertEquals($this->key, $credentials['key']);
    $this->assertEquals($this->application, $credentials['application']);
});

test('properties', function () {
    $credentials = new Keypair($this->key, $this->application);

    $this->assertEquals($this->key, $credentials->__get('key'));
    $this->assertEquals($this->application, $credentials->application);
});

test('default j w t', function () {
    $credentials = new Keypair($this->key, $this->application);

    //could use the JWT object, but hope to remove as a dependency
    $jwt = (string)$credentials->generateJwt()->toString();

    [$header, $payload] = decodeJWT($jwt);

    $this->assertArrayHasKey('typ', $header);
    $this->assertArrayHasKey('alg', $header);
    $this->assertEquals('JWT', $header['typ']);
    $this->assertEquals('RS256', $header['alg']);
    $this->assertArrayHasKey('application_id', $payload);
    $this->assertArrayHasKey('jti', $payload);
    $this->assertEquals($this->application, $payload['application_id']);
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
    $this->assertEquals($claims['arbitrary'], $payload['arbitrary']);
    $this->assertArrayHasKey('nbf', $payload);
    $this->assertEquals(900, $payload['nbf']);
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
    $this->assertEquals($claims['exp'], $payload['exp']);
    $this->assertEquals($claims['sub'], $payload['sub']);
});

// Helpers
/**
     * @param $jwt
     */
function decodeJWT($jwt): array
{
    $parts = explode('.', $jwt);

    test()->assertCount(3, $parts);

    $header = json_decode(base64_decode($parts[0]), true);
    $payload = json_decode(base64_decode($parts[1]), true);
    $sig = $parts[2];

    return [
        $header,
        $payload,
        $sig
    ];
}
