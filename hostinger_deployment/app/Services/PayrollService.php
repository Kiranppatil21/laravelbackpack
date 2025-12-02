<?php

namespace App\Services;

class PayrollService
{
    /**
     * Compute a simple payslip breakdown.
     *
     * @param float $baseSalary
     * @param float $allowances
     * @param float $deductions
     * @return array [gross, net, breakdown]
     */
    public function compute(float $baseSalary, float $allowances = 0.0, float $deductions = 0.0): array
    {
        $gross = $baseSalary + $allowances;
        $tax = $this->estimateTax($gross);
        $net = $gross - $tax - $deductions;

        $breakdown = [
            'base' => round($baseSalary, 2),
            'allowances' => round($allowances, 2),
            'gross' => round($gross, 2),
            'tax' => round($tax, 2),
            'deductions' => round($deductions, 2),
            'net' => round($net, 2),
        ];

        return [$gross, $net, $breakdown];
    }

    protected function estimateTax(float $gross): float
    {
        // Very simplistic progressive tax bands for demo purposes.
        if ($gross <= 25000) {
            return $gross * 0.05;
        }

        if ($gross <= 50000) {
            return $gross * 0.1;
        }

        return $gross * 0.15;
    }
}
