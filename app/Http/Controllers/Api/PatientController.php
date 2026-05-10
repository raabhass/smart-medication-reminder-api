<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->visiblePatients($request);

        if ($request->filled('search')) {
            $query->where('full_name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = (int) $request->get('per_page', 10);
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
        $this->authorizePatient($patient);

        return new PatientResource($patient);
    }

    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $this->authorizePatient($patient);

        $patient->update($request->validated());

        return new PatientResource($patient);
    }

    public function destroy(Patient $patient)
    {
        $this->authorizePatient($patient);

        $patient->delete();

        return response()->json(['message' => 'Patient deleted']);
    }

    public function linkUser(Request $request, Patient $patient)
    {
        $this->authorizePatient($patient);

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

    private function visiblePatients(Request $request): Builder
    {
        $user = $request->user();
        $query = Patient::query();

        if ($user->role === 'patient') {
            return $query->where('user_id', $user->id);
        }

        return $query->where('created_by_user_id', $user->id);
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
}
