<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Network Group table
        Schema::create('networkgroup', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamp('createdAt')->nullable();
            $table->timestamp('updatedAt')->nullable();
        });

        // User table
        Schema::create('user', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('SUPERVISOR'); // ADMIN, SUPERVISOR, SCHOOL, EXECUTIVE
            $table->string('image')->nullable();
            $table->string('googleId')->nullable();
            $table->string('avatar')->nullable(); // Google avatar URL
            $table->boolean('isActive')->default(true);
            $table->timestamp('createdAt')->nullable();
            $table->timestamp('updatedAt')->nullable();
        });

        // School table
        Schema::create('school', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('subDistrict')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('principalName')->nullable();
            $table->integer('studentCount')->default(0);
            $table->integer('teacherCount')->default(0);
            $table->string('networkGroup')->nullable();
            $table->string('networkGroupId', 26)->nullable();
            $table->timestamp('createdAt')->nullable();
            $table->timestamp('updatedAt')->nullable();

            $table->foreign('networkGroupId')->references('id')->on('networkgroup')->onDelete('set null');
        });

        // Supervisor-Schools pivot table
        Schema::create('_supervisorschools', function (Blueprint $table) {
            $table->string('A', 26); // school_id
            $table->string('B', 26); // user_id
            $table->primary(['A', 'B']);

            $table->foreign('A')->references('id')->on('school')->onDelete('cascade');
            $table->foreign('B')->references('id')->on('user')->onDelete('cascade');
        });

        // Policy table
        Schema::create('policy', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->string('type'); // PolicyType enum
            $table->string('code')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('isActive')->default(true);
            $table->timestamp('createdAt')->nullable();
            $table->timestamp('updatedAt')->nullable();
        });

        // Supervision table
        Schema::create('supervision', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->string('schoolId', 26);
            $table->string('userId', 26);
            $table->string('type')->nullable(); // ติดตามโครงการ, นิเทศกำกับ, etc.
            $table->date('date');
            $table->string('academicYear')->nullable();
            $table->string('ministerPolicy')->nullable(); // Text summary
            $table->string('obecPolicy')->nullable();
            $table->string('areaPolicy')->nullable();
            $table->string('ministerPolicyId', 26)->nullable();
            $table->string('obecPolicyId', 26)->nullable();
            $table->string('areaPolicyId', 26)->nullable();
            $table->text('summary')->nullable();
            $table->text('suggestions')->nullable();
            $table->string('status')->default('DRAFT'); // SupervisionStatus enum
            $table->timestamp('createdAt')->nullable();
            $table->timestamp('updatedAt')->nullable();

            $table->foreign('schoolId')->references('id')->on('school')->onDelete('cascade');
            $table->foreign('userId')->references('id')->on('user')->onDelete('cascade');
            $table->foreign('ministerPolicyId')->references('id')->on('policy')->onDelete('set null');
            $table->foreign('obecPolicyId')->references('id')->on('policy')->onDelete('set null');
            $table->foreign('areaPolicyId')->references('id')->on('policy')->onDelete('set null');
        });

        // Indicator table
        Schema::create('indicator', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->string('supervisionId', 26);
            $table->string('name');
            $table->string('level'); // IndicatorLevel enum
            $table->text('comment')->nullable();

            $table->foreign('supervisionId')->references('id')->on('supervision')->onDelete('cascade');
        });

        // Attachment table
        Schema::create('attachment', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->string('supervisionId', 26);
            $table->string('filename');
            $table->string('fileUrl');
            $table->string('fileType')->nullable();
            $table->integer('fileSize')->default(0);
            $table->timestamp('uploadedAt')->nullable();

            $table->foreign('supervisionId')->references('id')->on('supervision')->onDelete('cascade');
        });

        // Acknowledgement table
        Schema::create('acknowledgement', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->string('supervisionId', 26);
            $table->string('acknowledgedBy')->nullable();
            $table->timestamp('acknowledgedAt')->nullable();
            $table->text('comment')->nullable();

            $table->foreign('supervisionId')->references('id')->on('supervision')->onDelete('cascade');
        });

        // Improvement table
        Schema::create('improvement', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->string('schoolId', 26);
            $table->string('userId', 26);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('fileUrl')->nullable();
            $table->string('status')->default('pending'); // pending, approved, completed
            $table->timestamp('createdAt')->nullable();
            $table->timestamp('updatedAt')->nullable();

            $table->foreign('schoolId')->references('id')->on('school')->onDelete('cascade');
            $table->foreign('userId')->references('id')->on('user')->onDelete('cascade');
        });

        // System Settings table
        Schema::create('systemsetting', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('updatedAt')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('systemsetting');
        Schema::dropIfExists('improvement');
        Schema::dropIfExists('acknowledgement');
        Schema::dropIfExists('attachment');
        Schema::dropIfExists('indicator');
        Schema::dropIfExists('supervision');
        Schema::dropIfExists('policy');
        Schema::dropIfExists('_supervisorschools');
        Schema::dropIfExists('school');
        Schema::dropIfExists('user');
        Schema::dropIfExists('networkgroup');
    }
};
