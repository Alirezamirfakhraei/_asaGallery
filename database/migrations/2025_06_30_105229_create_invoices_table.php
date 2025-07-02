<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->string('client_id'); // ارتباط با کلاینت - به‌صورت string
            $table->string('code')->nullable();
            $table->string('type')->nullable();
            $table->string('sanadCode')->nullable();
            $table->string('comment')->nullable();
            $table->string('customerName')->nullable();
            $table->string('customerErpCode')->nullable();
            $table->string('date')->nullable();
            $table->string('time')->nullable();
            $table->string('sumNaghd')->nullable();
            $table->string('sumNesiyeh')->nullable();
            $table->string('sumDiscount')->nullable();
            $table->string('sumCheck')->nullable();
            $table->string('sumScot')->nullable();
            $table->string('sumPrice')->nullable();
            $table->string('erpCode')->nullable();
            $table->text('detail')->nullable();
            $table->text('serials')->nullable();

            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
