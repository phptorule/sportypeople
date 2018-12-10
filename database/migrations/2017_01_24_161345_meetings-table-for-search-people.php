<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MeetingsTableForSearchPeople extends Migration
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
            $table->dropColumn('radius');
            $table->dateTime('meeting_date')->after('user_id')->default("2017-05-24");
        });

        DB::statement("ALTER TABLE `meetings` CHANGE `gender` `gender` enum('ALL','M','F') NOT NULL DEFAULT 'ALL' ");
        DB::statement(
            "ALTER TABLE `meetings`
                CHANGE `full_address` `full_address` varchar(255) COLLATE 'utf8_unicode_ci' NULL AFTER `meeting_date`;"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meetings', function (Blueprint $table){
            $table->integer("radius")->after('gender');
            $table->dropColumn("meeting_date");
        });
        DB::statement("ALTER TABLE `meetings` CHANGE `gender` `gender` enum('M','F') NOT NULL DEFAULT 'M' ");
        DB::statement(
            "ALTER TABLE `meetings`
                CHANGE `full_address` `full_address` varchar(255) not null COLLATE 'utf8_unicode_ci';"
        );
    }
}
