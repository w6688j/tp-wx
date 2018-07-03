<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2017/12/19
 * Time: 16:23
 */

namespace Admin\Controller;


class AgentController extends BaseController
{
    public function index(){
        $this->display();
    }
    public function adddailido(){
        $username=I('username');
        $password=I('password');
        $tpassword=I('tpassword');
        $phone=I('phone');
//        $bili=I('bili');
        if (empty($username)||empty($password)||empty($tpassword)||empty($phone)){
            $this->error("不能为空");
        }
        if (preg_match("/^[A-Za-z0-9]+$/", $username) == false) {
            $this->error("用户名不能含有中文");
        }
        $ls=M('agent')->where(array('username'=>$username))->find();
        if ($ls){
            $this->error("账号已存在");
        }
        if ($password!=$tpassword){
            $this->error("两次密码不一致");
        }
        $data=array(
            'username'=>$username,
            'password'=>md5($password),
//            'bonus_d'=>$bili,
            'status'=>1,
            'phone'=>$phone,
            'create_time'=>time(),
            'd_id'=>1,
        );
        if (!M('agent')->add($data)){
            $this->error("错误,请联系管理员");
        }
        $this->success("添加成功",U('admin/Agent/index'),1);
    }
    public function zhitui(){
        $nickname = I('nickname');
        $userid = I('userid');
        $member = M('user');
        $date['d_id']=I('uid');
        $date['is_ztui']=1;
        if(!empty($nickname)){
            $count = $member->where("nickname like '%{$nickname}%'")->where($date)->count();
            $page = new \Think\Page($count,20);
            $show = $page->show();
            $list = $member->where("nickname like '%{$nickname}%'")->where($date)->limit($page->firstRow.','.$page->listRows)->order("id desc")->select();
        }elseif(!empty($userid)){
            $count = $member->where(array('id'=>$userid))->where($date)->count();
            $page = new \Think\Page($count,20);
            $show = $page->show();
            $list = $member->where(array('id'=>$userid))->where($date)->limit($page->firstRow.','.$page->listRows)->order("id desc")->select();
        } else{
            $count = $member->where($date)->count();
            $page = new \Think\Page($count,20);
            $show = $page->show();
            $list = $member->where($date)->limit($page->firstRow.','.$page->listRows)->order("id desc")->select();
        }
        for ($i=0;$i<count($list);$i++){
            $user=M('user')->where(array('id'=>$list[$i]['t_id']))->field('nickname')->find();
            $list[$i]['tname']=$user['nickname'];
        }
        $this->assign('did',$date['d_id']);
        $this->assign('show',$show);
        $this->assign('list',$list);
        $this->display();
    }
    public function userlist(){
        $nickname = I('nickname');
        $userid = I('userid');
        $member = M('user');
        $date['d_id']=I('uid');
        if(!empty($nickname)){
            $count = $member->where("nickname like '%{$nickname}%'")->where($date)->count();
            $page = new \Think\Page($count,20);
            $show = $page->show();
            $list = $member->where("nickname like '%{$nickname}%'")->where($date)->limit($page->firstRow.','.$page->listRows)->order("id desc")->select();
        }elseif(!empty($userid)){
            $count = $member->where(array('id'=>$userid))->where($date)->count();
            $page = new \Think\Page($count,20);
            $show = $page->show();
            $list = $member->where(array('id'=>$userid))->where($date)->limit($page->firstRow.','.$page->listRows)->order("id desc")->select();
        } else{
            $count = $member->where($date)->count();
            $page = new \Think\Page($count,20);
            $show = $page->show();
            $list = $member->where($date)->limit($page->firstRow.','.$page->listRows)->order("id desc")->select();
        }
        for ($i=0;$i<count($list);$i++){
            $user=M('user')->where(array('id'=>$list[$i]['t_id']))->field('nickname')->find();
            $list[$i]['tname']=$user['nickname'];
        }
        $this->assign('did',$date['d_id']);
        $this->assign('show',$show);
        $this->assign('list',$list);
        $this->display();
    }
    public function daili(){
        $date['t_id']=I('uid');
        $date['d_id']=2;
        if (I('uname')){
            $date['username']=I('uname');
        }
        $count = M('agent')->where($date)->count();
        $page = new \Think\Page($count,20);
        $show = $page->show();
        $list=M('agent')->where($date)->limit($page->firstRow.','.$page->listRows)->select();
        $this->assign('list',$list);
        $this->assign('show',$show);
        $this->display();
    }
    public function zongdaili(){
        $date['d_id']=1;
        $date['status']=1;
        if (I('uname')){
            $date['username']=I('uname');
        }
        $map['is_kefu'] =0;
        $count = M('agent')->where($map)->where($date)->count();
        $page = new \Think\Page($count,20);
        $show = $page->show();
        $data=array();
        $list=M('agent')->where($date)->where($map)->limit($page->firstRow.','.$page->listRows)->select();
        for ($i=0;$i<count($list);$i++){
            $data['xpoints']+=$list[$i]['xpoints'];
            $data['spoints']+=$list[$i]['spoints'];
            $data['upperfen']+=$list[$i]['upperfen'];
            $data['lowerfen']+=$list[$i]['lowerfen'];
            $data['handsel']+=$list[$i]['handsel'];
            $data['hbmoney']+=$list[$i]['hbmoney'];
            $data['yue']+=M('user')->where(array('td_id'=>$list[$i]['id'],'status'=>1,'iskefu'=>0))->sum("points");
        }
        $dats['xpoints']=M('agent')->where($date)->sum('xpoints');
        $dats['spoints']=M('agent')->where($date)->sum('spoints');
        $dats['upperfen']=M('agent')->where($date)->sum('upperfen');
        $dats['lowerfen']=M('agent')->where($date)->sum('lowerfen');
        $dats['handsel']=M('agent')->where($date)->sum('handsel');
        $dats['hbmoney']=M('agent')->where($date)->sum('hbmoney');
        if ($date['username']){
            $datx['td_id']=$list[0]['id'];
        }
        $datx['status']=1;
        $datx['iskefu']=0;
        $dats['yue']=M('user')->where('td_id>0')->where($datx)->sum("points");
        $this->assign('list',$list);
        $this->assign('data',$data);
        $this->assign('dats',$dats);
        $this->assign('show',$show);
        $this->display();
    }
    public function disables(){
        $id = I('id');
        $res = M('agent')->where("id = $id")->setField('status',0);
        if($res){
            $this->success('禁用成功！');
        }else{
            $this->error('禁用失败！');
        }
    }
    public function endisables(){
        $id = I('id');
        $res = M('agent')->where("id = $id")->setField('status',1);
        if($res){
            $this->success('启用成功！');
        }else{
            $this->error('启用失败！');
        }
    }
    public function password(){
        $id=I('uid');
        $password=I('password');
        if (empty($password)){
            $date['msg']="密码不能为空";
            die(json_encode($date));
        }
        $us=M('agent')->where(array('id'=>$id,'status'=>1))->find();
        if (!$us){
            $date['msg']="代理不存在";
            die(json_encode($date));
        }
        $data['password']=md5($password);
        if (!M('agent')->where(array('id'=>$us['id']))->save($data)){
            $date['msg']="设置失败";
            die(json_encode($date));
        }else{
            $date['msg']="设置成功";
            die(json_encode($date));
        }
    }
    public function setmessage(){
        if (IS_AJAX){
            if(M('agent')->where(array('id'=>$_POST['id']))->save($_POST)){
                $this->success("成功",U('agent/zongdaili/index'));
            }else{
                $this->error("失败");
            }
        }else{
            $user=M('agent')->where(array('id'=>I('id')))->find();
            $this->assign('userinfo',$user);
            $this->display();
        }
    }
}