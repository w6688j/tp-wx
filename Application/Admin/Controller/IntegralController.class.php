<?php

namespace Admin\Controller;

use Think\Controller;

class IntegralController extends BaseController
{

    public function lists()
    {
        $nickname = I('nickname');
        $userid = I('userid');
        $integral = M('money');
        $time = I('time');
        $status =I('status');
        $type2 =I('type2');
        if($status){
            $map['status']=$status;
            $maps['status']=$status;
        }
        if($status==3){
            $map['status']=0;
            $maps['status']=0;
        }
        if ($type2 && $type2!=2){
            $map['type2']=$type2;
        }elseif ($type2==2){
            $map['type2']=0;
        }
        if ($nickname) {
            $us=M('user')->where(array('nickname'=>$nickname))->find();
            if ($us){
                $maps['userid'] = $us['id'];
                $map['userid'] = $us['id'];
            }
        }
        if ($time) {
            $start = strtotime($time . '00:00:00');
            $end = strtotime($time . '23:59:59');
            $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
            $maps['time'] = array(array('egt', $start), array('elt', $end), 'and');
        }else{
            if (I('time1')){
                if (I('time2')){
                    $start = strtotime(I('time1') );
                    $end = strtotime(I('time2') );
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $maps['time'] = array(array('egt', $start), array('elt', $end), 'and');
                }else{
                    $start = strtotime(I('time1') );
                    $end = time();
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $maps['time'] = array(array('egt', $start), array('elt', $end), 'and');
                }
            }
        }
        if ($userid) {
            $map['userid'] = $userid;
            $maps['userid'] = $userid;
        }
        //不显示不通过的
//        $map['status'] = array('neq',2);
        $map['is_kefu'] =0;
        $maps['is_kefu'] =0;
        $count = $integral->where($map)->count();
        $page = new \Think\Page($count, 10);
        $show = $page->show();
        $list = $integral->where($map)->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
        $shangfen=0;
        $xiafen=0;
        $zhengsong=0;
        for ($i = 0; $i < count($list); $i++) {
            $list[$i]['user'] = M('user')->where("id = {$list[$i]['userid']}")->find();
            if ($list[$i]['type2']==1){
                $shangfen+=$list[$i]['points'];
            }elseif ($list[$i]['type2']==2){
                $xiafen+=$list[$i]['points'];
            }else{
                $zhengsong+=$list[$i]['points'];
            }
        }
        if ($type2==1){
            $zshangfen=$integral->where($maps)->where(array('type2'=>1,'is_kefu'=>0))->sum('points');
            $zhong=$zshangfen;
        }elseif ($type2==2){
            $zxiafen=$integral->where($maps)->where(array('type2'=>0,'is_kefu'=>0))->sum('points');
            $zhong=$zxiafen;
        }elseif ($type2==3){
            $zxiafen=$integral->where($maps)->where(array('type2'=>3,'is_kefu'=>0))->sum('points');
            $zhong=$zxiafen;
        }else{
            $zshangfen=$integral->where($maps)->where(array('is_kefu'=>0))->sum('points');
            $zhong=$zshangfen;
        }
        $this->assign('zhong', $zhong);
        $this->assign('zhengsong', $zhengsong);
        $this->assign('shangfen', $shangfen);
        $this->assign('xiafen', $xiafen);
        $this->assign('status', $status);
        $this->assign('nickname', $nickname);
        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->display('lists');
    }
    public function delsx(){
        $id =$_POST['id'];
        $del =M('money')->where(array('id'=>$id))->delete();
        if($del){
            $this->success('成功');
        }else{
            $this->error('失败');
        }
    }

