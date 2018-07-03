<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/10
 * Time: 13:42
 */

namespace Home\Controller;
use Think\Controller;

class JndController extends BaseController
{
    public function test3(){
//
//　　　　$content = file_get_contents("http://dwz.la/short/?url=" . urlencode('http://baidu.com') . "&format=json");
//　　　　var_dump(json_decode($content,1));
        $content = file_get_contents("http://dwz.la/short/?url=" . urlencode('http://baidu.com') . "&format=json");

        $ss = json_decode($content,1);
        var_dump($ss);
    }
    public function index(){
//        10期结果

        $list = M('dannumber')->where("game = 'jnd28'")->order("time DESC")->limit(10)->select();

        // 创建SDK实例
        $script = &  load_wechat('Script');
        // 获取JsApi使用签名，通常这里只需要传 $ur l参数
        $url = 'http://'.$_SERVER['SERVER_NAME'].'/Home/Circle/index.html';
        $options = $script->getJsSign($url, $timestamp, $noncestr, $appid);
        $kefu = M('config')->where("id = 1")->find();
//        $lists = M('jndmessage')->order("id DESC")->where(array('game'=>'Jnd28'))->limit(20)->select();
//        $this->assign('lists',$lists);
        $bj=json_decode(M('game_config')->where(array("id"=>1))->getField("jnd28"),true);
        $this->assign("config",$bj);
        $hezhi=explode(',',$bj['jnd_hezhi_bv']);
        foreach ($hezhi as $k=>$vo){
            $he=explode('=',$vo);
            $hezhis[$he[0]]=$he[1];
        }
        $this->assign('hezhi',$hezhis);
        $this->assign('kefu',$kefu);
        $this->assign('list',$list);
        $this->assign('options',$options);
        $this->display();
//        $data = F('dandata');
//        dump($data);
    }
    //实时金额刷新
    public function getpoint(){
        $data = M('user')->where(array('id'=>session('user')['id']))->field('points')->find();
        echo json_encode($data);
    }
    public function kjlist(){
        $list = M('dannumber')->where("game = 'jnd28'")->order("id DESC")->limit(10)->select();
        echo json_encode($list);
    }
    public function getdata(){
        $data = M('dannumber')->order('id DESC')->limit(1)->find();
        return $this->ajaxReturn (json_encode($data),'JSON');
    }
    public function jincai(){
        //聊天信息
        $list = M('danmessage')->order("id DESC")->limit(20)->select();
        $this->assign('list',$list);
        $this->display();
    }
    /*客服*/
    public function kefu(){
        $kefu = M('config')->where("id = 1")->find();
        $this->assign('kefu',$kefu);
        $this->display();
    }
    public function getjilu(){
        //记录开始
        $userinfo = session('user');
        $pkdata = F('getjnd28data');
        $jilu  = M('order')->where(array('userid'=>$userinfo['id'],'game'=>'jnd28','state'=>1))->limit(20)->order('time desc')->select();
        for($i =0;$i<count($jilu);$i++){
            $jilu[$i]['numbers']=$pkdata['next']['periodNumber'];
            $jilu[$i]['time'] = date('H:i:s',$jilu[$i]['time']);
        }
        $this->ajaxReturn($jilu);
    }
    /*记录*/
//    public function record(){
//        $t = I('t');
//        $pkdata = F('getjnd28data');
//        if($t == 1){
//            $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
//            $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
//        }
//        if($t == 2){
//            $beginToday=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
//            $endToday=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
//        }
//        if($t == 3){
//            $beginToday=mktime(0,0,0,date('m'),date('d')-2,date('Y'));
//            $endToday=mktime(0,0,0,date('m'),date('d')-1,date('Y'))-1;
//        }
//        if($t == 4){
//            $beginToday=mktime(0,0,0,date('m'),1,date('Y'));
//            $endToday=mktime(23,59,59,date('m'),date('t'),date('Y'));
//        }
//
//        $userinfo = session('user');
//        $order = M('order');
//        $count = $order->where("state=1 && userid = {$userinfo['id']} && time >=$beginToday && time < $endToday && game = 'Jnd28'")->count();
//        if($t == 4){
//            $page = new \Think\Page($count,7);
//        }else{
//            $page = new \Think\Page($count,5);
//        }
//        $show = $page->show();
//        $list = $order->where("state=1 && userid = {$userinfo['id']} && time >=$beginToday && time < $endToday && game = 'Jnd28'")->limit($page->firstRow.','.$page->listRows)->order("number DESC")->select();
//
//        if($t!=4){
//            $number = array();
//            for($i=0;$i<count($list);$i++){
//                if(!in_array($list[$i]['number'], $number)){
//                    $number[] = $list[$i]['number'];
//                }
//                for($a=0;$a<count($number);$a++){
//                    if($list[$i]['number']==$number[$a]){
//                        $list1[$a]['number'] = $number[$a];
//                        $list1[$a]['order'][] = $list[$i];
//                    }
//                }
//            }
//        }
//        //print_r($list1);
//        $this->assign('list1',$list1);
//        $this->assign('state',F('state'));
//        $this->assign('number',$pkdata['next']['periodNumber']);
//        $this->assign('list',$list);
//        $this->assign('show',$show);
//        $this->assign('today',mktime(0,0,0,date('m'),date('d'),date('Y')));
//        $this->assign('t',$t);
//        $this->display();
//    }
    //取消
    public function del_all(){
        $state = F('jndstatus');
        $userinfo = session('user');
        $pkdata = F('getjnd28data');
        $number = I('number');
        $list = M('order')->where("number = {$number} && userid = {$userinfo['id']} && state =1")->select();
        if(!$list){
            $data['error']=0;
            $data['msg']='没有下注，取消失败';
        }else{
            if($state==1){
                for($i=0;$i<count($list);$i++){
                    if($list[$i]['number']==$pkdata['next']['periodNumber']){
                        $res[$i] = M('order')->where("id = {$list[$i]['id']}")->setField('state',0);
                        if($res[$i]){
                            M('user')->where("id = {$list[$i]['userid']}")->setInc('points',$list[$i]['del_points']);
                            //把信息那邊取消了
                            M('danmessage')->where(array('uid'=>$userinfo['id'],'qihao'=>$pkdata['next']['periodNumber']))->delete();
                        }
                    }else{
                        $data['error']=0;
                        $data['msg']='本期已封盘';
                    }
                }
                $data['error']=1;
                //取消成功後推送
                $message = array(
                    'to' => $userinfo['id'],
                    'type' => 'admin',
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => '客服',
                    'time' => date('H:i:s'),
                    'content' => '玩家「' . $userinfo['nickname'] . '」取消了當前全部的投注'
                );
                M('danmessage')->add($message);
                $this->send($message);
            }else{
                $data['error']=0;
                $data['msg']='本期已封盘';
            }
        }
        $this->ajaxReturn($data);
    }
    //取消
    public function del(){
        $state = F('jndstatus');
        $pkdata = F('getjnd28data');
        if($state==1){
            $id = I('id');
            $info = M('order')->where("id = $id")->find();
            if($info['number']==$pkdata['next']['periodNumber']){
                $res = M('order')->where("id = $id")->setField('state',0);
                if($res){
                    $data['error']=1;
                    //加分
                    M('user')->where("id = {$info['userid']}")->setInc('points',$info['del_points']);
                }else{
                    $data['error']=0;
                    $data['msg']='删除失败';
                }
            }else{
                $data['error']=0;
                $data['msg']='本期已封盘';
            }
        }else{
            $data['error']=0;
            $data['msg']='本期已封盘';
        }
        $this->ajaxReturn($data);
    }
    public function tuiguang(){
        $tuiguangsum =0;
        $alldata = M('user')->select();
        for ($i=0;$i<count($alldata);$i++){
            if($alldata[$i]['t_id'] ==session('user')['id']){
                $tuiguangsum+=1;
            }
        }
        $data =M('user')->where(array('id'=>session('user')['id']))->find();
        $this->assign('psum',$tuiguangsum);
        $this->assign('msum',$data['t_add']);
        $this->display();
    }
    public function shangfen(){
        $kefu = M('config')->where("id = 1")->find();
        $this->assign('kefu',$kefu);
        $this->display();

    }
    public function xiafen(){
        if(IS_POST){
            //验证
            $datas = $_POST;
            if(!$datas['jine'] ||!is_numeric($datas['jine'])){
                show('金额不能为空，只能为数字',0);
            }
            if(!$datas['accountnumber']){
                show('账号不能为空',0);
            }
            if($datas['type'] =='bankcard'){
                if(!$datas['khh'] ||!is_numeric($datas['khh'])){
                    show('开户行不能为空',0);
                }
                if(!$datas['skr'] ||!is_numeric($datas['skr'])){
                    show('收款人不能为空',0);
                }
            }
            //数据库中的金额有没有这么多
            $shengxias = M('user')->where(array('id'=>session('user')['id']))->find();
            if($datas['jine'] >$shengxias['points'] || $datas['jine']<C('zuiditixian')){
                show('请输入正确的额度,提取失败',0);
            }
            if($datas['type'] =='bankcard'){
                //如果是银行卡的
                $datass['headimgurl'] = session('user')['headimgurl'];
                $datass['nickname'] =session('user')['nickname'];
                $datass['yue'] = $shengxias['points']-$datas['jine'];
                $datass['time']=time();
                $datass['userid'] = session('user')['id'];
                $datass['typepay'] =$datas['type'];
                $datass['status']=0;
                $datass['type2']=0;
                $datass['points']=$datas['jine'];
                $datass['msg']='待审核';
                $datass['accountnumber'] = $datas['accountnumber'];
                $datass['khh'] = $datas['khh'];
                $datass['skr'] = $datas['skr'];
                $res =M('money')->add($datass);
                $res2 =M('user')->where(array('id'=>session('user')['id']))->setDec('points',$datas['jine']);
                if($res &&$res2){
                    show('提交，请等待审核',1);
                }else{
                    show('申请失败，请重试',0);
                }
            }else{
                $datass['yue'] =$shengxias['points']-$datas['jine'];
                $datass['headimgurl'] = session('user')['headimgurl'];
                $datass['nickname'] =session('user')['nickname'];
                $datass['time']=time();
                $datass['userid'] = session('user')['id'];
                $datass['typepay'] =$datas['type'];
                $datass['status']=0;
                $datass['type2']=0;
                $datass['points']=$datas['jine'];
                $datass['msg']='待审核';
                $datass['accountnumber'] = $datas['accountnumber'];
                $res =M('money')->add($datass);
                $res2 =M('user')->where(array('id'=>session('user')['id']))->setDec('points',$datas['jine']);
                if($res &&$res2){
                    show('提交，请等待审核',1);
                }else{
                    show('提交失败，请重试',0);
                }
            }
            show('提交成功','1');
        }else{
            $kefu = M('config')->where("id = 1")->find();
            $this->assign('kefu',$kefu);
            $this->display();
        }
    }
    public function weixin(){
        if(IS_POST){
            $datas = $_POST;
            if($datas['sum']<=0 &&$datas['sum']>200000){
                $data['status']=1;
                echo json_encode($data);
            }
            $shengxias = M('user')->where(array('id'=>session('user')['id']))->find();
            if(is_numeric($datas['sum'])){

                $datass['headimgurl'] = session('user')['headimgurl'];
                $datass['nickname'] =session('user')['nickname'];
                $datass['yue'] = $shengxias['points'];
                $datass['time']=time();
                $datass['userid'] = session('user')['id'];
                $datass['typepay']='weixin';
                $datass['status']=0;
                $datass['type2']=$datas['type2'];
                $datass['points']=$datas['sum'];
                $datass['msg']='待审核';
                if(M('money')->add($datass)){
                    $data['status'] = 0;
                }else{
                    $data['status']=1;
                }
            }else{
                $data['status'] = 1;
            }
            echo json_encode($data);
        }else{
            $kefu = M('config')->where("id = 1")->find();
            $this->assign('kefu',$kefu);
            $this->display();
        }

    }
    public function zhifubao(){
        if(IS_POST){
            $datas = $_POST;
            if($datas['sum']<=0 &&$datas['sum']>200000){
                $data['status']=1;
                echo json_encode($data);
            }
            $shengxias = M('user')->where(array('id'=>session('user')['id']))->find();
            if(is_numeric($datas['sum'])){
                $datass['headimgurl'] = session('user')['headimgurl'];
                $datass['nickname'] =session('user')['nickname'];
                $datass['yue'] = $shengxias['points'];
                $datass['time']=time();
                $datass['userid'] = session('user')['id'];
                $datass['typepay']='alipay';
                $datass['status']=0;
                $datass['type2']=$datas['type2'];
                $datass['points']=$datas['sum'];
                $datass['msg']='待审核';
                if(M('money')->add($datass)){
                    $data['status'] = 0;
                }else{
                    $data['status']=1;
                }
            }else{
                $data['status'] = 1;
            }
            echo json_encode($data);
        }else{
            $kefu = M('config')->where("id = 3")->find();
            $this->assign('zhifubao',$kefu);
            $this->display();
        }

    }
    public function remenber(){
        $data =M('money')->select();
        $this->display();
    }
    public function chongzhijl(){

        $count = M('money')->where(array('userid'=>session('user')['id']))->count();
        $page = new \Think\Page($count,20);
        $show = $page->show();
        $list = M('money')->limit($page->firstRow.','.$page->listRows)->where(array('userid'=>session('user')['id']))->order("id DESC")->select();
        $this->assign('list',$list);
        $this->assign('show',$show);
        $this->display();
    }
    public function jnd28_header(){
        $this->display();
    }
}