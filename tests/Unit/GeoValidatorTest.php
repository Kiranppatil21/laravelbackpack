<?php

namespace Tests\Unit;

use App\Services\GeoValidator;
use PHPUnit\Framework\TestCase;

class GeoValidatorTest extends TestCase
{
    public function test_within_radius()
    {
        $g = new GeoValidator(200); // 200m
        // Two very close points
        $lat1 = 12.9715987; $lon1 = 77.594566;
        $lat2 = 12.9717; $lon2 = 77.5946;
        $this->assertTrue($g->isWithinRadius($lat1,$lon1,$lat2,$lon2));
    }

    public function test_outside_radius()
    {
        $g = new GeoValidator(10); // 10m
        $lat1 = 12.9715987; $lon1 = 77.594566;
        $lat2 = 12.9725; $lon2 = 77.5955;
        $this->assertFalse($g->isWithinRadius($lat1,$lon1,$lat2,$lon2));
    }
}
