<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FlexibleMeetingDay extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('meetings', function (Blueprint $table){
            $table->integer('flexible_days')->nullable()->after("gender");
        });
        // Schema::table('users', function (Blueprint $table) {
        //     $table->string('middle_name')->nullable()->change();
        // });

        DB::statement(
            "ALTER TABLE `users`
                CHANGE `middle_name` `middle_name` varchar(255) COLLATE 'utf8_unicode_ci' NULL AFTER `first_name`;"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('meetings', function (Blueprint $table){
            $table->dropColumn('flexible_days');
        });
    }
}
