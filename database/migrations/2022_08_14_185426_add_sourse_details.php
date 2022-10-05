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
        Schema::table('transcodes', function (Blueprint $table) {
            $table->uuid('source_video_id')->after('id')->nullable();
            $table->uuid('user_id')->after('id')->nullable();
            $table->string('disk')->nullable()->default(null)->after('check_try');
            $table->json('creation_meta')->nullable()->after('check_try');
            $table->json('progress')->nullable()->default(null)->after('check_try');

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
