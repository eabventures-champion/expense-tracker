<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('approval_status', 30)->default('pending')->after('role'); // pending, approved, rejected
            $table->timestamp('approved_at')->nullable()->after('approval_status');
            $table->boolean('has_sample_data')->default(true)->after('two_factor_confirmed_at');
            $table->boolean('tour_completed')->default(false)->after('has_sample_data');
        });

        // Mark existing seeded owner / admin accounts as approved
        DB::table('users')->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'has_sample_data' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'approval_status',
                'approved_at',
                'has_sample_data',
                'tour_completed',
            ]);
        });
    }
};
