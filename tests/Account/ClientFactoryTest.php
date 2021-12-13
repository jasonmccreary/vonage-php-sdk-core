<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Account\ClientFactory;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Factory\MapFactory;


beforeEach(function () {
    $this->vonageClient = $this->prophesize(Client::class);
    $this->vonageClient->getRestUrl()->willReturn('https://rest.nexmo.com');
    $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

    /** @noinspection PhpParamsInspection */
    $this->mapFactory = new MapFactory([APIResource::class => APIResource::class], $this->vonageClient->reveal());
});

test('u r is are correct', function () {
    $factory = new ClientFactory();
    $client = $factory($this->mapFactory);

    expect($client->getSecretsAPI()->getBaseUri())->toBe('/accounts');
    expect($client->getSecretsAPI()->getBaseUrl())->toBe('https://api.nexmo.com');
    expect($client->getAccountAPI()->getBaseUri())->toBe('/account');
    expect($client->getAccountAPI()->getBaseUrl())->toBe('https://rest.nexmo.com');
});
