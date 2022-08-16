<?php
/**
 * Created by PhpStorm.
 * User: rgy
 * Date: 2020/3/19
 * Time: 17:36
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LisServiceLianyungang;
use Aranyasen\HL7\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LisControllerLianyungang extends Controller
{
    public function __construct()
    {
        $this->service = app(LisServiceLianyungang::class);
    }

    public function send(Request $request)
    {
        try {
            $bodyContent = $request->getContent();

            $msg = new Message($bodyContent, null, true, true, false);

            DB::beginTransaction();

            try{
                $labIndex = $this->service->pushLaboratoryIndex($msg);
                if(isset($labIndex)){
                    $this->service->pushResults($msg, $labIndex->id);
                }
            }
            catch (\Exception $e){
                DB::rollback();

                return response()->json(array(
                    "status" => "error",
                    "message" => $e->getMessage()
                ));
            }

            DB::commit();

            $responseData = [
                'status' => 'success',
                'result' => $labIndex,
            ];

        } catch (\Exception $e) {

            $responseData = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return response()->json($responseData);

    }
}
