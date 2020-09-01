<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_start');
            $table->string('ip_end');
            $table->string('continent');
            $table->string('country');
            $table->string('stateprov');
            $table->string('district');
            $table->string('city');
            $table->string('zipcode');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('geoname_id');
            $table->string('timezone_offset');
            $table->string('timezone_name');
            $table->string('weather_code');
            $table->string('isp_name');
            $table->string('as_number');
            $table->string('connection_type');
            $table->string('organization_name');
            $table->timestamps();
            $table->unique(['ip_start', 'ip_end']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ips');
    }
}
