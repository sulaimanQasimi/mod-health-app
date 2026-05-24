<?php

namespace Tests\Unit;

use App\Http\Controllers\NephrologyRegistrationController;
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
        $this->assertArrayHasKey('lab_creatinine', $rules);
        $this->assertArrayHasKey('follow_up_plan', $rules);
        $this->assertStringContainsString('HD,PD,CRRT', $rules['dialysis_type']);
        $this->assertStringContainsString('av_fistula', $rules['access_type']);
    }
}
