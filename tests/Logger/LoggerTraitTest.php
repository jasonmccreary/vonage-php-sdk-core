<?php

use Psr\Log\LoggerInterface;
use Vonage\Logger\LoggerTrait;
use VonageTest\VonageTestCase;

uses(VonageTestCase::class);

test('can set and get logger', function () {
    /** @var LoggerTrait $trait */
    $trait = $this->getMockForTrait(LoggerTrait::class);
    $logger = $this->prophesize(LoggerInterface::class)->reveal();
    $trait->setLogger($logger);

    expect($trait->getLogger())->toBe($logger);
});

test('no logger returns null', function () {
    /** @var LoggerTrait $trait */
    $trait = $this->getMockForTrait(LoggerTrait::class);

    expect($trait->getLogger())->toBeNull();
});

test('can log message with logger', function () {
    /** @var LoggerTrait $trait */
    $trait = $this->getMockForTrait(LoggerTrait::class);
    $logger = $this->prophesize(LoggerInterface::class)->reveal();
    $trait->setLogger($logger);

    expect($trait->log('debug', 'This is a message'))->toBeNull();
});

test('logging accepts message with logger', function () {
    /** @var LoggerTrait $trait */
    $trait = $this->getMockForTrait(LoggerTrait::class);

    expect($trait->log('debug', 'This is a message'))->toBeNull();
});
