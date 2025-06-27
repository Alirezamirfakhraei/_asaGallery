<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('isPurchaser')->default(false);
            $table->boolean('isSeller')->default(false);
            $table->boolean('isBlackList')->default(false);
            $table->boolean('isVaseteh')->default(false);
            $table->string('vasetehPorsant')->nullable();
            $table->string('mandeh')->nullable();
            $table->string('credit')->nullable();
            $table->string('mobile')->nullable();
            $table->string('address')->nullable();
            $table->string('erpCode')->nullable();
            $table->string('type')->nullable();
            $table->boolean('isActive')->default(true);
            $table->string('selectedPriceType')->nullable();
            $table->boolean('isAmer')->default(false);
            $table->string('sumFloatCheques')->nullable();
            $table->string('sumFloatNotCashedCheques')->nullable();
            $table->string('debtInCheques')->nullable();
            $table->string('creditDiff')->nullable();
            $table->boolean('sellerWithTax')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clients');
    }
}

