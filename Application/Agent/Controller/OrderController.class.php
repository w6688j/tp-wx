<?php

namespace Agent\Controller;

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
    public function index()
    {
        $user=session('agent');
        if ($user['d_id']==1){
            $map['b.td_id']=$user['id'];
        }else{
            $map['b.d_id']=$user['id'];
        }
        $game=I('game');
        if (!empty($game)){
            if ($game!='sss'){
                $maps['a.game']=I('game');
            }else{
                $maps['a.state']=1;
            }
        }

        $nickname = I('nickname');
        $userid = I('userid');
        $time = I('time');
        if ($nickname) {
            $map['b.nickname'] = array("LIKE", '%' . $nickname . '%');
        }
        if ($userid) {
            $map['a.userid'] = $userid;
        }
        if ($time) {
            $start = strtotime($time . '00:00:00');
            $end = strtotime($time . '23:59:59');
            $map['a.time'] = array(array('egt', $start), array('elt', $end), 'and');
        }else{
            if (I('time1')){
                if (I('time2')){
                    $start = strtotime(I('time1') );
                    $end = strtotime(I('time2'));
                    $map['a.time'] = array(array('egt', $start), array('elt', $end), 'and');
                }else{
                    $start = strtotime(I('time1'));
                    $end = time();
                    $map['a.time'] = array(array('egt', $start), array('elt', $end), 'and');
                }
            }
        }
        $map['a.is_kefu'] = 0;
        $count = M('order as a')->join('think_user  as  b  on b.id = a.userid')->where($maps)->where($map)->count();
        $page = new \Think\Page($count, 20);
        $show = $page->show();
        $list1 = M('order as a')->join('think_user  as  b  on b.id = a.userid')->where($maps)->where($map)->order("a.id DESC")->limit($page->firstRow, $page->listRows)->field("a.*,b.id,b.d_id,b.td_id,b.is_ztui,b.nickname,b.headimgurl")->select();
        $this->assign('list', $list1);
        $this->assign('show', $show);
        $this->display();
    }
    public function lists()
    {
        $user=session('agent');
        if ($user['d_id']==1){
            $map['b.td_id']=$user['id'];
            $maps['td_id']=$user['id'];
        }else{
            $map['b.d_id']=$user['id'];
            $maps['d_id']=$user['id'];
        }
        $status =I('status');
        $type2 =I('type2');
        if($status){
            $map['a.status']=$status;
        }
        if ($status==3){
            $map['a.status']=0;
        }
        if ($type2 && $type2!=2){
            $map['a.type2']=$type2;
        }elseif ($type2==2){
            $map['a.type2']=0;
        }
        $nickname = I('nickname');
        $userid = I('userid');
        $time = I('time');
        if ($time) {
            $start = strtotime($time . '00:00:00');
            $end = strtotime($time . '23:59:59');
            $map['a.time'] = array(array('egt', $start), array('elt', $end), 'and');
            $maps['time'] = array(array('egt', $start), array('elt', $end), 'and');
        }else{
            if (I('time1')){
                if (I('time2')){
                    $start = strtotime(I('time1'));
                    $end = strtotime(I('time2') );
                    $map['a.time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $maps['time'] = array(array('egt', $start), array('elt', $end), 'and');
                }else{
                    $start = strtotime(I('time1') );
                    $end = time();
                    $map['a.time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $maps['time'] = array(array('egt', $start), array('elt', $end), 'and');
                }
            }
        }
        if ($userid) {
            $map['a.userid'] = $userid;
            $maps['userid'] = $userid;
        }
        //不显示不通过的
//        $map['a.status'] = array('neq',2);
        $map['a.is_kefu'] =0;
        if ($nickname) {
            $us=M('user')->where(array('nickname'=>$nickname))->find();
            if ($us){
                $map['a.userid'] = $us['id'];
                $maps['userid'] = $us['id'];
            }
        }
        $count = M('money as a')->join('think_user  as  b  on b.id = a.userid')->where($map)->count();
        $page = new \Think\Page($count, 10);
        $show = $page->show();
        $list = M('money as a')->join('think_user  as  b  on b.id = a.userid')->where($map)->order("a.id DESC")->limit($page->firstRow . ',' . $page->listRows)->field("a.*,b.id as bid,b.d_id,b.td_id,b.is_ztui")->select();
//        die(M()->getLastSql());
        $shangfen=0;
        $xiafen=0;
        $zhengsong=0;
        for ($i = 0; $i < count($list); $i++) {
            $list[$i]['user'] = M('user')->where("id = {$list[$i]['userid']}")->find();
            if ($list[$i]['type2']==1){
                $shangfen+=$list[$i]['points'];
            }elseif ($list[$i]['type2']==0){
                $xiafen+=$list[$i]['points'];
            }else{
                $zhengsong+=$list[$i]['points'];
            }
        }
        if ($type2==1){
            $zshangfen=M('money')->where($maps)->where(array('status'=>1,'type2'=>1,'is_kefu'=>0))->sum('points');
            $zhong=$zshangfen;
        }elseif ($type2==2){
            $zxiafen=M('money')->where($maps)->where(array('status'=>1,'type2'=>0,'is_kefu'=>0))->sum('points');
            $zhong=$zxiafen;
        }elseif ($type2==3){
            $zxiafen=M('money')->where($maps)->where(array('status'=>1,'type2'=>3,'is_kefu'=>0))->sum('points');
            $zhong=$zxiafen;
        }else{
            $zshangfen=M('money')->where($maps)->where(array('status'=>1,'is_kefu'=>0))->sum('points');
            $zhong=$zshangfen;
        }
        $this->assign('zong', $zhong);
        $this->assign('zhengsong', $zhengsong);
        $this->assign('shangfen', $shangfen);
        $this->assign('xiafen', $xiafen);
        $this->assign('nickname', $nickname);
        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->display('lists');
    }
    public function dali_win_lose(){
        $nickname = I('nickname');
        $userid = I('userid');
        $time = I('time');
        if ($nickname) {
            $user = M('agent')->where(array('username' => $nickname))->find();
            if ($user && $user['t_id']==session('agent')['id']){
                $maps['id'] = $user['id'];
            }
        }
        if ($userid) {
            $map4['d_id'] =$userid;
            $user = M('agent')->where(array('id' => $userid))->find();
            if ($user&& $user['t_id']==session('agent')['id']) {
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
                    $end = strtotime(I('time2'));
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
        $maps['t_id']=session('agent')['id'];
        $count = M('agent')->where($maps)->count();
        $page = new \Think\Page($count, 10);
        $show = $page->show();
        $user=M('agent')->where($maps)->limit($page->firstRow.','.$page->listRows)->select();
        $order = M('order');
        $money = M('money');
        for ($i=0;$i<count($user);$i++) {
            $map['is_kefu'] = 0;
            if ($user[$i]['d_id']==1){
                $map5['td_id']=$user[$i]['id'];
            }else{
                $map5['d_id']=$user[$i]['id'];
            }
            $date[$i]['userid']=$user[$i]['id'];
            $date[$i]['nickname']=$user[$i]['username'];
            $date[$i]['add_points'] = M('order')->where($map)->where($map5)->where(array('state' => 1,'is_add'=>1))->sum('add_points');
            $date[$i]['del_points'] = M('order')->where($map)->where($map5)->where(array('state' => 1,'is_add'=>1))->sum('del_points');
            $date[$i]['shangfen'] = $money->where($map)->where($map5)->where(array('type2' => 1, 'status' => 1))->sum('points');
            $date[$i]['xiafen'] = $money->where($map)->where($map5)->where(array('type2' => 0, 'status' => 1))->sum('points');
            $date[$i]['zhengsong'] = M('money')->where($map5)->where(array('type2' => 3))->where($map)->sum('points');
            $date[$i]['yue'] =M('user')->where(array('iskefu'=>0))->where($map5)->sum('points');
            $date[$i]['td'] = M('money')->where(array('is_kefu'=>0))->where($map5)->where($map4)->sum('points');
            $date[$i]['commisssion'] = M('commisssion')->where($map5)->where($map2)->sum('points');
            $date[$i]['fanshui'] = M('order_day')->where($map5)->where($map3)->sum('fanshui');
            $date[$i]['zhengsong']=M('money')->where($map5)->where(array('type2'=>3))->where($map)->sum('points');
        }
        $dats['add_points'] = M('order')->where($map)->where(array('state' => 1,'is_add'=>1,'td_id'=>session('agent')['id']))->sum('add_points');
        $dats['del_points'] = M('order')->where($map)->where(array('state' => 1,'is_add'=>1,'td_id'=>session('agent')['id']))->sum('del_points');
        $dats['shangfen'] = $money->where($map)->where(array('type2' => 1, 'status' => 1,'td_id'=>session('agent')['id']))->sum('points');
        $dats['xiafen'] = $money->where($map)->where(array('type2' => 0, 'status' => 1,'td_id'=>session('agent')['id']))->sum('points');
        $dats['zhengsong'] = M('money')->where(array('type2' => 3,'td_id'=>session('agent')['id']))->where($map)->sum('points');
        $dats['yue'] =M('user')->where(array('iskefu'=>0,'td_id'=>session('agent')['id']))->sum('points');
        $dats['commisssion'] = M('commisssion')->where(array('td_id'=>session('agent')['id']))->where($map2)->sum('points');
        $dats['fanshui'] = M('order_day')->where(array('td_id'=>session('agent')['id']))->where($map3)->sum('fanshui');
        $dats['zhengsong']=M('money')->where(array('type2'=>3,'td_id'=>session('agent')['id']))->where($map)->sum('points');
        $this->assign('date', $date);
        $this->assign('show', $show);
        $this->assign('times', $time);
        $this->assign('dats', $dats);
        $this->display();
    }
    public function user_win_lose()
    {
        $nickname = I('nickname');
        $userid = I('userid');
        $time = I('time');
        if ($nickname) {
            $user = M('user')->where(array('nickname' => $nickname))->find();
            if ($user&& $user['d_id']==session('agent')['id']) {
                $maps['id'] = $user['id'];
            }
        }
        if ($userid) {
            $map4['t_id'] =$userid;
            $user = M('user')->where(array('id' => $userid))->find();
            if ($user&& $user['d_id']==session('agent')['id']) {
                $maps['id'] = $user['id'];
            }
        }
        $maps['d_id'] = session('agent')['id'];
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
                    $end = strtotime(I('time2'));
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
                } else {
                    $start = strtotime(I('time1') );
                    $end = time();
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map2['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map3['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map4['time'] = array(array('egt', $start), array('elt', $end), 'and');
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
            } else {
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
        $maps['is_kefu']=0;
        $count = M('user')->where($maps)->count();
        $page = new \Think\Page($count, 10);
        $show = $page->show();
        $user=M('user')->where($maps)->limit($page->firstRow.','.$page->listRows)->select();
        for ($i=0;$i<count($user);$i++) {
            $order = M('order');
            $money = M('money');
            $map['is_kefu'] = 0;
            $map5['userid']=$user[$i]['id'];
            $map4['t_id'] =$user[$i]['id'];
            $date[$i]['userid']=$user[$i]['id'];
            $date[$i]['nickname']=$user[$i]['nickname'];
            $date[$i]['add_points'] = $order->where($map)->where($map5)->where(array('state' => 1,'is_add'=>1))->sum('add_points');
            $date[$i]['del_points'] = $order->where($map)->where($map5)->where(array('state' => 1,'is_add'=>1))->sum('del_points');
            $date[$i]['shangfen'] = $money->where($map)->where($map5)->where(array('type2' => 1, 'status' => 1))->sum('points');
            $date[$i]['xiafen'] = $money->where($map)->where($map5)->where(array('type2' => 0, 'status' => 1))->sum('points');
            $date[$i]['zhengsong'] = M('money')->where($map5)->where(array('type2' => 3))->where($map)->sum('points');
            $date[$i]['yue'] =$user[$i]['points'];
            $date[$i]['td'] = M('money')->where(array('is_kefu'=>0))->where($map4)->sum('points');
            $date[$i]['commisssion'] = M('commisssion')->where(array('uid'=>$user[$i]['id']))->where($map2)->sum('points');
            $date[$i]['fanshui'] = M('order_day')->where($map5)->where($map3)->sum('fanshui');
        }
        $dats['add_points'] = M('order')->where($map)->where(array('state' => 1,'is_add'=>1,'d_id'=>session('agent')['id']))->sum('add_points');
        $dats['del_points'] = M('order')->where($map)->where(array('state' => 1,'is_add'=>1,'d_id'=>session('agent')['id']))->sum('del_points');
        $dats['shangfen'] = M('money')->where($map)->where(array('type2' => 1, 'status' => 1,'d_id'=>session('agent')['id']))->sum('points');
        $dats['xiafen'] = M('money')->where($map)->where(array('type2' => 0, 'status' => 1,'d_id'=>session('agent')['id']))->sum('points');
        $dats['zhengsong'] = M('money')->where(array('type2' => 3,'d_id'=>session('agent')['id']))->where($map)->sum('points');
        $dats['commisssion'] = M('commisssion')->where(array('uid'=>$user[$i]['id'],'d_id'=>session('agent')['id']))->where($map2)->sum('points');
        $dats['fanshui'] = M('order_day')->where(array('d_id'=>session('agent')['id']))->where($map3)->sum('fanshui');
        $dats['zhengsong']=M('money')->where(array('type2'=>3,'d_id'=>session('agent')['id']))->where($map)->sum('points');
        $this->assign('date', $date);
        $this->assign('show', $show);
        $this->assign('times', $time);
        $this->assign('dats', $dats);
        $this->display();
    }
}


?>