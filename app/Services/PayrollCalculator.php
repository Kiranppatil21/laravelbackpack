<?php

namespace App\Services;

class PayrollCalculator
{
    /**
     * Compute payroll with options for Indian tax regimes and statutory deductions.
     *
     * Options supported:
     * - regime: 'old' or 'new' (defaults to 'old')
     * - include_epf: bool (defaults to true) — whether to deduct employee EPF (12% of basic)
     * - basic_percent: float (0-1) portion of gross counted as 'basic' for EPF (defaults to 0.4)
     * - state: string|null for professional tax rules (simple mapping)
     *
     * Inputs are monthly amounts (base salary, allowances, deductions).
     * We annualize by multiplying by 12 and compute annual tax then convert back to monthly.
     *
     * @param float $baseSalary Monthly base salary
     * @param float $allowances Monthly allowances
     * @param float $deductions Monthly other deductions (before EPF/prof tax)
     * @param array $options
     * @return array [monthlyGross, monthlyNet, breakdown]
     */
    public function compute(float $baseSalary, float $allowances = 0.0, float $deductions = 0.0, array $options = []): array
    {
    // Normalize incoming options

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
        // Ensure computeProfessionalTax receives $options so overrides can be used
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
        // Safely attempt to read config mapping; if Laravel's config() isn't usable (plain PHPUnit runs), try loading the file directly
        $configMapping = [];
        if (function_exists('config')) {
            try {
                // config/payroll.php uses the key 'professional_tax'
                $configMapping = config('payroll.professional_tax', []);
            } catch (\Throwable $e) {
                $configMapping = [];
            }
        }

        if (empty($configMapping)) {
            // Try to load config file directly from the repository when running outside a full Laravel bootstrap
            $projectRoot = dirname(__DIR__, 2); // app/Services -> app -> project root
            $configFile = $projectRoot . '/config/payroll.php';
            if (file_exists($configFile)) {
                try {
                    $cfg = include $configFile;
                    if (is_array($cfg) && isset($cfg['professional_tax']) && is_array($cfg['professional_tax'])) {
                        $configMapping = $cfg['professional_tax'];
                    }
                } catch (\Throwable $e) {
                    // ignore and leave mapping empty
                }
            }
        }

    // no-op: professional tax decision will be based on options override, config mapping, or legacy rules

        $s = $state ? strtolower(trim($state)) : null;

        // 1) override mapping
        if (is_array($mappingOverride) && $s && isset($mappingOverride[$s])) {
            $entry = $mappingOverride[$s];
            $threshold = $entry['threshold'] ?? null;
            $amount = $entry['amount'] ?? null;
            // using override for {$s}: threshold={$threshold}, amount={$amount}
            if ($threshold !== null && $amount !== null && $monthlyGross > $threshold) {
                return (float) $amount;
            }
            return 0.0;
        }

        // 2) config mapping
        if (is_array($configMapping) && $s && isset($configMapping[$s])) {
            $entry = $configMapping[$s];
            $threshold = $entry['threshold'] ?? null;
            $amount = $entry['amount'] ?? null;
            // using config for {$s}: threshold={$threshold}, amount={$amount}
            if ($threshold !== null && $amount !== null && $monthlyGross > $threshold) {
                return (float) $amount;
            }
            return 0.0;
        }

        // 3) legacy fallback rules
        if (! $s) return 0.0;
        switch ($s) {
            case 'maharashtra':
                return $monthlyGross > 15000 ? 200.0 : 0.0;
            case 'karnataka':
            case 'tamil nadu':
                return $monthlyGross > 10000 ? 200.0 : 0.0;
            default:
                return 0.0;
        }
    }
}
