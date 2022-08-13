<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transcodes', function (Blueprint $table) {
            $table->integer('duration')->nullable()->after('check_try');
            $table->text('hls_playlist')->nullable()->after('check_try');
            $table->text('thumbnail_url')->nullable()->after('check_try');
            $table->text('tooltip_url')->nullable()->after('check_try');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
