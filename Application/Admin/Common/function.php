<?php
/**
 * Created by PhpStorm.
 * User: smile
 * Date: 2017/9/8
 * Time: 15:362222
 */
function getkaijiang($data,$game){
    $dbdata="";
    if($game =='jnd28' ||$game =='bj28'){
      $dbdata = M('dannumber')->where(array('periodnumber'=>$data))->find();
    }elseif($game =='ssc'){
        $dbdata = M('sscnumber')->where(array('periodnumber'=>$data,'game'=>'ssc'))->find();
    }elseif ($game =='pk10'){
        $dbdata = M('number')->where(array('periodnumber'=>$data))->find();

    }elseif ($game =='kuai3'){
        $dbdata = M('kuainumber')->where(array('periodnumber'=>$data))->find();
    }elseif ($game =='fei') {
        $dbdata = M('number')->where(array('periodnumber' => $data))->find();

    }elseif ($game =='jscar') {
        $dbdata = M('number')->where(array('periodnumber' => $data,'game'=>'jscar'))->find();

    }elseif ($game =='jsssc'){
        $dbdata = M('sscnumber')->where(array('periodnumber' => $data,'game'=>'jsssc'))->find();

    }
    if(!$dbdata){
        return '没有存储或未开奖，如果迟迟不开奖，手动开奖';
    }else{
        return $dbdata['awardnumbers'];
    }
}