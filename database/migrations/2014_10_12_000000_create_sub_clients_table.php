<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('sub_clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('client_id'); // OK ✅
            $table->string('fullName')->nullable();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->rememberToken();
            $table->timestamps();
            $table->foreign('client_id')->references('id')->on('clients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sub_clients');
    }
}
