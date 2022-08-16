<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2020/11/19
 * Time: 17:10
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'setting';

    public $primaryKey = 'field_name';

    protected $guarded = [];

    public $incrementing = false;

    public $timestamps = false;
}