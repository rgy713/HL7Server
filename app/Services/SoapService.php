<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2020/12/17
 * Time: 17:42
 */

namespace App\Services;

use Aranyasen\Exceptions\HL7Exception;
use Aranyasen\HL7\Message;
use Aranyasen\HL7\Segment;
use Aranyasen\HL7\Segments\MSH;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

Class SoapService
{
    private function makeResponse($type, $id, $confirm_type, $error=null)
    {
        try{
            $msg = new Message();

            $msh = new Segment('MSH');
            $msh->setField(1,'|');
            $msh->setField(2,'^~\&');
            $msh->setField(3, 'AiNST');
            $msh->setField(5, 'ESB');
            $msh->setField(6, $type);
            $msh->setField(7, strftime('%Y%m%d%H%M%S'));
            $msh->setField(9, 'ACK^O01');
            $msh->setField(10, isset($id) ? $id : '' );
            $msh->setField(11, 'P');
            $msh->setField(12, '2.6');
            $msh->setField(15, 'NE');
            $msh->setField(16, 'AL');
            $msh->setField(18, 'utf-8');

            $msg->addSegment($msh);

            $msa = new Segment('MSA');
            $msa->setField(1, $confirm_type);
            $msa->setField(2, isset($id) ? $id : '');

            $msg->addSegment($msa);
            if(isset($error)){
                $err = new Segment('ERR');
                $err->setField(1, 'E');
                $err->setField(2, $error);
                $msg->addSegment($err);
            }

            return $msg->toString(true);
        }
        catch (\Exception $e){
            return $e->getMessage();
        }

    }

    public function sendHisLianyungang($content)
    {
        //Log::debug($content);
        try{
            $msg = new Message($content, null, true, true, false);

            $msh = $msg->getSegmentsByName("MSH")[0];

            $send_type = $msh->getField(3);
            $type = $msh->getField(9);
            $type_str = is_array($type) ? implode("^", $type) : $type;
            $id = $msh->getField(10);

            if(is_array($type) && count($type) == 3){

                DB::beginTransaction();

                try{
                    
                    if($type[0] == "ADT" && $type[1] == 'A01' && $type[2] == 'ADT_A01'){
                        app(HisServiceLianyungang::class)->send($msg);
                        //Log::debug($send_type ." ". $type_str . " " . $id . "->success");
                    }
                    else if($type[0] == "OUL" && $type[1] == 'R21' && $type[2] == 'OUL_R21'){

                        $labIndex = app(LisServiceLianyungang::class)->pushLaboratoryIndex($msg);

                        if(isset($labIndex)){
                            app(LisServiceLianyungang::class)->pushResults($msg, $labIndex->id);
                        }
                        //Log::debug($send_type ." ". $type_str . " " . $id . "->success");
                    }
                    else{
                        //Log::debug($content);
                        //Log::debug($send_type ." ". $type_str . " " . $id . "->no his,lis");
                    }
                }
                catch (\Exception $e){

                    DB::rollback();
                    Log::debug($content);
                    Log::error($send_type ." ". $type_str . " " . $id . "->" . $e->getMessage());

                    return $this->makeResponse($send_type, $id, 'AE', $e->getMessage());
                }

                DB::commit();

            }
            else{
                //Log::debug($content);
                //Log::debug($send_type ." ". $type_str . " " . $id . "->no his,lis");
            }

            $res = $this->makeResponse($send_type, $id, 'AA');
        }
        catch (\Exception $e){
            Log::debug($content);
            Log::error("UNKNOWN" . "->" . $e->getMessage());

            $res = $this->makeResponse("UNKNOWN", null, 'AE', $e->getMessage());
        }

        return $res;
    }

}