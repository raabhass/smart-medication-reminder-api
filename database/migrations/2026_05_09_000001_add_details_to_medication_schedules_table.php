<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medication_schedules', function (Blueprint $table) {
            $table->string('doctor_name')->nullable()->after('end_date');
            $table->string('hospital_name')->nullable()->after('doctor_name');
            $table->unsignedInteger('remaining_pills')->nullable()->after('hospital_name');
            $table->date('refill_date')->nullable()->after('remaining_pills');
        });
    }

    public function down(): void
    {
        Schema::table('medication_schedules', function (Blueprint $table) {
            $table->dropColumn([
                'doctor_name',
                'hospital_name',
                'remaining_pills',
                'refill_date',
            ]);
        });
    }
};
