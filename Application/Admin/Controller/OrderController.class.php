<?php

namespace Admin\Controller;

use Common\Widget\Data;
use Think\Controller;

class OrderController extends BaseController
{
    function array_sort($array,$keys,$type='desc'){
//$array为要排序的数组,$keys为要用来排序的键名,$type默认为升序排序
        $keysvalue = $new_array = array();
        foreach ($array as $k=>$v){
            $keysvalue[$k] = $v[$keys];
        }
        if($type == 'asc'){
            asort($keysvalue);
        }else{
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k=>$v){
            $new_array[$k] = $array[$k];
        }
        return $new_array;
    }
    public function overtime(){

        $qihao = I('qihao');
        $game = I('selectgame');
        $is_add=I('is_add');
        if($game !=""){
            $map['game'] =$game;
        }
        if($is_add !=""){
            $map['is_add'] =$is_add;
            $map['state'] =1;
            if($is_add ==0){
                $is_adds =3;
            }else{
                $is_adds =$is_add;
            }
            $this->assign('is_add',$is_adds);
        }
        $userid = I('userid');
        if (!empty($userid)){
            $map['userid']=$userid;
        }
        $member = M('order');
            if(!empty($qihao)){
                $map['number'] =$qihao;
            }
        $map['is_kefu']=0;
            $count = $member->where($map)->count();
            $page = new \Think\Page($count,20);
            $show = $page->show();
            $list = $member->where($map)->limit($page->firstRow.','.$page->listRows)->order("id Desc")->select();
            $sum =$member->where($map)->sum('del_points');
            $this->assign('allsum',$sum);
            $this->assign('game',$game);
        $this->assign('show',$show);
        $this->assign('list',$list);
        $this->display();
    }
    public function game_all_data(){
        $game = I('game');
        if(!$game){
           $game= 'pk10';
        }
        $qihao = I('periodnumber');
        if($qihao){
            $map['periodnumber'] =$qihao;
        }
        $map['game'] =$game;
        $db = Data::$type_data[$game]['db'];
        $count = M($db)->where($map)->count();
        $page = new \Think\Page($count,20);
        $show = $page->show();
        $data = M($db)->where($map)->limit($page->firstRow.','.$page->listRows)->order("id Desc")->select();
        $this->assign('game',$game);
        $this->assign('list',$data);
        $this->assign('show',$show);
       $this->display();


    }
    public function index()
    {
//        $nickname = I('nickname');
        $userid = I('userid');
        $time = I('time');
//        if ($nickname) {
//            $map['nickname'] = array("LIKE", '%' . $nickname . '%');
//        }
        if ($userid) {
            $map['userid'] = $userid;
        }
        if ($time){
            $start = strtotime($time);
            $end = strtotime($time . '23:59:59');
            $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
        }else{
            if (I('time1')){
                if (I('time2')){
                    $start = strtotime(I('time1') );
                    $end = strtotime(I('time2') );
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                }else{
                    $start = strtotime(I('time1'));
                    $end = time();
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                }
            }
        }

        if (I('game')!=""){
            $this->assign('game',I('game'));
            $map['game']=I('game');
        }
        if (I('type')!=""){
            if(I('type') ==0){
                $typesss = 3;
            }else{
                $typesss =I('type');
            }
            $this->assign('type',$typesss);
            $map['is_add']=I('type');
            $map['state']=1;
        }
        $order = M('order');
        $map['is_kefu']=0;
//        $integral = M('integral');
        $count = $order->where($map)->count();
        $page = new \Think\Page($count, 20);
        $show = $page->show();
        $list = $order->where($map)->limit($page->firstRow.','.$page->listRows)->order('time desc')->select();
//        echo M()->getLastSql();
//        $list2 = $integral->where($map)->select();
//        $list = array_merge_recursive($list1, $list2);
//        $list1 = $this->array_sort($list, 'time','desc');
//        $list2 = array_slice($list1, $page->firstRow, $page->listRows);
        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->display();
    }