    public function gonggao()
    {
        if (IS_POST) {
            $gongao = I('gonggao');
            $mes= array(
                'type' => 'admin',
                'time' => date('H:i:s'),
                'content' => $gongao,
                'from_client_name'=>'管理员',
                'head_img_url' => '/Public/main/img/kefu.jpg',
                'name'=>'客服',
            );
            $messages = array(
                'uid' => 000,
                'type'=>1,
                'uname' =>'威尼斯人',
                'imgurl' =>  '/Public/main/img/kefu.jpg',
                'iskefu' => 1,
                'ishon' => 0,
                'content' => $gongao,
                'time' => time()
            );
            M('chatroom')->add($messages);
            $res =send_to_web($mes);
            if ($res) {
                $this->success('成功');
            } else {
                $this->error('失败');
            }
        } else {
            $this->display();
        }
    }

    public function index()
    {
        if (IS_POST) {
            if (!IS_AJAX) {
                $this->error('提交方式不正确');
            } else {
                $key=S("chongzhi");
                if (!empty($key)){
                    return false;
                }
                S("chongzhi",1,10);
                $liushuinum=I('num');
                $userid = I('userid');
                $points = I('points');
                if (!preg_match('/^[1-9]\d*$/', $points)) {
                    S("chongzhi",0);
                    $this->error('充值点数为正整数');
                }
                $res2 = M('user')->where("id = $userid")->setInc('points', $points);
                if ($res2) {
                    M('user')->where(array('id'=>$userid))->setField('liushui',$liushuinum);
                    if (I('types')==2){
                        $datasss = M('user')->where(array('id'=>$userid))->find();
                        if($datasss['t_id'] !=0){
                            $money['t_id'] =$datasss['t_id'];
                        }
                        if (!empty($datasss['d_id'])){
                            $money['d_id'] =$datasss['d_id'];
                            $money['td_id'] =$datasss['td_id'];
                        }
                        if($datasss['iskefu'] ==1){
                            $money['is_kefu'] =1;
                        }
                        $money['status'] =1;
                        $money['type2'] =1;
                        $money['userid'] = $userid;
                        $money['typepay'] = I('type');
                        $money['msg'] = '公司入款';
                        $money['points'] =$points;
                        $money['time'] =time();
                        $money['nickname'] =$datasss['nickname'];
                        $money['headimgurl'] = $datasss['headimgurl'];
                        $money['yue'] =$datasss['points'];
                        M('money')->add($money);
                        $message = array(
//                            'to' => $userid,
                            'type' => 'system',
                            'head_img_url' => '/Public/main/img/kefu.jpg',
                            'from_client_name' => '客服',
                            'time' => date('H:i:s'),
                            'content' => '玩家「' . $datasss['nickname'] . '」上分已受理，请注意查看点数'
                        );
                        M('message')->add($message);
                        if (!empty($datasss['d_id']) && $datasss['iskefu']==0){
                            M('agent')->where(array('id'=>$datasss['d_id']))->setInc('upperfen',$points);
                            M('agent')->where(array('id'=>$datasss['td_id']))->setInc('upperfen',$points);
                        }
                        $shangfen=M('money')->where(array('status'=>1,'type2'=>1,'userid'=>$userid))->sum('points');
                        if (intVal($shangfen)>=intVal(C_set('shangfen_zu'))){
                            M('user')->where(array('id'=>$userid))->setField('sf_zu',1);
                        }
                        $date=array(
                            'ip'=>get_client_ip(),
                            'content'=>"公司入账会员上分账号".$userid,
                            'create_time'=>time(),
                            'uid'=>session('admin')['id']
                        );
                        M('admin_log')->add($date);
                        $message['points'] = $points;
                        send_to_web($message);
                        S("chongzhi",0);
                        $this->success('充值成功,跳转中~', U('Admin/Member/index'), 1);
                    }else{
                        $info = M('user')->where("id = $userid")->find();
                        //充值记录
                        $data['userid'] = $userid;
                        $data['time'] = time();
                        $data['points'] = $points;
                        $data['type'] = '1';
                        $data['ip'] = get_client_ip();
                        $data['balance'] = $info['points'];
                        $datasss = M('user')->where(array('id'=>$userid))->find();
                        if($datasss['iskefu'] ==1){
                            $data['is_kefu'] =1;
                        }
                        if($datasss['t_id'] !=0){
                            $money['t_id'] =$datasss['t_id'];
                        }
                        if (!empty($datasss['d_id'])){
                            $money['d_id'] =$datasss['d_id'];
                            $money['td_id'] =$datasss['td_id'];
                        }
                        $money['status'] =1;
                        $money['type2'] =3;
                        $money['userid'] = $userid;
                        $money['msg'] = '彩金';
                        $money['points'] =$points;
                        $money['time'] =time();
                        $money['nickname'] =$info['nickname'];
                        $money['headimgurl'] = $info['headimgurl'];
                        $money['yue'] =$info['points'];
                        M('money')->add($money);
                        M('integral')->add($data);
                        if (!empty($datasss['d_id']) && $datasss['iskefu']==0){
                            M('agent')->where(array('id'=>$datasss['d_id']))->setInc('handsel',$points);
                            M('agent')->where(array('id'=>$datasss['td_id']))->setInc('handsel',$points);
                        }
                        //是否有人推荐
//                    if ($info['t_id']) {
//                        if ($points >= C('fenxiao_min')) {//最低充值
//                            M('user')->where("id = {$info['t_id']}")->setInc('points',$points*C('fenxiao')*0.01);
//                            M('user')->where("id = {$info['t_id']}")->setInc('t_add',$points*C('fenxiao')*0.01);
//                        }
//                    }
                        $message = array(
//                            'to' => $userid,
                            'type' => 'system',
                            'head_img_url' => '/Public/main/img/kefu.jpg',
                            'from_client_name' => '客服',
                            'time' => date('H:i:s'),
                            'content' => '玩家「' . $info['nickname'] . '」上分已受理，请注意查看点数'
                        );
                        M('message')->add($message);
                        $date=array(
                            'ip'=>get_client_ip(),
                            'content'=>"彩金会员上分账号".$userid,
                            'create_time'=>time(),
                            'uid'=>session('admin')['id']
                        );
                        M('admin_log')->add($date);
                        $message['points'] = $points;
                        send_to_web($message);
                        S("chongzhi",0);
                        $this->success('充值成功,跳转中~', U('Admin/Member/index'), 1);
                    }

                } else {
                    $this->error('充值失败！');
                }
            }
        } else {
            $id = I('id');
            $userinfo = M('user')->where("id = $id")->find();
            $this->assign('userinfo', $userinfo);
            $this->display();
        }

    }
    public function setcode()
    {
        if (IS_POST) {
            if (!IS_AJAX) {
                $this->error('提交方式不正确！');
            } else {
                $userid = I('userid');
                $code = I('code');
                if(!$code || strlen($code) <6){
                    $this->error('请输入6位以上的密码');
                }
                $codes = md5($code);
                $res2 = M('user')->where("id = $userid")->setField('password', $codes);
                if($res2){
                    $this->success('修改成功,跳转中~', U('Admin/Member/index'), 1);
                }else{
                    $this->error('修改失败!');
                }
            }
        } else {
            $id = I('id');
            $userinfo = M('user')->where("id = $id")->find();
            $this->assign('userinfo', $userinfo);
            $this->display();
        }
    }
    public function under()
    {
        if (IS_POST) {
            if (!IS_AJAX) {
                $this->error('提交方式不正确！');
            } else {
                $key=S('xiafen');
                if (!empty($key)){
                    $this->error('正在提交中！');
                }
                S('xiafen',1,10);
                $userid = I('userid');
                $points = I('points');
                if (!preg_match('/^[1-9]\d*$/', $points)) {
                    S('xiafen',0);
                    $this->error('兑换点数为正整数');
                }
                $info = M('user')->where("id = $userid")->find();
                if ($info['points'] < $points) {
                    S('xiafen',0);
                    $this->error('点数不足');
                }
                $res2 = M('user')->where("id = $userid")->setDec('points', $points);
                if ($res2) {

                    //下分记录
                    $data['userid'] = $userid;
                    $data['time'] = time();
                    $data['points'] = $points;
                    $data['type'] = '0';
                    $data['ip'] = get_client_ip();
                    $data['balance'] = $info['points'] - $points;
                    $datasss = M('user')->where(array('id'=>$userid))->find();
                    if($datasss['iskefu'] ==1){
                        $data['is_kefu'] =1;
                    }
                    $money['status'] =1;
                    $money['type2'] =0;
                    $money['userid'] = $userid;
                    $money['msg'] = '系统下分';
                    $money['points'] =$points;
                    $money['time'] =time();
                    $money['nickname'] =$info['nickname'];
                    $money['headimgurl'] = $info['headimgurl'];
                    $money['yue'] =$info['points'];
                    if (!empty($datasss['d_id']) &&  $datasss['iskefu']==0){
                        M('agent')->where(array('id'=>$datasss['d_id']))->setInc('lowerfen',$points);
                        M('agent')->where(array('id'=>$datasss['td_id']))->setInc('lowerfen',$points);
                        $money['d_id'] = $datasss['d_id'];
                        $money['td_id'] =$datasss['td_id'];
                    }
                    M('money')->add($money);
                    M('integral')->add($data);
                    $message = array(
//                        'to' => $userid,
                        'type' => 'points',
                        'head_img_url' => '/Public/main/img/kefu.jpg',
                        'from_client_name' => '客服',
                        'time' => date('H:i:s'),
                        'content' => '玩家「' . $info['nickname'] . '」回分已受理，请确认'
                    );
                    M('message')->add($message);
                    $date=array(
                        'ip'=>get_client_ip(),
                        'content'=>"系统下分会员下分账号".$userid,
                        'create_time'=>time(),
                        'uid'=>session('admin')['id']
                    );
                    M('admin_log')->add($date);
                    $message['points'] = $points * (-1);
                    send_to_web($message);
                    S('xiafen',0);
                    $this->success('下分成功,跳转中~', U('Admin/Member/index'), 1);
                } else {
                    S('xiafen',0);
                    $this->error('下分失败！');
                }
                S('xiafen',0);
            }
            S('xiafen',0);
        } else {
            $id = I('id');
            $userinfo = M('user')->where("id = $id")->find();
            $this->assign('userinfo', $userinfo);
            $this->display();
        }
    }
    public function tongguo(){
        $datas = $_POST;
        $userid = $datas['userid'];
        $id = $datas['id'];
        $points = $datas['points'];
        //充值记录
        $datab['userid'] = $userid;
        $datab['time'] = time();
        $datab['points'] = $points;
        $datab['type'] = '1';
        $datab['ip'] = get_client_ip();
        $datab['balance'] ='--';
        $datasss = M('user')->where(array('id'=>$userid))->find();
        if($datasss['iskefu'] ==1){
            $data['is_kefu'] =1;
        }
        if (!M('money')->where(array('id'=>$id,'status'=>0))->find()){
            $this->error('订单不存在！');
        }
       $key= S('shangfen');
        if (!empty($key)){
            $this->error('正在提交中！');
        }
        S('shangfen',1,10);
        M('integral')->add($datab);
        $res2 = M('user')->where("id = $userid")->setInc('points', $points);
        if($res2){
            if (!empty($datasss['d_id']) &&$datasss['iskefu'] ==0){
                M('agent')->where(array('id'=>$datasss['d_id']))->setInc('upperfen',$points);
                M('agent')->where(array('id'=>$datasss['td_id']))->setInc('upperfen',$points);
                $money['d_id'] = $datasss['d_id'];
                $money['td_id'] =$datasss['td_id'];
                $money['status'] =1;
                $money['msg'] ='审核通过';
                M('money')->where(array('id'=>$id))->save($money);
            }else{
                M('money')->where(array('id'=>$id))->setField(array('status'=>1,'msg'=>'审核通过'));
            }
        }
        $message = array(
//            'to' => $userid,
            'type' => 'points',
            'head_img_url' => '/Public/main/img/kefu.jpg',
            'from_client_name' => '客服',
            'time' => date('H:i:s'),
            'content' => '玩家「' . $datas['nickname'] . '」上分已受理，请注意查看点数'
        );
        M('danmessage')->add($message);
        $date=array(
            'ip'=>get_client_ip(),
            'content'=>"通过上分账号".$userid,
            'create_time'=>time(),
            'uid'=>session('admin')['id']
        );
        $shangfen=M('money')->where(array('status'=>1,'type2'=>1,'userid'=>$userid))->sum('points');
        if (intVal($shangfen)>=intVal(C_set('shangfen_zu'))){
            M('user')->where(array('id'=>$userid))->setField('sf_zu',1);
        }
        M('admin_log')->add($date);
        send_to_web($message);
        S('shangfen',0);
        show('成功','1');
    }
    public function butongguo(){
        $datas = $_POST;
        $userid = $datas['userid'];
        $id = $datas['id'];
        $type =$datas['type'];
        M('money')->where(array('id'=>$id))->setField(array('status'=>2,'msg'=>'审核不通过'));
        if($type ==0){
            $data = M('money')->where(array('id'=>$id))->find();
            if($data['xingzheng'] !==0){
               $add =$datas['points'] +$data['xingzheng'];
            }else{
                $add =$datas['points'];
            }
            M('user')->where(array('id'=>$userid))->setInc('points',$add);
        }
        echo ('不通过成功');
    }
    public function passxia(){
        $datas = $_POST;
        $userid = $datas['userid'];
        $id = $datas['id'];
        $points = $datas['points'];
        //充值记录
        $datab['userid'] = $userid;
        $datab['time'] = time();
        $datab['points'] = $points;
        $datab['type'] = '0';
        $datab['ip'] = get_client_ip();
        $datab['balance'] ='--';
        $datasss = M('user')->where(array('id'=>$userid))->find();
        if($datasss['iskefu'] ==1){
            $data['is_kefu'] =1;
        }
        M('integral')->add($datab);
//        $res2 = M('user')->where("id = $userid")->setDec('points', $points);
        if (!empty($datasss['d_id']) && $datasss['iskefu']==0){
            M('agent')->where(array('id'=>$datasss['d_id']))->setInc('lowerfen',$points);
            M('agent')->where(array('id'=>$datasss['td_id']))->setInc('lowerfen',$points);
            $money['d_id'] = $datasss['d_id'];
            $money['td_id'] =$datasss['td_id'];
            $money['status'] =1;
            $money['msg'] ='审核通过';
            M('money')->where(array('id'=>$id))->save($money);
        }else{
            M('money')->where(array('id'=>$id))->setField(array('status'=>1,'msg'=>'审核通过'));
        }
        M('user')->where(array('id'=>$userid))->setField('liushui',0);
        $message = array(
//            'to' => $userid,
            'type' => 'points',
            'head_img_url' => '/Public/main/img/kefu.jpg',
            'from_client_name' => '客服',
            'time' => date('H:i:s'),
            'content' => '玩家「' . $datas['nickname'] . '」下分已受理，请注意查看点数'
        );
        M('danmessage')->add($message);
        $date=array(
            'ip'=>get_client_ip(),
            'content'=>"通过下分账号".$userid,
            'create_time'=>time(),
            'uid'=>session('admin')['id']
        );
        M('admin_log')->add($date);
        send_to_web($message);
        show('成功','1');
    }
    public function tname(){
        if (IS_POST) {
            if (!IS_AJAX) {
                $this->error('提交方式不正确！');
            } else {
                $userid = I('userid');
                $points = I('tname');
                if(!number_format($points)){
                    $this->error('请输入用户id！');
                }
                $info = M('user')->where(array('id'=>$points))->find();
                if (!$info) {
                    $this->error('推荐人不存在');
                }
               if (M('user')->where(array('id'=>$userid))->setField('t_id',$info['id'])){
                   $date=array(
                       'ip'=>get_client_ip(),
                       'content'=>"修改推荐人账号".$userid,
                       'create_time'=>time(),
                       'uid'=>session('admin')['id']
                   );
                   M('admin_log')->add($date);
                    $this->success("修改成功", U('Admin/Member/index'), 1);
               }else{
                   $this->error('修改失败！');
               }
            }
        } else {
            $id = I('id');
            $userinfo = M('user')->where("id = $id")->find();
            $this->assign('userinfo', $userinfo);
            $this->display();
        }
    }
    public function yonjindo(){
        $datas = $_POST;
        if ($datas['status']==1) {
            if (!empty($datasss['d_id']) && $datasss['iskefu'] == 0) {
                $money['d_id'] = $datasss['d_id'];
                $money['td_id'] = $datasss['td_id'];
                $money['status'] = 1;
                $money['msg'] = '审核通过';
                M('money')->where(array('id' => $datas['id']))->save($money);
            } else {
                M('money')->where(array('id' => $datas['id']))->setField(array('status' => 1, 'msg' => '审核通过'));
            }
            $date = array(
                'ip' => get_client_ip(),
                'content' => "通过佣金账号" . $datas['userid'],
                'create_time' => time(),
                'uid' => session('admin')['id']
            );
            M('admin_log')->add($date);
            show('成功', '1');
            exit();
        }elseif ($datas['status']==2){
            $userid = $datas['userid'];
            $id = $datas['id'];
            $type =$datas['type'];
            M('money')->where(array('id'=>$id))->setField(array('status'=>2,'msg'=>'审核不通过'));
            if($type ==4){
                M('user')->where(array('id'=>$userid))->setInc('commission',$datas['points']);
            }
            echo ('不通过成功');
        }
    }
    public function daili(){
        if (IS_POST) {
            if (!IS_AJAX) {
                $this->error('提交方式不正确');
            } else {
                $id=I('uid');
                $d_id=I('d_id');
                $td_id=I('td_id');
                $userinfo = M('user')->where("id = $id")->find();
                if (!$userinfo){
                    $this->error("用户错误");
                }
                $daili=M('agent')->where(array('id'=>$d_id,'status'=>1))->find();
                if (!$daili){
                    $this->error("代理错误");
                }
                if(empty($daili['t_id'])){
                    $this->error("代理错误");
                }
                M()->startTrans();
                if (!empty($userinfo['d_id'])){
                    if (!M('agent')->where(array('id'=>$userinfo['d_id']))->setDec("t_num") ||! M('agent')->where(array('id'=>$userinfo['td_id']))->setDec("t_num") ){
                        M()->rollback();
                        $this->error("错误");
                    }
                    if ($userinfo['is_ztui']==1){
                        if (!M('agent')->where(array('id'=>$userinfo['d_id']))->setDec("tnum") ||!M('agent')->where(array('id'=>$userinfo['td_id']))->setDec("tnum")){
                            M()->rollback();
                            $this->error("错误");
                        }
                    }
                }
                $data['d_id']=$daili['id'];
                $data['td_id']=$daili['t_id'];

                if (M('user')->where(array('id'=>$userinfo['id']))->save($data)==false){
                    M()->rollback();
                    $this->error("错误");
                }//
                if (!M('agent')->where(array('id'=>$daili['id']))->setInc("t_num") ||!M('agent')->where(array('id'=>$daili['t_id']))->setInc("t_num") ){
                    M()->rollback();
                    $this->error("错误");
                }
                M()->commit();
                $this->success("成功");
            }
        } else {
            $id = I('id');
            $userinfo = M('user')->where("id = $id")->find();
            $this->assign('userinfo', $userinfo);
            $this->display();
        }
    }
}


?>