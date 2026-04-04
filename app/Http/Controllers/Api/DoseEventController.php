<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoseEventRequest;
use App\Http\Resources\DoseEventHistoryResource;
use App\Models\DoseEvent;
use Illuminate\Http\Request;

class DoseEventController extends Controller
{
    public function store(StoreDoseEventRequest $request)
    {
        $event = DoseEvent::create($request->validated());

        $event->load(['patient', 'medicationSchedule']);

        return new DoseEventHistoryResource($event);
    }

    public function history(Request $request)
    {
        $query = DoseEvent::with(['patient', 'medicationSchedule']);

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('event_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('event_time', '<=', $request->date_to);
        }

        $perPage = (int) $request->get('per_page', 10);
        $events  = $query->orderByDesc('event_time')->paginate($perPage);

        return DoseEventHistoryResource::collection($events);
    }
}
