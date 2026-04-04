<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients');
            $table->string('medication_name');
            $table->string('dosage');
            $table->string('frequency');
            $table->time('scheduled_time');
            $table->text('instructions')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['patient_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_schedules');
    }
};
