<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('savings_records', 'funding_source')) {
            Schema::table('savings_records', function (Blueprint $table) {
                $table->string('funding_source', 40)
                    ->default('monthly_income')
                    ->after('action_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('savings_records', 'funding_source')) {
            Schema::table('savings_records', function (Blueprint $table) {
                $table->dropColumn('funding_source');
            });
        }
    }
};
