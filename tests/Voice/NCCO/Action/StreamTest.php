<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Vonage\Voice\NCCO\Action\Stream;

test('simple setup', function () {
    $this->assertSame([
        'action' => 'stream',
        'streamUrl' => ['https://test.domain/music.mp3']
    ], (new Stream('https://test.domain/music.mp3'))->toNCCOArray());
});

test('json serialize looks correct', function () {
    $this->assertSame([
        'action' => 'stream',
        'streamUrl' => ['https://test.domain/music.mp3'],
        'bargeIn' => 'false',
        'level' => '0',
        'loop' => '1',
    ], (new Stream('https://test.domain/music.mp3'))
        ->setBargeIn(false)
        ->setLevel(0)
        ->setLoop(1)
        ->jsonSerialize());
});
