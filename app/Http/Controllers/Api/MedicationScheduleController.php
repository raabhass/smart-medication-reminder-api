<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicationScheduleRequest;
use App\Http\Requests\UpdateMedicationScheduleRequest;
use App\Http\Resources\MedicationScheduleResource;
use App\Models\MedicationSchedule;
use App\Models\Patient;

class MedicationScheduleController extends Controller
{
    public function indexByPatient(Patient $patient)
    {
        $this->authorizePatient($patient);

        $schedules = $patient->medicationSchedules()->orderBy('scheduled_time')->get();

        return MedicationScheduleResource::collection($schedules);
    }

    public function store(StoreMedicationScheduleRequest $request, Patient $patient)
    {
        $this->authorizePatient($patient);

        $schedule = $patient->medicationSchedules()->create($request->validated());

        return new MedicationScheduleResource($schedule);
    }

    public function update(UpdateMedicationScheduleRequest $request, Patient $patient, MedicationSchedule $medicationSchedule)
    {
        $this->authorizePatient($patient);
        $this->authorizeScheduleForPatient($patient, $medicationSchedule);

        $medicationSchedule->update($request->validated());

        return new MedicationScheduleResource($medicationSchedule);
    }

    public function destroy(Patient $patient, MedicationSchedule $medicationSchedule)
    {
        $this->authorizePatient($patient);
        $this->authorizeScheduleForPatient($patient, $medicationSchedule);

        $medicationSchedule->update(['is_active' => false]);

        return response()->json(['message' => 'Schedule deactivated']);
    }

    private function authorizePatient(Patient $patient): void
    {
        $user = request()->user();

        abort_unless(
            ($user->role === 'patient' && $patient->user_id === $user->id)
                || ($user->role === 'caregiver' && $patient->created_by_user_id === $user->id),
            404
        );
    }

    private function authorizeScheduleForPatient(Patient $patient, MedicationSchedule $medicationSchedule): void
    {
        abort_unless($medicationSchedule->patient_id === $patient->id, 404);
    }
}
