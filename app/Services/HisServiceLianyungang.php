<?php
/**
 * Created by PhpStorm.
 * User: rgy
 * Date: 2020/3/19
 * Time: 17:39
 */

namespace App\Services;


use App\Models\PatientInformation;
use Aranyasen\HL7\Message;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HisServiceLianyungang
{
    public function send(Message $hl7msg)
    {

        $evn = $hl7msg->getSegmentsByName("EVN")[0];
        $tran_code = trim($evn->getField(1));
        $pv1 = $hl7msg->getSegmentsByName("PV1")[0];
        $ward_id_num = $this->get2($pv1, 3, 1);

        $ward_arr = app(SettingService::class)->getDepartments();
        if(isset($ward_arr)){
            $ward_arr = explode(",", $ward_arr);
        }else{
            $ward_arr = array();
        }

        if(!empty($ward_arr)){
            if(in_array($ward_id_num, $ward_arr)){
                if ($tran_code == "C") {
                    $this->enter($hl7msg);
                }
                else if ($tran_code == "T") {
                    $this->transfer($hl7msg);
                }
                else if ($tran_code == "N") {
                    $this->cancel_exit($hl7msg);
                }
                else if ($tran_code == "V"  || $tran_code == "Y") {
                    $this->move_ward($hl7msg);
                }
                else if ($tran_code == "K") {
                    $this->move_department($hl7msg);
                }
                else if ($tran_code == "P") {
                    $this->move_bed($hl7msg);
                }
                else if ($tran_code == "B") {
                    $this->move_room($hl7msg);
                }
                else if ($tran_code == "F") {
                    $this->exit($hl7msg);
                }
                else if ($tran_code == "D" || $tran_code == "O") {
                    $this->cancel_enter($hl7msg);
                }
                else if ($tran_code == "X" || $tran_code == "U") {
                    $this->move_exit($hl7msg);
                }
            }
        }else{
            if ($tran_code == "C") {
            $this->enter($hl7msg);
            }
            else if ($tran_code == "T") {
                $this->transfer($hl7msg);
            }
            else if ($tran_code == "N") {
                $this->cancel_exit($hl7msg);
            }
            else if ($tran_code == "V"  || $tran_code == "Y") {
                $this->move_ward($hl7msg);
            }
            else if ($tran_code == "K") {
                $this->move_department($hl7msg);
            }
            else if ($tran_code == "P") {
                $this->move_bed($hl7msg);
            }
            else if ($tran_code == "B") {
                $this->move_room($hl7msg);
            }
            else if ($tran_code == "F") {
                $this->exit($hl7msg);
            }
            else if ($tran_code == "D" || $tran_code == "O") {
                $this->cancel_enter($hl7msg);
            }
            else if ($tran_code == "X" || $tran_code == "U") {
                $this->move_exit($hl7msg);
            }
        }
        
    }

    private function enter(Message $hl7msg)
    {
        $evn = $hl7msg->getSegmentsByName("EVN")[0];
        $pid = $hl7msg->getSegmentsByName("PID")[0];
        $pv1 = $hl7msg->getSegmentsByName("PV1")[0];
        $dg1 = $hl7msg->getSegmentsByName("DG1")[0];
        $nk1 = $hl7msg->getSegmentsByName("NK1")[0];

        $hospitalization_number = trim($pid->getField(2));
        $tran_code = trim($evn->getField(1));
        $recode_time = trim($evn->getField(2));
        $patient_name = $this->get2($pid, 5, 0);
        $sex_code = trim($pid->getField(8));
        $sex_name = $this->getGB2261_80($sex_code);
        $birthday = trim($pid->getField(7));
        $phone_number = trim($nk1->getField(5));
        $marital_code = trim($pid->getField(16));
        $marital_name = $this->getGB4766_84($marital_code);
        $ethnic_group = trim($pid->getField(22));
        $nationality = trim($pid->getField(28));
        $enter_time = trim($pv1->getField(44));
        $ward_id = $this->get2($pv1, 3, 1);
        $ward_name = $this->get3($pv1, 3, 9, 1);
        $department_id = $this->get2($pv1, 3, 0);
        $department_name = $this->get3($pv1, 3, 9, 0);
        $room = $this->get2($pv1, 3, 3);
        $bed = $this->get2($pv1, 3, 2);
        $patient_class = trim($pv1->getField(2));
        $diagnosis_time = trim($dg1->getField(5));
        $diagnosis_type_id = trim($dg1->getField(6));
        $diagnosis_code_id = $this->get2($dg1, 3, 0);
        $diagnosis_code_text = $this->get2($dg1, 3, 1);

        PatientInformation::updateOrCreate(
            ['id' => trim($pid->getField(2))],
            [
                'hospitalization_number' => !empty($hospitalization_number) ? $hospitalization_number : null,
                'tran_code' => !empty($tran_code) ? $tran_code : null,
                'recode_time' => !empty($recode_time) ? $recode_time : null,
                'hospital_name' => '连云港',
                'event_name' => '新入',
                'patient_name' => !empty($patient_name) ? $patient_name : null,
                'sex_code' => !empty($sex_code) ? $sex_code : null,
                'sex_name' => $sex_name,
                'birthday' => !empty($birthday) ? $birthday : null,
                'phone_number' => !empty($phone_number) ? $phone_number : null,
                'marital_code' => !empty($marital_code) ? $marital_code : null,
                'marital_name' => $marital_name,
                'ethnic_group' => !empty($ethnic_group) ? $ethnic_group : null,
                'nationality' => !empty($nationality) ? $nationality : null,
                //'visit_status_id' => trim($data['Request']['Body']['PatientVisit']['VisitStatus']['Identifier']),
                //'visit_status_text' => trim($data['Request']['Body']['PatientVisit']['VisitStatus']['Text']),
                //'admission_type' => trim($data['Request']['Body']['PatientVisit']['AdmissionType']),
                //'admit_source_id' => trim($data['Request']['Body']['PatientVisit']['AdmitSource']['Identifier']),
                //'admit_source_text' => trim($data['Request']['Body']['PatientVisit']['AdmitSource']['Text']),
                //'handle_type_id' => trim($data['Request']['Body']['PatientVisit']['HandleList'][0]['Type']['Identifier']),
                //'handle_type_text' => trim($data['Request']['Body']['PatientVisit']['HandleList'][0]['Type']['Text']),
                'enter_time' => !empty($enter_time) ? $enter_time : null,
                //'exit_time' => trim($data['Request']['Body']['PatientVisit']['HandleList'][0]['HandleTime']),
                'ward_id' => !empty($ward_id) ? $ward_id : null,
                'ward_name' => !empty($ward_name) ? $ward_name : null,
                'department_id' => !empty($department_id) ? $department_id : null,
                'department_name' => !empty($department_name) ? $department_name : null,
                'room' => !empty($room) ? $room : null,
                'bed' => !empty($bed) ? $bed : null,
                'patient_class' => !empty($patient_class) ? $patient_class : null,
                'diagnosis_time' => !empty($diagnosis_time) ? $diagnosis_time : null,
                //'diagnosis_class_id' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisClass']['Identifier']),
                //'diagnosis_class_text' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisClass']['Text']),
                'diagnosis_type_id' => !empty($diagnosis_type_id) ? $diagnosis_type_id : null,
                //'diagnosis_type_text' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisType']['Text']),
                'diagnosis_code_id' => !empty($diagnosis_code_id) ? $diagnosis_code_id : null,
                'diagnosis_code_text' => !empty($diagnosis_code_text) ? $diagnosis_code_text : null,
                'created_ip' => \Request::getClientIp()
            ]
        );
    }

    private function transfer(Message $hl7msg)
    {
        $evn = $hl7msg->getSegmentsByName("EVN")[0];
        $pid = $hl7msg->getSegmentsByName("PID")[0];
        $pv1 = $hl7msg->getSegmentsByName("PV1")[0];
        $dg1 = $hl7msg->getSegmentsByName("DG1")[0];
        $nk1 = $hl7msg->getSegmentsByName("NK1")[0];

        $hospitalization_number = trim($pid->getField(2));
        $tran_code = trim($evn->getField(1));
        $recode_time = trim($evn->getField(2));
        $patient_name = $this->get2($pid, 5, 0);
        $sex_code = trim($pid->getField(8));
        $sex_name = $this->getGB2261_80($sex_code);
        $birthday = trim($pid->getField(7));
        $phone_number = trim($nk1->getField(5));
        $marital_code = trim($pid->getField(16));
        $marital_name = $this->getGB4766_84($marital_code);
        $ethnic_group = trim($pid->getField(22));
        $nationality = trim($pid->getField(28));
        $enter_time = trim($pv1->getField(44));
        $ward_id = $this->get2($pv1, 3, 1);
        $ward_name = $this->get3($pv1, 3, 9, 1);
        $department_id = $this->get2($pv1, 3, 0);
        $department_name = $this->get3($pv1, 3, 9, 0);
        $room = $this->get2($pv1, 3, 3);
        $bed = $this->get2($pv1, 3, 2);
        $patient_class = trim($pv1->getField(2));
        $diagnosis_time = trim($dg1->getField(5));
        $diagnosis_type_id = trim($dg1->getField(6));
        $diagnosis_code_id = $this->get2($dg1, 3, 0);
        $diagnosis_code_text = $this->get2($dg1, 3, 1);

        PatientInformation::updateOrCreate(
            ['id' => trim($pid->getField(2))],
            [
                'hospitalization_number' => !empty($hospitalization_number) ? $hospitalization_number : null,
                'tran_code' => !empty($tran_code) ? $tran_code : null,
                'recode_time' => !empty($recode_time) ? $recode_time : null,
                'hospital_name' => '连云港',
                'event_name' => '转入',
                'patient_name' => !empty($patient_name) ? $patient_name : null,
                'sex_code' => !empty($sex_code) ? $sex_code : null,
                'sex_name' => $sex_name,
                'birthday' => !empty($birthday) ? $birthday : null,
                'phone_number' => !empty($phone_number) ? $phone_number : null,
                'marital_code' => !empty($marital_code) ? $marital_code : null,
                'marital_name' => $marital_name,
                'ethnic_group' => !empty($ethnic_group) ? $ethnic_group : null,
                'nationality' => !empty($nationality) ? $nationality : null,
                //'visit_status_id' => trim($data['Request']['Body']['PatientVisit']['VisitStatus']['Identifier']),
                //'visit_status_text' => trim($data['Request']['Body']['PatientVisit']['VisitStatus']['Text']),
                //'admission_type' => trim($data['Request']['Body']['PatientVisit']['AdmissionType']),
                //'admit_source_id' => trim($data['Request']['Body']['PatientVisit']['AdmitSource']['Identifier']),
                //'admit_source_text' => trim($data['Request']['Body']['PatientVisit']['AdmitSource']['Text']),
                //'handle_type_id' => trim($data['Request']['Body']['PatientVisit']['HandleList'][0]['Type']['Identifier']),
                //'handle_type_text' => trim($data['Request']['Body']['PatientVisit']['HandleList'][0]['Type']['Text']),
                'enter_time' => !empty($enter_time) ? $enter_time : null,
                'exit_time' => null,
                'ward_id' => !empty($ward_id) ? $ward_id : null,
                'ward_name' => !empty($ward_name) ? $ward_name : null,
                'department_id' => !empty($department_id) ? $department_id : null,
                'department_name' => !empty($department_name) ? $department_name : null,
                'room' => !empty($room) ? $room : null,
                'bed' => !empty($bed) ? $bed : null,
                'patient_class' => !empty($patient_class) ? $patient_class : null,
                'diagnosis_time' => !empty($diagnosis_time) ? $diagnosis_time : null,
                //'diagnosis_class_id' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisClass']['Identifier']),
                //'diagnosis_class_text' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisClass']['Text']),
                'diagnosis_type_id' => !empty($diagnosis_type_id) ? $diagnosis_type_id : null,
                //'diagnosis_type_text' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisType']['Text']),
                'diagnosis_code_id' => !empty($diagnosis_code_id) ? $diagnosis_code_id : null,
                'diagnosis_code_text' => !empty($diagnosis_code_text) ? $diagnosis_code_text : null,
                'created_ip' => \Request::getClientIp()
            ]
        );
    }

    private function cancel_exit(Message $hl7msg)
    {
        $evn = $hl7msg->getSegmentsByName("EVN")[0];
        $pid = $hl7msg->getSegmentsByName("PID")[0];
        $pv1 = $hl7msg->getSegmentsByName("PV1")[0];
        $dg1 = $hl7msg->getSegmentsByName("DG1")[0];
        $nk1 = $hl7msg->getSegmentsByName("NK1")[0];

        $hospitalization_number = trim($pid->getField(2));
        $tran_code = trim($evn->getField(1));
        $recode_time = trim($evn->getField(2));
        $patient_name = $this->get2($pid, 5, 0);
        $sex_code = trim($pid->getField(8));
        $sex_name = $this->getGB2261_80($sex_code);
        $birthday = trim($pid->getField(7));
        $phone_number = trim($nk1->getField(5));
        $marital_code = trim($pid->getField(16));
        $marital_name = $this->getGB4766_84($marital_code);
        $ethnic_group = trim($pid->getField(22));
        $nationality = trim($pid->getField(28));
        $enter_time = trim($pv1->getField(44));
        $ward_id = $this->get2($pv1, 3, 1);
        $ward_name = $this->get3($pv1, 3, 9, 1);
        $department_id = $this->get2($pv1, 3, 0);
        $department_name = $this->get3($pv1, 3, 9, 0);
        $room = $this->get2($pv1, 3, 3);
        $bed = $this->get2($pv1, 3, 2);
        $patient_class = trim($pv1->getField(2));
        $diagnosis_time = trim($dg1->getField(5));
        $diagnosis_type_id = trim($dg1->getField(6));
        $diagnosis_code_id = $this->get2($dg1, 3, 0);
        $diagnosis_code_text = $this->get2($dg1, 3, 1);

        PatientInformation::updateOrCreate(
            ['id' => trim($pid->getField(2))],
            [
                'hospitalization_number' => !empty($hospitalization_number) ? $hospitalization_number : null,
                'tran_code' => !empty($tran_code) ? $tran_code : null,
                'recode_time' => !empty($recode_time) ? $recode_time : null,
                'hospital_name' => '连云港',
                'event_name' => '取消出院',
                'patient_name' => !empty($patient_name) ? $patient_name : null,
                'sex_code' => !empty($sex_code) ? $sex_code : null,
                'sex_name' => $sex_name,
                'birthday' => !empty($birthday) ? $birthday : null,
                'phone_number' => !empty($phone_number) ? $phone_number : null,
                'marital_code' => !empty($marital_code) ? $marital_code : null,
                'marital_name' => $marital_name,
                'ethnic_group' => !empty($ethnic_group) ? $ethnic_group : null,
                'nationality' => !empty($nationality) ? $nationality : null,
                //'visit_status_id' => trim($data['Request']['Body']['PatientVisit']['VisitStatus']['Identifier']),
                //'visit_status_text' => trim($data['Request']['Body']['PatientVisit']['VisitStatus']['Text']),
                //'admission_type' => trim($data['Request']['Body']['PatientVisit']['AdmissionType']),
                //'admit_source_id' => trim($data['Request']['Body']['PatientVisit']['AdmitSource']['Identifier']),
                //'admit_source_text' => trim($data['Request']['Body']['PatientVisit']['AdmitSource']['Text']),
                //'handle_type_id' => trim($data['Request']['Body']['PatientVisit']['HandleList'][0]['Type']['Identifier']),
                //'handle_type_text' => trim($data['Request']['Body']['PatientVisit']['HandleList'][0]['Type']['Text']),
                'enter_time' => !empty($enter_time) ? $enter_time : null,
                'exit_time' => null,
                'ward_id' => !empty($ward_id) ? $ward_id : null,
                'ward_name' => !empty($ward_name) ? $ward_name : null,
                'department_id' => !empty($department_id) ? $department_id : null,
                'department_name' => !empty($department_name) ? $department_name : null,
                'room' => !empty($room) ? $room : null,
                'bed' => !empty($bed) ? $bed : null,
                'patient_class' => !empty($patient_class) ? $patient_class : null,
                'diagnosis_time' => !empty($diagnosis_time) ? $diagnosis_time : null,
                //'diagnosis_class_id' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisClass']['Identifier']),
                //'diagnosis_class_text' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisClass']['Text']),
                'diagnosis_type_id' => !empty($diagnosis_type_id) ? $diagnosis_type_id : null,
                //'diagnosis_type_text' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisType']['Text']),
                'diagnosis_code_id' => !empty($diagnosis_code_id) ? $diagnosis_code_id : null,
                'diagnosis_code_text' => !empty($diagnosis_code_text) ? $diagnosis_code_text : null,
                'created_ip' => \Request::getClientIp()
            ]
        );
    }

    private function move_department(Message $hl7msg)
    {
        $evn = $hl7msg->getSegmentsByName("EVN")[0];
        $pid = $hl7msg->getSegmentsByName("PID")[0];
        $pv1 = $hl7msg->getSegmentsByName("PV1")[0];
        $dg1 = $hl7msg->getSegmentsByName("DG1")[0];
        $nk1 = $hl7msg->getSegmentsByName("NK1")[0];

        $hospitalization_number = trim($pid->getField(2));
        $tran_code = trim($evn->getField(1));
        $recode_time = trim($evn->getField(2));
        $patient_name = $this->get2($pid, 5, 0);
        $sex_code = trim($pid->getField(8));
        $sex_name = $this->getGB2261_80($sex_code);
        $birthday = trim($pid->getField(7));
        $phone_number = trim($nk1->getField(5));
        $marital_code = trim($pid->getField(16));
        $marital_name = $this->getGB4766_84($marital_code);
        $ethnic_group = trim($pid->getField(22));
        $nationality = trim($pid->getField(28));
        $enter_time = trim($pv1->getField(44));
        $ward_id = $this->get2($pv1, 3, 1);
        $ward_name = $this->get3($pv1, 3, 9, 1);
        $department_id = $this->get2($pv1, 3, 0);
        $department_name = $this->get3($pv1, 3, 9, 0);
        $room = $this->get2($pv1, 3, 3);
        $bed = $this->get2($pv1, 3, 2);
        $patient_class = trim($pv1->getField(2));
        $diagnosis_time = trim($dg1->getField(5));
        $diagnosis_type_id = trim($dg1->getField(6));
        $diagnosis_code_id = $this->get2($dg1, 3, 0);
        $diagnosis_code_text = $this->get2($dg1, 3, 1);

        $prior_department_id = $this->get2($pv1, 6, 0);
        $prior_ward_id = $this->get2($pv1, 6, 1);
        $prior_bed = $this->get2($pv1, 6, 2);
        $prior_room = $this->get2($pv1, 6, 3);

        PatientInformation::updateOrCreate(
            ['id' => trim($pid->getField(2))],
            [
                    'hospitalization_number' => !empty($hospitalization_number) ? $hospitalization_number : null,
                    'tran_code' => !empty($tran_code) ? $tran_code : null,
                    'recode_time' => !empty($recode_time) ? $recode_time : null,
                    'hospital_name' => '连云港',
                    'event_name' => '转科入床',
                    'patient_name' => !empty($patient_name) ? $patient_name : null,
                    'sex_code' => !empty($sex_code) ? $sex_code : null,
                    'sex_name' => $sex_name,
                    'birthday' => !empty($birthday) ? $birthday : null,
                    'phone_number' => !empty($phone_number) ? $phone_number : null,
                    'marital_code' => !empty($marital_code) ? $marital_code : null,
                    'marital_name' => $marital_name,
                    'ethnic_group' => !empty($ethnic_group) ? $ethnic_group : null,
                    'nationality' => !empty($nationality) ? $nationality : null,
                    'enter_time' => !empty($enter_time) ? $enter_time : null,
                    //'exit_time' => null,
                    'ward_id' => !empty($ward_id) ? $ward_id : null,
                    'ward_name' => !empty($ward_name) ? $ward_name : null,
                    'department_id' => !empty($department_id) ? $department_id : null,
                    'department_name' => !empty($department_name) ? $department_name : null,
                    'room' => !empty($room) ? $room : null,
                    'bed' => !empty($bed) ? $bed : null,
                    'patient_class' => !empty($patient_class) ? $patient_class : null,
                    'diagnosis_time' => !empty($diagnosis_time) ? $diagnosis_time : null,
                    //'diagnosis_class_id' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisClass']['Identifier']),
                    //'diagnosis_class_text' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisClass']['Text']),
                    'diagnosis_type_id' => !empty($diagnosis_type_id) ? $diagnosis_type_id : null,
                    //'diagnosis_type_text' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisType']['Text']),
                    'diagnosis_code_id' => !empty($diagnosis_code_id) ? $diagnosis_code_id : null,
                    'diagnosis_code_text' => !empty($diagnosis_code_text) ? $diagnosis_code_text : null,
                    'prior_department_id' => !empty($prior_department_id) ? $prior_department_id : null,
                    'prior_ward_id' => !empty($prior_ward_id) ? $prior_ward_id : null,
                    'prior_bed' => !empty($prior_bed) ? $prior_bed : null,
                    'prior_room' => !empty($prior_room) ? $prior_room : null,
                    'updated_ip' => \Request::getClientIp()
                ]
            );
    }

    private function move_ward(Message $hl7msg)
    {
        $evn = $hl7msg->getSegmentsByName("EVN")[0];
        $pid = $hl7msg->getSegmentsByName("PID")[0];
        $pv1 = $hl7msg->getSegmentsByName("PV1")[0];
        $dg1 = $hl7msg->getSegmentsByName("DG1")[0];
        $nk1 = $hl7msg->getSegmentsByName("NK1")[0];

        $hospitalization_number = trim($pid->getField(2));
        $tran_code = trim($evn->getField(1));
        $recode_time = trim($evn->getField(2));
        $patient_name = $this->get2($pid, 5, 0);
        $sex_code = trim($pid->getField(8));
        $sex_name = $this->getGB2261_80($sex_code);
        $birthday = trim($pid->getField(7));
        $phone_number = trim($nk1->getField(5));
        $marital_code = trim($pid->getField(16));
        $marital_name = $this->getGB4766_84($marital_code);
        $ethnic_group = trim($pid->getField(22));
        $nationality = trim($pid->getField(28));
        $enter_time = trim($pv1->getField(44));
        $ward_id = $this->get2($pv1, 3, 1);
        $ward_name = $this->get3($pv1, 3, 9, 1);
        $department_id = $this->get2($pv1, 3, 0);
        $department_name = $this->get3($pv1, 3, 9, 0);
        $room = $this->get2($pv1, 3, 3);
        $bed = $this->get2($pv1, 3, 2);
        $patient_class = trim($pv1->getField(2));
        $diagnosis_time = trim($dg1->getField(5));
        $diagnosis_type_id = trim($dg1->getField(6));
        $diagnosis_code_id = $this->get2($dg1, 3, 0);
        $diagnosis_code_text = $this->get2($dg1, 3, 1);

        $prior_department_id = $this->get2($pv1, 6, 0);
        $prior_ward_id = $this->get2($pv1, 6, 1);
        $prior_bed = $this->get2($pv1, 6, 2);
        $prior_room = $this->get2($pv1, 6, 3);

        PatientInformation::updateOrCreate(
            ['id' => trim($pid->getField(2))],
            [
                    'hospitalization_number' => !empty($hospitalization_number) ? $hospitalization_number : null,
                    'tran_code' => !empty($tran_code) ? $tran_code : null,
                    'recode_time' => !empty($recode_time) ? $recode_time : null,
                    'hospital_name' => '连云港',
                    'event_name' => '转病区入床',
                    'patient_name' => !empty($patient_name) ? $patient_name : null,
                    'sex_code' => !empty($sex_code) ? $sex_code : null,
                    'sex_name' => $sex_name,
                    'birthday' => !empty($birthday) ? $birthday : null,
                    'phone_number' => !empty($phone_number) ? $phone_number : null,
                    'marital_code' => !empty($marital_code) ? $marital_code : null,
                    'marital_name' => $marital_name,
                    'ethnic_group' => !empty($ethnic_group) ? $ethnic_group : null,
                    'nationality' => !empty($nationality) ? $nationality : null,
                    'enter_time' => !empty($enter_time) ? $enter_time : null,
                    //'exit_time' => null,
                    'ward_id' => !empty($ward_id) ? $ward_id : null,
                    'ward_name' => !empty($ward_name) ? $ward_name : null,
                    'department_id' => !empty($department_id) ? $department_id : null,
                    'department_name' => !empty($department_name) ? $department_name : null,
                    'room' => !empty($room) ? $room : null,
                    'bed' => !empty($bed) ? $bed : null,
                    'patient_class' => !empty($patient_class) ? $patient_class : null,
                    'diagnosis_time' => !empty($diagnosis_time) ? $diagnosis_time : null,
                    //'diagnosis_class_id' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisClass']['Identifier']),
                    //'diagnosis_class_text' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisClass']['Text']),
                    'diagnosis_type_id' => !empty($diagnosis_type_id) ? $diagnosis_type_id : null,
                    //'diagnosis_type_text' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisType']['Text']),
                    'diagnosis_code_id' => !empty($diagnosis_code_id) ? $diagnosis_code_id : null,
                    'diagnosis_code_text' => !empty($diagnosis_code_text) ? $diagnosis_code_text : null,
                    'prior_department_id' => !empty($prior_department_id) ? $prior_department_id : null,
                    'prior_ward_id' => !empty($prior_ward_id) ? $prior_ward_id : null,
                    'prior_bed' => !empty($prior_bed) ? $prior_bed : null,
                    'prior_room' => !empty($prior_room) ? $prior_room : null,
                    'updated_ip' => \Request::getClientIp()
                ]
            );
    }

    private function move_bed(Message $hl7msg)
    {
        $evn = $hl7msg->getSegmentsByName("EVN")[0];
        $pid = $hl7msg->getSegmentsByName("PID")[0];
        $pv1 = $hl7msg->getSegmentsByName("PV1")[0];
        $dg1 = $hl7msg->getSegmentsByName("DG1")[0];
        $nk1 = $hl7msg->getSegmentsByName("NK1")[0];

        $tran_code = trim($evn->getField(1));
        $recode_time = trim($evn->getField(2));
        $patient_name = $this->get2($pid, 5, 0);
        $sex_code = trim($pid->getField(8));
        $sex_name = $this->getGB2261_80($sex_code);
        $birthday = trim($pid->getField(7));
        $phone_number = trim($nk1->getField(5));
        $marital_code = trim($pid->getField(16));
        $marital_name = $this->getGB4766_84($marital_code);
        $ethnic_group = trim($pid->getField(22));
        $nationality = trim($pid->getField(28));
        $enter_time = trim($pv1->getField(44));
        $ward_id = $this->get2($pv1, 3, 1);
        $ward_name = $this->get3($pv1, 3, 9, 1);
        $department_id = $this->get2($pv1, 3, 0);
        $department_name = $this->get3($pv1, 3, 9, 0);
        $room = $this->get2($pv1, 3, 3);
        $bed = $this->get2($pv1, 3, 2);
        $patient_class = trim($pv1->getField(2));
        $diagnosis_time = trim($dg1->getField(5));
        $diagnosis_type_id = trim($dg1->getField(6));
        $diagnosis_code_id = $this->get2($dg1, 3, 0);
        $diagnosis_code_text = $this->get2($dg1, 3, 1);

        $prior_department_id = $this->get2($pv1, 6, 0);
        $prior_ward_id = $this->get2($pv1, 6, 1);
        $prior_bed = $this->get2($pv1, 6, 2);
        $prior_room = $this->get2($pv1, 6, 3);

        PatientInformation::where('id', trim($pid->getField(2)))
            ->update(
                [
                    'tran_code' => !empty($tran_code) ? $tran_code : null,
                    'recode_time' => !empty($recode_time) ? $recode_time : null,
                    'hospital_name' => '连云港',
                    'event_name' => '换床',
                    'patient_name' => !empty($patient_name) ? $patient_name : null,
                    'sex_code' => !empty($sex_code) ? $sex_code : null,
                    'sex_name' => $sex_name,
                    'birthday' => !empty($birthday) ? $birthday : null,
                    'phone_number' => !empty($phone_number) ? $phone_number : null,
                    'marital_code' => !empty($marital_code) ? $marital_code : null,
                    'marital_name' => $marital_name,
                    'ethnic_group' => !empty($ethnic_group) ? $ethnic_group : null,
                    'nationality' => !empty($nationality) ? $nationality : null,
                    'enter_time' => !empty($enter_time) ? $enter_time : null,
                    //'exit_time' => null,
                    'ward_id' => !empty($ward_id) ? $ward_id : null,
                    'ward_name' => !empty($ward_name) ? $ward_name : null,
                    'department_id' => !empty($department_id) ? $department_id : null,
                    'department_name' => !empty($department_name) ? $department_name : null,
                    'room' => !empty($room) ? $room : null,
                    'bed' => !empty($bed) ? $bed : null,
                    'patient_class' => !empty($patient_class) ? $patient_class : null,
                    'diagnosis_time' => !empty($diagnosis_time) ? $diagnosis_time : null,
                    //'diagnosis_class_id' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisClass']['Identifier']),
                    //'diagnosis_class_text' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisClass']['Text']),
                    'diagnosis_type_id' => !empty($diagnosis_type_id) ? $diagnosis_type_id : null,
                    //'diagnosis_type_text' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisType']['Text']),
                    'diagnosis_code_id' => !empty($diagnosis_code_id) ? $diagnosis_code_id : null,
                    'diagnosis_code_text' => !empty($diagnosis_code_text) ? $diagnosis_code_text : null,
                    'prior_department_id' => !empty($prior_department_id) ? $prior_department_id : null,
                    'prior_ward_id' => !empty($prior_ward_id) ? $prior_ward_id : null,
                    'prior_bed' => !empty($prior_bed) ? $prior_bed : null,
                    'prior_room' => !empty($prior_room) ? $prior_room : null,
                    'updated_ip' => \Request::getClientIp()
                ]
            );
    }

    private function move_room(Message $hl7msg)
    {
        $evn = $hl7msg->getSegmentsByName("EVN")[0];
        $pid = $hl7msg->getSegmentsByName("PID")[0];
        $pv1 = $hl7msg->getSegmentsByName("PV1")[0];
        $dg1 = $hl7msg->getSegmentsByName("DG1")[0];
        $nk1 = $hl7msg->getSegmentsByName("NK1")[0];

        $tran_code = trim($evn->getField(1));
        $recode_time = trim($evn->getField(2));
        $patient_name = $this->get2($pid, 5, 0);
        $sex_code = trim($pid->getField(8));
        $sex_name = $this->getGB2261_80($sex_code);
        $birthday = trim($pid->getField(7));
        $phone_number = trim($nk1->getField(5));
        $marital_code = trim($pid->getField(16));
        $marital_name = $this->getGB4766_84($marital_code);
        $ethnic_group = trim($pid->getField(22));
        $nationality = trim($pid->getField(28));
        $enter_time = trim($pv1->getField(44));
        $ward_id = $this->get2($pv1, 3, 1);
        $ward_name = $this->get3($pv1, 3, 9, 1);
        $department_id = $this->get2($pv1, 3, 0);
        $department_name = $this->get3($pv1, 3, 9, 0);
        $room = $this->get2($pv1, 3, 3);
        $bed = $this->get2($pv1, 3, 2);
        $patient_class = trim($pv1->getField(2));
        $diagnosis_time = trim($dg1->getField(5));
        $diagnosis_type_id = trim($dg1->getField(6));
        $diagnosis_code_id = $this->get2($dg1, 3, 0);
        $diagnosis_code_text = $this->get2($dg1, 3, 1);

        $prior_department_id = $this->get2($pv1, 6, 0);
        $prior_ward_id = $this->get2($pv1, 6, 1);
        $prior_bed = $this->get2($pv1, 6, 2);
        $prior_room = $this->get2($pv1, 6, 3);

        PatientInformation::where('id', trim($pid->getField(2)))
            ->update(
                [
                    'tran_code' => !empty($tran_code) ? $tran_code : null,
                    'recode_time' => !empty($recode_time) ? $recode_time : null,
                    'hospital_name' => '连云港',
                    'event_name' => '报重病',
                    'patient_name' => !empty($patient_name) ? $patient_name : null,
                    'sex_code' => !empty($sex_code) ? $sex_code : null,
                    'sex_name' => $sex_name,
                    'birthday' => !empty($birthday) ? $birthday : null,
                    'phone_number' => !empty($phone_number) ? $phone_number : null,
                    'marital_code' => !empty($marital_code) ? $marital_code : null,
                    'marital_name' => $marital_name,
                    'ethnic_group' => !empty($ethnic_group) ? $ethnic_group : null,
                    'nationality' => !empty($nationality) ? $nationality : null,
                    'enter_time' => !empty($enter_time) ? $enter_time : null,
                    //'exit_time' => null,
                    'ward_id' => !empty($ward_id) ? $ward_id : null,
                    'ward_name' => !empty($ward_name) ? $ward_name : null,
                    'department_id' => !empty($department_id) ? $department_id : null,
                    'department_name' => !empty($department_name) ? $department_name : null,
                    'room' => !empty($room) ? $room : null,
                    'bed' => !empty($bed) ? $bed : null,
                    'patient_class' => !empty($patient_class) ? $patient_class : null,
                    'diagnosis_time' => !empty($diagnosis_time) ? $diagnosis_time : null,
                    //'diagnosis_class_id' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisClass']['Identifier']),
                    //'diagnosis_class_text' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisClass']['Text']),
                    'diagnosis_type_id' => !empty($diagnosis_type_id) ? $diagnosis_type_id : null,
                    //'diagnosis_type_text' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisType']['Text']),
                    'diagnosis_code_id' => !empty($diagnosis_code_id) ? $diagnosis_code_id : null,
                    'diagnosis_code_text' => !empty($diagnosis_code_text) ? $diagnosis_code_text : null,
                    'prior_department_id' => !empty($prior_department_id) ? $prior_department_id : null,
                    'prior_ward_id' => !empty($prior_ward_id) ? $prior_ward_id : null,
                    'prior_bed' => !empty($prior_bed) ? $prior_bed : null,
                    'prior_room' => !empty($prior_room) ? $prior_room : null,
                    'updated_ip' => \Request::getClientIp()
                ]
            );
    }

    private function exit(Message $hl7msg)
    {
        $evn = $hl7msg->getSegmentsByName("EVN")[0];
        $pid = $hl7msg->getSegmentsByName("PID")[0];
        $pv1 = $hl7msg->getSegmentsByName("PV1")[0];
        $dg1 = $hl7msg->getSegmentsByName("DG1")[0];
        $nk1 = $hl7msg->getSegmentsByName("NK1")[0];

        $tran_code = trim($evn->getField(1));
        $recode_time = trim($evn->getField(2));
        $patient_name = $this->get2($pid, 5, 0);
        $sex_code = trim($pid->getField(8));
        $sex_name = $this->getGB2261_80($sex_code);
        $birthday = trim($pid->getField(7));
        $phone_number = trim($nk1->getField(5));
        $marital_code = trim($pid->getField(16));
        $marital_name = $this->getGB4766_84($marital_code);
        $ethnic_group = trim($pid->getField(22));
        $nationality = trim($pid->getField(28));
        $enter_time = trim($pv1->getField(44));
        $ward_id = $this->get2($pv1, 3, 1);
        $ward_name = $this->get3($pv1, 3, 9, 1);
        $department_id = $this->get2($pv1, 3, 0);
        $department_name = $this->get3($pv1, 3, 9, 0);
        $room = $this->get2($pv1, 3, 3);
        $bed = $this->get2($pv1, 3, 2);
        $patient_class = trim($pv1->getField(2));
        $diagnosis_time = trim($dg1->getField(5));
        $diagnosis_type_id = trim($dg1->getField(6));
        $diagnosis_code_id = $this->get2($dg1, 3, 0);
        $diagnosis_code_text = $this->get2($dg1, 3, 1);

        $exit_time = trim($pv1->getField(45));

        PatientInformation::where('id', trim($pid->getField(2)))
            ->update(
                [
                    'tran_code' => !empty($tran_code) ? $tran_code : null,
                    'recode_time' => !empty($recode_time) ? $recode_time : null,
                    'hospital_name' => '连云港',
                    'event_name' => '出院',
                    'patient_name' => !empty($patient_name) ? $patient_name : null,
                    'sex_code' => !empty($sex_code) ? $sex_code : null,
                    'sex_name' => $sex_name,
                    'birthday' => !empty($birthday) ? $birthday : null,
                    'phone_number' => !empty($phone_number) ? $phone_number : null,
                    'marital_code' => !empty($marital_code) ? $marital_code : null,
                    'marital_name' => $marital_name,
                    'ethnic_group' => !empty($ethnic_group) ? $ethnic_group : null,
                    'nationality' => !empty($nationality) ? $nationality : null,
                    'enter_time' => !empty($enter_time) ? $enter_time : null,
                    'exit_time' => !empty($exit_time) ? $exit_time : null,
                    'ward_id' => !empty($ward_id) ? $ward_id : null,
                    'ward_name' => !empty($ward_name) ? $ward_name : null,
                    'department_id' => !empty($department_id) ? $department_id : null,
                    'department_name' => !empty($department_name) ? $department_name : null,
                    'room' => !empty($room) ? $room : null,
                    'bed' => !empty($bed) ? $bed : null,
                    'patient_class' => !empty($patient_class) ? $patient_class : null,
                    'diagnosis_time' => !empty($diagnosis_time) ? $diagnosis_time : null,
                    //'diagnosis_class_id' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisClass']['Identifier']),
                    //'diagnosis_class_text' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisClass']['Text']),
                    'diagnosis_type_id' => !empty($diagnosis_type_id) ? $diagnosis_type_id : null,
                    //'diagnosis_type_text' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisType']['Text']),
                    'diagnosis_code_id' => !empty($diagnosis_code_id) ? $diagnosis_code_id : null,
                    'diagnosis_code_text' => !empty($diagnosis_code_text) ? $diagnosis_code_text : null,
                    'updated_ip' => \Request::getClientIp()
                ]
            );
    }

    private function cancel_enter(Message $hl7msg)
    {
        $evn = $hl7msg->getSegmentsByName("EVN")[0];
        $pid = $hl7msg->getSegmentsByName("PID")[0];
        $pv1 = $hl7msg->getSegmentsByName("PV1")[0];
        $dg1 = $hl7msg->getSegmentsByName("DG1")[0];
        $nk1 = $hl7msg->getSegmentsByName("NK1")[0];

        $tran_code = trim($evn->getField(1));
        $recode_time = trim($evn->getField(2));
        $patient_name = $this->get2($pid, 5, 0);
        $sex_code = trim($pid->getField(8));
        $sex_name = $this->getGB2261_80($sex_code);
        $birthday = trim($pid->getField(7));
        $phone_number = trim($nk1->getField(5));
        $marital_code = trim($pid->getField(16));
        $marital_name = $this->getGB4766_84($marital_code);
        $ethnic_group = trim($pid->getField(22));
        $nationality = trim($pid->getField(28));
        $enter_time = trim($pv1->getField(44));
        $ward_id = $this->get2($pv1, 3, 1);
        $ward_name = $this->get3($pv1, 3, 9, 1);
        $department_id = $this->get2($pv1, 3, 0);
        $department_name = $this->get3($pv1, 3, 9, 0);
        $room = $this->get2($pv1, 3, 3);
        $bed = $this->get2($pv1, 3, 2);
        $patient_class = trim($pv1->getField(2));
        $diagnosis_time = trim($dg1->getField(5));
        $diagnosis_type_id = trim($dg1->getField(6));
        $diagnosis_code_id = $this->get2($dg1, 3, 0);
        $diagnosis_code_text = $this->get2($dg1, 3, 1);

        $exit_time = trim($pv1->getField(45));
        $time = Carbon::now()->toDateTimeString();

        PatientInformation::where('id', trim($pid->getField(2)))
            ->update(
                [
                    'tran_code' => !empty($tran_code) ? $tran_code : null,
                    'recode_time' => !empty($recode_time) ? $recode_time : null,
                    'hospital_name' => '连云港',
                    'event_name' => '取消新入',
                    'patient_name' => !empty($patient_name) ? $patient_name : null,
                    'sex_code' => !empty($sex_code) ? $sex_code : null,
                    'sex_name' => $sex_name,
                    'birthday' => !empty($birthday) ? $birthday : null,
                    'phone_number' => !empty($phone_number) ? $phone_number : null,
                    'marital_code' => !empty($marital_code) ? $marital_code : null,
                    'marital_name' => $marital_name,
                    'ethnic_group' => !empty($ethnic_group) ? $ethnic_group : null,
                    'nationality' => !empty($nationality) ? $nationality : null,
                    'enter_time' => !empty($enter_time) ? $enter_time : null,
                    'exit_time' => !empty($exit_time) ? $exit_time : $time,
                    'ward_id' => !empty($ward_id) ? $ward_id : null,
                    'ward_name' => !empty($ward_name) ? $ward_name : null,
                    'department_id' => !empty($department_id) ? $department_id : null,
                    'department_name' => !empty($department_name) ? $department_name : null,
                    'room' => !empty($room) ? $room : null,
                    'bed' => !empty($bed) ? $bed : null,
                    'patient_class' => !empty($patient_class) ? $patient_class : null,
                    'diagnosis_time' => !empty($diagnosis_time) ? $diagnosis_time : null,
                    //'diagnosis_class_id' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisClass']['Identifier']),
                    //'diagnosis_class_text' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisClass']['Text']),
                    'diagnosis_type_id' => !empty($diagnosis_type_id) ? $diagnosis_type_id : null,
                    //'diagnosis_type_text' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisType']['Text']),
                    'diagnosis_code_id' => !empty($diagnosis_code_id) ? $diagnosis_code_id : null,
                    'diagnosis_code_text' => !empty($diagnosis_code_text) ? $diagnosis_code_text : null,
                    'updated_ip' => \Request::getClientIp()
                ]
            );
    }

    private function move_exit(Message $hl7msg)
    {
        $evn = $hl7msg->getSegmentsByName("EVN")[0];
        $pid = $hl7msg->getSegmentsByName("PID")[0];
        $pv1 = $hl7msg->getSegmentsByName("PV1")[0];
        $dg1 = $hl7msg->getSegmentsByName("DG1")[0];
        $nk1 = $hl7msg->getSegmentsByName("NK1")[0];

        $tran_code = trim($evn->getField(1));
        $recode_time = trim($evn->getField(2));
        $patient_name = $this->get2($pid, 5, 0);
        $sex_code = trim($pid->getField(8));
        $sex_name = $this->getGB2261_80($sex_code);
        $birthday = trim($pid->getField(7));
        $phone_number = trim($nk1->getField(5));
        $marital_code = trim($pid->getField(16));
        $marital_name = $this->getGB4766_84($marital_code);
        $ethnic_group = trim($pid->getField(22));
        $nationality = trim($pid->getField(28));
        $enter_time = trim($pv1->getField(44));
        $ward_id = $this->get2($pv1, 3, 1);
        $ward_name = $this->get3($pv1, 3, 9, 1);
        $department_id = $this->get2($pv1, 3, 0);
        $department_name = $this->get3($pv1, 3, 9, 0);
        $room = $this->get2($pv1, 3, 3);
        $bed = $this->get2($pv1, 3, 2);
        $patient_class = trim($pv1->getField(2));
        $diagnosis_time = trim($dg1->getField(5));
        $diagnosis_type_id = trim($dg1->getField(6));
        $diagnosis_code_id = $this->get2($dg1, 3, 0);
        $diagnosis_code_text = $this->get2($dg1, 3, 1);

        $prior_department_id = $this->get2($pv1, 6, 0);
        $prior_ward_id = $this->get2($pv1, 6, 1);
        $prior_bed = $this->get2($pv1, 6, 2);
        $prior_room = $this->get2($pv1, 6, 3);
        $exit_time = trim($pv1->getField(45));
        $time = Carbon::now()->toDateTimeString();

        PatientInformation::where('id', trim($pid->getField(2)))
            ->update(
                [
                    'tran_code' => !empty($tran_code) ? $tran_code : null,
                    'recode_time' => !empty($recode_time) ? $recode_time : null,
                    'hospital_name' => '连云港',
                    'event_name' => '换病区转出',
                    'patient_name' => !empty($patient_name) ? $patient_name : null,
                    'sex_code' => !empty($sex_code) ? $sex_code : null,
                    'sex_name' => $sex_name,
                    'birthday' => !empty($birthday) ? $birthday : null,
                    'phone_number' => !empty($phone_number) ? $phone_number : null,
                    'marital_code' => !empty($marital_code) ? $marital_code : null,
                    'marital_name' => $marital_name,
                    'ethnic_group' => !empty($ethnic_group) ? $ethnic_group : null,
                    'nationality' => !empty($nationality) ? $nationality : null,
                    'enter_time' => !empty($enter_time) ? $enter_time : null,
                    'exit_time' => !empty($exit_time) ? $exit_time : $time,
                    'ward_id' => !empty($ward_id) ? $ward_id : null,
                    'ward_name' => !empty($ward_name) ? $ward_name : null,
                    'department_id' => !empty($department_id) ? $department_id : null,
                    'department_name' => !empty($department_name) ? $department_name : null,
                    'room' => !empty($room) ? $room : null,
                    'bed' => !empty($bed) ? $bed : null,
                    'patient_class' => !empty($patient_class) ? $patient_class : null,
                    'diagnosis_time' => !empty($diagnosis_time) ? $diagnosis_time : null,
                    //'diagnosis_class_id' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisClass']['Identifier']),
                    //'diagnosis_class_text' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisClass']['Text']),
                    'diagnosis_type_id' => !empty($diagnosis_type_id) ? $diagnosis_type_id : null,
                    //'diagnosis_type_text' => trim($data['Request']['Body']['DiagnosisList'][0]['DiagnosisType']['Text']),
                    'diagnosis_code_id' => !empty($diagnosis_code_id) ? $diagnosis_code_id : null,
                    'diagnosis_code_text' => !empty($diagnosis_code_text) ? $diagnosis_code_text : null,
                    'prior_department_id' => !empty($prior_department_id) ? $prior_department_id : null,
                    'prior_ward_id' => !empty($prior_ward_id) ? $prior_ward_id : null,
                    'prior_bed' => !empty($prior_bed) ? $prior_bed : null,
                    'prior_room' => !empty($prior_room) ? $prior_room : null,
                    'updated_ip' => \Request::getClientIp()
                ]
            );
    }

    //GB2261-80
    private function getGB2261_80($code)
    {
        if (empty($code)) {
            return null;
        }

        $name = '未知';
        if ($code == 1) {
            $name = '男性';
        }
        else if ($code == 2) {
            $name = '女性';
        }
        else if ($code == 3) {
            $name = '女性改男性';
        }
        else if ($code == 4) {
            $name = '男性改女性';
        }
        else if ($code == 5) {
            $name = '未明的性';
        }

        return $name;
    }

    //GB4766-84
    private function getGB4766_84($code)
    {
        if (empty($code)) {
            return null;
        }

        $name = '其他';
        if ($code == 1) {
            $name = '未婚';
        }
        else if ($code == 2) {
            $name = '已婚';
        }
        else if ($code == 3) {
            $name = '丧偶';
        }
        else if ($code == 4) {
            $name = '离异';
        }

        return $name;
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
