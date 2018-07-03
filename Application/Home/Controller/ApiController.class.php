<?php

namespace Home\Controller;
use Home\Tool\Pk10;
use Think\Cache\Driver\Db;
use Think\Controller;

header('content-type:text/html;charset=utf-8');

class ApiController extends Controller{
    protected $game_config;
    public function getApi(){
        $type="Pk10";
        $type::js();
}
    public function getsscjisu(){
        echo json_encode(getjsssc());
        die();
    }
    public function test(){
        $data = M('number')->find();
        $num= $data['lh'];
        dump(json_decode($num));
    }
    public function getpk10jisu(){

        echo json_encode(getjscar());
        die();
    }
	public function getPk10(){
		echo json_encode(getPk10());
		die();
	}
	public function getXyft(){
        echo json_encode(getfei());
        die();
	}
    public function getBj28(){
        echo json_encode(getBj28());
        die();
    }
    public function getJnd28(){
        echo json_encode(getJnd28());
        die();
    }
    public function lhc(){
        $data =getgamedata('lhc');
        $time =strtotime($data['next']['awardTime']);
        $data['next']['awardTime'] =date('m-d-H:i',$time);
        $data['current']['shengxiao'] = get_all_shengxiao($data['current']['awardNumbers']);
        echo json_encode($data);
    }
    public function getkuai3(){
        echo json_encode(getkuai3());
        die();
    }
    public function getssc(){
        echo json_encode(getssc());
        die();
    }
    public function getfei(){
        echo json_encode(getfei());
        die();
    }



}
?>