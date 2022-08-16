<?php
/**
 * Created by PhpStorm.
 * User: rgy
 * Date: 2020/3/19
 * Time: 17:39
 */

namespace App\Services;


use App\Models\PatientInformation;

class HisService
{
    private function enter($data)
    {
        $department_id = $this->getValue($data, ['Request','Body','PatientVisit','PatientLocation','Department','Identifier']);

        if(!isset($department_id)){
            return false;
        }

        $departments = app(SettingService::class)->getDepartments();

        if(isset($departments)){
            $departments = explode(",", $departments);

            if(!in_array($department_id, $departments)){
                return false;
            }
        }

        PatientInformation::updateOrCreate(
            ['id' => $this->getValue($data, ['Request', 'Body', 'Demography', 'PatientIdentifierList', 0, 'IDNumber'])],
            [
                'hospitalization_number' => $this->getValue($data, ['Request', 'Body', 'Demography', 'PatientIdentifierList', 1, 'IDNumber']),
                'tran_code' => $this->getValue($data, ['Request', 'Head', 'TranCode']),
                'recode_time' => $this->getValue($data, ['Request', 'Body', 'Event', 'RecoedDatetime']),
                'hospital_name' => $this->getValue($data, ['Request', 'Body', 'Event', 'EventFacility', 'Text']),
                'event_name' => $this->getValue($data, ['Request', 'Body', 'Event', 'EventCode', 'Text']),
                'patient_name' => $this->getValue($data, ['Request', 'Body', 'Demography', 'PatientName']),
                'sex_code' => $this->getValue($data, ['Request', 'Body', 'Demography', 'Sex', 'Identifier']),
                'sex_name' => $this->getValue($data, ['Request', 'Body', 'Demography', 'Sex', 'Text']),
                'birthday' => $this->getValue($data, ['Request', 'Body', 'Demography', 'Birthday']),
                'phone_number' => $this->getValue($data, ['Request', 'Body', 'Demography', 'PhoneList', 0, 'PhoneNumberST']),
                'marital_code' => $this->getValue($data, ['Request', 'Body', 'Demography', 'Maritalstatus', 'Identifier']),
                'marital_name' => $this->getValue($data, ['Request', 'Body', 'Demography', 'Maritalstatus', 'Text']),
                'ethnic_group' => $this->getValue($data, ['Request', 'Body', 'Demography', 'EthnicGroup', 'Text']),
                'nationality' => $this->getValue($data, ['Request', 'Body', 'Demography', 'Nationality', 'Text']),
                'visit_status_id' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'VisitStatus', 'Identifier']),
                'visit_status_text' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'VisitStatus', 'Text']),
                'admission_type' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'AdmissionType']),
                'admit_source_id' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'AdmitSource', 'Identifier']),
                'admit_source_text' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'AdmitSource', 'Text']),
                'handle_type_id' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'HandleList', 0, 'Type', 'Identifier']),
                'handle_type_text' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'HandleList', 0, 'Type', 'Text']),
                'enter_time' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'HandleList', 0, 'HandleTime']),
                //'exit_time' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'HandleList', 0, 'HandleTime']),
                'ward_id' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PatientLocation', 'Ward', 'Identifier']),
                'ward_name' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PatientLocation', 'Ward', 'Text']),
                'department_id' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PatientLocation', 'Department', 'Identifier']),
                'department_name' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PatientLocation', 'Department', 'Text']),
                'room' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PatientLocation', 'Room']),
                'bed' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PatientLocation', 'Bed']),
                'patient_class' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PatientClass']),
                'diagnosis_time' => $this->getValue($data, ['Request', 'Body', 'DiagnosisList', 0, 'DiagnosisTime']),
                'diagnosis_class_id' => $this->getValue($data, ['Request', 'Body', 'DiagnosisList', 0, 'DiagnosisClass', 'Identifier']),
                'diagnosis_class_text' => $this->getValue($data, ['Request', 'Body', 'DiagnosisList', 0, 'DiagnosisClass', 'Text']),
                'diagnosis_type_id' => $this->getValue($data, ['Request', 'Body', 'DiagnosisList', 0, 'DiagnosisType', 'Identifier']),
                'diagnosis_type_text' => $this->getValue($data, ['Request', 'Body', 'DiagnosisList', 0, 'DiagnosisType', 'Text']),
                'diagnosis_code_id' => $this->getValue($data, ['Request', 'Body', 'DiagnosisList', 0, 'DiagnosisCode', 'Identifier']),
                'diagnosis_code_text' => $this->getValue($data, ['Request', 'Body', 'DiagnosisList', 0, 'DiagnosisCode', 'Text']),
                'created_ip' => \Request::getClientIp()
            ]
        );
    }

    private function transfer($data)
    {
        PatientInformation::where('id', $this->getValue($data, ['Request', 'Body', 'Demography', 'PatientIdentifierList', 0, 'IDNumber']))
            ->update([
                    'tran_code' => $this->getValue($data, ['Request', 'Head', 'TranCode']),
                    'recode_time' => $this->getValue($data, ['Request', 'Body', 'Event', 'RecoedDatetime']),
                    'event_name' => $this->getValue($data, ['Request', 'Body', 'Event', 'EventCode', 'Text']),
                    'visit_status_id' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'VisitStatus', 'Identifier']),
                    'visit_status_text' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'VisitStatus', 'Text']),
                    'room' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PatientLocation', 'Room']),
                    'bed' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PatientLocation', 'Bed']),
                    'updated_ip' => \Request::getClientIp()
                ]
            );
    }

    private function move($data)
    {
        PatientInformation::where('id', $this->getValue($data, ['Request', 'Body', 'Demography', 'PatientIdentifierList', 0, 'IDNumber']))
            ->update(
                [
                    'tran_code' => $this->getValue($data, ['Request', 'Head', 'TranCode']),
                    'recode_time' => $this->getValue($data, ['Request', 'Body', 'Event', 'RecoedDatetime']),
                    'event_name' => $this->getValue($data, ['Request', 'Body', 'Event', 'EventCode', 'Text']),
                    'visit_status_id' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'VisitStatus', 'Identifier']),
                    'visit_status_text' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'VisitStatus', 'Text']),
                    'admit_source_id' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'AdmitSource', 'Identifier']),
                    'admit_source_text' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'AdmitSource', 'Text']),
                    'handle_type_id' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'HandleList', 0, 'Type', 'Identifier']),
                    'handle_type_text' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'HandleList', 0, 'Type', 'Text']),
                    'ward_id' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PatientLocation', 'Ward', 'Identifier']),
                    'ward_name' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PatientLocation', 'Ward', 'Text']),
                    'department_id' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PatientLocation', 'Department', 'Identifier']),
                    'department_name' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PatientLocation', 'Department', 'Text']),
                    'room' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PatientLocation', 'Room']),
                    'bed' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PatientLocation', 'Bed']),
                    'prior_ward_id' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PriorPatientLocation', 'Ward', 'Identifier']),
                    'prior_ward_name' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PriorPatientLocation', 'Ward', 'Text']),
                    'prior_department_id' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PriorPatientLocation', 'Department', 'Identifier']),
                    'prior_department_name' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PriorPatientLocation', 'Department', 'Text']),
                    'prior_room' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PriorPatientLocation', 'Room']),
                    'prior_bed' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'PriorPatientLocation', 'Bed']),
                    'updated_ip' => \Request::getClientIp()
                ]
            );
    }

    private function exit($data)
    {
        PatientInformation::where('id', $this->getValue($data, ['Request', 'Body', 'Demography', 'PatientIdentifierList', 0, 'IDNumber']))
            ->update(
                [
                    'tran_code' => $this->getValue($data, ['Request', 'Head', 'TranCode']),
                    'recode_time' => $this->getValue($data, ['Request', 'Body', 'Event', 'RecoedDatetime']),
                    'event_name' => $this->getValue($data, ['Request', 'Body', 'Event', 'EventCode', 'Text']),
                    'visit_status_id' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'VisitStatus', 'Identifier']),
                    'visit_status_text' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'VisitStatus', 'Text']),
                    'exit_time' => $this->getValue($data, ['Request', 'Body', 'PatientVisit', 'HandleList', 0, 'HandleTime']),
                    'diagnosis_time' => $this->getValue($data, ['Request', 'Body', 'DiagnosisList', 0, 'DiagnosisTime']),
                    'diagnosis_class_id' => $this->getValue($data, ['Request', 'Body', 'DiagnosisList', 0, 'DiagnosisClass', 'Identifier']),
                    'diagnosis_class_text' => $this->getValue($data, ['Request', 'Body', 'DiagnosisList', 0, 'DiagnosisClass', 'Text']),
                    'diagnosis_type_id' => $this->getValue($data, ['Request', 'Body', 'DiagnosisList', 0, 'DiagnosisType', 'Identifier']),
                    'diagnosis_type_text' => $this->getValue($data, ['Request', 'Body', 'DiagnosisList', 0, 'DiagnosisType', 'Text']),
                    'diagnosis_code_id' => $this->getValue($data, ['Request', 'Body', 'DiagnosisList', 0, 'DiagnosisCode', 'Identifier']),
                    'diagnosis_code_text' => $this->getValue($data, ['Request', 'Body', 'DiagnosisList', 0, 'DiagnosisCode', 'Text']),
                    'updated_ip' => \Request::getClientIp()
                ]
            );
    }

    public function send($data)
    {
        $tran_code = $this->getValue($data, ["Request", "Head", "TranCode"]);
        if ( $tran_code == "PVM0101") {
            $this->enter($data);
        } else if ($tran_code == "PVM0501") {
            $this->transfer($data);
        } else if ($tran_code == "PVM0307") {
            $this->move($data);
        } else if ($tran_code == "PVM0201") {
            $this->exit($data);
        }
    }

    private function getValue($data, $indexes)
    {
        try{
            foreach ($indexes as $index){
                if(isset($data) && is_array($data) && isset($data[$index])){
                    $data = $data[$index];
                }
                else{
                    return null;
                }
            }
            return trim($data);
        }
        catch (\Exception $e){
            return null;
        }
    }
}