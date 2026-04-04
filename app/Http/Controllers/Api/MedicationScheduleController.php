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
        $schedules = $patient->medicationSchedules()->orderBy('scheduled_time')->get();

        return MedicationScheduleResource::collection($schedules);
    }

    public function store(StoreMedicationScheduleRequest $request, Patient $patient)
    {
        $schedule = $patient->medicationSchedules()->create($request->validated());

        return new MedicationScheduleResource($schedule);
    }

    public function update(UpdateMedicationScheduleRequest $request, MedicationSchedule $medicationSchedule)
    {
        $medicationSchedule->update($request->validated());

        return new MedicationScheduleResource($medicationSchedule);
    }

    public function destroy(MedicationSchedule $medicationSchedule)
    {
        $medicationSchedule->update(['is_active' => false]);

        return response()->json(['message' => 'Schedule deactivated']);
    }
}
