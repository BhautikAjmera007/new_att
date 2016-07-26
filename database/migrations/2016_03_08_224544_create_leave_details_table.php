<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    
    public function up()
    {
        Schema::create('leave_details', function (Blueprint $table) {
            $table->increments('id');
            $table->string('employee_code');
            $table->integer('nid');
            $table->string('type');
            $table->string('usr_name');
            $table->date('from_date');
            $table->date('to_date');
            $table->string('total_days');
            $table->string('from_session');
            $table->string('to_session');
            $table->date('updated_date');
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
        Schema::drop('leave_details');
    }
}
