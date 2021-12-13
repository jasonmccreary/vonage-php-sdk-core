<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Vonage\SMS\Message\Vcard;

test('can create vcard message', function () {
    $card = 'BEGIN%3aVCARD%0d%0aVERSION%3a2.1%0d%0aFN%3aFull+Name%0d%0aTEL%3a%2b12345678%0d%0aEMAIL%3ainfo%40acm ' .
        'e.com%0d%0aURL%3awww.acme.com%0d%0aEND%3aVCARD';

    $data = (new Vcard(
        '447700900000',
        '16105551212',
        $card
    ))->toArray();

    expect($data['to'])->toBe('447700900000');
    expect($data['from'])->toBe('16105551212');
    expect($data['vcard'])->toBe($card);
});
