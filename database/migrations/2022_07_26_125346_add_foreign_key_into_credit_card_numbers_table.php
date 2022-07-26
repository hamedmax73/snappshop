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
        Schema::table('credit_card_numbers', function (Blueprint $table) {
            $table->foreign('account_number_id')
                ->references('id')
                ->on('account_numbers')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('credit_card_numbers', function (Blueprint $table) {
            $table->dropForeign('credit_card_numbers_account_number_id_foreign');
        });
    }
};
