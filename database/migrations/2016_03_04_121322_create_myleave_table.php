<?php

use Jenssegers\Mongodb\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMyleaveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('myleave', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_name');
            $table->string('employee_code');
            $table->integer('nid');
            $table->dateTime('leave_from');
            $table->dateTime('leave_to');
            $table->integer('total_leavedays');
            $table->string('reporting_manager');
            $table->string('state');
            $table->string('reason');
            $table->integer('leave_type');
            $table->string('from_session');
            $table->string('from_session_ampm');
            $table->string('to_session');
            $table->string('to_session_ampm');
            $table->integer('leave_user_id');
            $table->string('reporting_employee');
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
        Schema::drop('myleave');
    }
}
