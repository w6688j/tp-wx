<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/10
 * Time: 13:42
 */

namespace Home\Controller;
use Think\Controller;

class Kuai3Controller extends BaseController
{
    public function index2(){
        $this->display();
    }

    public function index(){
//        10期结果
        $list = M('kuainumber')->where("game = 'kuai3'")->order("time DESC")->limit(10)->select();
        $lists = M('order')->where("game = 'kuai3'")->order("id DESC")->limit(10)->select();
        // 创建SDK实例
        $script = &  load_wechat('Script');
        // 获取JsApi使用签名，通常这里只需要传 $ur l参数
        $url = 'http://'.$_SERVER['SERVER_NAME'].'/Home/Circle/index.html';
        $options = $script->getJsSign($url, $timestamp, $noncestr, $appid);
        $kefu = M('config')->where("id = 1")->find();
        $bj=json_decode(M('game_config')->where(array("id"=>1))->getField("kuai3"),true);
        $this->assign("config",$bj);
        $hezhi=explode(',',$bj['kuai3_hezhi_bv']);
        foreach ($hezhi as $k=>$vo){
            $he=explode('=',$vo);
            $hezhis[$he[0]]=$he[1];
        }
        $this->assign('hezhi',$hezhis);
        $this->assign('kefu',$kefu);
        $this->assign('list',$list);
        $this->assign('lists',$lists);
        $this->assign('options',$options);
        $this->display();
//        $data = F('dandata');
//        dump($data);
    }
//    public function getdata(){
//        $data = M('dannumber')->where("game = 'jnd28'")->order('id DESC')->limit(1)->find();
//        return $this->ajaxReturn (json_encode($data),'JSON');
//    }
    public function jincai(){
        //聊天信息
        $list = M('kuai3message')->order("id DESC")->limit(20)->select();
        $this->assign('list',$list);
        $this->display();
    }
    public function getjilu(){
        //记录开始
        $userinfo = session('user');
        $pkdata = F('getkuai3data');
        $jilu  = M('order')->where(array('userid'=>$userinfo['id'],'game'=>'kuai3','state'=>1))->limit(20)->order('time desc')->select();
        for($i =0;$i<count($jilu);$i++){
            $jilu[$i]['numbers']=$pkdata['next']['periodNumber'];
            $jilu[$i]['time'] = date('H:i:s',$jilu[$i]['time']);
        }
        $this->ajaxReturn($jilu);
    }
    /*客服*/
    public function kefu(){
        $kefu = M('config')->where("id = 1")->find();
        $this->assign('kefu',$kefu);
        $this->display();
    }

    /*记录*/
    public function record(){
        $t = I('t');
        $pkdata = F('getkuai3data');
        if($t == 1){
            $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
            $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        }
        if($t == 2){
            $beginToday=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
            $endToday=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
        }
        if($t == 3){
            $beginToday=mktime(0,0,0,date('m'),date('d')-2,date('Y'));
            $endToday=mktime(0,0,0,date('m'),date('d')-1,date('Y'))-1;
        }
        if($t == 4){
            $beginToday=mktime(0,0,0,date('m'),1,date('Y'));
            $endToday=mktime(23,59,59,date('m'),date('t'),date('Y'));
        }

        $userinfo = session('user');
        $order = M('order');
        $count = $order->where("state=1 && userid = {$userinfo['id']} && time >=$beginToday && time < $endToday && game = 'kuai3'")->count();
        if($t == 4){
            $page = new \Think\Page($count,7);
        }else{
            $page = new \Think\Page($count,5);
        }
        $show = $page->show();
        $list = $order->where("state=1 && userid = {$userinfo['id']} && time >=$beginToday && time < $endToday && game = 'kuai3'")->limit($page->firstRow.','.$page->listRows)->order("number DESC")->select();

        if($t!=4){
            $number = array();
            for($i=0;$i<count($list);$i++){
                if(!in_array($list[$i]['number'], $number)){
                    $number[] = $list[$i]['number'];
                }
                for($a=0;$a<count($number);$a++){
                    if($list[$i]['number']==$number[$a]){
                        $list1[$a]['number'] = $number[$a];
                        $list1[$a]['order'][] = $list[$i];
                    }
                }
            }
        }
        //print_r($list1);
        $this->assign('list1',$list1);
        $this->assign('state',F('state'));
        $this->assign('number',$pkdata['next']['periodNumber']);
        $this->assign('list',$list);
        $this->assign('show',$show);
        $this->assign('today',mktime(0,0,0,date('m'),date('d'),date('Y')));
        $this->assign('t',$t);
        $this->display();
    }

    //取消
    public function del_all(){
        $state = F('kuai3_status');
        $userinfo = session('user');
        $pkdata = F('getkuai3data');
        if($state==1){
            $number = I('number');
            $list = M('order')->where("number = {$number} && userid = {$userinfo['id']}")->select();
            for($i=0;$i<count($list);$i++){
                if($list[$i]['number']==$pkdata['next']['periodNumber']){
                    $res[$i] = M('order')->where("id = {$list[$i]['id']}")->setField('state',0);
                    if($res[$i]){
                        M('user')->where("id = {$list[$i]['userid']}")->setInc('points',$list[$i]['del_points']);
                    }
                }else{
                    $data['error']==0;
                    $data['msg']=='本期已封盘';
                }
            }
            $data['error']==1;
        }else{
            $data['error']==0;
            $data['msg']=='本期已封盘';
        }
        $this->ajaxReturn($data);
    }
    //取消
    public function del(){
        $state = F('kuai3_status');
        $pkdata = F('getkuai3data');
        if($state==1){
//            $this->ajaxReturn('66');
            $id = I('id');
            $info = M('order')->where("id = $id")->find();

            if($info['number']==$pkdata['next']['periodNumber']){
//                $this->ajaxReturn('66');
                $res = M('order')->where("id = $id")->setField('state',0);

                if($res){
                    $data['error'] =1;
                    //加分
                    M('user')->where("id = {$info['userid']}")->setInc('points',$info['del_points']);

                }else{
                    $data['error'] =0;
                    $data['msg'] ='失败';

                }
            }else{
//                $this->ajaxReturn('44');
                $data['error'] =0;
                $data['msg'] ='失败';

            }
        }else{
            $data['error'] =0;
            $data['msg'] ='封盘了';

        }
        $this->ajaxReturn($data);
    }
    public function kuai3_header(){
        $this->display();
    }
}