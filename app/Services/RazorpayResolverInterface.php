<?php

namespace App\Services;

interface RazorpayResolverInterface
{
    /**
     * Resolve and return a Razorpay API instance or null when unavailable.
     *
     * @return mixed|null
     */
    public function resolve();
}
