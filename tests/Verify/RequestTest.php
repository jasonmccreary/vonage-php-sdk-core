<?php

use InvalidArgumentException;
use VonageTest\VonageTestCase;
use Vonage\Verify\Request;

uses(VonageTestCase::class);

test('invalid data', function (string $expectedMessage, string $brand, string $method, $data) {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage($expectedMessage);

    (new Request('14845551212', $brand))
        ->$method($data);
})->with('invalidData');

test('can set code length', function () {
    $request = new Request(14845551212, 'Vonage');

    $request->setCodeLength(4);
    $this->assertSame(4, $request->getCodeLength());

    $request->setCodeLength(6);
    $this->assertSame(6, $request->getCodeLength());
});

// Datasets
dataset('invalidData', [
    [
        'Country must be in two character format',
        'Test Invalid Country',
        'setCountry',
        'GER'
    ],
    [
        sprintf('Pin length must be either %d or %d digits', Request::PIN_LENGTH_4, Request::PIN_LENGTH_6),
        'Test Invalid Code Length',
        'setCodeLength',
        123
    ],
    [
        'Pin expiration must be between 60 and 3600 seconds',
        'Test Invalid Pin Expiry',
        'setPinExpiry',
        30
    ],
    [
        'Next Event time must be between 60 and 900 seconds',
        'Test Invalid Next Event Wait',
        'setNextEventWait',
        30
    ],
    [
        'Workflow ID must be from 1 to 7',
        'Test Invalid Invalid Workflow Id',
        'setWorkflowId',
        123
    ],
]);
