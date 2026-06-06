<?php

namespace Tests\Unit;

use App\Http\Controllers\NephrologyRegistrationController;
use App\Rules\NephrologyDisease;
use Tests\TestCase;

class NephrologyRegistrationValidationTest extends TestCase
{
    public function test_clinical_validation_rules_include_required_fields(): void
    {
        $rules = NephrologyRegistrationController::clinicalValidationRules();

        $this->assertArrayHasKey('visit_date', $rules);
        $this->assertArrayHasKey('chief_complaint', $rules);
        $this->assertArrayHasKey('diagnosis', $rules);
        $this->assertArrayHasKey('disease_id', $rules);
        $this->assertArrayHasKey('ckd_aki_stage', $rules);
        $this->assertArrayHasKey('dialysis_required', $rules);
        $this->assertArrayHasKey('dialysis_type', $rules);
        $this->assertArrayHasKey('access_type', $rules);
        $this->assertArrayHasKey('follow_up_plan', $rules);
        $this->assertArrayNotHasKey('lab_creatinine', $rules);
        $this->assertStringContainsString('HD,PD,CRRT', $rules['dialysis_type']);
        $this->assertStringContainsString('av_fistula', $rules['access_type']);
        $this->assertInstanceOf(NephrologyDisease::class, $rules['disease_id'][2]);
    }

    public function test_normalize_visit_date_accepts_iso_gregorian_format(): void
    {
        $normalized = NephrologyRegistrationController::normalizeVisitDate('2026-06-03');

        $this->assertSame('2026-06-03', $normalized);
    }

    public function test_apply_clinical_defaults_clears_dialysis_fields_when_not_required(): void
    {
        $request = \Illuminate\Http\Request::create('/', 'POST', [
            'dialysis_required' => '0',
        ]);

        $result = NephrologyRegistrationController::applyClinicalDefaults([
            'dialysis_required' => false,
            'dialysis_type' => 'HD',
            'access_type' => 'catheter',
        ], $request);

        $this->assertFalse($result['dialysis_required']);
        $this->assertNull($result['dialysis_type']);
        $this->assertNull($result['access_type']);
    }
}
