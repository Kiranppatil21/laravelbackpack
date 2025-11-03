<?php
require __DIR__ . '/../vendor/autoload.php';
$c = new App\Services\PayrollCalculator();
$m = new ReflectionMethod(get_class($c), 'computeProfessionalTax');
$m->setAccessible(true);
$res = $m->invokeArgs($c, ['kerala', 13000, ['kerala' => ['threshold' => 12000, 'amount' => 150.0]]]);
var_export($res);
echo PHP_EOL;

// Also call compute()
$full = $c->compute(13000, 0, 0, ['state'=>'kerala', 'professional_tax_mapping' => ['kerala' => ['threshold' => 12000, 'amount' => 150.0]]]);
var_export($full);
echo PHP_EOL;