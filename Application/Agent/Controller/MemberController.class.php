<?php

namespace Agent\Controller;
use Think\Controller;

class MemberController extends BaseController{
	
	public function index(){
        $user=session('agent');
        if ($user['d_id']==1){
            $dates['td_id']=$user['id'];
            $date['a.td_id']=$user['id'];
        }else{
            $dates['d_id']=$user['id'];
            $date['a.d_id']=$user['id'];
        }
        if (I('uid')){
            $dates['d_id']=I('uid');
            $date['a.d_id']=I('uid');
        }
		$nickname = I('nickname');
		$userid = I('userid');
		$member = M('user');
		if(!empty($nickname)){
			$count = $member->where("nickname like '%{$nickname}%'")->where($dates)->count();
            $page = new \Think\Page($count,20);
			$show = $page->show();
			$list = M('user  as  a')->join('think_user  as  b  on b.id = a.t_id','LEFT')->join('think_agent  as  c  on c.id = a.d_id','LEFT')->field('a.*,b.nickname as tname,c.username as dname,b.id as bid,c.id as cid')->where("a.nickname like '%{$nickname}%'")->where($date)->limit($page->firstRow.','.$page->listRows)->order("a.id desc")->select();
		}elseif(!empty($userid)){
			$count = $member->where(array('id'=>$userid))->where($dates)->count();
			$page = new \Think\Page($count,20);
			$show = $page->show();
			$list =M('user  as  a')->join('think_user  as  b  on b.id = a.t_id','LEFT')->join('think_agent  as  c  on c.id = a.d_id','LEFT')->field('a.*,b.nickname as tname,c.username as dname,b.id as bid,c.id as cid')->where(array('a.id'=>$userid))->where($date)->limit($page->firstRow.','.$page->listRows)->order("a.id desc")->select();
		} else{
			$count = $member->where($dates)->count();
			$page = new \Think\Page($count,20);
			$show = $page->show();
			$list =M('user  as  a')->join('think_user  as  b  on b.id = a.t_id','LEFT')->join('think_agent  as  c  on c.id = a.d_id','LEFT')->field('a.*,b.nickname as tname,c.username as dname,b.id as bid,c.id as cid')->where($date)->limit($page->firstRow.','.$page->listRows)->order("a.id desc")->select();
		}
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}
	
	public function disable(){
		$id = I('id');
		$res = M('user')->where("id = $id")->setField('status',0);
		if($res){
			$this->success('禁用成功！');
		}else{
			$this->error('禁用失败！');
		}
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
	public function endisable(){
		$id = I('id');
		$res = M('user')->where("id = $id")->setField('status',1);
		if($res){
			$this->success('启用成功！');
		}else{
			$this->error('启用失败！');
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
    public function showdldo(){
        $ip =$_POST['ip'];
        $data =GetIpLookup($ip);
        $res =$data['country']."-".$data['province'].'-'.$data['city'].'-'.$data['district'];
        die(json_encode($res));
    }
	public function gagdo(){
        if (I('status')==1){
            $id = I('id');
            $res = M('user')->where("id = $id")->setField('isgag',1);
            if($res){
                $this->success('禁言成功！');
            }else{
                $this->error('禁言失败！');
            }
        }else{
            $id = I('id');
            $res = M('user')->where("id = $id")->setField('isgag',0);
            if($res){
                $this->success('取消禁言成功！');
            }else{
                $this->error('取消禁言失败！');
            }
        }
    }

    public function kefudo(){
        if (I('status')==1){
            $id = I('id');
            $res = M('user')->where("id = $id")->setField('iskefu',1);
            if($res){
                $this->success('设置成功！');
            }else{
                $this->error('设置失败！');
            }
        }else{
            $id = I('id');
            $res = M('user')->where("id = $id")->setField('iskefu',0);
            if($res){
                $this->success('取消客服成功！');
            }else{
                $this->error('取消客服失败！');
            }
        }
    }
    public function daili(){
        $user=session('agent');
        if ($user['d_id']!=1){
            $this->error("未成为总代理");
        }
        $date['t_id']=$user['id'];
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
    public function fenhonbili(){
        $id=I('uid');
        $num=I('num');
        if (empty($num)){
            $date['msg']="分红比例不能为空";
            die(json_encode($date));
        }
        $user=session('agent');
        if ($num>=$user['bonus_d']){
           $date['msg']="比例设置错误";
           die(json_encode($date));
        }
        $us=M('agent')->where(array('id'=>$id,'status'=>1,'t_id'=>$user['id']))->find();
        if (!$us){
            $date['msg']="代理不存在";
            die(json_encode($date));
        }
        $data['bonus_d']=$num;
        if (!M('agent')->where(array('id'=>$us['id']))->save($data)){
            $date['msg']="设置失败";
            die(json_encode($date));
        }else{
            $date['msg']="设置成功";
            die(json_encode($date));
        }
    }
    public function password(){
        $id=I('uid');
        $password=I('password');
        $user=session('agent');
        if ($user['d_id']!=1){
            $date['msg']="错误";
            die(json_encode($date));
        }
        if (empty($password)){
            $date['msg']="密码不能为空";
            die(json_encode($date));
        }
        $us=M('agent')->where(array('id'=>$id,'status'=>1,'t_id'=>$user['id']))->find();
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
    public function adddaili(){
        $this->display();
    }
    public function adddailido(){
        $username=I('username');
        $password=I('password');
        $tpassword=I('tpassword');
        $user=session('agent');
        $phone=I('phone');
        $name=I('name');
        $qqnum=I('qqnum');
        $bank_num=I('bank_num');
        $bank_dress=I('bank_dress');
        if (empty($username)||empty($password)||empty($tpassword)||empty($phone)||empty($name)||empty($qqnum)||empty($bank_num)||empty($bank_dress)){
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
            'status'=>1,
            'create_time'=>time(),
            'd_id'=>2,
            't_id'=>$user['id'],
            'phone'=>$phone,
            'name'=>$name,
            'qqnum'=>$qqnum,
            'bank_num'=>$bank_num,
            'bank_dress'=>$bank_dress,
            'beizhu'=>I('beizhu'),
        );
        if (!M('agent')->add($data)){
            $this->error("错误,请联系管理员");
        }
        $this->success("添加成功",U('Agent/member/daili'),1);
    }
    public function geterweima(){
        $user=session('agent');
        $url =  'http://'.$_SERVER['HTTP_HOST']."/home/index/index?d_id=" . $user['id'];
//        $url= file_get_contents('http://suo.im/api.php?url='.$url);
        //$url = C('dh_url') . "?d_id=" . $user['id'];
        $this->assign('url', $url);
        $this->display();
    }
    public function setmessage(){
        if (IS_AJAX){
            $user=M('agent')->where(array('id'=>$_POST['id']))->find();
            $_POST['password']=md5($_POST['password']);
            if (empty($_POST['password'])){
                $_POST['password']=$user['password'];
            }
            if(M('agent')->where(array('id'=>$_POST['id']))->save($_POST)){
                $this->success("成功",U('agent/member/index'));
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

?>