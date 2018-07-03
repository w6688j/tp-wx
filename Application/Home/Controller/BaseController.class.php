<?php

namespace Home\Controller;

use Think\Controller;

header('content-type:text/html;charset=utf-8');

class BaseController extends Controller
{
    public function __construct()
    {
        /* 读取站点配置 */
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $config =  M('config')->find();
            S('DB_CONFIG_DATA',$config);
        }
       C($config); //添加配置
       $g_config= S('DB_CONFIG_G_DATA');
        if(!$g_config){
            $g_config =  M('game_config')->where(array("id"=>2))->find();
            S('DB_CONFIG_G_DATA',$g_config);
        }
        foreach ($g_config as $key=>$value){
            if($key!="id"){
                C($key.'_on_off',json_decode($value,true)['on_off']);
            }
        }
        parent::__construct();
    }

    public function _initialize()
    {
        //检测登录状态
        $userid = session('user');
//        if (CONTROLLER_NAME !== 'Index') {
        if (empty($userid['id'])) {
                if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
                    $this->redirect('Index/index');
                } else {
                    $this->redirect('publics/login');
                }
            }
//        }
        $userinfo = M('user')->where("id = {$userid['id']}")->find();
        if (!$userinfo) {
            session('user', null);
            $this->redirect('publics/login');
            return;
        }
        $datasssssssss = $_SERVER["SERVER_NAME"];
        $this->assign('severname', $datasssssssss);
        $this->assign('userinfo', $userinfo);
        //获取数据库配置文件

    }

}


?>