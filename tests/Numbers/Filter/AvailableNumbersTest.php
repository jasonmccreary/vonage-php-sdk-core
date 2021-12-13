<?php

use InvalidArgumentException;
use Vonage\Numbers\Number;
use VonageTest\VonageTestCase;
use Vonage\Numbers\Filter\AvailableNumbers;

uses(VonageTestCase::class);

test('can set valid number type', function (string $type) {
    $filter = new AvailableNumbers();
    $filter->setType($type);

    $this->assertSame($type, $filter->getType());
})->with('numberTypes');

test('invalid type throws exception', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid type of number');

    $filter = new AvailableNumbers();
    $filter->setType('foo-bar');
});

// Datasets
/**
 * List of valid number types that can be searched on
 * 
 * @return array<array<string>>
 */
dataset('numberTypes', [
    [Number::TYPE_FIXED],
    [Number::TYPE_MOBILE],
    [Number::TYPE_TOLLFREE]
]);
