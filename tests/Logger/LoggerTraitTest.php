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

    $this->assertSame($logger, $trait->getLogger());
});

test('no logger returns null', function () {
    /** @var LoggerTrait $trait */
    $trait = $this->getMockForTrait(LoggerTrait::class);

    $this->assertNull($trait->getLogger());
});

test('can log message with logger', function () {
    /** @var LoggerTrait $trait */
    $trait = $this->getMockForTrait(LoggerTrait::class);
    $logger = $this->prophesize(LoggerInterface::class)->reveal();
    $trait->setLogger($logger);

    $this->assertNull($trait->log('debug', 'This is a message'));
});

test('logging accepts message with logger', function () {
    /** @var LoggerTrait $trait */
    $trait = $this->getMockForTrait(LoggerTrait::class);

    $this->assertNull($trait->log('debug', 'This is a message'));
});
