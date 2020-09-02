<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSyncStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sync_statuses', function (Blueprint $table) {
            $table->id();
            
            $table->string('type');
            $table->string('status');
            $table->string('file_name');
            $table->string('url');
            $table->unsignedBigInteger('rows');
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
        Schema::dropIfExists('sync_statuses');
    }
}
