<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatTableInvitesFreands extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invite_friends', function (Blueprint $table){
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string("first_name")->nullable();
            $table->string("last_name", 255)->nullable();
            $table->string("short_message", 255)->nullable();
            $table->string("username", 255)->nullable();
            $table->string("remember_token", 255)->nullable();
            $table->string("invite_link", 255)->nullable();
            $table->string("sent_on", 255)->nullable();
            $table->integer("added_by_user_id");
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
        Schema::dropIfExists('invite_friends');
    }
}
