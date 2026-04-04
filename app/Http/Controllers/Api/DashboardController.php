<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlertResource;
use App\Models\Alert;
use App\Models\DoseEvent;
use App\Models\Patient;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function summary()
    {
        $totalPatients     = Patient::count();
        $missedDosesToday  = DoseEvent::where('status', 'missed')
            ->whereDate('event_time', Carbon::today())
            ->count();
        $activeAlerts      = Alert::where('is_acknowledged', false)->count();
        $upcomingRefills   = Alert::where('type', 'refill_due')
            ->where('is_acknowledged', false)
            ->count();
        $recentAlerts      = Alert::with('patient')
            ->orderByDesc('alert_time')
            ->limit(5)
            ->get();

        return response()->json([
            'total_patients'    => $totalPatients,
            'missed_doses_today' => $missedDosesToday,
            'active_alerts'     => $activeAlerts,
            'upcoming_refills'  => $upcomingRefills,
            'recent_alerts'     => AlertResource::collection($recentAlerts),
        ]);
    }
}
