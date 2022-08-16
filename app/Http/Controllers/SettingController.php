<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2020/11/19
 * Time: 17:05
 */

namespace App\Http\Controllers;


use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function index()
    {
        $departments = app(SettingService::class)->getDepartments();
        $test_items = app(SettingService::class)->getTestItems();
        return view('setting', [
            "departments" => $departments,
            "test_items" => $test_items,
        ]);
    }

    public function update(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params,
            [
                'departments' => ['nullable', 'string'],
                '$test_items' => ['nullable', 'string'],
            ]
        );

        if ($validator->fails()) {
            $msg = implode(" ",$validator->messages()->all());
            return Redirect::back()->withInput()->withErrors([$msg]);
        }
        try{
            app(SettingService::class)->setDepartments($params);
            app(SettingService::class)->setTestItems($params);
        }
        catch (\Exception $e){
            $msg = $e->getMessage();

            return Redirect::back()->withInput()->withErrors([$msg]);
        }

        $msg = '设置成功。';

        return Redirect::back()->withInput()->withErrors([$msg]);
    }
}