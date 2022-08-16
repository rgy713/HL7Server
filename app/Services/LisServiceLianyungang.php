<?php
/**
 * Created by PhpStorm.
 * User: rgy
 * Date: 2020/3/19
 * Time: 17:39
 */

namespace App\Services;


use App\Models\LaboratoryIndex;
use App\Models\TestItem;
use App\Models\TestMedium;
use App\Models\TestResult;
use Aranyasen\HL7\Message;
use Illuminate\Support\Facades\Log;
use Symfony\Component\ErrorHandler\Debug;

class LisServiceLianyungang
{
    public function __construct()
    {
        $testItems = app(SettingService::class)->getTestItems();

        if(isset($testItems)){
            $testItems = explode(",", $testItems);
        }

        $this->test_items = $testItems;
    }

    public function pushLaboratoryIndex(Message $hl7msg)
    {
        $msh = $hl7msg->getSegmentsByName("MSH")[0];
        $pid = $hl7msg->getSegmentsByName("PID")[0];
        $pv1 = $hl7msg->getSegmentsByName("PV1")[0];

        $hospitalization_number = trim($pid->getField(2));
        if(!isset($hospitalization_number) || empty($hospitalization_number)) return null;


        $check = app(SettingService::class)->checkHospitalizationNumber($hospitalization_number);

        if(!$check) return null;

        /*
           $beds = app(SettingService::class)->getBeds();

           $bed_number = trim($pv1->getField(3)[2]);

           if(!isset($beds) || !in_array($bed_number, $beds)){
               return null;
           }
        */

        $patient_number = trim($pid->getField(2));
        $send_time = trim($msh->getField(7));
        $number = trim($pid->getField(2));
        if(is_array($pid->getField(5))){
            $patient_name = trim($pid->getField(5)[0]);
        }else{
            $patient_name = trim($pid->getField(5));
        }
        
        $labIndex = LaboratoryIndex::updateOrCreate(
            [
                'patient_number' => !empty($patient_number) ? $patient_number : null,
                'hospitalization_number' => $hospitalization_number,
                'send_time' => !empty($send_time) ? $send_time : null
            ],
            [
                'number' => !empty($number) ? $number : null,
                'patient_name' => !empty($patient_name) ? $patient_name : null,
                //'bed_number' => trim($pv1->getField(3)[2]),
                //'ward_code' => trim($pv1->getField(3)[1]),
                //'ward_name' => trim($pv1->getField(3)[7]),
                //'doctor_number' => trim($pv1->getField(7)[0]),
                //'doctor_name' => trim($pv1->getField(7)[1])
            ]
        );

        return $labIndex;
    }

    public function pushResults(Message $hl7msg, $laboratoryIndexId)
    {
        $obrs = $hl7msg->getSegmentsByName("OBR");
        $obrs_count = count($obrs);
        $obxs = $hl7msg->getSegmentsByName("OBX");
        $obxs_count = count($obxs);

        $obr_index = -1;
        $obx_index = 0;

        while ($obx_index < $obxs_count) {
            $obx = $obxs[$obx_index];

            $obx_number = trim($obx->getField(1));
            if ($obx_number == 1) {
                $obr_index++;
                if ($obr_index >= $obrs_count) {
                    break;
                }

                $obr = $obrs[$obr_index];

                $testMedium = $this->pushTestMedium($obr);
            }

            if (isset($testMedium)) {
                $this->pushTestItemAndResult($obx, $laboratoryIndexId, $testMedium->id);
            }

            $obx_index++;
        }

    }

    public function pushTestMedium($obr)
    {
        $code = $this->get2($obr, 4, 0);

        $testMedium = null;
        if (!empty($code)) {
            $name = $this->get2($obr, 4, 1);

            $testMedium = TestMedium::updateOrCreate(
                ['code' => $code ],
                [
                    'name' => !empty($name) ? $name : null
                ]
            );
        }

        return $testMedium;
    }

    public function pushTestItemAndResult($obx, $laboratoryIndexId, $testMediumId)
    {
        $code = $this->get2($obx, 3, 0);
        $name = $this->get2($obx, 3, 1);
        $value = (double)trim($obx->getField(5));

        if(isset($this->test_items) && !empty($this->test_items) && !in_array($code, $this->test_items)){
            return false;
        }

        $minmax = trim($obx->getField(7));
        if (empty($minmax)) {
            $min = null;
            $max = null;
        }
        else {
            $min = $minmax;
            $max = $minmax;
            $pattern = '/[^0-9\.\-]/';
            if(preg_match($pattern, $minmax, $matches, PREG_OFFSET_CAPTURE)) {
                $index = $matches[0][1];
                $minmax = substr($minmax, 0, $index);
            }

            $minmax = str_replace('--', '-', $minmax);
            $minmax = explode('-', $minmax);
            $min = $minmax[0];
            if (count($minmax) > 1) {
                $max = $minmax[1];
            }
        }

        $unit = $obx->getField(6);
        if (is_array($unit)) {
            $unit = $this->get2($obx, 6, 1);
        }

        $testItemId = trim($obx->getField(4));
        if (!empty($testItemId)) {
            $testItem = TestItem::updateOrCreate(
                ['id' => $testItemId ],
                [
                    'test_medium_id' => $testMediumId,
                    'code' => !empty($code) ? $code : null,
                    'name' => !empty($name) ? $name : null,
                    'unit' => !empty($unit) ? $unit : null,
                    'min' => $min,
                    'max' => $max
                ]
            );

            $testResult = TestResult::updateOrCreate(
                [
                    'laboratory_index_id' => $laboratoryIndexId,
                    'test_item_id' => $testItem->id,
                    'test_medium_id' => $testMediumId
                ],
                [
                    'code' => !empty($code) ? $code : null,
                    'name' => !empty($name) ? $name : null,
                    'value' => $value,
                    'unit' => !empty($unit) ? $unit : null,
                    'min' => $min,
                    'max' => $max
                ]
            );
        }
    }

    private function get2($obj, $index1, $index2)
    {
        if ($index2 > 0) {
            return trim(!is_array($obj->getField($index1)) ? '' : $obj->getField($index1)[$index2] ?? '');
        }
        else {
            return trim(!is_array($obj->getField($index1)) ? $obj->getField($index1) : $obj->getField($index1)[0] ?? '');
        }
    }

    private function get3($obj, $index1, $index2, $index3)
    {
        if ($index3 > 0) {
            return trim(!is_array($obj->getField($index1)) || !isset($obj->getField($index1)[$index2]) || !is_array($obj->getField($index1)[$index2]) ? '' : $obj->getField($index1)[$index2][$index3] ?? '');
        }
        else {
            return trim(!is_array($obj->getField($index1)) || !isset($obj->getField($index1)[$index2]) ? '' :
                (!is_array($obj->getField($index1)[$index2]) ? $obj->getField($index1)[$index2] : $obj->getField($index1)[$index2][0] ?? ''));
        }
    }

}
