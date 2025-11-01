<?php

namespace App\Services;

use App\Models\Payslip;
use Illuminate\Support\Facades\View;

class PayslipService
{
    /**
     * Render payslip HTML from Blade template.
     */
    public function renderHtml(Payslip $payslip): string
    {
        // Use a simple blade view to render the payslip HTML
        if (! View::exists('payslips.template')) {
            // fallback minimal HTML
            return "<html><body><h1>Payslip #{$payslip->id}</h1><p>Employee: {$payslip->employee_id}</p></body></html>";
        }

        return View::make('payslips.template', ['payslip' => $payslip])->render();
    }

    /**
     * Generate PDF stream using DOMPDF (if available).
     *
     * @throws \RuntimeException if Dompdf is not installed/configured
     */
    public function generatePdf(Payslip $payslip, string $filename = null)
    {
        $html = $this->renderHtml($payslip);

        // barryvdh/laravel-dompdf binds 'dompdf.wrapper'
        if (app()->bound('dompdf.wrapper')) {
            $pdf = app('dompdf.wrapper');
            $pdf->loadHTML($html);
            $fname = $filename ?? sprintf('payslip-%s.pdf', $payslip->id);
            return $pdf->stream($fname);
        }

        // If dompdf is not installed, try to detect native Dompdf class
        if (class_exists('\Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->render();
            $pdfOutput = $dompdf->output();
            $fname = $filename ?? sprintf('payslip-%s.pdf', $payslip->id);
            // Return a Symfony response-like array (controller will adapt)
            return response($pdfOutput, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$fname.'"',
            ]);
        }

        throw new \RuntimeException('Dompdf is not installed. Please run: composer require barryvdh/laravel-dompdf');
    }
}
