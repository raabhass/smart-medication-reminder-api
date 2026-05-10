<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlertResource;
use App\Models\Alert;
use App\Models\DoseEvent;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $patientScope = $this->visiblePatients($request);
        $user = $request->user();

        $totalPatients = (clone $patientScope)->count();
        $missedDosesToday = DoseEvent::where('status', 'missed')
            ->whereHas('patient', fn (Builder $query) => $this->scopePatientsForUser($query, $user))
            ->whereDate('event_time', Carbon::today())
            ->count();
        $activeAlerts = Alert::where('is_acknowledged', false)
            ->whereHas('patient', fn (Builder $query) => $this->scopePatientsForUser($query, $user))
            ->count();
        $upcomingRefills = Alert::where('type', 'refill_due')
            ->whereHas('patient', fn (Builder $query) => $this->scopePatientsForUser($query, $user))
            ->where('is_acknowledged', false)
            ->count();
        $recentAlerts = Alert::with('patient')
            ->whereHas('patient', fn (Builder $query) => $this->scopePatientsForUser($query, $user))
            ->orderByDesc('alert_time')
            ->limit(5)
            ->get();

        return response()->json([
            'total_patients' => $totalPatients,
            'missed_doses_today' => $missedDosesToday,
            'active_alerts' => $activeAlerts,
            'upcoming_refills' => $upcomingRefills,
            'recent_alerts' => AlertResource::collection($recentAlerts),
        ]);
    }

    private function visiblePatients(Request $request): Builder
    {
        return $this->scopePatientsForUser(Patient::query(), $request->user());
    }

    private function scopePatientsForUser(Builder $query, $user): Builder
    {
        if ($user->role === 'admin') {
            return $query;
        }

        if ($user->role === 'patient') {
            return $query->where('user_id', $user->id);
        }

        return $query->where('created_by_user_id', $user->id);
    }
}
