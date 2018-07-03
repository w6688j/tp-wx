<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2017/12/25
 * Time: 14:12
 */

namespace Home\Controller;


class LhcController extends BaseController
{
    public function index(){
        //        10期结果
        $list = M('lhcnumber')->where("game = 'lhc'")->order("time DESC")->limit(10)->select();
        // 创建SDK实例
        $script = &  load_wechat('Script');
        // 获取JsApi使用签名，通常这里只需要传 $ur l参数
        $url = 'http://'.$_SERVER['SERVER_NAME'].'/Home/Circle/index.html';
        $options = $script->getJsSign($url, $timestamp, $noncestr, $appid);
        $kefu = M('config')->where("id = 1")->find();
        //留言信息
        $userinfo = session('user');
        $liuyan  = M('liuyan')->where(array('uid'=>$userinfo['id']))->order('time desc')->limit(15)->select();
        $this->assign('liuyan',$liuyan);
//        dump($liuyan);exit;
        $lists = M('sscmessage')->order("id DESC")->limit(20)->select();
        for ($i=0;$i<count($list);$i++){
            $list[$i]['arr'] = $this->panduan_tema($list[$i]['awardnumbers']);
        }
        $getlhcdata =F('getlhcdata');
        $all_shengxiaof=$this->get_all_shengxiao($getlhcdata['current']['awardNumbers']);
        $peilv_all =json_decode(M('game_config')->where(array("id"=>1))->getField("lhc"),true);
        $this->assign("tema_peilv", split_pv($peilv_all['lhc_tema_bv'],'='));
        $this->assign("sebo_peilv", split_pv($peilv_all['lhc_sebo_bv'],':'));
        $this->assign("pingtexiao_peilv", split_pv($peilv_all['lhc_pingtexiao_bv'],':'));
        $this->assign("shengxiao_peilv",split_pv($peilv_all['lhc_shengxiao_bv'],':'));
        $this->assign("liangmian_peilv",split_pv($peilv_all['lhc_liangmian_bv'],':'));
        $this->assign("config",$peilv_all);
        $this->assign('all_shengxiao',$all_shengxiaof);
        $this->assign('lists',$lists);
        $this->assign('kefu',$kefu);
        $this->assign('list',$list);
        $this->assign('options',$options);
        $this->display();
    }

    public function getjilu(){
        //记录开始
        $userinfo = session('user');
        $pkdata = F('getlhcdata');
        $jilu  = M('order')->where(array('userid'=>$userinfo['id'],'game'=>'lhc','state'=>1))->limit(20)->order('time desc')->select();
        for($i =0;$i<count($jilu);$i++){
            $jilu[$i]['numbers']=$pkdata['next']['periodNumber'];
            $jilu[$i]['time'] = date('H:i:s',$jilu[$i]['time']);
        }
        $this->ajaxReturn($jilu);
    }
    public function panduan_tema($number){
        //获取当前的开奖号码
        $number1 = explode(',',$number);
        //当期特码
        $tema = $number1[6];
        //当期的色波
        $sebo = '';
        $redbo = '1,2,7,8,12,13,18,19,23,24,29,30,34,35,40,45,46';
        $bluebo ='3,4,9,10,14,15,20,25,26,31,36,37,41,42,47,48';
        //$greebo ='5,6,11,16,17,21,22,27,28,32,33,38,39,43,44,49';
        if(in_array($tema,explode(',',$redbo))){
            $sebo= "红";
        }elseif (in_array($tema,explode(',',$bluebo))){
            $sebo= '蓝';
        }else{
            $sebo= "绿";
        }
        $wuxing ='';
        //获取当期的金木水火土
        $wuxingjin = '3,4,17,18,25,26,33,34,47,48';
        $wuxingmu = '7,8,15,16,29,30,37,38,45,46';
        $wuxingshui = '5,6,13,14,21,22,35,36,43,44';
        $wuxingtu ='11,12,19,20,27,28,41,42,49';
//             $wuxinghuo ='1,2,9,10,23,24,31,32,39,40';
        if(in_array($tema,explode(',',$wuxingjin))){
            $wuxing ="金";
        }elseif (in_array($tema,explode(',',$wuxingmu))){
            $wuxing ='木';
        }elseif (in_array($tema,explode(',',$wuxingshui))){
            $wuxing ='水';
        }elseif (in_array($tema,explode(',',$wuxingtu))){
            $wuxing ='土';
        }else{
            $wuxing ="火";
        }
        //查看当前开的是什么生肖
        $lhc_data = json_decode(M('game_config')->where("id=1")->getField("lhc"),true);
        $jinnian = $lhc_data['shengxiao'];
        $jinnianling = '';
        $arr = ['鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊', '猴', '鸡', '狗', '猪'];
        foreach ($arr as $key=>$value){
            if($jinnian ==$value){
                $jinnianling =$key;
            }
        }
        $sum =0;
        $chuli ='';
        while($sum <$tema) {
            $chuli =$jinnianling;
            $jinnianling--;
            if($jinnianling <0){
                $jinnianling =11;
            }
            $sum++;
        }

        return array('tema'=>$tema,'sebo'=>$sebo,'wuxing'=>$wuxing,'shengxiao'=>$arr[$chuli]);
    }
    //查看六合彩所有的开奖的生肖全部
    public function get_all_shengxiao($periodnumber){
        $arr = explode(',',$periodnumber);
        $list =array();
        foreach ($arr as $value){
            $res = $this->seeshengxiao($value);
            array_push($list,$res);
        }
        /*
         *  return array()
         */
        return $list;
    }
    public function sx(){
       dump($this->get_all_shengxiao(' 13,1,25,38,36,22,37'));
}

    //查看六合彩所有的开奖的生肖单个
    public function seeshengxiao($tema){
        //查看当前开的是什么生肖
        $lhc_data = json_decode(M('game_config')->where("id=1")->getField("lhc"),true);
        $jinnian = $lhc_data['shengxiao'];
        $jinnianling = '';
        $arr = ['鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊', '猴', '鸡', '狗', '猪'];
        foreach ($arr as $key=>$value){
            if($jinnian ==$value){
                $jinnianling =$key;
            }
        }
        $sum =0;
        $chuli ='';
        while($sum <$tema) {
            $chuli =$jinnianling;
            $jinnianling--;
            if($jinnianling <0){
                $jinnianling =11;
            }
            $sum++;
        }
        return $arr[$chuli];
    }
    public function lhc_header(){
        $this->display();
    }
}