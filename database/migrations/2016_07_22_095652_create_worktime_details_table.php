<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorktimeDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('worktime_details', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nid');
            $table->string('employee_code');
            $table->string('name');
            $table->string('email');
            $table->string('role');
            $table->string('username');
            $table->string('settime');
            $table->date('fromdate');
            $table->date('todate');
            $table->string('state');
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
        Schema::drop('worktime_details');
    }
}
