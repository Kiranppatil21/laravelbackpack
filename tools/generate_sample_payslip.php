<?php
// Simple script to generate a sample payslip PDF to /tmp/payslip-sample.pdf
// It boots the app, renders payslip HTML via PayslipService::renderHtml and uses Dompdf if available.

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payslip;
use App\Services\PayslipService;

// Create an in-memory Payslip model (not saved)
$p = new Payslip();
$p->id = 'sample';
$p->employee_id = 123;
$p->tenant_uuid = 'local-tenant';
$p->period_start = date('Y-m-01');
$p->period_end = date('Y-m-t');
$p->gross = 100000;
$p->net = 85000;
$p->breakdown = [
    'basic' => 50000,
    'hra' => 20000,
    'allowances' => 20000,
    'deductions' => 15000,
];

$service = new PayslipService();
$html = $service->renderHtml($p);

// Try using Dompdf directly
if (class_exists('\Dompdf\Dompdf')) {
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $output = $dompdf->output();
    $outPath = sys_get_temp_dir() . '/payslip-sample.pdf';
    file_put_contents($outPath, $output);
    echo "OK: wrote $outPath\n";
    exit(0);
}

// Fallback: try dompdf.wrapper from container
if (app()->bound('dompdf.wrapper')) {
    $pdf = app('dompdf.wrapper');
    $pdf->loadHTML($html);
    $outPath = sys_get_temp_dir() . '/payslip-sample.pdf';
    // Save by streaming and capturing output buffer
    ob_start();
    $pdf->stream();
    $contents = ob_get_clean();
    file_put_contents($outPath, $contents);
    echo "OK: wrote $outPath (via dompdf.wrapper)\n";
    exit(0);
}

fwrite(STDERR, "Dompdf not available. Please install barryvdh/laravel-dompdf or dompdf/dompdf\n");
exit(2);
