<?php

use Prophecy\Argument;
use Vonage\Secrets\Client;
use Vonage\Secrets\Secret;
use VonageTest\HTTPTestTrait;
use Vonage\Client\APIResource;
use VonageTest\VonageTestCase;
use Vonage\Client as VonageClient;
use Psr\Http\Message\RequestInterface;

uses(VonageTestCase::class);
uses(HTTPTestTrait::class);

beforeEach(function () {
    $this->vonage = $this->prophesize(VonageClient::class);
    $this->vonage->getRestUrl()->willReturn('https://rest.nexmo.com');
    $this->vonage->getApiUrl()->willReturn('https://api.nexmo.com');

    $api = new APIResource();
    $api->setClient($this->vonage->reveal())
        ->setBaseUri('/accounts')
        ->setCollectionName('secrets');

    $this->client = new Client($api);
});

test('list all secrets', function () {
    $this->vonage->send(Argument::that(function (RequestInterface $request) {
        $this->assertRequestUrl('api.nexmo.com', '/accounts/abcd123/secrets', 'GET', $request);
        return true;
    }))->willReturn($this->getResponse('list', 200));

    $response = $this->client->list('abcd123');

    expect($response)->toHaveCount(2);
    foreach ($response as $i => $secret) {
        expect($secret)->toBeInstanceOf(Secret::class);
        expect($secret->getId())->toBe($i);
    }
});

test('get secret', function () {
    $this->vonage->send(Argument::that(function (RequestInterface $request) {
        $this->assertRequestUrl('api.nexmo.com', '/accounts/abcd123/secrets/105abf14-aa00-45a3-9d27-dd19c5920f2c', 'GET', $request);
        return true;
    }))->willReturn($this->getResponse('single', 200));

    $secret = $this->client->get('abcd123', '105abf14-aa00-45a3-9d27-dd19c5920f2c');

    expect($secret->getId())->toBe('105abf14-aa00-45a3-9d27-dd19c5920f2c');
    $this->assertSame('2020-09-08T21:54:14Z', $secret->getCreatedAt()->format('Y-m-d\TH:i:s\Z'));
});

test('revoke secret', function () {
    $this->vonage->send(Argument::that(function (RequestInterface $request) {
        $this->assertRequestUrl('api.nexmo.com', '/accounts/abcd123/secrets/105abf14-aa00-45a3-9d27-dd19c5920f2c', 'DELETE', $request);
        return true;
    }))->willReturn($this->getResponse('empty', 204));

    $secret = $this->client->revoke('abcd123', '105abf14-aa00-45a3-9d27-dd19c5920f2c');
});

test('create secret', function () {
    $this->vonage->send(Argument::that(function (RequestInterface $request) {
        $this->assertRequestUrl('api.nexmo.com', '/accounts/abcd123/secrets', 'POST', $request);
        return true;
    }))->willReturn($this->getResponse('new', 204));

    $secret = $this->client->create('abcd123', '105abf14-aa00-45a3-9d27-dd19c5920f2c');

    expect($secret->getId())->toBe('527ffe03-dfba-46c4-9b40-da5cbefb22c4');
    $this->assertSame('2020-09-08T21:54:14Z', $secret->getCreatedAt()->format('Y-m-d\TH:i:s\Z'));
});
