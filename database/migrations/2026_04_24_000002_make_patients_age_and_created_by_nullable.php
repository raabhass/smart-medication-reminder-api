<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->unsignedInteger('age')->nullable()->change();
            $table->dropForeign(['created_by_user_id']);
            $table->unsignedBigInteger('created_by_user_id')->nullable()->change();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->unsignedInteger('age')->nullable(false)->change();
            $table->dropForeign(['created_by_user_id']);
            $table->unsignedBigInteger('created_by_user_id')->nullable(false)->change();
            $table->foreign('created_by_user_id')->references('id')->on('users');
        });
    }
};
