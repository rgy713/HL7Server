<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLaboratoryIndexTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laboratory_index', function (Blueprint $table) {
            $table->id()->comment('实验室指标id');
            $table->string('patient_number',30)->comment('患者院内标识符 PID[3][0]');
            $table->string('hospitalization_number',30)->comment('住院病历号/门诊病历号 PID[3][1]');
            $table->string('patient_name',32)->comment('患者姓名 PID[5]');
            $table->string('number',32)->comment('实验室指标编号 PID[1]');
            $table->string('bed_number',32)->comment('床位号 PV1[3][2]');
            $table->string('ward_code',32)->comment('病区编码 PV1[3][6]');
            $table->string('ward_name',32)->nullable()->comment('病区名称 PV1[3][7]');
            $table->timestamp('send_time')->comment('采样时间 OBR[8]');
            $table->string('doctor_number', 32)->nullable()->comment('采样医生编码 OBR[10][0]');
            $table->string('doctor_name', 32)->nullable()->comment('采样医生姓名 OBR[10][1]');
            $table->string('created_ip', 32)->nullable()->comment('创建ip');
            $table->string('updated_ip', 32)->nullable()->comment('更新ip');

            $table->timestamps();

            $table->unique(['patient_number', 'hospitalization_number', 'send_time'], 'patient_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('laboratory_index');
    }
}
