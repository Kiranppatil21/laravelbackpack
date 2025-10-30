<?php

namespace Tests\Unit;

use App\Services\QRValidator;
use PHPUnit\Framework\TestCase;

class QRValidatorTest extends TestCase
{
    public function test_accepts_non_empty_code_when_no_list()
    {
        $v = new QRValidator();
        $this->assertTrue($v->isValid('abc123'));
        $this->assertFalse($v->isValid(''));
    }

    public function test_checks_against_valid_list()
    {
        $v = new QRValidator(['ok1','ok2']);
        $this->assertTrue($v->isValid('ok1'));
        $this->assertFalse($v->isValid('nope'));
    }
}
