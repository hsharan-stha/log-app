<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'teacher', 'student', 'guardian', 'attendance', 'hr', 'finance', 'staff', 'other') NOT NULL DEFAULT 'teacher'");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::table('users')->whereIn('role', ['hr', 'finance', 'staff', 'other'])->update(['role' => 'teacher']);
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'teacher', 'student', 'guardian', 'attendance') NOT NULL DEFAULT 'teacher'");
        }
    }
};
