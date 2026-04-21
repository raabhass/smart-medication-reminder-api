<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $query = Patient::query();

        if ($request->filled('search')) {
            $query->where('full_name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage  = (int) $request->get('per_page', 10);
        $patients = $query->paginate($perPage);

        return PatientResource::collection($patients);
    }

    public function store(StorePatientRequest $request)
    {
        $patient = Patient::create(
            array_merge($request->validated(), ['created_by_user_id' => $request->user()->id])
        );

        return new PatientResource($patient);
    }

    public function show(Patient $patient)
    {
        return new PatientResource($patient);
    }

    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $patient->update($request->validated());

        return new PatientResource($patient);
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();

        return response()->json(['message' => 'Patient deleted']);
    }

    public function linkUser(Request $request, Patient $patient)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = User::findOrFail($request->user_id);

        if ($user->role !== 'patient') {
            return response()->json(['message' => 'User must have role patient'], 422);
        }

        if (Patient::where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'User is already linked to another patient'], 422);
        }

        $patient->update(['user_id' => $user->id]);

        return new PatientResource($patient->fresh());
    }
}
