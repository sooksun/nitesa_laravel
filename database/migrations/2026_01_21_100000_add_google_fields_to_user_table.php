<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            if (! Schema::hasColumn('user', 'avatar')) {
                $table->string('avatar')->nullable()->after('googleId');
            }
            if (! Schema::hasColumn('user', 'isActive')) {
                $table->boolean('isActive')->default(true)->after('avatar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            if (Schema::hasColumn('user', 'avatar')) {
                $table->dropColumn('avatar');
            }
            if (Schema::hasColumn('user', 'isActive')) {
                $table->dropColumn('isActive');
            }
        });
    }
};
