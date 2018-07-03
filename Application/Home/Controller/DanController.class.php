<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/10
 * Time: 13:42
 */

namespace Home\Controller;
use Think\Controller;

class DanController extends BaseController
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

        $list = M('dannumber')->where("game = 'bj28'")->order("time DESC")->limit(10)->select();

        // 创建SDK实例
        $script = &  load_wechat('Script');
        // 获取JsApi使用签名，通常这里只需要传 $ur l参数
        $url = 'http://'.$_SERVER['SERVER_NAME'].'/Home/Circle/index.html';
        $options = $script->getJsSign($url, $timestamp, $noncestr, $appid);
        $kefu = M('config')->where("id = 1")->find();
//        $lists = M('danmessage')->order("id DESC")->where(array('game'=>'Bj28'))->limit(20)->select();
//        $this->assign('lists',$lists);
        $bj=json_decode(M('game_config')->where(array("id"=>1))->getField("bj28"),true);
        $this->assign("config",$bj);
        $hezhi=explode(',',$bj['hezhi_bv']);
        foreach ($hezhi as $k=>$vo){
            $he=explode('=',$vo);
            $hezhis[$he[0]]=$he[1];
        }
        $this->assign('hezhi',$hezhis);
        $this->assign('kefu',$kefu);
        $this->assign('list',$list);
        $this->assign('options',$options);
        $this->display();
    }
    /*
     * 全部通用
     */
    public function getpoint(){
        $data = M('user')->where(array('id'=>session('user')['id']))->field('points')->find();
        echo json_encode($data);
    }
    public function kjlist(){
        $list = M('dannumber')->where("game = 'bj28'")->order("id DESC")->limit(10)->select();
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

    /*记录*/
    public function record(){
        $t = I('t');
        $pkdata = F('getbj28data');
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
        $count = $order->where("state=1 && userid = {$userinfo['id']} && time >=$beginToday && time < $endToday && game = 'bj28'")->count();
        if($t == 4){
            $page = new \Think\Page($count,7);
        }else{
            $page = new \Think\Page($count,5);
        }
        $show = $page->show();
        $list = $order->where("state=1 && userid = {$userinfo['id']} && time >=$beginToday && time < $endToday && game = 'bj28'")->limit($page->firstRow.','.$page->listRows)->order("number DESC")->select();

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
    public function checkxiafen(){
        $userid =session('user')['id'];
        //查看最后一次上分的金钱
        $points =M('money')->where(array('userid'=>$userid,'type2'=>1,'status'=>1))->order('time desc')->find();
        $begin =$points['time'];
        $end =time();
        //查看区间流水3
        $map['time'] = array('between', "$begin,$end");
        $map['state'] =1;
        $map['is_add']=1;
        $map['userid'] =$userid;
        $del_points =M('order')->where($map)->sum('del_points');
       //比例
        $bfb = $del_points/$points['points'];
        $user=M('user')->where(array('id'=>$userid))->find();
        if ($user['liushui']>0){
            if ($user['liushui']<=$del_points){
                $data['status'] =1;
                $data['bfb'] =$bfb;
                $data['ls'] =$del_points;
                echo json_encode($data);exit;
            }else{
                //不通过
                $data['status'] =0;
                $data['bfb'] =$bfb;
                $data['ls'] =$del_points;
                echo json_encode($data);exit;
            }
        }else{
            if(C('liushuib')*0.01 <=$bfb ){
                //通过
                $data['status'] =1;
                $data['bfb'] =$bfb;
                $data['ls'] =$del_points;
                echo json_encode($data);exit;
            }else{
                //不通过
                $data['status'] =0;
                $data['bfb'] =$bfb;
                $data['ls'] =$del_points;
                echo json_encode($data);
            }
        }


    }
    public function xiafen(){
        if(IS_POST){
            //验证
            $datas = $_POST;
            if(!$datas['jine']){
                show('金额不能为空，只能为数字',0);
            }
            if(!$datas['accountnumber']){
                show('账号不能为空',0);
            }
            if($datas['type'] =='bankcard'){
                if(!$datas['khh']){
                    show('开户行不能为空',0);
                }
                if(!$datas['skr']){
                    show('收款人不能为空',0);
                }
            }
            if(S('xiafensuo'.session('user')['id'])){
                show('请勿频繁下分，稍后再试。。。',0);
            }
            S('xiafensuo'.session('user')['id'],'1',6);

            //数据库中的金额有没有这么多
            $shengxias = M('user')->where(array('id'=>session('user')['id']))->find();

            if(!$shengxias['username']){
                 show('为保障你的资金安全，请绑定手机号',3);
            }
            if($datas['panduan']==0){
                if($datas['jine'] >$shengxias['points'] || $datas['jine']<C('zuiditixian')){
                    show('请输入正确的额度,提取失败',0);
                }
            }else{
                if($datas['jine']>$shengxias['points'] || $datas['jine']<C('zuiditixian')){
                    show('请输入正确的额度,提取失败',0);
                }
            }

            if($datas['type'] =='bankcard'){
                //如果是银行卡的
                $datass['headimgurl'] = session('user')['headimgurl'];
                $datass['nickname'] =session('user')['nickname'];
                $datass['yue'] = $shengxias['points']-$datas['jine'];
                $datass['time']=time();
                $datass['userid'] = session('user')['id'];
                $datass['typepay'] ='yignhangka';
                $datass['status']=0;
                $datass['type2']=0;
                if($datas['panduan']==0){
                    $datass['points'] =$datas['jine']- $datas['jine']*C('xingzheng')*0.01;
                }else{
                    $datass['points']=$datas['jine'];
                }
                $datass['msg']='待审核';
                $datass['accountnumber'] = $datas['accountnumber'];
                $datass['khh'] = $datas['khh'];
                $datass['skr'] = $datas['skr'];
                $datass['bfb'] =$datas['bfb'];
                if($datas['panduan']==0){
                    $datass['xingzheng'] = C('xingzheng')*$datas['jine']*0.01;
                }
                //判断是否为客服
                $data = M('user')->where(array('id'=>$datass['userid']))->find();
                if($data['iskefu'] ==1){
                    $datass['is_kefu'] =1;
                }
                $res =M('money')->add($datass);
                $res2 =M('user')->where(array('id'=>session('user')['id']))->setDec('points',$datas['jine']);
                if($res &&$res2){
                    if($shengxias['khh'] !== $datas['khh'] ||$shengxias['name'] !== $datas['skr']||$shengxias['banksou'] !== $datas['accountnumber']){
                        $ins['khh']  = $datas['khh'] ;
                        $ins['name'] = $datas['skr'] ;
                        $ins['banksou']=$datas['accountnumber'];
                        M('user')->where(array('id'=>session('user')['id']))->save($ins);
                    }
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
                if($datas['panduan']==0){
                    $datass['points']=$datas['jine']-C('xingzheng')*$datas['jine']*0.01;
                }else{
                    $datass['points']=$datas['jine'];
                }
                $datass['msg']='待审核';
                $datass['accountnumber'] = $datas['accountnumber'];
                if($datas['panduan']==0){
                    $datass['xingzheng'] = C('xingzheng')*$datas['jine']*0.01;
                }
                $datass['bfb'] = $datas['bfb'];
                //判断是否为客服
                $data = M('user')->where(array('id'=>$datass['userid']))->find();
                if($data['iskefu'] ==1){
                    $datass['is_kefu'] =1;
                }
                $res =M('money')->add($datass);
                $res2 =M('user')->where(array('id'=>session('user')['id']))->setDec('points',$datas['jine']);
                if($res &&$res2){
                    if($datas['type'] =='alipay'){
                        if($shengxias['zhifubao'] !== $datas['accountnumber']){
                            $ins['zhifubao']=$datas['accountnumber'];
                            M('user')->where(array('id'=>session('user')['id']))->save($ins);
                        }
                    }elseif ($datas['type']=='weixin'){
                        if($shengxias['weixin'] !== $datas['accountnumber']){
                            $ins['weixin']=$datas['accountnumber'];
                            M('user')->where(array('id'=>session('user')['id']))->save($ins);
                        }
                    }
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
            if(S('shangfen'.session('user')['id'])){
                show('频繁操作','1');
            }
            S('shangfen'.session('user')['id'],'1',2);
            $datas = $_POST;
            if($datas['sum']<=0 &&$datas['sum']>200000){
                $data['status']=1;
                echo json_encode($data);
            }
            if($datas['sum']<C('upper_money')){
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
                $datass['accountnumber'] =$_POST['accountnumber'];
                $datass['type2']=$datas['type2'];
                $datass['points']=$datas['sum'];
                $datass['msg']='待审核';
                //判断是否为客服
                $data = M('user')->where(array('id'=>$datass['userid']))->find();
                if($data['iskefu'] ==1){
                    $datass['is_kefu'] =1;
                }
                if($data['t_id'] !=0){
                    $datass['t_id'] =$data['t_id'];
                }
                if(M('money')->add($datass)){
                    $data['status'] = 0;
                }else{
                    $data['status']=1;
                }
            }else{
                $data['status'] = 1;
            }
            //储存账户
            if($shengxias['weixin'] !== $_POST['accountnumber']){
                $ins['weixin']  = $_POST['accountnumber'];
                M('user')->where(array('id'=>session('user')['id']))->save($ins);
            }
            echo json_encode($data);
        }else{
            $data = M('user')->where(array('id'=>session('user')['id']))->find();
            $this->assign('datas',$data);
            $kefu = M('config')->where("id = 1")->find();
            $this->assign('kefu',$kefu);
            $this->display();
        }

    }
    public function yinghangka(){
        if(S('shangfen'.session('user')['id'])){
            show('频繁操作','1');
        }
        S('shangfen'.session('user')['id'],'1',2);
        $datas = $_POST;
        if($datas['sum']<=0 &&$datas['sum']>200000){
            $data['status']=1;
            echo json_encode($data);
        }
        if($datas['sum']<C('upper_money')){
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
            $datass['typepay']='yignhangka';
            $datass['status']=0;
            $datass['accountnumber'] =$_POST['accountnumber'];
            $datass['type2']=$datas['type2'];
            $datass['points']=$datas['sum'];
            $datass['msg']='待审核';
            //判断是否为客服
            $data = M('user')->where(array('id'=>$datass['userid']))->find();
            if($data['iskefu'] ==1){
                $datass['is_kefu'] =1;
            }
            if($data['t_id'] !=0){
                $datass['t_id'] =$data['t_id'];
            }
            if(M('money')->add($datass)){
                $data['status'] = 0;
            }else{
                $data['status']=1;
            }
        }else{
            $data['status'] = 1;
        }
        //储存账户
        if($shengxias['banksou'] !== $_POST['accountnumber']){
            $ins['banksou']  = $_POST['accountnumber'];
            M('user')->where(array('id'=>session('user')['id']))->save($ins);
        }
        echo json_encode($data);
    }
    public function zhifubao(){
        if(IS_POST){
            if(S('shangfen'.session('user')['id'])){
                show('频繁操作','1');
            }
            S('shangfen'.session('user')['id'],'1',2);
            $datas = $_POST;
            if($datas['sum']<=0 &&$datas['sum']>200000){
                $data['status']=1;
                echo json_encode($data);
            }
            if($datas['sum']<C('upper_money')){
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
                $datass['accountnumber'] =$_POST['accountnumber'];
                $datass['type2']=$datas['type2'];
                $datass['points']=$datas['sum'];
                $datass['msg']='待审核';
                //判断是否为客服
                $data = M('user')->where(array('id'=>$datass['userid']))->find();
                if($data['iskefu'] ==1){
                    $datass['is_kefu'] =1;
                }
                if($data['t_id'] !=0){
                    $datass['t_id'] =$data['t_id'];
                }
                if(M('money')->add($datass)){
                    $data['status'] = 0;
                }else{
                    $data['status']=1;
                }
            }else{
                $data['status'] = 1;
            }
            //储存账户
            if($shengxias['zhifubao'] !== $_POST['accountnumber']){
                $ins['zhifubao']  = $_POST['accountnumber'];
                M('user')->where(array('id'=>session('user')['id']))->save($ins);
            }
            echo json_encode($data);
        }else{
            $data = M('user')->where(array('id'=>session('user')['id']))->find();
            $this->assign('datas',$data);
            $kefu = M('config')->where("id = 1")->find();
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
    public function bj28_header(){
        $this->display();
    }


}