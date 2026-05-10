<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['role'] = $data['role'] ?? 'caregiver';

        $user = User::create($data);

        $this->ensurePatientRecord($user);

        $user->load('patient');
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => new UserResource($user),
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $this->ensurePatientRecord($user);

        $user->load('patient');
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $this->ensurePatientRecord($user);

        return new UserResource($user->load('patient'));
    }

    public function updateMe(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $user->update($request->validated());

        return new UserResource($user->fresh()->load('patient'));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    private function ensurePatientRecord(User $user): void
    {
        if ($user->role !== 'patient' || $user->patient()->exists()) {
            return;
        }

        Patient::create([
            'user_id' => $user->id,
            'full_name' => $user->name,
            'status' => 'stable',
        ]);
    }
}
