<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('examresult', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->string('examType', 20);
            $table->string('academicYear', 10);
            $table->string('scope', 20);
            $table->string('schoolId', 26)->nullable();
            $table->unsignedInteger('examinees')->nullable();
            $table->decimal('thai', 5, 2)->nullable();
            $table->decimal('math', 5, 2)->nullable();
            $table->decimal('english', 5, 2)->nullable();
            $table->decimal('science', 5, 2)->nullable();
            $table->decimal('readAloud', 5, 2)->nullable();
            $table->decimal('readComprehension', 5, 2)->nullable();
            $table->decimal('average', 5, 2)->nullable();
            $table->string('qualityLevel', 20)->nullable();
            $table->string('sourceCode')->nullable();
            $table->string('sourceName')->nullable();
            $table->timestamp('createdAt')->nullable();
            $table->timestamp('updatedAt')->nullable();

            $table->index(['examType', 'academicYear', 'scope'], 'idx_examresult_type_year_scope');
            $table->index('schoolId', 'idx_examresult_school');
            $table->unique(['examType', 'academicYear', 'scope', 'schoolId'], 'uq_examresult_school_exam');
            $table->foreign('schoolId')->references('id')->on('school')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examresult');
    }
};
