<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_card_numbers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_number_id')->index();
            $table->string('card_number','16')->unique();// based on ISO-13616 standard
            $table->date('expiry');
            $table->string('cvv','4');
            $table->softDeletes();
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
        Schema::dropIfExists('credit_card_numbers');
    }
};
