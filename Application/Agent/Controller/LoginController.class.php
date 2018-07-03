<?php

namespace Agent\Controller;
use Think\Controller;

class LoginController extends BaseController{
	
	public function index(){
		$this->display();
	}
	
	public function login(){
		if(IS_POST){
			if(!IS_AJAX){
				$this->error('提交方式不正确！');
			}else{
				$username = I('username');
				$password = md5(I('password'));
				$remember = I('remember');
				$res = M('agent')->where("username = '{$username}' && password = '{$password}'")->find();
				if($res){
					if($remember){
//						session(array('agentname'=>$res['username'],'expire'=>3600*24*3));
						session('agent',$res);
					}else{
//						session(array('name'=>'admin','expire'=>3600));
						session('agent',$res);
					}
					$map['ip_dress'] = get_client_ip();
					$map['last_time'] = time();
					M('agent')->where("id = {$res['id']}")->save($map);
					$this->success('登录成功,跳转中~',U('Agent/Index/index'),1);
				}else{
					$this->error('用户名或密码错误');
				}
			}
		}
	}
	
	
	public function logout(){
		session('agent',null);
		$this->redirect('Agent/Login/index');
	}
	
}

?>