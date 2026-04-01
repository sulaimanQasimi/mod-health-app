<?php

namespace Tests\Unit;

use App\Models\BloodBank;
use App\Models\BloodUnit;
use App\Services\BloodCrossmatchService;
use Tests\TestCase;

class BloodCrossmatchServiceTest extends TestCase
{
    public function test_abo_compatibility_matrix(): void
    {
        $service = new BloodCrossmatchService();

        $this->assertTrue($service->isAboCompatible('A', 'O'));
        $this->assertTrue($service->isAboCompatible('AB', 'B'));
        $this->assertFalse($service->isAboCompatible('O', 'A'));
    }

    public function test_rh_compatibility_matrix(): void
    {
        $service = new BloodCrossmatchService();

        $this->assertTrue($service->isRhCompatible('+', '+'));
        $this->assertTrue($service->isRhCompatible('+', '-'));
        $this->assertTrue($service->isRhCompatible('-', '-'));
        $this->assertFalse($service->isRhCompatible('-', '+'));
    }

    public function test_evaluate_returns_incompatible_when_lab_is_incompatible(): void
    {
        $service = new BloodCrossmatchService();

        $request = new BloodBank(['group' => 'A', 'rh' => '+', 'type' => 'RBC']);
        $unit = new BloodUnit(['blood_group' => 'A', 'rh' => '+', 'component_type' => 'RBC']);

        $result = $service->evaluateCompatibility($request, $unit, [
            'major_result' => 'incompatible',
            'minor_result' => 'compatible',
        ]);

        $this->assertSame('incompatible', $result['status']);
    }

    public function test_evaluate_returns_compatible_when_rules_pass(): void
    {
        $service = new BloodCrossmatchService();

        $request = new BloodBank(['group' => 'AB', 'rh' => '+', 'type' => 'RBC']);
        $unit = new BloodUnit(['blood_group' => 'O', 'rh' => '-', 'component_type' => 'RBC']);

        $result = $service->evaluateCompatibility($request, $unit, [
            'major_result' => 'compatible',
            'minor_result' => 'compatible',
        ]);

        $this->assertSame('compatible', $result['status']);
    }
}
