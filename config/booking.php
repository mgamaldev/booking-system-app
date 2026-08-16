<?php

return [
    'lock' => [
        'wait_seconds' => env('BOOKING_LOCK_WAIT_SECONDS', 1),
        'ttl_seconds' => env('BOOKING_LOCK_TTL_SECONDS', 5),
    ],
];
