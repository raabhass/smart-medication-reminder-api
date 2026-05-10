<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\DoseEvent;
use App\Models\MedicationSchedule;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiRequirementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->create(['role' => 'caregiver']);
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/auth/me', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.email', 'updated@example.com');
    }

    public function test_patient_registration_creates_linked_patient_and_returns_patient_id(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Jenny Patient',
            'email' => 'jenny@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'patient',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.role', 'patient')
            ->assertJsonPath('user.name', 'Jenny Patient')
            ->assertJsonStructure(['user' => ['patient_id']]);

        $patientId = $response->json('user.patient_id');
        $this->assertNotNull($patientId);
        $this->assertDatabaseHas('patients', [
            'id' => $patientId,
            'full_name' => 'Jenny Patient',
            'status' => 'stable',
        ]);
    }

    public function test_patient_login_repairs_missing_patient_record_and_returns_patient_id(): void
    {
        $user = User::factory()->create([
            'name' => 'Jenny Patient',
            'email' => 'jenny@example.com',
            'password' => 'password',
            'role' => 'patient',
        ]);

        $this->assertDatabaseMissing('patients', ['user_id' => $user->id]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'jenny@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.role', 'patient')
            ->assertJsonStructure(['user' => ['patient_id']]);

        $patientId = $response->json('user.patient_id');
        $this->assertNotNull($patientId);
        $this->assertDatabaseHas('patients', [
            'id' => $patientId,
            'user_id' => $user->id,
            'full_name' => 'Jenny Patient',
            'status' => 'stable',
        ]);
    }

    public function test_patients_are_scoped_to_authenticated_user_role(): void
    {
        $caregiver = User::factory()->create(['role' => 'caregiver']);
        $otherCaregiver = User::factory()->create(['role' => 'caregiver']);
        $patientUser = User::factory()->create(['role' => 'patient']);

        $ownedPatient = Patient::create([
            'created_by_user_id' => $caregiver->id,
            'full_name' => 'Owned Patient',
            'status' => 'stable',
        ]);
        Patient::create([
            'created_by_user_id' => $otherCaregiver->id,
            'full_name' => 'Other Patient',
            'status' => 'stable',
        ]);
        $linkedPatient = Patient::create([
            'user_id' => $patientUser->id,
            'full_name' => 'Linked Patient',
            'status' => 'stable',
        ]);

        Sanctum::actingAs($caregiver);
        $this->getJson('/api/patients?per_page=50')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownedPatient->id);

        Sanctum::actingAs($patientUser);
        $this->getJson('/api/patients?per_page=50')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $linkedPatient->id);
    }

    public function test_patient_responses_include_emergency_contact_fields(): void
    {
        $caregiver = User::factory()->create(['role' => 'caregiver']);
        $patient = Patient::create([
            'created_by_user_id' => $caregiver->id,
            'full_name' => 'Visible Patient',
            'status' => 'stable',
            'emergency_contact_name' => 'Stella Contact',
            'emergency_contact_phone' => '555-0100',
            'emergency_contact_relationship' => 'Sister',
        ]);

        Sanctum::actingAs($caregiver);

        $this->getJson("/api/patients/{$patient->id}")
            ->assertOk()
            ->assertJsonPath('data.emergency_contact_name', 'Stella Contact')
            ->assertJsonPath('data.emergency_contact_phone', '555-0100')
            ->assertJsonPath('data.emergency_contact_relationship', 'Sister');

        $this->patchJson("/api/patients/{$patient->id}", [
            'emergency_contact_name' => 'Updated Contact',
            'emergency_contact_phone' => '555-0199',
            'emergency_contact_relationship' => 'Mother',
        ])->assertOk()
            ->assertJsonPath('data.emergency_contact_name', 'Updated Contact')
            ->assertJsonPath('data.emergency_contact_phone', '555-0199')
            ->assertJsonPath('data.emergency_contact_relationship', 'Mother');
    }

    public function test_medication_schedule_updates_are_scoped_to_the_route_patient(): void
    {
        $caregiver = User::factory()->create(['role' => 'caregiver']);
        $patient = Patient::create([
            'created_by_user_id' => $caregiver->id,
            'full_name' => 'Owned Patient',
            'status' => 'stable',
        ]);
        $otherPatient = Patient::create([
            'created_by_user_id' => $caregiver->id,
            'full_name' => 'Other Patient',
            'status' => 'stable',
        ]);
        $schedule = MedicationSchedule::create([
            'patient_id' => $otherPatient->id,
            'medication_name' => 'Aspirin',
            'dosage' => '75 mg',
            'frequency' => 'daily',
            'scheduled_time' => '08:00:00',
            'start_date' => now()->toDateString(),
        ]);

        Sanctum::actingAs($caregiver);

        $this->putJson("/api/patients/{$patient->id}/medication-schedules/{$schedule->id}", [
            'medication_name' => 'Updated Aspirin',
        ])->assertNotFound();
    }

    public function test_medication_history_is_scoped_and_includes_patient_name(): void
    {
        $caregiver = User::factory()->create(['role' => 'caregiver']);
        $otherCaregiver = User::factory()->create(['role' => 'caregiver']);
        $patient = Patient::create([
            'created_by_user_id' => $caregiver->id,
            'full_name' => 'Visible Patient',
            'status' => 'stable',
        ]);
        $otherPatient = Patient::create([
            'created_by_user_id' => $otherCaregiver->id,
            'full_name' => 'Hidden Patient',
            'status' => 'stable',
        ]);
        $schedule = $this->scheduleFor($patient);
        $otherSchedule = $this->scheduleFor($otherPatient);

        DoseEvent::create([
            'patient_id' => $patient->id,
            'medication_schedule_id' => $schedule->id,
            'status' => 'missed',
            'event_time' => now(),
        ]);
        DoseEvent::create([
            'patient_id' => $otherPatient->id,
            'medication_schedule_id' => $otherSchedule->id,
            'status' => 'skipped',
            'event_time' => now(),
        ]);

        Sanctum::actingAs($caregiver);

        $this->getJson('/api/medication-history?per_page=50')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.patient_name', 'Visible Patient');
    }

    public function test_schedule_response_includes_refill_and_provider_fields(): void
    {
        $caregiver = User::factory()->create(['role' => 'caregiver']);
        $patient = Patient::create([
            'created_by_user_id' => $caregiver->id,
            'full_name' => 'Visible Patient',
            'status' => 'stable',
        ]);
        $schedule = $this->scheduleFor($patient, [
            'doctor_name' => 'Dr. Stone',
            'hospital_name' => 'City Clinic',
            'remaining_pills' => 12,
            'refill_date' => '2026-06-01',
        ]);

        Sanctum::actingAs($caregiver);

        $this->getJson("/api/patients/{$patient->id}/medication-schedules")
            ->assertOk()
            ->assertJsonPath('data.0.id', $schedule->id)
            ->assertJsonPath('data.0.doctor_name', 'Dr. Stone')
            ->assertJsonPath('data.0.hospital_name', 'City Clinic')
            ->assertJsonPath('data.0.remaining_pills', 12)
            ->assertJsonPath('data.0.refill_date', '2026-06-01');
    }

    public function test_dose_event_accepts_expected_statuses_and_returns_id(): void
    {
        $caregiver = User::factory()->create(['role' => 'caregiver']);
        $patient = Patient::create([
            'created_by_user_id' => $caregiver->id,
            'full_name' => 'Visible Patient',
            'status' => 'stable',
        ]);
        $schedule = $this->scheduleFor($patient);

        Sanctum::actingAs($caregiver);

        foreach (['taken', 'missed', 'skipped'] as $status) {
            $this->postJson('/api/dose-events', [
                'patient_id' => $patient->id,
                'medication_schedule_id' => $schedule->id,
                'status' => $status,
                'event_time' => now()->toDateTimeString(),
            ])->assertCreated()
                ->assertJsonStructure(['data' => ['id']])
                ->assertJsonPath('data.status', $status);
        }
    }

    public function test_taken_dose_event_status_is_normalized_and_visible_in_history(): void
    {
        $caregiver = User::factory()->create(['role' => 'caregiver']);
        $patient = Patient::create([
            'created_by_user_id' => $caregiver->id,
            'full_name' => 'Visible Patient',
            'status' => 'stable',
        ]);
        $schedule = $this->scheduleFor($patient);

        Sanctum::actingAs($caregiver);

        $this->postJson('/api/dose-events', [
            'patient_id' => $patient->id,
            'medication_schedule_id' => $schedule->id,
            'status' => 'Taken',
            'event_time' => now()->toDateTimeString(),
        ])->assertCreated()
            ->assertJsonPath('data.patient_name', 'Visible Patient')
            ->assertJsonPath('data.status', 'taken');

        $this->getJson('/api/medication-history?status=Taken&per_page=50')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.patient_name', 'Visible Patient')
            ->assertJsonPath('data.0.status', 'taken');
    }

    public function test_medication_history_returns_patient_name_for_soft_deleted_patient(): void
    {
        $caregiver = User::factory()->create(['role' => 'caregiver']);
        $patient = Patient::create([
            'created_by_user_id' => $caregiver->id,
            'full_name' => 'Deleted Patient',
            'status' => 'stable',
        ]);
        $schedule = $this->scheduleFor($patient);

        DoseEvent::create([
            'patient_id' => $patient->id,
            'medication_schedule_id' => $schedule->id,
            'status' => 'taken',
            'event_time' => now(),
        ]);
        $patient->delete();

        Sanctum::actingAs($caregiver);

        $this->getJson('/api/medication-history?per_page=50')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.patient_name', 'Deleted Patient')
            ->assertJsonPath('data.0.status', 'taken');
    }

    public function test_alerts_are_scoped_and_paginated(): void
    {
        $caregiver = User::factory()->create(['role' => 'caregiver']);
        $otherCaregiver = User::factory()->create(['role' => 'caregiver']);
        $patient = Patient::create([
            'created_by_user_id' => $caregiver->id,
            'full_name' => 'Visible Patient',
            'status' => 'stable',
        ]);
        $otherPatient = Patient::create([
            'created_by_user_id' => $otherCaregiver->id,
            'full_name' => 'Hidden Patient',
            'status' => 'stable',
        ]);

        Alert::create([
            'patient_id' => $patient->id,
            'type' => 'missed_dose',
            'message' => 'Visible alert',
            'alert_time' => now(),
        ]);
        Alert::create([
            'patient_id' => $otherPatient->id,
            'type' => 'missed_dose',
            'message' => 'Hidden alert',
            'alert_time' => now(),
        ]);

        Sanctum::actingAs($caregiver);

        $this->getJson('/api/alerts?per_page=50')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.message', 'Visible alert')
            ->assertJsonPath('meta.per_page', 50);
    }

    public function test_dashboard_summary_matches_authenticated_caregiver_patients(): void
    {
        $caregiver = User::factory()->create(['role' => 'caregiver']);
        $otherCaregiver = User::factory()->create(['role' => 'caregiver']);
        $patient = Patient::create([
            'created_by_user_id' => $caregiver->id,
            'full_name' => 'Visible Patient',
            'status' => 'stable',
        ]);
        $secondPatient = Patient::create([
            'created_by_user_id' => $caregiver->id,
            'full_name' => 'Second Visible Patient',
            'status' => 'stable',
        ]);
        $otherPatient = Patient::create([
            'created_by_user_id' => $otherCaregiver->id,
            'full_name' => 'Hidden Patient',
            'status' => 'stable',
        ]);
        $schedule = $this->scheduleFor($patient);
        $otherSchedule = $this->scheduleFor($otherPatient);

        DoseEvent::create([
            'patient_id' => $patient->id,
            'medication_schedule_id' => $schedule->id,
            'status' => 'missed',
            'event_time' => now(),
        ]);
        DoseEvent::create([
            'patient_id' => $otherPatient->id,
            'medication_schedule_id' => $otherSchedule->id,
            'status' => 'missed',
            'event_time' => now(),
        ]);

        Alert::create([
            'patient_id' => $secondPatient->id,
            'type' => 'refill_due',
            'message' => 'Visible refill',
            'alert_time' => now(),
        ]);
        Alert::create([
            'patient_id' => $otherPatient->id,
            'type' => 'refill_due',
            'message' => 'Hidden refill',
            'alert_time' => now(),
        ]);

        Sanctum::actingAs($caregiver);

        $this->getJson('/api/dashboard/summary')
            ->assertOk()
            ->assertJsonPath('total_patients', 2)
            ->assertJsonPath('missed_doses_today', 1)
            ->assertJsonPath('active_alerts', 1)
            ->assertJsonPath('upcoming_refills', 1)
            ->assertJsonCount(1, 'recent_alerts');
    }

    private function scheduleFor(Patient $patient, array $attributes = []): MedicationSchedule
    {
        return MedicationSchedule::create(array_merge([
            'patient_id' => $patient->id,
            'medication_name' => 'Aspirin',
            'dosage' => '75 mg',
            'frequency' => 'daily',
            'scheduled_time' => '08:00:00',
            'start_date' => now()->toDateString(),
        ], $attributes));
    }
}
