<?php
/**
 * Created by PhpStorm.
 * User: rgy
 * Date: 2020/3/19
 * Time: 17:36
 */

namespace App\Http\Controllers\Api;

use App\Services\HisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HisController
{
    public function __construct()
    {
        $this->service = app(HisService::class);
    }

    public function send(Request $request)
    {
        try{
            $body = json_decode($request->getContent(), true);

            if ($body === null && json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(array(
                    "status" => "error",
                    "message" => "json data is incorrect"
                ));
            }

            $validator = Validator::make($body, [
                'Request.Body.Demography.PatientIdentifierList.0.IDNumber' => ['required', 'integer', 'min:1'],
                'Request.Head.TranCode' => ['required', 'string', 'max:32'],
            ]);

            if ($validator->fails()) {
                return response()->json(array(
                    "status" => "error",
                    "message" => implode(" ",$validator->messages()->all())
                ));
            }

            DB::beginTransaction();

            try{
                $this->service->send($body);
            }
            catch (\Exception $e){
                DB::rollback();

                return response()->json(array(
                    "status" => "error",
                    "message" => $e->getMessage()
                ));
            }

            DB::commit();

            $response_array = array(
                "Response" => array(
                    "Head" => $body['Request']['Head'],
                    "Body" => array()
                )
            );
            return response()->json($response_array);
        }
        catch (\Exception $e){
            return response()->json(array(
                "status" => "error",
                "message" => $e->getMessage()
            ));
        }
    }
}