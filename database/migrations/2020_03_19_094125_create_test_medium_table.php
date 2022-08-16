<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestMediumTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_medium', function (Blueprint $table) {
            $table->id()->comment('检验介质id');
            $table->string('code', 32)->comment('检验介质编码 OBR[4][0]');
            $table->string('name', 32)->comment('检验介质名称 OBR[4][1]');

            $table->string('created_ip', 32)->nullable()->comment('创建ip');
            $table->string('updated_ip', 32)->nullable()->comment('更新ip');

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
        Schema::dropIfExists('test_medium');
    }
}
