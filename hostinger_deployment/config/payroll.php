<?php

return [
    // Simplified professional tax table used by PayrollCalculator.
    // Key is lowercase state name or code, values provide threshold and amount.
    // These are pragmatic approximations for demo/testing; replace with
    // authoritative per-state tables for production.
    'professional_tax' => [
        'maharashtra' => ['threshold' => 15000, 'amount' => 200.0],
        'mh' => ['threshold' => 15000, 'amount' => 200.0],
        'karnataka' => ['threshold' => 10000, 'amount' => 200.0],
        'ka' => ['threshold' => 10000, 'amount' => 200.0],
        'tamil nadu' => ['threshold' => 10000, 'amount' => 200.0],
        'tn' => ['threshold' => 10000, 'amount' => 200.0],
        'kerala' => ['threshold' => 12000, 'amount' => 150.0],
        'kl' => ['threshold' => 12000, 'amount' => 150.0],
        'west bengal' => ['threshold' => 15000, 'amount' => 110.0],
        'wb' => ['threshold' => 15000, 'amount' => 110.0],
        'delhi' => ['threshold' => 10000, 'amount' => 200.0],
        'dl' => ['threshold' => 10000, 'amount' => 200.0],
    ],
];
