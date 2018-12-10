<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnMessagesInvites extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if(Schema::hasColumn('invite_messages', 'message_body'))
        {
           Schema::table('invite_messages', function(Blueprint $table)
            {
                $table->dropColumn('message_body');
            });
        }

        Schema::table('invite_messages', function(Blueprint $table)
        {
            $table->text('message_body')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasColumn('invite_messages', 'message_body'))
        {
           Schema::table('invite_messages', function(Blueprint $table)
            {
                $table->dropColumn('message_body');
            });
        }

        Schema::table('invite_messages', function(Blueprint $table)
        {
            $table->string('message_body', 255)->nullable();
        });
    }
}
