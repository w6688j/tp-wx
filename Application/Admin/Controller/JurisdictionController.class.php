<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2017/6/15
 * Time: 10:15
 */

namespace Admin\Controller;

class JurisdictionController extends BaseController
{
    public function backups()
    {
        $count = M('auth_group')->count();
        $page = new \Think\Page($count,20);
        $show = $page->show();
        $list = M('auth_group')->limit($page->firstRow.','.$page->listRows)->select();
        $rule = M('auth_rule')->select();
        for ($i=0;$i<count($rule);$i++){
            $su[$rule[$i]['id']]=$rule[$i]['title'];
        }
        $this->assign('page', $show);
        $this->assign('list', $list);
        $this->assign('rule', $su);
        $this->display();
    }

    public function addgroup()
    {
        $this->display();
    }

    public function powers()
    {
        $count = M('admin as b')->join('think_auth_group as u on b.group_id=u.id')->count();
        $page = new \Think\Page($count, 20);
        $show = $page->show();
        $list =M('admin as b')->join('think_auth_group as u on b.group_id=u.id')->field("b.*,u.title")->order("b.id DESC")->select();
        $this->assign('show', $show);
        $this->assign('list', $list);
        $this->display();
    }

    public function addgroupdo()
    {
        $arr = array(
            'title' => I('username'),
            'status' => I('status')
        );
        if (M('auth_group')->add($arr)) {
            $this->success('成功');
        } else {
            $this->error('失败');
        }
    }

    public function node()
    {
        $count = M('auth_rule')->count();
        $page = new \Think\Page($count,20);
        $show = $page->show();
        $list = M('auth_rule')->limit($page->firstRow.','.$page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->display();
    }

    public function addnode()
    {
        $this->display();
    }

    public function addnodedo()
    {
        $arr = array(
            'title' => I('username'),
            'name' => I('content'),
            'type' => I('type'),
            'status' => I('status')
        );
        if (M('auth_rule')->add($arr)) {
            $this->success('成功');
        } else {
            $this->error('失败');
        }
    }

    public function permission_setting()
    {
        $quan = M('auth_rule')->select();
        $group = M('auth_group')->where(array('id'=>I('id')))->find();
        $grous=substr($group['rules'],0,strlen($group['rules'])-1);
        $grous=explode(',',$grous);
        $this->assign('list', $quan);
        $this->assign('rule',$grous );
        $this->assign('id', I('id'));
        $this->display();
    }

    public function permissiondo()
    {
        $quan = M('auth_rule')->where('status=1')->count();
        $a = '';
        for ($i = 1; $i <= $quan; $i++) {
            if (I('name' . $i)) {
                $a = $a . I('name' . $i) . ',';
            }
        }
        if ($a != null) {
            $date['rules'] = $a;
            if (!M('auth_group')->where(array('id' => I('id')))->save($date)) {
                $this->success("成功");
            } else {
                $this->error('失败');
            }
        } else {
            $this->error('失败1');
        }
    }

    public function deletepermission()
    {
        if (M('auth_group')->where(array('id' => I('id')))->delete() != false) {
            $this->success("成功");
        } else {
            $this->error('失败');
        }
    }

    public function deletenode()
    {
        if (M('auth_rule')->where(array('id' => I('id')))->delete() != false) {
            $this->success("成功");
        } else {
            $this->error('失败');
        }
    }

    public function editnode()
    {
        $sss = M('auth_rule')->where(array('id' => I('id')))->find();
        $this->assign('list', $sss);
        $this->display();
    }

    public function editnodedo()
    {
        $arr = array(
            'title' => I('username'),
            'name' => I('content'),
            'type' => I('type'),
            'status' => I('status')
        );
        if (M('auth_rule')->where(array('id' => I('id')))->save($arr)) {
            $this->success('成功');
        } else {
            $this->error('失败');
        }
    }

    public function addpowers()
    {
        $jues = M('auth_group')->select();
        $this->assign('jues', $jues);
        $this->display();
    }

    public function addpowersdo()
    {
        if (M('admin')->where(array('username'=>I('username')))->find()){
            $this->error("用户已存在");
        }
        $arr = array(
            'username' => I('username'),
            'password' => I('password', '', 'md5'),
            'group_id' => I('type')
        );
         M('admin')->add($arr);
        $ss=M('admin')->where(array('username'=>I('username')))->find();
        $arr2 = array(
            'uid' => $ss['id'],
            'group_id' => I('type')
        );
        if (M('auth_group_access')->add($arr2)) {
            $this->success("成功");
        } else {
            $this->error("失败");
        }
    }

    public function edituser()
    {
        $user = M('admin')->where(array('id' => I('id')))->find();
        $this->assign('list', $user);
        $jues = M('auth_group')->select();
        $this->assign('jues', $jues);
        $re = M('auth_group_access')->where(array('uid' => $user['id']))->find();
        $us = M('auth_group')->where(array('id' => $re['group_id']))->find();
        $this->assign('us', $us);
        $this->display();
    }

    public function edituserdo()
    {
        $pwd1 = I('password');
        if (!empty($pwd1)) {
            $date['password'] = I('password', '', 'md5');
        }
        $date['uname'] = I('username');
        $date['group_id'] = I('type');
        $data['group_id'] = I('type');
        M('admin')->where(array('id' => I('id')))->save($date);
        M('auth_group_access')->where(array('uid' => I('id')))->save($data);
        $this->success("成功");
    }

    public function deleteuser()
    {
        M('admin')->where(array('id' => I('id')))->delete();
        M('auth_group_access')->where(array('uid' => I('id')))->delete();
        $this->success('成功');
    }
    public function operation(){
        $count = M('admin_log')->count();
        $page = new \Think\Page($count,20);
        $show = $page->show();
        $list = M('admin_log')->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->display();
    }
    public function clear(){
        $time = I('time');
        if ($time){
            $time = strtotime($time . '00:00:00');
            if ($time>strtotime("-0 year -1 month -0 day")){
                $time=strtotime("-0 year -1 month -0 day");
            }
        }else{
            $time=strtotime("-0 year -1 month -0 day");
        }
        M('dannumber')->where('time<'.$time)->delete();
        M('kuainumber')->where('time<'.$time)->delete();
        M('lhcnumber')->where('time<'.$time)->delete();
        M('number')->where('time<'.$time)->delete();
        M('sscnumber')->where('time<'.$time)->delete();
        M('order')->where('time<'.$time)->delete();
        $this->success("成功");
    }
}