<?php

namespace App\Services;

/**
 * Temporary debug variant of PayrollCalculator used for reproducing mapping override behavior in tests.
 * This is intentionally self-contained so the PHPUnit run can exercise the override logic and write
 * debug traces to /tmp for inspection.
 */
class PayrollCalculatorDebug
{
    public function compute(float $baseSalary, float $allowances = 0.0, float $deductions = 0.0, array $options = []): array
    {
        @file_put_contents('/tmp/prof_tax_debug.log', "PayrollCalculatorDebug::compute called with options: " . json_encode($options) . PHP_EOL, FILE_APPEND);

        $regime = $options['regime'] ?? 'old';
        $includeEpf = $options['include_epf'] ?? true;
        $basicPercent = $options['basic_percent'] ?? 0.4;
        $state = $options['state'] ?? null;

        $monthlyGross = round($baseSalary + $allowances, 2);
        $annualGross = $monthlyGross * 12;
        $annualDeductions = $deductions * 12;

        $standardDeduction = 50000;
        $taxableAnnual = max(0, $annualGross - $standardDeduction - $annualDeductions);

        if ($regime === 'new') {
            $annualTaxBeforeCess = $this->computeAnnualIndianTaxNewRegime($taxableAnnual);
        } else {
            $annualTaxBeforeCess = $this->computeAnnualIndianTax($taxableAnnual);
        }

        $rebate = 0;
        if ($taxableAnnual <= 500000) {
            $rebate = min($annualTaxBeforeCess, 12500);
        }

        $annualTaxAfterRebate = max(0, $annualTaxBeforeCess - $rebate);
        $cess = round($annualTaxAfterRebate * 0.04, 2);
        $annualTaxTotal = round($annualTaxAfterRebate + $cess, 2);
        $monthlyTax = round($annualTaxTotal / 12, 2);

        $basic = $monthlyGross * $basicPercent;
        $epf = $includeEpf ? round($basic * 0.12, 2) : 0.0;
        $professionalTax = $this->computeProfessionalTax($state, $monthlyGross, $options);

        $monthlyNet = round($monthlyGross - $monthlyTax - $deductions - $epf - $professionalTax, 2);

        $breakdown = [
            'base' => round($baseSalary, 2),
            'allowances' => round($allowances, 2),
            'monthly_gross' => $monthlyGross,
            'annual_gross' => round($annualGross, 2),
            'standard_deduction' => $standardDeduction,
            'annual_deductions' => round($annualDeductions, 2),
            'taxable_annual' => round($taxableAnnual, 2),
            'annual_tax_before_rebate' => round($annualTaxBeforeCess, 2),
            'rebate' => round($rebate, 2),
            'cess' => $cess,
            'annual_tax' => $annualTaxTotal,
            'monthly_tax' => $monthlyTax,
            'deductions_monthly' => round($deductions, 2),
            'epf_monthly' => $epf,
            'professional_tax_monthly' => $professionalTax,
            'net_monthly' => $monthlyNet,
            'regime' => $regime,
        ];

        return [$monthlyGross, $monthlyNet, $breakdown];
    }

    protected function computeAnnualIndianTax(float $taxableAnnual): float
    {
        $tax = 0.0;
        if ($taxableAnnual <= 250000) return 0.0;
        $remaining = $taxableAnnual - 250000;

        $slab = min(250000, $remaining);
        $tax += $slab * 0.05;
        $remaining -= $slab;
        if ($remaining <= 0) return round($tax, 2);

        $slab = min(500000, $remaining);
        $tax += $slab * 0.20;
        $remaining -= $slab;
        if ($remaining <= 0) return round($tax, 2);

        $tax += $remaining * 0.30;
        return round($tax, 2);
    }

    protected function computeAnnualIndianTaxNewRegime(float $taxableAnnual): float
    {
        $tax = 0.0;
        if ($taxableAnnual <= 250000) return 0.0;
        $remaining = $taxableAnnual - 250000;
        $bands = [250000 => 0.05, 250000 => 0.10, 250000 => 0.15, 250000 => 0.20, 250000 => 0.25, PHP_INT_MAX => 0.30];
        foreach ($bands as $bandAmount => $rate) {
            $take = min($bandAmount, $remaining);
            $tax += $take * $rate;
            $remaining -= $take;
            if ($remaining <= 0) break;
        }
        return round($tax, 2);
    }

    protected function computeProfessionalTax(?string $state, float $monthlyGross, array $options = []): float
    {
        $mappingOverride = $options['professional_tax_mapping'] ?? null;

        $line = "computeProfessionalTax Debug called with state=" . var_export($state, true) . " monthlyGross=" . var_export($monthlyGross, true) . " mappingOverride=" . json_encode($mappingOverride) . PHP_EOL;
        @file_put_contents('/tmp/prof_tax_debug.log', $line, FILE_APPEND);

        $s = $state ? strtolower(trim($state)) : null;
        if (is_array($mappingOverride) && $s && isset($mappingOverride[$s])) {
            $entry = $mappingOverride[$s];
            $threshold = $entry['threshold'] ?? null;
            $amount = $entry['amount'] ?? null;
            $line = "using override for {$s}: threshold={$threshold}, amount={$amount}\n";
            @file_put_contents('/tmp/prof_tax_debug.log', $line, FILE_APPEND);
            if ($threshold !== null && $amount !== null && $monthlyGross > $threshold) {
                return (float) $amount;
            }
            return 0.0;
        }

        return 0.0;
    }
}
