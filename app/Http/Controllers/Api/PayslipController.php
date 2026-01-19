<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use App\Services\PayslipService;
use Illuminate\Http\Request;

class PayslipController extends Controller
{
    protected $service;

    public function __construct(PayslipService $service)
    {
        $this->service = $service;
    }

    public function download(Payslip $payslip)
    {
        try {
            return $this->service->generatePdf($payslip);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 501);
        }
    }
}
