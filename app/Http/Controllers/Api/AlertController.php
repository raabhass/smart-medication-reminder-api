<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlertResource;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $query = Alert::with('patient');

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_acknowledged')) {
            $query->where('is_acknowledged', filter_var($request->is_acknowledged, FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = (int) $request->get('per_page', 10);
        $alerts  = $query->orderByDesc('alert_time')->paginate($perPage);

        return AlertResource::collection($alerts);
    }

    public function acknowledge(Request $request, Alert $alert)
    {
        $alert->update([
            'is_acknowledged'          => true,
            'acknowledged_by_user_id'  => $request->user()->id,
            'acknowledged_at'          => Carbon::now(),
        ]);

        return response()->json(['message' => 'Alert acknowledged']);
    }
}
