<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('address_line1')->nullable()->after('contact_person');
            $table->string('address_line2')->nullable()->after('address_line1');
            $table->string('city')->nullable()->after('address_line2');
            $table->string('province')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('province');
            $table->decimal('gps_lat', 10, 7)->nullable()->after('postal_code');
            $table->decimal('gps_lng', 10, 7)->nullable()->after('gps_lat');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['address_line1', 'address_line2', 'city', 'province', 'postal_code', 'gps_lat', 'gps_lng']);
        });
    }
};
