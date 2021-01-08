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
            $table->string('type');
            $table->string('token')->unique();
            $table->datetime('heartbeat')
            $table->string('mac');
            $table->string('firmware_hash');
            $table->string('ip_address')->unique();
            $table->string('sleep_time');
            $table->string('owner_id'); //TODO: Foregin key to user Table
            $table->boolval('approved');
            $table->string('icon');
            $table->string('command');
            $table->string('name')->unique();
            $table->string('description')->nullable();
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
