<?php

namespace App\Services;

class PayrollCalculatorWrapper extends PayrollCalculator
{
    /**
     * Override compute so we can honor a professional_tax_mapping option (or config) deterministically
     * while keeping the parent tax logic intact.
     */
    public function compute(float $baseSalary, float $allowances = 0.0, float $deductions = 0.0, array $options = []): array
    {
        [$monthlyGross, $monthlyNet, $breakdown] = parent::compute($baseSalary, $allowances, $deductions, $options);

        $state = $options['state'] ?? $options['state'] ?? null;
        $mapping = $options['professional_tax_mapping'] ?? config('payroll.professional_tax', []);

        if ($state && is_array($mapping)) {
            $s = strtolower(trim($state));
            if (isset($mapping[$s])) {
                $entry = $mapping[$s];
                $threshold = $entry['threshold'] ?? null;
                $amount = $entry['amount'] ?? null;
                if ($threshold !== null && $amount !== null) {
                    $newProf = ($monthlyGross > $threshold) ? (float) $amount : 0.0;
                    $oldProf = $breakdown['professional_tax_monthly'] ?? 0.0;
                    if ($newProf !== $oldProf) {
                        $breakdown['professional_tax_monthly'] = $newProf;
                        // adjust net accordingly
                        $breakdown['net_monthly'] = round(($breakdown['net_monthly'] ?? $monthlyNet) + ($oldProf - $newProf), 2);
                        $monthlyNet = $breakdown['net_monthly'];
                    }
                }
            }
        }

        return [$monthlyGross, $monthlyNet, $breakdown];
    }
}
