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

class LisService
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
        $pid = $hl7msg->getSegmentsByName("PID")[0];
        $pv1 = $hl7msg->getSegmentsByName("PV1")[0];
        $obr = $hl7msg->getSegmentsByName("OBR")[0];

        $hospitalization_number = trim($pid->getField(3)[1]);
        if(!isset($hospitalization_number) || empty($hospitalization_number)) return null;

        $check = app(SettingService::class)->checkHospitalizationNumber($hospitalization_number);

        if(!$check) return null;

        /*$beds = app(SettingService::class)->getBeds();

        $bed_number = trim($pv1->getField(3)[2]);

        if(!isset($beds) || !in_array($bed_number, $beds)){
            return false;
        }*/
        $patient_number = $this->get2($pid, 3, 0);
        $send_time = trim($obr->getField(8));
        $number = trim($pid->getField(1));
        $patient_name = trim($pid->getField(5));
        $bed_number = $this->get2($pv1, 3, 2);
        $ward_code = $this->get2($pv1, 3, 6);
        $ward_name = $this->get2($pv1, 3, 7);
        $doctor_number = $this->get2($obr, 10, 0);
        $doctor_name = $this->get2($obr, 10, 1);

        $labIndex = LaboratoryIndex::updateOrCreate(
            [
                'patient_number' => !empty($patient_number) ? $patient_number : null,
                'hospitalization_number' => $hospitalization_number,
                'send_time' => !empty($send_time) ? $send_time : null
            ],
            [
                'number' => !empty($number) ? $number : null,
                'patient_name' => !empty($patient_name) ? $patient_name : null,
                'bed_number' => !empty($bed_number) ? $bed_number : null,
                'ward_code' => !empty($ward_code) ? $ward_code : null,
                'ward_name' => !empty($ward_name) ? $ward_name : null,
                'doctor_number' => !empty($doctor_number) ? $doctor_number : null,
                'doctor_name' => !empty($doctor_name) ? $doctor_name : null
            ]
        );

        return $labIndex;
    }

    public function pushTestMedium(Message $hl7msg)
    {
        $obr = $hl7msg->getSegmentsByName("OBR")[0];

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

    public function pushTestItemAndResults(Message $hl7msg, $laboratoryIndexId, $testMediumId)
    {
        $obxs = $hl7msg->getSegmentsByName("OBX");
        for ($i = 0; $i < count($obxs); $i++) {
            $obx = $obxs[$i];

            $testItemId = $this->get2($obx, 3, 0);

            if(isset($this->test_items) && !empty($this->test_items) && !in_array($testItemId, $this->test_items)){
               continue;
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

            $code = $this->get2($obx, 3, 1);
            $name = trim($obx->getField(4));
            $value = trim($obx->getField(5));
            $unit = trim($obx->getField(6));

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
