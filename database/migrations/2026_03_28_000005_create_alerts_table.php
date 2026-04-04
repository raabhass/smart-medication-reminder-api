<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients');
            $table->string('type');
            $table->text('message');
            $table->dateTime('alert_time');
            $table->boolean('is_acknowledged')->default(false);
            $table->foreignId('acknowledged_by_user_id')->nullable()->constrained('users');
            $table->dateTime('acknowledged_at')->nullable();
            $table->timestamps();

            $table->index('patient_id');
            $table->index('type');
            $table->index('is_acknowledged');
            $table->index('alert_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
