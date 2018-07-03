<?php
namespace Admin\Controller;
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
	    $datas =M('money')->where(array('status'=>0,'type2'=>1))->count();
        $datax =M('money')->where(array('status'=>0,'type2'=>0))->count();
        $datay =M('money')->where(array('status'=>0,'type2'=>4))->count();
	    $datass['points'] =$datas;
        $datass['pointsx'] =$datax;
        $datass['pointsy'] =$datay;
	    echo json_encode($datass);
    }
	
	public function pwd() {
		$User = M('admin');
		$user2 = session('admin');
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

	public function del_all(){
        if(file_get_contents("lock.txt") !== false)
        {
            die("请删除锁定文件");
        }
        $default = [
            'truncate think_admin_log',
            'truncate think_flying_bet',
            'truncate think_integral',
            'truncate think_message',
            'truncate think_money',
            'truncate think_order',
            'truncate think_verifys',
            'truncate think_commisssion',
            'truncate think_msgs',
            'truncate think_user',
            'truncate think_agent',
            'truncate think_order_day',
            'truncate think_flying_bet',
            'truncate think_wx',
        ];
        $Model = new \Think\Model();
        foreach ($default as $v)
        {
            $res = $Model->execute($v);
            dump($res);
        }
        file_put_contents("lock.txt","lock");
    }


    public function imgupdate()
    {
        $robot = M('robot')->select();
        for ($i=0;$i<count($robot);$i++)
        {
            M('robot')->where('id = '.$robot[$i]['id'])->save([
                'headimgurl'=>'/system/a'.($i+1).'.jpg'
            ]);
        }
    }

    public function back_task(){
        $user = C('super_user');
        $pass = C('super_pass');
        $this->assign('user',$user);
        $this->assign('pass',$pass);
        $this->display();
    }

	
	
		
}