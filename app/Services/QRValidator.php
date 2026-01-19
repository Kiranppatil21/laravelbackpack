<?php

namespace App\Services;

class QRValidator
{
    protected $validCodes;

    public function __construct(array $validCodes = [])
    {
        // validCodes could be loaded from DB in real implementation
        $this->validCodes = $validCodes;
    }

    public function isValid(string $code): bool
    {
        if (empty($this->validCodes)) {
            // fallback: accept non-empty codes for scaffold
            return ! empty($code);
        }

        return in_array($code, $this->validCodes, true);
    }
}
