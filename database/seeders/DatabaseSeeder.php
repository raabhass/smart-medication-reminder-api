<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\DoseEvent;
use App\Models\MedicationSchedule;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Caregiver user
        $user = User::create([
            'name'     => 'Primary Caregiver',
            'email'    => 'caregiver@example.com',
            'password' => Hash::make('password'),
            'role'     => 'caregiver',
        ]);

        // Patients
        $john = Patient::create([
            'created_by_user_id' => $user->id,
            'full_name'          => 'John Doe',
            'age'                => 78,
            'gender'             => 'male',
            'status'             => 'stable',
        ]);

        $mary = Patient::create([
            'created_by_user_id' => $user->id,
            'full_name'          => 'Mary Smith',
            'age'                => 82,
            'gender'             => 'female',
            'status'             => 'needs_attention',
        ]);

        $robert = Patient::create([
            'created_by_user_id' => $user->id,
            'full_name'          => 'Robert Brown',
            'age'                => 75,
            'gender'             => 'male',
            'status'             => 'stable',
        ]);

        // Medication schedules
        $aspirin = MedicationSchedule::create([
            'patient_id'      => $john->id,
            'medication_name' => 'Aspirin',
            'dosage'          => '75 mg',
            'frequency'       => 'daily',
            'scheduled_time'  => '08:00:00',
            'instructions'    => 'After breakfast',
            'start_date'      => Carbon::today(),
            'is_active'       => true,
        ]);

        $metformin = MedicationSchedule::create([
            'patient_id'      => $mary->id,
            'medication_name' => 'Metformin',
            'dosage'          => '500 mg',
            'frequency'       => 'twice_daily',
            'scheduled_time'  => '09:00:00',
            'instructions'    => 'With food',
            'start_date'      => Carbon::today(),
            'is_active'       => true,
        ]);

        $vitaminD = MedicationSchedule::create([
            'patient_id'      => $robert->id,
            'medication_name' => 'Vitamin D',
            'dosage'          => '1000 IU',
            'frequency'       => 'daily',
            'scheduled_time'  => '13:00:00',
            'instructions'    => 'With lunch',
            'start_date'      => Carbon::today(),
            'is_active'       => true,
        ]);

        // Dose events
        DoseEvent::create([
            'patient_id'             => $john->id,
            'medication_schedule_id' => $aspirin->id,
            'status'                 => 'taken',
            'event_time'             => Carbon::today()->setTime(8, 2),
        ]);

        DoseEvent::create([
            'patient_id'             => $mary->id,
            'medication_schedule_id' => $metformin->id,
            'status'                 => 'missed',
            'event_time'             => Carbon::today()->setTime(9, 0),
        ]);

        DoseEvent::create([
            'patient_id'             => $robert->id,
            'medication_schedule_id' => $vitaminD->id,
            'status'                 => 'taken',
            'event_time'             => Carbon::today()->setTime(13, 5),
        ]);

        // Alerts
        Alert::create([
            'patient_id' => $mary->id,
            'type'       => 'missed_dose',
            'message'    => 'Missed morning Metformin dose',
            'alert_time' => Carbon::today()->setTime(9, 0),
        ]);

        Alert::create([
            'patient_id' => $john->id,
            'type'       => 'refill_due',
            'message'    => 'Aspirin prescription needs refill',
            'alert_time' => Carbon::today()->setTime(8, 0),
        ]);
    }
}
