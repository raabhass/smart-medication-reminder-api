<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (! Schema::hasColumn('patients', 'allergies')) {
                $table->string('allergies')->nullable()->after('emergency_contact_relationship');
            }

            if (! Schema::hasColumn('patients', 'medical_notes')) {
                $table->string('medical_notes')->nullable()->after('allergies');
            }
        });
    }

    public function down(): void
    {
        $columns = array_filter([
            Schema::hasColumn('patients', 'allergies') ? 'allergies' : null,
            Schema::hasColumn('patients', 'medical_notes') ? 'medical_notes' : null,
        ]);

        if ($columns === []) {
            return;
        }

        Schema::table('patients', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
