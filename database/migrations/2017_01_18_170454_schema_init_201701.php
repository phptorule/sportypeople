<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SchemaInit201701 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('meetings', function (Blueprint $table){
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer("user_id")->unsigned();
            $table->string("full_address");
            $table->string("address")->nullable();
            $table->string("city",100)->nullable();
            $table->string("zipcode",50)->nullable();
            $table->decimal('latitude', 10, 7);    
            $table->decimal('longitude', 10, 7);    
            $table->string("age_min");
            $table->string("age_max");
            $table->enum('gender',['M','F']);
            $table->integer("radius");

            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('NO ACTION');

        });

        Schema::create('invites', function (Blueprint $table){

            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer("user_id")->unsigned();
            $table->integer("meeting_id")->unsigned();
            $table->enum('status',['pending', 'accepted', 'rejected', 'old', 'cancelled']);

            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('NO ACTION');

            $table->foreign('meeting_id')
                  ->references('id')->on('meetings')
                  ->onDelete('NO ACTION');

        });

        Schema::create('invite_messages', function (Blueprint $table){
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer("invite_id")->unsigned();
            $table->integer("user_id")->unsigned();
            $table->string("message_body")->nullable();
            $table->enum("status", ['new', 'old', 'deleted']);
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('NO ACTION');

            $table->foreign('invite_id')
                ->references('id')->on('invites')
                ->onDelete('NO ACTION');
        });

        Schema::create('invite_logs', function (Blueprint $table){
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer("invite_id")->unsigned();
            $table->integer("user_id")->unsigned();

            $table->enum("old_status", ['active']);
            $table->enum("new_status", ['active']);

            $table->string("ip")->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('NO ACTION');

            $table->foreign('invite_id')
                ->references('id')->on('invites')
                ->onDelete('NO ACTION');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invite_messages');
        Schema::dropIfExists('invite_logs');

        Schema::dropIfExists('invites');
        Schema::dropIfExists('meetings');

    }
}
