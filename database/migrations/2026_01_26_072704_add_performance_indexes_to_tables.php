<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Supervision table indexes
        Schema::table('supervision', function (Blueprint $table) {
            // Index for status filtering (used in many queries)
            $table->index('status', 'idx_supervision_status');
            
            // Index for academic year filtering
            $table->index('academicYear', 'idx_supervision_academic_year');
            
            // Composite index for school and date queries
            $table->index(['schoolId', 'date'], 'idx_supervision_school_date');
            
            // Index for user (supervisor) queries
            $table->index('userId', 'idx_supervision_user');
            
            // Index for date sorting
            $table->index('date', 'idx_supervision_date');
        });

        // School table indexes
        Schema::table('school', function (Blueprint $table) {
            // Index for district filtering
            $table->index('district', 'idx_school_district');
            
            // Index for network group filtering
            $table->index('networkGroupId', 'idx_school_network_group');
            
            // Index for code lookups
            $table->index('code', 'idx_school_code');
        });

        // Indicator table indexes
        Schema::table('indicator', function (Blueprint $table) {
            // Index for supervision relationship
            $table->index('supervisionId', 'idx_indicator_supervision');
            
            // Index for level filtering
            $table->index('level', 'idx_indicator_level');
        });

        // Activity log table indexes (if exists)
        if (Schema::hasTable('activity_log')) {
            Schema::table('activity_log', function (Blueprint $table) {
                // Index for date filtering and sorting
                $table->index('created_at', 'idx_activity_created_at');
                
                // Index for causer queries
                $table->index('causer_id', 'idx_activity_causer');
                
                // Composite index for subject queries
                $table->index(['subject_type', 'subject_id'], 'idx_activity_subject');
                
                // Index for event filtering
                $table->index('event', 'idx_activity_event');
            });
        }

        // User table indexes
        Schema::table('user', function (Blueprint $table) {
            // Index for role filtering
            $table->index('role', 'idx_user_role');
            
            // Index for active status filtering
            $table->index('isActive', 'idx_user_is_active');
        });

        // Policy table indexes
        Schema::table('policy', function (Blueprint $table) {
            // Index for type filtering
            $table->index('type', 'idx_policy_type');
            
            // Index for active status filtering
            $table->index('isActive', 'idx_policy_is_active');
        });

        // Attachment table indexes
        Schema::table('attachment', function (Blueprint $table) {
            // Index for supervision relationship
            $table->index('supervisionId', 'idx_attachment_supervision');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop Supervision indexes
        Schema::table('supervision', function (Blueprint $table) {
            $table->dropIndex('idx_supervision_status');
            $table->dropIndex('idx_supervision_academic_year');
            $table->dropIndex('idx_supervision_school_date');
            $table->dropIndex('idx_supervision_user');
            $table->dropIndex('idx_supervision_date');
        });

        // Drop School indexes
        Schema::table('school', function (Blueprint $table) {
            $table->dropIndex('idx_school_district');
            $table->dropIndex('idx_school_network_group');
            $table->dropIndex('idx_school_code');
        });

        // Drop Indicator indexes
        Schema::table('indicator', function (Blueprint $table) {
            $table->dropIndex('idx_indicator_supervision');
            $table->dropIndex('idx_indicator_level');
        });

        // Drop Activity Log indexes
        if (Schema::hasTable('activity_log')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->dropIndex('idx_activity_created_at');
                $table->dropIndex('idx_activity_causer');
                $table->dropIndex('idx_activity_subject');
                $table->dropIndex('idx_activity_event');
            });
        }

        // Drop User indexes
        Schema::table('user', function (Blueprint $table) {
            $table->dropIndex('idx_user_role');
            $table->dropIndex('idx_user_is_active');
        });

        // Drop Policy indexes
        Schema::table('policy', function (Blueprint $table) {
            $table->dropIndex('idx_policy_type');
            $table->dropIndex('idx_policy_is_active');
        });

        // Drop Attachment indexes
        Schema::table('attachment', function (Blueprint $table) {
            $table->dropIndex('idx_attachment_supervision');
        });
    }
};
