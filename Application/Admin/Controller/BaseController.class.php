<?php

namespace Admin\Controller;
use Think\Controller;
use Think\Auth;
class BaseController extends Controller{
	
	public function _initialize(){
		if(CONTROLLER_NAME!='Login'){
			if(empty($_SESSION['admin'])){
				$this->redirect('Login/index');
			}
		}
        $not_check = array('Index/index','Index/main','Order/index',
            'Login/login','Login/index','Login/logout');

        //当前操作的请求                 模块名/方法名
        if(in_array(CONTROLLER_NAME.'/'.ACTION_NAME, $not_check)){
        }else{
            $user=M('admin')->where(array('id'=>$_SESSION['admin']['id']))->find();
            if($user['group_id']!=1){
                $auth = new Auth();
                if(!$auth->check(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME,$_SESSION['admin']['id']) && $_SESSION['admin']['id'] != 1){
                    $this->error('没有权限');
                }
            }
        }
	}
	
	
}


?>