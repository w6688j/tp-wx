<?php
namespace Agent\Controller;
use Think\Controller;

class IndexController extends BaseController {
	
    public function index(){
        $this->display();
	}
		
	public function main(){
		$number = M('number');
		$count = $number->count();
		$page = new \Think\Page($count,20);
		$show = $page->show();
		$list = $number->limit($page->firstRow.','.$page->listRows)->order("id DESC")->select();
		for($i=0;$i<count($list);$i++){
			$list[$i]['order'] = M('order')->where("number = {$list[$i]['']}")->select();
		}
		
		print_r($list);die();
		$this->display();
	}
	public function getmsg(){
	    $data =M('money')->where(array('status'=>0))->count();
	    $datas['points'] =$data;
        $datas['liuyan'] = M('liuyan')->where(array('see'=>'','is_kefu'=>0))->count();
	    echo json_encode($datas);
    }
	
	public function pwd() {
		$User = M('agent');
		$user2 = session('agent');
		if ($_POST) {
			if (!IS_AJAX) {
				$this->error('提交方式不正确', U('index/pwd'), 0);
			} else {
				$data['user'] = I('post.user');
				$data['password'] = md5(I('post.oldpassword'));
				$newpassword = md5(I('post.newpassword'));
				$repassword = md5(I('post.repassword'));
				$result = $User->where($data)->find();
				if ($result) {
					if ($newpassword != $repassword) {
						$this->error("两次输入新密码不一致");
					} else {
						$User->where($data)->setField('password', $newpassword);
						$this->success("修改成功", U('Login/index'),1);
					}
				} else {
					$this->error("账号或密码不正确");
				}
			}
		}
		$this -> assign('user2', $user2);
		$this -> display();
	}

	public function del() {
		delFileByDir(APP_PATH.'Runtime/');
		$this->success('删除缓存成功！',U('Admin/Index/index'));
	}

	
	
		
}