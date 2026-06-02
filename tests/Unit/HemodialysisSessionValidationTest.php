<?php

namespace Tests\Unit;

use App\Http\Controllers\HemodialysisSessionController;
use Tests\TestCase;

class HemodialysisSessionValidationTest extends TestCase
{
    public function test_validation_rules_include_required_session_fields(): void
    {
        $rules = HemodialysisSessionController::validationRules();

        $this->assertArrayHasKey('patient_id', $rules);
        $this->assertArrayHasKey('session_date', $rules);
        $this->assertArrayHasKey('diagnosis', $rules);
        $this->assertArrayHasKey('dialysis_schedule', $rules);
        $this->assertArrayHasKey('duration_minutes', $rules);
        $this->assertArrayHasKey('vascular_access_type', $rules);
        $this->assertArrayHasKey('pre_blood_pressure', $rules);
        $this->assertArrayHasKey('post_blood_pressure', $rules);
        $this->assertArrayHasKey('fluid_removed_ml', $rules);
        $this->assertArrayHasKey('blood_type', $rules);
        $this->assertArrayHasKey('dialyzer_type', $rules);
        $this->assertArrayHasKey('complications_notes', $rules);
        $this->assertArrayHasKey('status', $rules);
        $this->assertStringContainsString('av_fistula', $rules['vascular_access_type']);
    }
}
