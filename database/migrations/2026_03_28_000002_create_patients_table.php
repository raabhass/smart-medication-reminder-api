<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->string('full_name');
            $table->unsignedInteger('age');
            $table->string('gender')->nullable();
            $table->string('status')->default('stable');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('created_by_user_id');
            $table->index('status');
            $table->index('full_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