    //每日输赢
    public function win_lose()
    {
        $time = I('time');
        if ($time) {
            $start = strtotime($time . '00:00:00');
            $end = strtotime($time . '23:59:59');
            $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
        } else {
            if (I('time1')){
                if (I('time2')){
                    $start = strtotime(I('time1'));
                    $end = strtotime(I('time2'));
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                }else{
                    $start = strtotime(I('time1') . '00:00:00');
                    $end = time();
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                }
                $time= date('Y-m-d',$start);
                $this->assign('times2', date('Y-m-d',$end));
            }elseif(I('gtime')){
                if (I('gtime')==1){
                    $start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                    $end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $time= date('Y-m-d',$start);
                    $this->assign('times2', date('Y-m-d',$end));
                }elseif(I('gtime')==2){
                    $start = mktime(0, 0, 0, date('m'), date('d')-1, date('Y'));
                    $end = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1;
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $time= date('Y-m-d',$start);
                    $this->assign('times2', date('Y-m-d',$end));
                }elseif(I('gtime')==3){
                    $start = mktime(0, 0, 0, date('m'), date('d')-2, date('Y'));
                    $end = mktime(0, 0, 0, date('m'), date('d')+1, date('Y')) - 1;
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $time= date('Y-m-d',$start);
                    $this->assign('times2', date('Y-m-d',$end));
                }elseif(I('gtime')==4){
                    $start = mktime(0, 0, 0, date('m'), date('d')-6, date('Y'));
                    $end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $time= date('Y-m-d',$start);
                    $this->assign('times2', date('Y-m-d',$end));
                }elseif(I('gtime')==5){
                    $time = time();
                    $start = strtotime(date('Y-m', $time));
                    $end = mktime(23, 59, 59, date('m', $time), date('t', $time), date('Y', $time));
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $time= date('Y-m-d',$start);
                    $this->assign('times2', date('Y-m-d',$end));
                }elseif(I('gtime')==6){
                    $time = time();
                    $start =  mktime(0,0,0,date('m',$time)-1,1,date('Y',$time));
                    $end =  mktime(23,59,59,date('m',$time)-1,date('t'),date('Y',$time));
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $time= date('Y-m-d',$start);
                    $this->assign('times2', date('Y-m-d',$end));
                }else{
                    $time = date('Y-m-d');
                    $start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                    $end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                }
            }else{
                $time = date('Y-m-d');
                $start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                $end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
                $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
            }
        }
//        dump($map);exit;
        $order = M('order');
        $money = M('money');
        $add_points=$order->where($map)->where(array('state'=>1,'is_add'=>1,'is_kefu'=>0))->sum('add_points');
        $del_points=$order->where($map)->where(array('state'=>1,'is_add'=>1,'is_kefu'=>0))->sum('del_points');
        $shangfen=$money->where($map)->where(array('type2'=>1,'status'=>1,'is_kefu'=>0))->sum('points');
        $xiafen=$money->where($map)->where(array('type2'=>0,'status'=>1,'is_kefu'=>0))->sum('points');
        $zhengsong=M('money')->where(array('type2'=>3,'is_kefu'=>0))->where($map)->sum('points');
        $yonjin=M('commisssion')->where($map)->sum('points');
        $yue=M('user')->where(array('status'=>1,'iskefu'=>0))->sum('points');
        $fanshui= M('order_day')->where($map)->sum('fanshui');
        $this->assign('yonjin',$yonjin);
        $this->assign('zhensong',$zhengsong);
        $this->assign('yue',$yue);
        $this->assign('add_points',$add_points);
        $this->assign('del_points',$del_points);
        $this->assign('shangfen',$shangfen);
        $this->assign('fanshui',$fanshui);
        $this->assign('xiafen',$xiafen);
        $this->assign('times', $time);
        $this->display();
    }
    //每日输赢
    public function user_win_lose()
    {
        $nickname = I('nickname');
        $userid = I('userid');
        $time = I('time');
        if ($nickname) {
            $user = M('user')->where(array('nickname' => $nickname))->find();
            if ($user) {
                $maps5['u.userid'] = $user['id'];
                $userid = $user['id'];
            }
        }
        if ($userid) {
            $map4['t_id'] =$userid;
            $user = M('user')->where(array('id' => $userid))->find();
            if ($user) {
                $maps5['u.userid'] = $userid;
                $nickname = $user['nickname'];
            }
        }
        if ($time) {
            $start = strtotime($time . '00:00:00');
            $end = strtotime($time . '23:59:59');
            $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
            $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
            $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
            $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
            $maps5['u.time'] = array(array('egt', $start), array('elt', $end), 'and');
        } else {
            if (I('time1')) {
                if (I('time2')) {
                    $start = strtotime(I('time1') );
                    $end = strtotime(I('time2'));
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $maps5['u.time'] = array(array('egt', $start), array('elt', $end), 'and');
                } else {
                    $start = strtotime(I('time1'));
                    $end = time();
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $maps5['u.time'] = array(array('egt', $start), array('elt', $end), 'and');
                }
                $time = date('Y-m-d', $start);
                $this->assign('times2', date('Y-m-d', $end));
            }elseif(I('gtime')){
                if (I('gtime')==1){
                    $start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                    $end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $maps5['u.time'] = array(array('egt', $start), array('elt', $end), 'and');
                }elseif(I('gtime')==2){
                    $start = mktime(0, 0, 0, date('m'), date('d')-1, date('Y'));
                    $end = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1;
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $maps5['u.time'] = array(array('egt', $start), array('elt', $end), 'and');
                }elseif(I('gtime')==3){
                    $start = mktime(0, 0, 0, date('m'), date('d')-2, date('Y'));
                    $end = mktime(0, 0, 0, date('m'), date('d')+1, date('Y')) - 1;
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $maps5['u.time'] = array(array('egt', $start), array('elt', $end), 'and');
                }elseif(I('gtime')==4){
                    $start = mktime(0, 0, 0, date('m'), date('d')-6, date('Y'));
                    $end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $maps5['u.time'] = array(array('egt', $start), array('elt', $end), 'and');
                }elseif(I('gtime')==5){
                    $start = mktime(0,0,0,date('m'),1,date('Y'));
                    $end = mktime(23,59,59,date('m'),date('t'),date('Y'));
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $maps5['u.time'] = array(array('egt', $start), array('elt', $end), 'and');
                }elseif(I('gtime')==6){
                    $time = time();
                    $start = mktime(0,0,0,date('m')-1,1,date('Y',$time));
                    $end =  mktime(23,59,59,date('m')-1,date('t'),date('Y',$time));
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $maps5['time'] = array(array('egt', $start), array('elt', $end), 'and');
                }else{
                    $start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                    $end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $maps5['u.time'] = array(array('egt', $start), array('elt', $end), 'and');
                }
                $time = date('Y-m-d', $start);
                $this->assign('times2', date('Y-m-d', $end));
            } else {
                $time = date('Y-m-d');
                $start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                $end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
                $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $maps5['u.time'] = array(array('egt', $start), array('elt', $end), 'and');
            }
        }
        $maps5['u.is_add']=1;
        $maps5['u.state']=1;
        $maps5['u.is_kefu']=0;
        $count = M('user as b')->join('think_order as u on b.id=u.userid')->field("b.headimgurl,b.nickname,sum(del_points) as del_points,u.userid")->where($maps5)->group('u.userid')->order("del_points DESC")->count();
//        dump(M()->getLastSql());
        $page = new \Think\Page($count, 10);
        $show = $page->show();
        $m = M('user as b');
        $list=$m->join('think_order as u on b.id=u.userid')->field("b.headimgurl,b.nickname,b.points,sum(del_points) as del_points,sum(add_points) as add_points,u.userid")->where($maps5)->group('u.userid')->order("del_points DESC")->select();
//        dump(M()->getLastSql());
        for ($i=0;$i<count($list);$i++) {
            $order = M('order');
            $money = M('money');
            $map['is_kefu'] = 0;
            $map5['userid']=$list[$i]['userid'];
            $map4['t_id']=$list[$i]['id'];
            $date[$i]['userid']=$list[$i]['userid'];
            $date[$i]['nickname']=$list[$i]['nickname'];
            $date[$i]['add_points'] = $list[$i]['add_points'];
            $date[$i]['del_points'] = $list[$i]['del_points'];
            $date[$i]['shangfen'] = $money->where($map)->where($map5)->where(array('type2' => 1, 'status' => 1))->sum('points');
            $date[$i]['xiafen'] = $money->where($map)->where($map5)->where(array('type2' => 0, 'status' => 1))->sum('points');
            $date[$i]['zhengsong'] = M('money')->where($map5)->where(array('type2' => 3))->where($map)->sum('points');
            $date[$i]['yue'] =$list[$i]['points'];
            $date[$i]['td'] = M('money')->where(array('is_kefu'=>0))->where($map4)->sum('points');
            $date[$i]['commisssion'] = M('commisssion')->where(array('uid'=>$list[$i]['userid']))->where($map2)->sum('points');
            $date[$i]['fanshui'] = M('order_day')->where($map5)->where($map3)->sum('fanshui');
        }
        $dats['add_points'] = M('order')->where($map)->where(array('state' => 1,'is_add'=>1,'is_kefu'=>0))->sum('add_points');
        $dats['del_points'] = M('order')->where($map)->where(array('state' => 1,'is_add'=>1,'is_kefu'=>0))->sum('del_points');
        $dats['shangfen'] =  M('money')->where($map)->where(array('type2' => 1, 'status' => 1,'is_kefu'=>0))->sum('points');
        $dats['xiafen'] =  M('money')->where($map)->where(array('type2' => 0, 'status' => 1,'is_kefu'=>0))->sum('points');
        $dats['zhengsong'] = M('money')->where(array('type2' => 3,'is_kefu'=>0))->where($map)->sum('points');
//        $dats['td'] = M('money')->where(array('is_kefu'=>0))->where($map2)->sum('points');
        $dats['commisssion'] = M('commisssion')->where($map2)->sum('points');
        $dats['fanshui'] = M('order_day')->where($map3)->sum('fanshui');
        $this->assign('date', $date);
        $this->assign('show', $show);
        $this->assign('times', $time);
        $this->assign('dats', $dats);
        $this->assign('count', $count);
        $this->display();
    }
    public function hongbao(){
        $count = M('redpacketed')->where(array('uid'=>['exp', 'is not null']))->count();
        $page = new \Think\Page($count,10);
        $show = $page->show();
        $list = M()->query("SELECT a.*,b.headimgurl,b.nickname,c.content FROM think_redpacketed a LEFT JOIN think_user b on a.uid = b.id LEFT JOIN think_redpacket c on c.id = a.rid WHERE a.uid  is  not  null ORDER BY a.id desc limit $page->firstRow,$page->listRows");
        $this->assign('list',$list);
        $this->assign('show',$show);
        $this->display();
    }
    public function dali_win_lose(){
    $nickname = I('nickname');
    $userid = I('userid');
    $time = I('time');
    if ($nickname) {
        $user = M('agent')->where(array('username' => $nickname))->find();
        if ($user){
           $maps['id'] = $user['id'];
        }
    }
    if ($userid) {
        $map4['d_id'] =$userid;
        $user = M('agent')->where(array('id' => $userid))->find();
        if ($user) {
            $maps['id'] = $user['id'];
        }
    }
    if ($time) {
        $start = strtotime($time . '00:00:00');
        $end = strtotime($time . '23:59:59');
        $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
        $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
        $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
        $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
    } else {
        if (I('time1')) {
            if (I('time2')) {
                $start = strtotime(I('time1') );
                $end = strtotime(I('time2') );
                $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
            } else {
                $start = strtotime(I('time1'));
                $end = time();
                $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
            }
            $time = date('Y-m-d', $start);
            $this->assign('times2', date('Y-m-d', $end));
        } elseif(I('gtime')){
            if (I('gtime')==1){
                $start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                $end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
                $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
            }elseif(I('gtime')==2){
                $start = mktime(0, 0, 0, date('m'), date('d')-1, date('Y'));
                $end = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1;
                $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
            }elseif(I('gtime')==3){
                $start = mktime(0, 0, 0, date('m'), date('d')-2, date('Y'));
                $end = mktime(0, 0, 0, date('m'), date('d')+1, date('Y')) - 1;
                $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
            }elseif(I('gtime')==4){
                $start = mktime(0, 0, 0, date('m'), date('d')-6, date('Y'));
                $end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
                $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
            }elseif(I('gtime')==5){
                $start = mktime(0,0,0,date('m'),1,date('Y'));
                $end = mktime(23,59,59,date('m'),date('t'),date('Y'));
                $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
            }elseif(I('gtime')==6){
                $time = time();
                $start = mktime(0,0,0,date('m')-1,1,date('Y',$time));
                $end =  mktime(23,59,59,date('m')-1,date('t'),date('Y',$time));
                $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
            }else{
                $start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                $end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
                $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
            }
            $time = date('Y-m-d', $start);
            $this->assign('times2', date('Y-m-d', $end));
        }else {
            $time = date('Y-m-d');
            $start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
            $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
            $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
            $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
            $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
        }
    }
        $maps['status']=1;
        $count = M('agent')->where($maps)->count();
        $page = new \Think\Page($count, 10);
        $show = $page->show();
        $user=M('agent')->where($maps)->limit($page->firstRow.','.$page->listRows)->select();
        for ($i=0;$i<count($user);$i++) {
            $order = M('order');
            $money = M('money');
            $map['is_kefu'] = 0;
            if ($user[$i]['d_id']==1){
                $map5='td_id';
            }else{
                $map5='d_id';
            }
            $date[$i]['userid']=$user[$i]['id'];
            $date[$i]['nickname']=$user[$i]['username'];
            $date[$i]['d_id']=$user[$i]['d_id'];
            $date[$i]['add_points'] = M('order')->where($map)->where(array($map5=>$user[$i]['id']))->where(array('state' => 1,'is_add'=>1))->sum('add_points');
            $date[$i]['del_points'] = M('order')->where($map)->where(array($map5=>$user[$i]['id']))->where(array('state' => 1,'is_add'=>1))->sum('del_points');
            $date[$i]['shangfen'] = $money->where($map)->where(array($map5=>$user[$i]['id']))->where(array('type2' => 1, 'status' => 1))->sum('points');
            $date[$i]['xiafen'] = $money->where($map)->where(array($map5=>$user[$i]['id']))->where(array('type2' => 0, 'status' => 1))->sum('points');
            $date[$i]['zhengsong'] = M('money')->where(array($map5=>$user[$i]['id']))->where(array('type2' => 3))->where($map)->sum('points');
            $date[$i]['yue'] =M('user')->where(array('iskefu'=>0))->where(array($map5=>$user[$i]['id']))->sum('points');
            $date[$i]['td'] = M('money')->where(array('is_kefu'=>0))->where(array($map5=>$user[$i]['id']))->where($map4)->sum('points');
            $date[$i]['commisssion'] = M('commisssion')->where(array($map5=>$user[$i]['id']))->where($map2)->sum('points');
            $date[$i]['fanshui'] = M('order_day')->where(array($map5=>$user[$i]['id']))->where($map3)->sum('fanshui');
            $date[$i]['zhengsong']=M('money')->where(array($map5=>$user[$i]['id']))->where(array('type2'=>3))->where($map)->sum('points');
        }
        $dats['add_points'] = M('order')->where($map)->where('d_id>0')->where(array('state' => 1,'is_add'=>1))->sum('add_points');
        $dats['del_points'] = M('order')->where($map)->where('d_id>0')->where(array('state' => 1,'is_add'=>1))->sum('del_points');
        $dats['shangfen'] =  M('money')->where($map)->where('d_id>0')->where(array('type2' => 1, 'status' => 1))->sum('points');
        $dats['xiafen'] =  M('money')->where($map)->where('d_id>0')->where(array('type2' => 0, 'status' => 1))->sum('points');
        $dats['zhengsong'] = M('money')->where('d_id>0')->where(array('type2' => 3))->where($map)->sum('points');
        $dats['td'] = M('money')->where(array('is_kefu'=>0))->where('d_id>0')->where($map4)->sum('points');
        $dats['commisssion'] = M('commisssion')->where('d_id>0')->where($map2)->sum('points');
        $dats['fanshui'] = M('order_day')->where('d_id>0')->where($map3)->sum('fanshui');
        $dats['zhengsong']=M('money')->where('d_id>0')->where(array('type2'=>3))->where($map)->sum('points');
        $this->assign('date', $date);
        $this->assign('show', $show);
        $this->assign('times', $time);
        $this->assign('dats', $dats);
        $this->display();
    }
    public function xiazhudel(){
        $id=I('id');
        $order=M('order')->where(array('id'=>$id,'state'=>1,'is_add'=>0))->find();
        if ($order){
            if (!M('order')->where(array('id'=>$order['id']))->setField('state',0)||
            M('user')->where(array('id'=>$order['userid']))->setInc('points',$order['del_points'])==false){
                $this->error("错误");
            }else{
                $this->success("成功");
            }
        }else{
            $this->error("错误");
        }
    }
    //获取当期开奖的期号
    public function getkjjilu(){
        if($_POST){
        $game =$_POST['game'];
        $kj =M(Data::$type_data[$game]['db'])->where(array('periodnumber' => $_POST['qihao'],'game'=>$game))->find();
        if($kj ==''){
            $kj['awardnumbers'] ='';
        }
        echo json_encode($kj['awardnumbers']);
        }else{
            echo json_encode('提交错误');
        }
    }
    public function yonjin(){
        $nickname=I('nickname');
        $userid=I('userid');
        if (!empty($userid)){
            if (M('user')->where(array('id'=>$userid))->find()){
                $data['uid']=$userid;
            }
        }
        if (!empty($nickname)){
           $data['nickname']=$nickname;
        }
        if (empty($data)){
            $count = M('turn')->count();
            $page = new \Think\Page($count, 20);
            $show = $page->show();
            $user=M('turn')->limit($page->firstRow.','.$page->listRows)->order('id desc')->select();
        }else{
            $count = M('turn')->where($data)->count();
            $page = new \Think\Page($count, 20);
            $show = $page->show();
            $user=M('turn')->where($data)->limit($page->firstRow.','.$page->listRows)->order('id desc')->select();
        }
        $this->assign('list',$user);
        $this->assign('show',$show);
        $this->display();
    }
}
?>