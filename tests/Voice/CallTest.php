<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Voice\Call;



/**
 * @throws Exception
 */
test('converts to array properly', function () {
    $data = json_decode(file_get_contents(__DIR__ . '/responses/call.json'), true);
    $call = new Call($data);
    $callData = $call->toArray();

    expect($callData['uuid'])->toEqual($data['uuid']);
    expect($callData['status'])->toEqual($data['status']);
    expect($callData['direction'])->toEqual($data['direction']);
    expect($callData['rate'])->toEqual($data['rate']);
    expect($callData['price'])->toEqual($data['price']);
    expect($callData['duration'])->toEqual($data['duration']);
    expect($callData['start_time'])->toEqual($data['start_time']);
    expect($callData['end_time'])->toEqual($data['end_time']);
    expect($callData['network'])->toEqual($data['network']);
    expect($callData['to'][0]['type'])->toEqual($data['to'][0]['type']);
    expect($callData['to'][0]['number'])->toEqual($data['to'][0]['number']);
    expect($callData['from'][0]['type'])->toEqual($data['from'][0]['type']);
    expect($callData['from'][0]['number'])->toEqual($data['from'][0]['number']);
});
