<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'staff', 'teacher', 'student', 'guardian') NOT NULL DEFAULT 'teacher'");
            DB::table('users')->where('role', 'staff')->update(['role' => 'teacher']);
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'teacher', 'student', 'guardian') NOT NULL DEFAULT 'teacher'");
        } else {
            DB::table('users')->where('role', 'staff')->update(['role' => 'teacher']);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 40)->nullable()->after('email');
            $table->unsignedBigInteger('class_id')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'class_id']);
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::table('users')->where('role', 'teacher')->update(['role' => 'staff']);
            DB::table('users')->whereIn('role', ['student', 'guardian'])->update(['role' => 'staff']);
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff'");
        } else {
            DB::table('users')->where('role', 'teacher')->update(['role' => 'staff']);
        }
    }
};
