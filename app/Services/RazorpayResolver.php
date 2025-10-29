<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class RazorpayResolver implements RazorpayResolverInterface
{
    /**
     * Resolve a Razorpay API instance.
     *
     * Preference order:
     * 1. Container bound keys (several common names)
     * 2. Instantiate \Razorpay\Api\Api with RAZORPAY_KEY_ID/RAZORPAY_KEY_SECRET if present
     *
     * @return mixed|null
     */
    public function resolve()
    {
        $boundKeys = ['Razorpay\\Api\\Api', '\\Razorpay\\Api\\Api', 'razorpay.api', 'razorpay'];

        foreach ($boundKeys as $key) {
            try {
                if (app()->bound($key)) {
                    return app()->make($key);
                }
            } catch (\Throwable $t) {
                // ignore and continue
            }
        }

        if (class_exists('Razorpay\\Api\\Api')) {
            try {
                // prefer config values (phpstan-friendly)
                $keyId = config('services.razorpay.key_id');
                $keySecret = config('services.razorpay.key_secret');
                if ($keyId && $keySecret) {
                    return new \Razorpay\Api\Api($keyId, $keySecret);
                }
            } catch (\Throwable $t) {
                Log::error('Failed to instantiate Razorpay Api in resolver: '.$t->getMessage());
            }
        }

        return null;
    }
}
