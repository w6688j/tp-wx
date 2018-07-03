<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/10
 * Time: 13:42
 */

namespace Home\Controller;
use Think\Controller;

class ChatController extends BaseController
{
    public function index(){
        $list=M('chatroom')->order('id desc')->limit(40)->select();
        $this->assign('list',$list);
        $this->display();
    }
}