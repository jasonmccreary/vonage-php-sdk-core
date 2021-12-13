<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Voice\Endpoint\SIP;


test('default endpoint is created properly', function () {
    $endpoint = new SIP($this->uri);

    expect($endpoint->getId())->toBe($this->uri);
    expect($endpoint->getHeaders())->toBeEmpty();
});

test('factory creates app endpoint', function () {
    $headers = [
        'location' => 'New York City',
        'occupation' => 'Developer'
    ];

    $endpoint = SIP::factory($this->uri, $headers);

    expect($endpoint->getId())->toBe($this->uri);
    expect($endpoint->getHeaders())->toBe($headers);
});

test('to array has correct structure', function () {
    $this->assertSame([
        'type' => $this->type,
        'uri' => $this->uri
    ], (new SIP($this->uri))->toArray());
});

test('headers are returned as array', function () {
    $headers = [
        'location' => 'New York City',
        'occupation' => 'Developer'
    ];

    $expected = [
        'type' => $this->type,
        'uri' => $this->uri,
        'headers' => $headers
    ];

    $this->assertSame($expected, ((new SIP($this->uri))->setHeaders($headers))->toArray());
});

test('serializes to j s o n correctly', function () {
    $this->assertSame([
        'type' => $this->type,
        'uri' => $this->uri
    ], (new SIP($this->uri))->jsonSerialize());
});

test('header can be individually added', function () {
    expect((new SIP($this->uri))->addHeader('key', 'value')->getHeaders())->toBe(['key' => 'value']);
});
