<?php
require __DIR__ . '/../vendor/autoload.php';

class X extends \App\Services\PayrollCalculator {
    public function probe($state, $gross) {
        return $this->computeProfessionalTax($state, $gross);
    }
}

$x = new X();
var_export($x->probe('kerala', 13000));
