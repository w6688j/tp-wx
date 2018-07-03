<?php
namespace Home\Controller;
use Think\Controller;
header('content-type:text/html;charset=utf-8');
class IndexController extends Controller {
    public function __construct()
    {
        /* 读取站点配置 */
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $config =  M('config')->find();
            S('DB_CONFIG_DATA',$config);
        }
        C($config); //添加配置
        parent::__construct();
    }

	public function error(){
		$this->display();
	}

    public function index(){
	    if(session('user'))
        {
            $result ="/home/select/index";
            header("location:" . $result);
            return;
        }
    	if(C('is_open')==0){
    		$this->redirect('error');
    	}
        if(C('is_wai') ==1){
    	    $getip = get_client_ip();
            $iparr = explode(',',$getip);
            if(count($iparr) >1){
                $getip = $iparr[1];
            }
            if(is_ch($getip) ==0){
                die('hello world');
            }
        }
    	$t_id = I('t');
		session('tid',$t_id);
        $d_id = I('d_id');
        if (!empty($d_id)){
            session('d_id',$d_id);
        }

        $oauth = load_wechat('Oauth');
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
          //  http://www.baidu.com/home/index/redirect_url?opendi=dfasd //变成跳转
            $url = base64_encode(urlencode('http://'.$_SERVER['HTTP_HOST']."/home/index/redirect_url"));
            //$url =
            $url  =C('dh_url'). $url;
            $this->assign('url',$url);
            $this->display();
			//$result = $oauth->getOauthRedirect(C('weixin_url').'/home/index/redirect_url');
			//dump($result);exit;
		}else{
			$result ="/home/publics/login";
            header("location:" . $result);
        }
	}

	public function redirect_url(){
		$oauth = load_wechat('Oauth');
		$result = $oauth->getOauthAccessToken();
//		$result['openid'] = "oHscexBLPJMtQQ8jsOOGvj1m0BFU";
		$userinfo = $oauth->getOauthUserinfo($result['access_token'], $result['openid']);
		//判断是否第一次登陆
		$wx = M('wx');
		$user = M('user');
		$res = $wx->where("openid = '{$result['openid']}'")->find();
		if($res){
			//是否过期
//			if($res['expires_in']<time()){
//				$wx->where("openid = '{$result['openid']}'")->setField('access_token',$result['access_token']);
//			}
			//查找会员数据
			$info = $user->where("id = {$res['userid']}")->find();
			$user->where("id = {$res['id']}")->setField('last_time',time());
            M('user')->where("id = {$res['id']}")->setField('last_ip',get_client_ip());
			session('user',$info);
			//是否禁用
			if($info['status']==0){
				$this->redirect('error');
			}
			$this->redirect('Home/Select/index');
		}else{
			if(C('is_open_reg')==0){
	    		$this->redirect('error');
	    	}
			
			//是否推荐
			$t_id = session('tid');
			if($t_id){
				$data['t_id'] = $t_id;
                $tname=M('user')->where(array('id'=>$t_id))->find();
                $tnum=M('user')->where(array('tid'=>$t_id))->count();
                if (intVal($tnum)>=intVal(C('tuijian_zu'))){
                    M('user')->where(array('id'=>$t_id))->setField('tj_zu',1);
                }
				if (!empty($tname['d_id'])){
                    $data['d_id'] = $tname['d_id'];
                    $data['td_id'] = $tname['td_id'];
                }
			}
			if (session('d_id')){
                $shang=M('agent')->where(array('id'=>session('d_id')))->find();
			    if ($shang){
                    $data['d_id'] = session('d_id');
                    $data['td_id'] = $shang['t_id'];
                    $data['is_ztui'] = 1;
                }
            }
			//自动注册
			$data['nickname'] = htmlspecialchars($userinfo['nickname']);
//			$headimgurl = $userinfo['headimgurl'];
			$data['headimgurl'] = $userinfo['headimgurl'];
			$data['country'] = $userinfo['country'];
			$data['province'] = $userinfo['province'];
			$data['sex'] = $userinfo['sex'];
			$data['user_agent'] = serialize(get__browser());
			$data['city'] = $userinfo['city'];
			$data['reg_ip'] = get_client_ip();
//			$data['points'] = 10;
			$data['last_ip'] = get_client_ip();
			$data['reg_time'] = time();
			$data['last_time'] = time();
			$data['logins'] = 1;
			$userid = $user->add($data);
			if($userid){
				$data1['userid'] = $userid;
				$data1['openid'] = $result['openid'];
				$data1['access_token'] = $result['access_token'];
				$data1['expires_in'] = time()+$result['expires_in'];
				$res2 = $wx->add($data1);
				if($res2){
					$data['id'] = $userid;
                    if (session('d_id')) {
                        M('agent')->where(array('id'=>session('d_id')))->setInc('tnum');
                        M('agent')->where(array('id'=>$data['td_id']))->setInc('tnum');
                    }
                    if (!empty($data['d_id'])){
                        M('agent')->where(array('id'=>$data['d_id']))->setInc('t_num');
                        M('agent')->where(array('id'=>$data['td_id']))->setInc('t_num');
                    }
					session('user',$data);
					$this->redirect('Home/Select/index');
				}
			}
		}
	}

    public function redirect2_url(){
        $t_id = I('t');
        if(!empty($t_id)){
            session('tid',$t_id);
        }
        $d_id = I('d_id');
        if (!empty($d_id)){
            session('d_id',$d_id);
        }
		$result = json_decode(htmlspecialchars_decode(I('openid')),true);
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false) {
            $this->redirect("/home/publics/login");
        }
        $userinfo = json_decode(htmlspecialchars_decode(I('userinfo')),true);
		//die(var_dump($result));
        //判断是否第一次登陆
        $wx = M('wx');
        $user = M('user');
        $res = $wx->where("openid = '{$result['openid']}'")->find();
        if($res){
            //是否过期
//			if($res['expires_in']<time()){
//				$wx->where("openid = '{$result['openid']}'")->setField('access_token',$result['access_token']);
//			}
            //查找会员数据
            $info = $user->where("id = {$res['userid']}")->find();
            $user->where("id = {$res['id']}")->setField('last_time',time());
            M('user')->where("id = {$res['id']}")->setField('last_ip',get_client_ip());
            session('user',$info);
            //是否禁用
            if($info['status']==0){
                $this->redirect('error');
            }
            $this->redirect('Home/Select/index');
        }else{
            if(C_set('is_open_reg')==0){
                $this->redirect('error');
            }
            //是否推荐
            $t_id = session('tid');
            if($t_id){
                $data['t_id'] = $t_id;
                $tname=M('user')->where(array('id'=>$t_id))->find();
                $tnum=M('user')->where(array('tid'=>$t_id))->count();
                if (intVal($tnum)>=intVal(C('tuijian_zu'))){
                    M('user')->where(array('id'=>$t_id))->setField('tj_zu',1);
                }
                if (!empty($tname['d_id'])){
                    $data['d_id'] = $tname['d_id'];
                    $data['td_id'] = $tname['td_id'];
                }
            }
            if (session('d_id')){
                $shang=M('agent')->where(array('id'=>session('d_id')))->find();
                if ($shang){
                    $data['d_id'] = session('d_id');
                    $data['td_id'] = $shang['t_id'];
                    $data['is_ztui'] = 1;
                }
            }
            //自动注册
            $data['nickname'] = htmlspecialchars($userinfo['nickname']);
//			$headimgurl = $userinfo['headimgurl'];
            $data['headimgurl'] = $userinfo['headimgurl'];
            $data['country'] = $userinfo['country'];
            $data['province'] = $userinfo['province'];
            $data['sex'] = $userinfo['sex'];
            $data['user_agent'] = serialize(get__browser());
            $data['city'] = $userinfo['city'];
            $data['reg_ip'] = get_client_ip();
//			$data['points'] = 10;
            $data['last_ip'] = get_client_ip();
            $data['reg_time'] = time();
            $data['last_time'] = time();
            $data['logins'] = 1;
            $userid = $user->add($data);
			
            if($userid){
				
                $data1['userid'] = $userid;
                $data1['openid'] = $result['openid'];
                $data1['access_token'] = $result['access_token'];
                $data1['expires_in'] = time()+$result['expires_in'];
                $res2 = $wx->add($data1);
				//die(var_dump($data1));
                if($res2){
					
                    $data['id'] = $userid;
                    if (session('d_id')) {
                        M('agent')->where(array('id'=>session('d_id')))->setInc('tnum');
                        M('agent')->where(array('id'=>$data['td_id']))->setInc('tnum');
                    }
                    if (!empty($data['d_id'])){
                        M('agent')->where(array('id'=>$data['d_id']))->setInc('t_num');
                        M('agent')->where(array('id'=>$data['td_id']))->setInc('t_num');
                    }
                    session('user',$data);
                    $this->redirect('Home/Select/to');
                }
            }
        }
    }

}