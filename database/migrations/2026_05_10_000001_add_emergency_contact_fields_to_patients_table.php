<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (! Schema::hasColumn('patients', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('notes');
            }

            if (! Schema::hasColumn('patients', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            }

            if (! Schema::hasColumn('patients', 'emergency_contact_relationship')) {
                $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_phone');
            }
        });
    }

    public function down(): void
    {
        $columns = array_filter([
            Schema::hasColumn('patients', 'emergency_contact_name') ? 'emergency_contact_name' : null,
            Schema::hasColumn('patients', 'emergency_contact_phone') ? 'emergency_contact_phone' : null,
            Schema::hasColumn('patients', 'emergency_contact_relationship') ? 'emergency_contact_relationship' : null,
        ]);

        if ($columns === []) {
            return;
        }

        Schema::table('patients', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
