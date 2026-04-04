<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dose_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('medication_schedule_id')->constrained('medication_schedules');
            $table->string('status');
            $table->dateTime('event_time');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('patient_id');
            $table->index('medication_schedule_id');
            $table->index('status');
            $table->index('event_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dose_events');
    }
};
