<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\DoseEvent;
use App\Models\MedicationSchedule;
use App\Services\PushNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class DetectMedicationAlerts extends Command
{
    protected $signature = 'medications:detect-alerts';

    protected $description = 'Detect missed medication doses and refill alerts.';

    public function handle(PushNotificationService $pushNotifications): int
    {
        $now = Carbon::now();

        $this->recordMissedDoses($now, $pushNotifications);
        $this->recordRefillAlerts($now);

        return self::SUCCESS;
    }

    private function recordMissedDoses(Carbon $now, PushNotificationService $pushNotifications): void
    {
        $today = $now->toDateString();

        MedicationSchedule::with(['patient.user'])
            ->whereHas('patient')
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $today);
            })
            ->whereTime('scheduled_time', '<=', $now->format('H:i:s'))
            ->get()
            ->each(function (MedicationSchedule $schedule) use ($now, $pushNotifications, $today) {
                if (! $schedule->patient) {
                    $this->warn("Skipping schedule ID {$schedule->id}: patient not found.");

                    return;
                }

                $alreadyRecorded = DoseEvent::where('medication_schedule_id', $schedule->id)
                    ->whereDate('event_time', $today)
                    ->exists();

                if ($alreadyRecorded) {
                    return;
                }

                DoseEvent::create([
                    'patient_id' => $schedule->patient_id,
                    'medication_schedule_id' => $schedule->id,
                    'status' => 'missed',
                    'event_time' => Carbon::parse($today.' '.$schedule->scheduled_time),
                    'notes' => 'Automatically marked missed by scheduled detection.',
                ]);

                $message = "{$schedule->patient->full_name} missed {$schedule->medication_name}.";

                Alert::create([
                    'patient_id' => $schedule->patient_id,
                    'type' => 'missed_dose',
                    'message' => $message,
                    'alert_time' => $now,
                ]);

                if ($schedule->patient->user) {
                    $pushNotifications->send(
                        $schedule->patient->user,
                        'Missed medication',
                        $message
                    );
                }
            });
    }

    private function recordRefillAlerts(Carbon $now): void
    {
        MedicationSchedule::with('patient')
            ->whereHas('patient')
            ->where('is_active', true)
            ->whereNotNull('remaining_pills')
            ->where('remaining_pills', '<=', 7)
            ->get()
            ->each(function (MedicationSchedule $schedule) use ($now) {
                if (! $schedule->patient) {
                    $this->warn("Skipping schedule ID {$schedule->id}: patient not found.");

                    return;
                }

                $message = "{$schedule->patient->full_name} is low on {$schedule->medication_name}.";

                $alreadyAlertedToday = Alert::where('patient_id', $schedule->patient_id)
                    ->where('type', 'refill_due')
                    ->where('message', $message)
                    ->whereDate('alert_time', $now->toDateString())
                    ->exists();

                if ($alreadyAlertedToday) {
                    return;
                }

                Alert::create([
                    'patient_id' => $schedule->patient_id,
                    'type' => 'refill_due',
                    'message' => $message,
                    'alert_time' => $now,
                ]);
            });
    }
}
