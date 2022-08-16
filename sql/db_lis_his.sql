/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 100411
 Source Host           : localhost:3306
 Source Schema         : db_hlserver

 Target Server Type    : MySQL
 Target Server Version : 100411
 File Encoding         : 65001

 Date: 19/11/2020 10:20:50
*/
SET global innodb_large_prefix = ON;
SET SESSION innodb_strict_mode=OFF;
SET SESSION sql_mode='';
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for laboratory_index
-- ----------------------------
DROP TABLE IF EXISTS `laboratory_index`;
CREATE TABLE `laboratory_index`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '实验室指标id',
  `patient_number` varchar(30) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL COMMENT '患者院内标识符 PID[3][0]',
  `hospitalization_number` varchar(30) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL COMMENT '住院病历号/门诊病历号 PID[3][1]',
  `patient_name` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL COMMENT '患者姓名 PID[5]',
  `number` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL COMMENT '实验室指标编号 PID[1]',
  `bed_number` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL COMMENT '床位号 PV1[3][2]',
  `ward_code` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL COMMENT '病区编码 PV1[3][6]',
  `ward_name` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '病区名称 PV1[3][7]',
  `send_time` timestamp(0) NOT NULL DEFAULT current_timestamp(0) COMMENT '采样时间 OBR[8]',
  `doctor_number` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '采样医生编码 OBR[10][0]',
  `doctor_name` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '采样医生姓名 OBR[10][1]',
  `created_at` timestamp(0) NOT NULL DEFAULT current_timestamp(0) COMMENT '创建时间',
  `updated_at` timestamp(0) NOT NULL DEFAULT current_timestamp(0) ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
  `created_ip` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '创建ip',
  `updated_ip` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '更新ip',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `patient_number`(`patient_number`, `hospitalization_number`, `send_time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf16 COLLATE = utf16_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for patient_information
-- ----------------------------
DROP TABLE IF EXISTS `patient_information`;
CREATE TABLE `patient_information`  (
  `id` int(11) UNSIGNED NOT NULL COMMENT '患者编号 Request.Body.Demography.PatientIdentifierList[0].IDNumber(IDType=\"PatientID\")',
  `hospitalization_number` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL COMMENT '住院病历号/门诊病历号 Request.Body.Demography.PatientIdentifierList[1].IDNumber(IDType=\"MedicalRecordNo\")',
  `tran_code` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL COMMENT '代号 Request.Head.TranCode',
  `recode_time` datetime(0) NOT NULL COMMENT '推送时间 Request.Body.Event.RecoedDatetime',
  `hospital_name` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL COMMENT '医院名称 Request.Body.Event.EventFacility.Text',
  `event_name` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL COMMENT '事件名称 Request.Body.Event.EventCode.Text',
  `patient_name` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL COMMENT '患者姓名 Request.Body.Demography.PatientName',
  `sex_code` char(1) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL COMMENT '性别编码 Request.Body.Demography.Sex.Identifier',
  `sex_name` varchar(2) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL COMMENT '性别名称 Request.Body.Demography.Sex.Text',
  `birthday` date NULL DEFAULT NULL COMMENT '出生日期 Request.Body.Demography.Birthday %Y-%m-%d',
  `phone_number` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '电话号码 Request.Body.Demography.PhoneList[0].PhoneNumberST',
  `marital_code` char(3) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '婚姻状况code Request.Body.Demography.Maritalstatus.Identifier',
  `marital_name` varchar(4) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '婚姻状况名称 Request.Body.Demography.Maritalstatus.Text',
  `ethnic_group` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '名族 Request.Body.Demography.EthnicGroup.Text',
  `nationality` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '国籍 Request.Body.Demography.Nationality.Text',
  `visit_status_id` tinyint(2) NULL DEFAULT NULL COMMENT '就诊状态id，0：入院登记,1：病区分床,2：患者出院,3：患者出区,4：取消结算,5：进入 ICU,6：进入产房,7：转科状态,8：数据转出,9：作废记录,10：取消入院登记,11：取消入区,12：取消出区,13：取消出院 Request.Body.PatientVisit.VisitStatus.Identifier',
  `visit_status_text` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '就诊状态描述 Request.Body.PatientVisit.VisitStatus.Text',
  `admission_type` varchar(2) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '入院/挂号类型:R-常规,N-新生儿变,B-母婴同床 Request.Body.PatientVisit.AdmissionType',
  `admit_source_id` tinyint(2) NULL DEFAULT NULL COMMENT '入院方式信息 1-门诊转入,2-急诊转入,3-转院转入,9-其他入院/挂号类型:R-常规,N-新生儿变,B-母婴同床 Request.Body.PatientVisit.AdmitSource.Identifier',
  `admit_source_text` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '入院方式名称 Request.Body.PatientVisit.AdmitSource.Text',
  `handle_type_id` varchar(11) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '操作类别 Entry-录入,Verify-审核/确认,Action-执行 Request.Body.PatientVisit.HandleList[0].Type.Identifier',
  `handle_type_text` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '操作类别名称 Request.Body.PatientVisit.HandleList[0].Type.Text',
  `enter_time` datetime(0) NULL DEFAULT NULL COMMENT '入院时间 Request.Body.PatientVisit.HandleList.HandleTime',
  `exit_time` datetime(0) NULL DEFAULT NULL COMMENT '出院时间 Request.Body.PatientVisit.HandleList.HandleTime',
  `ward_id` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '患者当前位置病区id Request.Body.PatientVisit.PatientLocation.Ward.Identifier',
  `ward_name` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '患者当前位置病区名称 Request.Body.PatientVisit.PatientLocation.Ward.Text',
  `department_id` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '患者当前位置科室id	Request.Body.PatientVisit.PatientLocation.Department.Identifier',
  `department_name` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '患者当前位置科室名称	Request.Body.PatientVisit.PatientLocation.Department.Text',
  `room` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '患者当前位置病房	Request.Body.PatientVisit.PatientLocation.Room',
  `bed` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '患者当前位置床号	Request.Body.PatientVisit.PatientLocation.Bed',
  `prior_ward_id` smallint(5) NULL DEFAULT NULL COMMENT '患者之前位置病区id Request.Body.PatientVisit.PriorPatientLocation.Ward.Identifier',
  `prior_ward_name` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '患者之前位置病区名称 Request.Body.PatientVisit.PriorPatientLocation.Ward.Text',
  `prior_department_id` smallint(5) NULL DEFAULT NULL COMMENT '患者之前位置科室id	Request.Body.PatientVisit.PriorPatientLocation.Department.Identifier',
  `prior_department_name` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '患者之前位置科室名称	Request.Body.PatientVisit.PriorPatientLocation.Department.Text',
  `prior_room` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '患者之前位置病房	Request.Body.PatientVisit.PriorPatientLocation.Room',
  `prior_bed` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '患者之前位置床号	Request.Body.PatientVisit.PriorPatientLocation.Bed',
  `patient_class` varchar(2) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '患者类别 I-住院	 Request.Body.PatientVisit.PatientClass',
  `diagnosis_time` datetime(0) NULL DEFAULT NULL COMMENT '诊断时间	Request.Body.DiagnosisList.DiagnosisTime',
  `diagnosis_class_id` varchar(11) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '诊断类别代码	 Request.Body.DiagnosisList.DiagnosisClass.Identifier',
  `diagnosis_class_text` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '诊断类别名称 	Request.Body.DiagnosisList.DiagnosisClass.Text',
  `diagnosis_type_id` varchar(11) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '诊断类型代码	Request.Body.DiagnosisList.DiagnosisType.Identifier',
  `diagnosis_type_text` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '诊断类型名称	Request.Body.DiagnosisList.DiagnosisType.Text',
  `diagnosis_code_id` varchar(11) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '诊断代码	Request.Body.DiagnosisList.DiagnosisCode.Identifier',
  `diagnosis_code_text` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '诊断名称	Request.Body.DiagnosisList.DiagnosisCode.Text',
  `created_at` timestamp(0) NOT NULL DEFAULT current_timestamp(0) COMMENT '创建时间',
  `updated_at` timestamp(0) NOT NULL DEFAULT current_timestamp(0) ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
  `created_ip` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '创建ip',
  `updated_ip` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '更新ip',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf16 COLLATE = utf16_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for test_item
-- ----------------------------
DROP TABLE IF EXISTS `test_item`;
CREATE TABLE `test_item`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '检查项id 医技项目代码 OBX[3][0]',
  `test_medium_id` int(11) UNSIGNED NOT NULL COMMENT '检验介质id',
  `code` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL COMMENT '医技项目名称 OBX[3][1]',
  `name` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL COMMENT '医技项目中文名称 OBX[4]',
  `unit` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '项目单位 OBX[6]',
  `min` double NULL DEFAULT NULL COMMENT '结果参考值min OBX[7][0]*',
  `max` double NULL DEFAULT NULL COMMENT '结果参考值max OBX[7][1]*',
  `created_at` timestamp(0) NOT NULL DEFAULT current_timestamp(0) COMMENT '创建时间',
  `updated_at` timestamp(0) NOT NULL DEFAULT current_timestamp(0) ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
  `created_ip` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '创建ip',
  `updated_ip` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '更新ip',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `test_item_test_medium_id_foreign`(`test_medium_id`) USING BTREE,
  CONSTRAINT `test_item_test_medium_id_foreign` FOREIGN KEY (`test_medium_id`) REFERENCES `test_medium` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf16 COLLATE = utf16_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for test_medium
-- ----------------------------
DROP TABLE IF EXISTS `test_medium`;
CREATE TABLE `test_medium`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '检验介质id',
  `code` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL COMMENT '检验介质编码 OBR[4][0]',
  `name` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL COMMENT '检验介质名称 OBR[4][1]',
  `created_at` timestamp(0) NOT NULL DEFAULT current_timestamp(0) COMMENT '创建时间',
  `updated_at` timestamp(0) NOT NULL DEFAULT current_timestamp(0) ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
  `created_ip` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '创建ip',
  `updated_ip` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '更新ip',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `test_medium_code_unique`(`code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf16 COLLATE = utf16_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for test_result
-- ----------------------------
DROP TABLE IF EXISTS `test_result`;
CREATE TABLE `test_result`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '检验结果id',
  `laboratory_index_id` int(11) UNSIGNED NOT NULL COMMENT '实验室指标id',
  `test_medium_id` int(11) UNSIGNED NOT NULL COMMENT '检验介质id',
  `test_item_id` int(11) UNSIGNED NOT NULL COMMENT '检验项id 医技项目代码 OBX[3][0]',
  `code` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL COMMENT '医技项目名称 OBX[3][1]',
  `name` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL COMMENT '医技项目中文名称 OBX[4]',
  `value` double NOT NULL COMMENT '项目结果 OBX[5]',
  `unit` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '项目单位 OBX[6]',
  `min` double NULL DEFAULT NULL COMMENT '结果参考值min OBX[7][0]*',
  `max` double NULL DEFAULT NULL COMMENT '结果参考值max OBX[7][1]*',
  `created_at` timestamp(0) NOT NULL DEFAULT current_timestamp(0) COMMENT '创建时间',
  `updated_at` timestamp(0) NOT NULL DEFAULT current_timestamp(0) ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
  `created_ip` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '创建ip',
  `updated_ip` varchar(32) CHARACTER SET utf16 COLLATE utf16_general_ci NULL DEFAULT NULL COMMENT '更新ip',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `test_item_laboratory_index_id_foreign`(`laboratory_index_id`) USING BTREE,
  INDEX `test_item_test_item_id_foreign`(`test_item_id`) USING BTREE,
  INDEX `test_item_test_medium_id_foreign`(`test_medium_id`) USING BTREE,
  CONSTRAINT `test_result_laboratory_index_id_foreign` FOREIGN KEY (`laboratory_index_id`) REFERENCES `laboratory_index` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `test_result_test_item_id_foreign` FOREIGN KEY (`test_item_id`) REFERENCES `test_item` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `test_result_test_medium_id_foreign` FOREIGN KEY (`test_medium_id`) REFERENCES `test_medium` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf16 COLLATE = utf16_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for setting
-- ----------------------------
DROP TABLE IF EXISTS `setting`;
CREATE TABLE `setting`  (
  `field_name` varchar(255) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL,
  `condition` text CHARACTER SET utf16 COLLATE utf16_general_ci NULL,
  PRIMARY KEY (`field_name`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf16 COLLATE = utf16_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of setting
-- ----------------------------
INSERT INTO `setting` VALUES ('departments', NULL);
INSERT INTO `setting` VALUES ('test_items', NULL);

SET FOREIGN_KEY_CHECKS = 1;
