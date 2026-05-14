<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('checkin_photo_path', 512)->nullable()->after('checkin_time');
            $table->string('checkout_photo_path', 512)->nullable()->after('checkout_time');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['checkin_photo_path', 'checkout_photo_path']);
        });
    }
};
