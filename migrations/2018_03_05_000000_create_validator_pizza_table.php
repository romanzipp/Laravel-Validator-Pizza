<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQueueMonitorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = config('validator-pizza.checks_table');

        Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');

            $table->string('domain')->unique();

            $table->boolean('mx');
            $table->boolean('disposable');

            $table->integer('hits')->default(1);

            $table->timestamp('last_queried');

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
        $tableName = config('validator-pizza.checks_table');

        Schema::drop($tableName);
    }
}
