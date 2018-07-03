<?php
/**
 * Created by PhpStorm.
 * User: smile
 * Date: 2018/1/7
 * Time: 10:34
 */

namespace Home\Controller;

use Common\Widget\Data;
use Think\Controller;
class CommonController extends BaseController
{
    public function getjilu(){
        if($_POST){
            $game = $_POST['game'];
            $userinfo = session('user');
            $pkdata = getgamekjdata($game);
            $jilu  = M('order')->where(array('userid'=>$userinfo['id'],'game'=>$_POST['game'],'state'=>1))->limit(20)->order('time desc')->select();
            for($i =0;$i<count($jilu);$i++){
                $jilu[$i]['numbers']=$pkdata['next']['periodNumber'];
                $jilu[$i]['time'] = date('H:i:s',$jilu[$i]['time']);
            }
            $this->ajaxReturn($jilu);
        }else{
            show('提交格式错误',0);
        }
    }
    //取消订单
    public function quxiao(){
        /*
         * id , game
         */
        if($_POST){
            $order_id = $_POST['id'];
            $game = $_POST['game'];
            $gamekjdata =getgamekjdata($game);
            $game_next_time = strtotime($gamekjdata['next']['awardTime']) -time();
            $config =C_set2();
            if($game_next_time >= $config[$game]['on_off']){
                $id = $order_id;
                $info = M('order')->where(array('id'=>$order_id))->find();
                if($info['number']==$gamekjdata['next']['periodNumber']){
                    $res = M('order')->where("id = $id")->setField('state',0);
                    if($res){
                        //加分
                        M('user')->where(array('id'=>$info['userid']))->setInc('points',$info['del_points']);
                        show('取消成功',1);
                    }else{
                       show('取消失败');
                    }
                }else{
                   show('本期已封盘');
                }
            }else{
                show('本期已封盘',0);
            }
        }else{
            show('提交方式错误',0);
        }

    }
}