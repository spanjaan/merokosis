<?php

namespace Martin\Forms\Updates;

use Winter\Storm\Support\Facades\Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class CreateRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('spanjaan_workspace_records', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('c_name')->nullable();
            $table->string('c_address')->nullable();
            $table->string('c_mobile')->nullable();
            $table->string('c_email')->nullable();
            $table->text('c_comment')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('spanjaan_workspace_records');
    }
}
