<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\report;
use DB;

class ResourceController extends Controller
{
	public function attendanceReport(Request $request)
	{
        $temp = array();
		foreach ($_POST as $key => $p){
			if($temp[$key]['status'] = DB::collection('report')->insert($p)){
						$temp[$key]['data'] = $p;
			}else{
						$temp[$key]['status'] = false;
						$temp[$key]['data'] = $p;
			}
		}
        return response()->json($temp);
	}

	public function dailyReport(Request $request)
	{
		$temp = array();
		foreach ($_POST as $key => $p){
			if($temp[$key]['status'] = DB::collection('report')->insert($p)){
						$temp[$key]['data'] = $p;
			}else{
						$temp[$key]['status'] = false;
						$temp[$key]['data'] = $p;
			}
		}
        return response()->json($temp);
	}
}