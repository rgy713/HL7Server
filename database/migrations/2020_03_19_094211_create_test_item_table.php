<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_item', function (Blueprint $table) {
            $table->id()->comment('检查项id 医技项目代码 OBX[3][0]');
            $table->unsignedBigInteger('test_medium_id')->comment('检验介质id');
            $table->string('code', 32)->comment('医技项目名称 OBX[3][1]');
            $table->string('name', 32)->comment('医技项目中文名称 OBX[4]');
            $table->string('unit', 32)->nullable()->comment('项目单位 OBX[6]');
            $table->double('min')->nullable()->comment('结果参考值min OBX[7][0]*');
            $table->double('max')->nullable()->comment('结果参考值max OBX[7][1]*');

            $table->string('created_ip', 32)->nullable()->comment('创建ip');
            $table->string('updated_ip', 32)->nullable()->comment('更新ip');

            $table->timestamps();

            $table->foreign('test_medium_id')->references('id')->on('test_medium');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_item');
    }
}
