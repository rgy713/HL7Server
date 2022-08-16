<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2020/12/17
 * Time: 17:31
 */

namespace App\Http\Controllers;


use App\Soap\SoapDiscovery;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SoapCallbackController
{
    public function service()
    {
        try {
            $server_ip = request()->server('SERVER_ADDR');

            $old_server_ip =  Cache::get('server_ip');

            $procClass     = 'App\Services\SoapService';

            $classNameFull = explode('\\', $procClass);

            $className     = array_pop($classNameFull);

            $storagePath   = storage_path();

            if (! file_exists($storagePath . '/wsdl/' . $className . '.wsdl') || !isset($old_server_ip) || $server_ip != $old_server_ip) {
                if (! file_exists($storagePath . '/wsdl/')) {
                    mkdir($storagePath . '/wsdl/', 0777, true);
                }

                $soapDiscovery = new SoapDiscovery($procClass, 'soap');
                $file = fopen($storagePath . '/wsdl/' . $className . '.wsdl', 'w');
                fwrite($file, $soapDiscovery->getWSDL());
                fclose($file);
            }

            $server = new \SoapServer($storagePath . '/wsdl/' . $className . '.wsdl', array('soap_version' => SOAP_1_2));

            $server->setClass($procClass);

            $response = new Response();

            $response->headers->set("Content-Type","text/xml; charset=utf-8");

            ob_start();

            $server->handle();

            $response->setContent(ob_get_clean());

            Cache::forever('server_ip', $server_ip);

            return $response;

        } catch (\Exception $e) {
            Log::error( $e->getMessage());
            $responseData = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];

            return response()->json($responseData);
        }
    }
}