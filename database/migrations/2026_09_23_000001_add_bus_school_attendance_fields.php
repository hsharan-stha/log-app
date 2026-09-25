<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('rides_bus')->default(false)->after('roll_number');
        });

        Schema::table('kiosk_devices', function (Blueprint $table) {
            $table->string('location', 20)->default('school')->after('name');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dateTime('bus_checkin_time')->nullable()->after('checkout_photo_path');
            $table->string('bus_checkin_photo_path', 512)->nullable()->after('bus_checkin_time');
            $table->dateTime('bus_checkout_time')->nullable()->after('bus_checkin_photo_path');
            $table->string('bus_checkout_photo_path', 512)->nullable()->after('bus_checkout_time');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'bus_checkin_time',
                'bus_checkin_photo_path',
                'bus_checkout_time',
                'bus_checkout_photo_path',
            ]);
        });

        Schema::table('kiosk_devices', function (Blueprint $table) {
            $table->dropColumn('location');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('rides_bus');
        });
    }
};
