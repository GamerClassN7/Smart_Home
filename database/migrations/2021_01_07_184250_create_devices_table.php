<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id()->unique();
            $table->int('room_id'); //TODO: Foregin key to Room Table
            $table->string('owner_id'); //TODO: Foregin key to user Table
            $table->string('token')->unique();
            $table->boolval('approved');
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->string('type');
            $table->string('icon');
            $table->string('mac_address');
            $table->string('ip_address')->unique();
            $table->string('firmware_hash');
            $table->string('sleep_time');
            $table->datetime('heartbeat')
            $table->string('command');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('devices');
    }
}
