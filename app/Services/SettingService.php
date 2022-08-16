<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2020/11/19
 * Time: 17:06
 */

namespace App\Services;


use App\Models\PatientInformation;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class SettingService
{
    public function getDepartments()
    {
        $data = Setting::select("condition")
            ->where("field_name", "departments")
            ->first();

        if(isset($data) && isset($data["condition"])){
            return  $data["condition"];
        }
        else{
            return null;
        }
    }

    public function setDepartments($params)
    {
        Setting::updateOrCreate(
            ["field_name"=>"departments"],
            ["condition"=>$params["departments"]]
        );
    }

    public function getBeds()
    {
        $query = PatientInformation::select("bed");

        $departments = app(SettingService::class)->getDepartments();

        if(isset($departments)){
            $departments = explode(",", $departments);

            $query->whereIn("department_id", $departments);
        }

        $data = $query->groupBy("bed")
            ->pluck("bed")->toArray();

        return $data;
    }

    public function checkHospitalizationNumber($hospitalization_number)
    {
        $patient_info = PatientInformation::where("hospitalization_number", $hospitalization_number)->first();

        return isset($patient_info);
    }

    public function getTestItems()
    {
        $data = Setting::select("condition")
            ->where("field_name", "test_items")
            ->first();

        if(isset($data) && isset($data["condition"])){
            return  $data["condition"];
        }
        else{
            return null;
        }
    }

    public function setTestItems($params)
    {
        Setting::updateOrCreate(
            ["field_name"=>"test_items"],
            ["condition"=>$params["test_items"]]
        );
    }
}