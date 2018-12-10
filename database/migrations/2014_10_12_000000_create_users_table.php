<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       /* Schema::create('users', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });*/
		
		Schema::create('users', function (Blueprint $table){
            $table->engine = 'InnoDB';
            $table->increments("id")->unsigned();
            $table->integer("country_id")->unsigned();
            $table->string("first_name", 255)->nullable();
			$table->string("middle_name", 255)->nullable();
            $table->string("username", 255)->nullable();
            $table->string("remember_token", 255)->nullable();
            $table->string("email", 255)->nullable();
            $table->string("password", 255)->nullable();
            $table->string("last_name", 255)->nullable();
            $table->string("full_address", 255)->nullable();
            $table->integer("birth_year")->unsigned();
            $table->integer("birth_month")->unsigned();
            $table->integer("birth_day")->unsigned();
			$table->decimal("latitude",10,7)->nullable();
            $table->decimal("longitude",10,7)->nullable();
            $table->integer("able_min")->unsigned();
            $table->integer("able_max")->unsigned();
			$table->tinyinteger("availability")->unsigned();
            $table->enum("gender", ['M','F']);
			$table->text("about_me");
			$table->string("file")->nullable();
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
        Schema::dropIfExists('users');
    }
}
