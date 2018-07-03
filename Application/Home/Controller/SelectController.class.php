<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/10
 * Time: 11:50
 */

namespace Home\Controller;

use Think\Controller;
use Qiniu\Auth;
// 引入上传类
use Qiniu\Storage\UploadManager;

class SelectController extends BaseController
{

    public function to()
    {
        $this->display();
    }
    public function index()
    {
        $list = M('carousel')->select();
        $this->assign('list', $list);
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            //为微信登录
            $status = 1;
        } else {
            //手机登录
            $status = 0;
        }
        $this->assign('status', $status);
        $msg = M('msgs')->where(array('userid' => session('user')['id']))->order('id desc')->find();
        $this->assign('msgs', $msg);
        $this->display();
    }

    public function person()
    {
        $data = session('user');
        $today = strtotime(date('Y-m-d', time()));
        $map['time'] = array(array('lt', $today), array('gt', $today - 86400), 'and');
        $info['jinmoney'] = M('order')->where(array('is_add' => 1, 'state' => 1, 'userid' => $data['id']))->where('time>' . $today)->sum('add_points');
        $info['zoumoney'] = M('order')->where(array('is_add' => 1, 'state' => 1, 'userid' => $data['id']))->where($map)->sum('add_points');
        if (!$info['zoumoney']) {
            $info['zoumoney'] = 0;
        }
        if (!$info['jinmoney']) {
            $info['jinmoney'] = 0;
        }
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            //为微信登录
            $status = 1;
        } else {
            //手机登录
            $status = 0;
        }
        $user=M('user')->where(array('id'=>$data['id']))->field('sign_time')->find();
        if ($user['sign_time']>strtotime(date('Y-m-d'))){
            $datt['qiandao']=1;
        }else{
            $datt['qiandao']=2;
        }
        if ($user['sign_time']<strtotime(date('Y-m-d',strtotime('-1 day')))){
            $datt['qiandaonum']=1;
        }else{
            $datt['qiandaonum']=2;
        }
        $tname = M('user')->where(array('id' => $data['t_id']))->find();
//       die(var_dump($tname));
        $this->assign('datt', $datt);
        $this->assign('info', $info);
        $this->assign('tname', $tname);
        $this->assign('status', $status);
        $this->display();
    }

    //银行卡汇款账号
    public function yinghangka()
    {
        $data = M('user')->where(array('id' => session('user')['id']))->find();
        $this->assign('datas', $data);
        $data = M('config')->where('id =1')->find();
        $dataarr = explode(',', $data['bank_data']);
        $this->assign('dataarr', $dataarr);
        $this->display();
    }

    //选择上下界面
    public function sxselect()
    {
        $data = M('user')->where(array('id' => session('user')['id']))->find();
        $this->assign('datas', $data);
        $this->display();
    }

    public function persons()
    {
        $this->display();
    }

    public function toprecord()
    {
        $data = session('user');
        if ($_GET['type'] == 0 || !$_GET['type']) {
            $count = M('money')->where(array("userid" => $data['id'], 'status' => 0, 'type2' => 1))->count();
            $page = new \Think\Page($count, 10);
            $show = $page->show();
            $money = M('money')->where(array("userid" => $data['id'], 'status' => 0, 'type2' => 1))->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
            $type = 1;
        } elseif ($_GET['type'] == 1) {
            $map['userid'] = $data['id'];
            $map['_string'] = 'status=1 OR status=6';
            $map['type2'] = 1;
            $count = M('money')->where($map)->count();
            $page = new \Think\Page($count, 10);
            $show = $page->show();
            $money = M('money')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
            $type = 2;
        } elseif ($_GET['type'] == 2) {
            $count = M('money')->where(array("userid" => $data['id'], 'status' => 2, 'type2' => 1))->count();
            $page = new \Think\Page($count, 10);
            $show = $page->show();
            $money = M('money')->where(array("userid" => $data['id'], 'status' => 2, 'type2' => 1))->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
            $type = 3;
        }
        $this->assign('money', $money);
        $this->assign('type', $type);
        $this->assign('show', $show);
        $this->display();
    }

    public function downrecord()
    {
        $data = session('user');
        if ($_GET['type'] == 0 || !$_GET['type']) {
            $count = M('money')->where(array("userid" => $data['id'], 'status' => 0, 'type2' => 0))->count();
            $page = new \Think\Page($count, 10);
            $show = $page->show();
            $money = M('money')->where(array("userid" => $data['id'], 'status' => 0, 'type2' => 0))->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
            $type = 1;
        } elseif ($_GET['type'] == 1) {
            $map['userid'] = $data['id'];
            $map['_string'] = 'status=1 OR status=5';
            $map['type2'] = 0;
            $count = M('money')->where($map)->count();
            $page = new \Think\Page($count, 10);
            $show = $page->show();
            $money = M('money')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
            $type = 2;
        } elseif ($_GET['type'] == 2) {
            $count = M('money')->where(array("userid" => $data['id'], 'status' => 2, 'type2' => 0))->count();
            $page = new \Think\Page($count, 10);
            $show = $page->show();
            $money = M('money')->where(array("userid" => $data['id'], 'status' => 2, 'type2' => 0))->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
            $type = 3;
        }
        $this->assign('money', $money);
        $this->assign('type', $type);
        $this->assign('show', $show);
        $this->display();
    }

    public function quizrecord()
    {
        $data = session('user');
        if ($_GET['time']) {
            $time = $_GET['time'];
        } else {
            $time = strtotime(date('Y-m-d', time()));
        }
        //日
        if (!$_GET['type'] || $_GET['type'] == 3) {
            $map['time'] = array(array('gt', $time), array('lt', $time + 86400), 'and');
            $map['userid'] = $data['id'];
            $map['state'] = 1;
            $map['userid'] = $data['id'];
            $count = M('order')->where($map)->count();
            $page = new \Think\Page($count, 10);
            $show = $page->show();
            $list = M('order')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
//            $list=M('order')->where($map)->select();
            $type = 3;
            if ($time + 86400 > time()) {
                $state = 1;
            } else {
                $state = 2;
            }
            $date['xiazhu'] = M('order')->where($map)->sum('del_points');
            $date['pai'] = M('order')->where($map)->sum('add_points');
            $date['yk'] = $date['pai'] - $date['xiazhu'];
            $date['num'] = $count;
        } elseif ($_GET['type'] == 2) {
            //周
            $first = 1;
            $sdefaultDate = date('Y-m-d', $time);
            $w = date('w', strtotime($sdefaultDate));
            $aLastweek = strtotime("$sdefaultDate -" . ($w ? $w - $first : 6) . ' days');
            $bLastweek = $aLastweek + 7 * 86400 - 1;
            $map['time'] = array(array('gt', $aLastweek), array('lt', $bLastweek), 'and');
            $map['userid'] = $data['id'];
            $map['state'] = 1;
            $map['userid'] = $data['id'];
            $count = M('order')->where($map)->count();
            $page = new \Think\Page($count, 10);
            $show = $page->show();
            $list = M('order')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
//            $list=M('order')->where($map)->select();
            $time = $aLastweek;
            $type = 2;
            if ($bLastweek > time()) {
                $state = 1;
            } else {
                $state = 2;
            }
            $date['xiazhu'] = M('order')->where($map)->sum('del_points');
            $date['pai'] = M('order')->where($map)->sum('add_points');
            $date['num'] = $count;
            $date['yk'] = $date['pai'] - $date['xiazhu'];
            $this->assign('time2', $bLastweek);
        } else {
            //月
            $yuechu = strtotime(date('Y-m', $time));
            $yuemo = $endThismonth = mktime(23, 59, 59, date('m', $time), date('t', $time), date('Y', $time));
            $map['time'] = array(array('gt', $yuechu), array('lt', $yuemo), 'and');
            $map['userid'] = $data['id'];
            $map['state'] = 1;
            $map['userid'] = $data['id'];
            $count = M('order')->where($map)->count();
            $page = new \Think\Page($count, 10);
            $show = $page->show();
            $list = M('order')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
//            $list=M('order')->where($map)->select();
            $date['xiazhu'] = M('order')->where($map)->sum('del_points');
            $date['pai'] = M('order')->where($map)->sum('add_points');
            $date['num'] = $count;
            $date['yk'] = $date['pai'] - $date['xiazhu'];
            $time = $yuechu;
            $type = 1;
            if ($yuemo > time()) {
                $state = 1;
            } else {
                $state = 2;
            }
            $this->assign('time2', $yuemo);
        }
        $game = array(
            'bj28' => "北京28",
            'jnd28' => "加拿大28",
            'pk10' => "北京赛车",
            'ssc' => "时时彩",
            'fei' => "幸运飞艇",
            'kuai3' => "快3",
            'lhc' => "六合彩",
            'jsssc' => "极速时时彩",
            'jscar' => "极速赛车",
        );
        $this->assign('game', $game);
        $this->assign('list', $list);
        $this->assign('time', $time);
        $this->assign('type', $type);
        $this->assign('state', $state);
        $this->assign('show', $show);
        $this->assign('date', $date);
        $this->display();
    }

    public function wallet()
    {
        $data = session('user');
        $type = $_GET['type'];
        if ($_GET['type'] == 1 || !$_GET['type']) {
            //今日
            $type = 1;
            $time = strtotime(date('Y-m-d', time()));
            $msg['time'] = array(array('gt', $time), array('lt', $time + 86400), 'and');
            $msg['userid'] = $data['id'];
            $msg['state'] = 1;
            $msgs['time'] = array(array('gt', $time), array('lt', $time + 86400), 'and');
            $msgs['userid'] = $data['id'];
            $list1 = M('order')->where($msg)->select();
            $list2 = M('integral')->where($msgs)->select();
            $list = array_merge($list1, $list2);
            $sort = array(
                'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
                'field' => 'time',       //排序字段
            );
            $arrSort = array();
            foreach ($list AS $uniqid => $row) {
                foreach ($row AS $key => $value) {
                    $arrSort[$key][$uniqid] = $value;
                }
            }
            if ($sort['direction']) {
                array_multisort($arrSort[$sort['field']], constant($sort['direction']), $list);
            }

        } elseif ($_GET['type'] == 2) {
            $time = strtotime(date('Y-m-d', time()));
            $msg['time'] = array(array('gt', $time - 86400), array('lt', $time), 'and');
            $msg['userid'] = $data['id'];
            $msg['state'] = 1;
            $msgs['time'] = array(array('gt', $time - 86400), array('lt', $time), 'and');
            $msgs['userid'] = $data['id'];
            $list1 = M('order')->where($msg)->select();
            $list2 = M('integral')->where($msgs)->select();
            $list = array_merge($list1, $list2);
            $sort = array(
                'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
                'field' => 'time',       //排序字段
            );
            $arrSort = array();
            foreach ($list AS $uniqid => $row) {
                foreach ($row AS $key => $value) {
                    $arrSort[$key][$uniqid] = $value;
                }
            }
            if ($sort['direction']) {
                array_multisort($arrSort[$sort['field']], constant($sort['direction']), $list);
            }
        } elseif ($_GET['type'] == 3) {
            $time = strtotime(date('Y-m-d', time()));
            $first = 1;
            $sdefaultDate = date('Y-m-d', $time);
            $w = date('w', strtotime($sdefaultDate));
            $aLastweek = strtotime("$sdefaultDate -" . ($w ? $w - $first : 6) . ' days');
            $bLastweek = $aLastweek + 7 * 86400;
            $msg['time'] = array(array('gt', $aLastweek), array('lt', $bLastweek), 'and');
            $msg['userid'] = $data['id'];
            $msg['state'] = 1;
            $msgs['time'] = array(array('gt', $aLastweek), array('lt', $bLastweek), 'and');
            $msgs['userid'] = $data['id'];
            $list1 = M('order')->where($msg)->select();
            $list2 = M('integral')->where($msgs)->select();
            $list = array_merge($list1, $list2);
            $sort = array(
                'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
                'field' => 'time',       //排序字段
            );
            $arrSort = array();
            foreach ($list AS $uniqid => $row) {
                foreach ($row AS $key => $value) {
                    $arrSort[$key][$uniqid] = $value;
                }
            }
            if ($sort['direction']) {
                array_multisort($arrSort[$sort['field']], constant($sort['direction']), $list);
            }
        } elseif ($_GET['type'] == 4) {
            $time = strtotime(date('Y-m-d', time()));
            $msg['time'] = array(array('gt', $time - 86400 * 30), array('lt', $time + 86400), 'and');
            $msg['userid'] = $data['id'];
            $msg['state'] = 1;
            $msgs['time'] = array(array('gt', $time - 86400 * 30), array('lt', $time + 86400), 'and');
            $msgs['userid'] = $data['id'];
            $list1 = M('order')->where($msg)->select();
            $list2 = M('integral')->where($msgs)->select();
            $list = array_merge($list1, $list2);
            $sort = array(
                'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
                'field' => 'time',       //排序字段
            );
            $arrSort = array();
            foreach ($list AS $uniqid => $row) {
                foreach ($row AS $key => $value) {
                    $arrSort[$key][$uniqid] = $value;
                }
            }
            if ($sort['direction']) {
                array_multisort($arrSort[$sort['field']], constant($sort['direction']), $list);
            }
        } else {
            $type = 5;
            $time = strtotime(date('Y-m-d', time()));
            $msgs['time'] = array(array('gt', $time - 86400 * 7), array('lt', $time + 86400), 'and');
            $msgs['userid'] = $data['id'];
            $list = M('integral')->where($msgs)->order('id desc')->select();
        }
        $this->assign('list', $list);
        $this->assign('type', $type);
        $this->display();
    }

    public function account()
    {
        $this->display();
    }

    public function accountdo()
    {
        $data = session('user');
        if (!$data = M('user')->where(array('id' => $data['id']))->find()) {
            $date['info'] = "账户错误";
            $date['status'] = 0;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        }
        $oldpwd = $_POST['oldpwd'];
        $newpwd = $_POST['passwd'];
        if ($data['password'] != md5($oldpwd)) {
            $date['info'] = "密码错误";
            $date['status'] = 0;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        }
        if (empty($newpwd)) {
            $date['info'] = "密码不能为空";
            $date['status'] = 0;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        }
        $dats['password'] = md5($newpwd);
        $ys = M('user')->where(array('id' => $data['id']))->setField($dats);
        if ($ys) {
            $date['info'] = "修改成功";
            $date['status'] = 1;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        } else {
            $date['info'] = "系统错误，请联系管理员";
            $date['status'] = 0;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        }
    }

    public function caiwukefu()
    {
        $kefu = M('config')->where(array('id' => 1))->find();
        $this->assign('kefu', $kefu);
        $this->display();
    }

    public function kefu()
    {
        $kefu = M('config')->where(array('id' => 1))->find();
        $this->assign('kefu', $kefu);
        $this->display();
    }

    public function ajax_charge()
    {
        $data = session('user');
        if ($_POST['money'] <= 0 && $_POST['money'] > 200000) {
            $date['info'] = "金额错误";
            $date['status'] = 1;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        }
        $shengxias = M('user')->where(array('id' => session('user')['id']))->find();
        if ($_POST['type'] == 'charge') {
            if (is_numeric($_POST['money'])) {
                $datass['headimgurl'] = session('user')['headimgurl'];
                $datass['nickname'] = session('user')['nickname'];
                $datass['yue'] = $shengxias['points'];
                $datass['time'] = time();
                $datass['userid'] = session('user')['id'];
                if ($_POST['zhifufanshi'] == "zhifubao") {
                    $datass['typepay'] = 'alipay';
                } else {
                    $datass['typepay'] = 'weixin';
                }
                $datass['status'] = 0;
                $datass['type2'] = 1;
                $datass['points'] = $_POST['money'];
                $datass['msg'] = '待审核';
                if (M('money')->add($datass) !== false) {
                    $date['info'] = "成功";
                    $date['status'] = 0;
                    $date['url'] = "";
                    echo json_encode($date);
                    exit();
                } else {
                    $date['info'] = "失败";
                    $date['status'] = 1;
                    $date['url'] = "";
                    echo json_encode($date);
                    exit();
                }
            } else {
                $date['info'] = "金额错误";
                $date['status'] = 1;
                $date['url'] = "";
                echo json_encode($date);
                exit();
            }
        } else {
            if ($data['iszhifu'] == 1) {
                if (empty($data['zhifubao'])) {
                    $date['info'] = "支付未设置";
                    $date['status'] = 1;
                    $date['url'] = "/Home/Select/bank";
                    echo json_encode($date);
                    exit();
                }
            } else {
                if (empty($data['weixin'])) {
                    $date['info'] = "支付未设置";
                    $date['status'] = 1;
                    $date['url'] = "/Home/Select/bank";
                    echo json_encode($date);
                    exit();
                }
            }
            if ($_POST['money'] > $shengxias['points']) {
                $date['info'] = "金额错误";
                $date['status'] = 1;
                $date['url'] = "";
                echo json_encode($date);
                exit();
            } else {
                $datass['headimgurl'] = session('user')['headimgurl'];
                $datass['nickname'] = session('user')['nickname'];
                $datass['yue'] = $shengxias['points'] - $_POST['money'];
                $datass['time'] = time();
                $datass['userid'] = session('user')['id'];
                $datass['typepay'] = '';
                $datass['status'] = 0;
                $datass['type2'] = 0;
                $datass['points'] = $_POST['money'];
                $datass['msg'] = '待审核';
                $res = M('money')->add($datass);
                $res2 = M('user')->where(array('id' => session('user')['id']))->setDec('points', $_POST['money']);
                if ($res && $res2) {
                    $date['info'] = "成功";
                    $date['status'] = 0;
                    $date['url'] = "";
                    echo json_encode($date);
                    exit();
                } else {
                    $date['info'] = "金额错误";
                    $date['status'] = 1;
                    $date['url'] = "";
                    echo json_encode($date);
                    exit();
                }
            }
        }
    }

    public function binding()
    {
        $this->display();
    }

    public function bindingdo()
    {
        $data = session('user');
        $verlist = I('verlist');
        $data = M('user')->where(array('id' => $data['id']))->find();
        if (!$data) {
            $date['info'] = "账户错误";
            $date['status'] = 0;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        }
        /*$x = M('verifys')->where(array('uname' => $data['nickname']))->order('id desc')->find();
        if ($verlist != $x['verify'] || time() - $x['vtime'] > 15 * 60) {
            $date['info'] = "验证码错误";
            $date['status'] = 0;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        }*/
        $username = $_POST['username'];
        $newpwd = $_POST['passwd'];
        $user = M('user')->where(array('username' => $username))->find();
        if ($user) {
            $date['info'] = "账号已存在";
            $date['status'] = 0;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        }
        if (empty($newpwd)) {
            $date['info'] = "密码不能为空";
            $date['status'] = 0;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        }
        if (empty($username)) {
            $date['info'] = "电话号码不能为空";
            $date['status'] = 0;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        }
        $dats['username'] = $username;
        $dats['password'] = md5($newpwd);
        $ys = M('user')->where(array('id' => $data['id']))->save($dats);
        if ($ys != false) {
            $date['info'] = "保存成功";
            $date['status'] = 1;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        } else {
            $date['info'] = "系统错误，请联系管理员";
            $date['status'] = 0;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        }
    }

    public function verify()
    {
        $Verify = new \Think\Verify();
        $Verify->fontSize = 30;
        $Verify->length = 4;
        $Verify->codeSet = '0123456789';
        $Verify->useNoise = false;
        $Verify->entry();
    }

    public function login()
    {
        $this->display();
    }

    public function logindo()
    {
        $password = $_POST['pwd'];
        $code = $_POST['code'];
        $name = $_POST['name'];
        if (empty($password) || empty($code) || empty($name)) {
            $data['status'] = 0;
            $data['info'] = "信息不能为空";
            $data['url'] = "";
            echo json_encode($data);
            exit();
        }
        if (!$this->check_verify($code)) {
            $data['status'] = 0;
            $data['info'] = "验证码错误";
            $data['url'] = "";
            echo json_encode($data);
            exit();
        }
        $user = M('user')->where(array('username' => $name, 'status' => 1))->find();
        if (!$user) {
            $data['status'] = 0;
            $data['info'] = "账号错误";
            $data['url'] = "";
            echo json_encode($data);
            exit();
        }
        if ($user['password'] != md5($password)) {
            $data['status'] = 0;
            $data['info'] = "密码错误";
            $data['url'] = "";
            echo json_encode($data);
            exit();
        }
        $data['status'] = 1;
        $data['info'] = "登录成功";
        $data['url'] = "/home/select/index";
        echo json_encode($data);
        exit();
    }

    function check_verify($code, $id = '')
    {
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }

    public function bank()
    {
        $userid = session('user');
        $data = M('user')->where("id = {$userid['id']}")->find();
        $this->assign('data', $data);
        $this->display();
    }

    public function bankdo()
    {
        $date = session('user');
        if ($_POST) {
            $dats['zhifubao'] = $_POST['zhifubao'];
            $dats['weixin'] = $_POST['weixin'];
            $dats['khh'] = $_POST['khh'];
            $dats['name'] = $_POST['name'];
            $dats['banksou'] = $_POST['banksou'];
//            dump($dats);
//            if ($dats['iszhifu']==1){
//                if (empty($_POST['zhifubao'])){
//                    $data['status']=0;
//                    $data['info']="默认支付输入为空";
//                    $data['url']="";
//                    echo json_encode($data);
//                    exit();
//                }
//            }else{
//                if (empty($_POST['weixin'])){
//                    $data['status']=0;
//                    $data['info']="默认支付输入为空";
//                    $data['url']="";
//                    echo json_encode($data);
//                    exit();
//                }
//            }
            if (M('user')->where(array('id' => $date['id']))->save($dats)) {
                $data['status'] = 1;
                $data['info'] = "设置成功";
                $data['url'] = "";
                echo json_encode($data);
                exit();
            } else {
                $data['status'] = 0;
                $data['info'] = "设置失败";
                $data['url'] = "";
                echo json_encode($data);
                exit();
            }
        } else {
            $data['status'] = 0;
            $data['info'] = "系统错误，请联系管理员";
            $data['url'] = "";
            echo json_encode($data);
            exit();
        }
    }

    public function logout()
    {
        session(null);
        $data['url'] = "/home/publics/login";
        echo json_encode($data);
        exit();
    }

    public function team()
    {
        $date = session('user');
        $count = M('user')->where(array('t_id' => $date['id']))->count();
        $page = new \Think\Page($count, 10);
        $show = $page->show();
        $user = M('user')->where(array('t_id' => $date['id']))->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
        $myt = M('user')->where(array('id' => $date['id']))->find();
        $mytg = $myt['t_add'];
        $this->assign('mytg', $mytg);
        $this->assign('show', $show);
        $this->assign('list', $user);
        $this->display();
    }

    public function spread()
    {
        $user = session('user');
        $url =  'http://'.$_SERVER['HTTP_HOST']."/home/index/index?t=" . $user['id'];
        //$url = C('dh_url') .urlencode('http://'.$_SERVER['HTTP_HOST']."/home/index/redirect2_url?t=" . $user['id']);
//        $url= file_get_contents('http://suo.im/api.php?url='.$url);
        //$url = C('dh_url') . "?d_id=" . $user['id'];
        $this->assign('url', $url);
        $this->display();
    }

    public function getmoney()
    {
        $date = session('user');
        $user = M('user')->where(array('id' => $date['id']))->find();
        die(json_encode($user['points']));
    }

    public function verlist()
    {
        $phone = I('username');
        if (empty($phone)) {
            $data['status'] = 202;
            $data['msg'] = "手机号码不能为空";
            die(json_encode($data));
        }
        $date = session('user');
        $verify = rand(1000, 9999);
        $x = M('verifys')->where(array('uname' => $date['nickname']))->order('id desc')->find();
        if (time() - $x['vtime'] < 60) {
            $data['status'] = 202;
            $data['msg'] = "一分钟后再获取";
            die(json_encode($data));
        }
        $date = array(
            'uname' => $date['nickname'],
            'nums' => $x['nums'] + 1,
            'vtime' => time(),
            'verify' => $verify,
        );
        $t = M('verifys')->add($date);
        if (!$t) {
            $data['status'] = 202;
            $data['msg'] = "获取验证码失败";
            die(json_encode($data));
        }
        $xmss = retrieval($phone, $verify);
        if ($xmss > 0) {
            $data['status'] = 200;
            $data['msg'] = "成功获取验证码";
            die(json_encode($data));
        } else {
            $data['status'] = 202;
            $data['msg'] = "获取验证码失败";
            die(json_encode($data));
        }
    }

    public function commission()
    {
        $data = session('user');
        if ($_GET['type'] == 1 || !$_GET['type']) {
            //今日
            $time = strtotime(date('Y-m-d', time()));
            $msg['time'] = array(array('gt', $time), array('lt', $time + 86400), 'and');
            $count = M('commisssion')->where(array("uid" => $data['id'], 'status' => 1))->where($msg)->count();
            $page = new \Think\Page($count, 10);
            $show = $page->show();
            $list = M('commisssion')->where(array("uid" => $data['id'], 'status' => 1))->where($msg)->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
            $type = 1;
        } elseif ($_GET['type'] == 2) {
            $time = strtotime(date('Y-m-d', time()));
            $msg['time'] = array(array('gt', $time - 86400), array('lt', $time), 'and');
            $count = M('commisssion')->where(array("uid" => $data['id'], 'status' => 1))->where($msg)->count();
            $page = new \Think\Page($count, 10);
            $show = $page->show();
            $list = M('commisssion')->where(array("uid" => $data['id'], 'status' => 1))->where($msg)->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
            $type = 2;
        } elseif ($_GET['type'] == 3) {
            $time = strtotime(date('Y-m-d', time()));
            $first = 1;
            $sdefaultDate = date('Y-m-d', $time);
            $w = date('w', strtotime($sdefaultDate));
            $aLastweek = strtotime("$sdefaultDate -" . ($w ? $w - $first : 6) . ' days');
            $bLastweek = $aLastweek + 7 * 86400;
            $msg['time'] = array(array('gt', $aLastweek), array('lt', $bLastweek), 'and');
            $count = M('commisssion')->where(array("uid" => $data['id'], 'status' => 1))->where($msg)->count();
            $page = new \Think\Page($count, 10);
            $show = $page->show();
            $list = M('commisssion')->where(array("uid" => $data['id'], 'status' => 1))->where($msg)->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
            $type = 3;
        } else {
            $time = strtotime(date('Y-m-d', time()));
            $msg['time'] = array(array('gt', $time - 86400 * 30), array('lt', $time + 86400), 'and');
            $count = M('commisssion')->where(array("uid" => $data['id'], 'status' => 1))->where($msg)->count();
            $page = new \Think\Page($count, 10);
            $show = $page->show();
            $list = M('commisssion')->where(array("uid" => $data['id'], 'status' => 1))->where($msg)->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
            $type = 4;
        }
        $this->assign('list', $list);
        $this->assign('type', $type);
        $this->assign('show', $show);
        $this->display();
    }

    public function defection()
    {
        $data = session('user');
        if ($_GET['type'] == 1 || !$_GET['type']) {
            //今日
            $time = strtotime(date('Y-m-d', time()));
            $msg['order_time'] = array(array('egt', $time), array('lt', $time + 86400), 'and');
            $type = 1;
        } elseif ($_GET['type'] == 2) {
            $time = strtotime(date('Y-m-d', time()));
            $msg['order_time'] = array(array('egt', $time - 86400), array('lt', $time), 'and');
            $type = 2;
        } elseif ($_GET['type'] == 3) {
            $time = strtotime(date('Y-m-d', time()));
            $first = 1;
            $sdefaultDate = date('Y-m-d', $time);
            $w = date('w', strtotime($sdefaultDate));
            $aLastweek = strtotime("$sdefaultDate -" . ($w ? $w - $first : 6) . ' days');
            $bLastweek = $aLastweek + 7 * 86400;
            $msg['order_time'] = array(array('egt', $aLastweek), array('lt', $bLastweek), 'and');
            $type = 3;
        } else {
            $time = strtotime(date('Y-m-d', time()));
            $msg['order_time'] = array(array('egt', $time - 86400 * 30), array('lt', $time + 86400), 'and');
            $type = 4;
        }
        $count = M('order_day')->where(array("userid" => $data['id']))->where($msg)->count();
        $page = new \Think\Page($count, 10);
        $show = $page->show();
        $list = M('order_day')->where(array("userid" => $data['id']))->where($msg)->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
        $this->assign('list', $list);
        $this->assign('type', $type);
        $this->assign('show', $show);
        $this->display();
    }

    public function bangding()
    {
        $this->display();
    }

    public function bangdingdo()
    {
        if (S('bd_' . session('user')['id'])) {
            die(json_encode(array('info' => "不要重复绑定", 'status' => 0, 'url' => "")));
        }
        S('bd_' . session('user')['id'], 1, 60);
        $data_dangqian = M('user')->where(array('id' => session('user')['id'], 'status' => 1))->find();//当前账号
        if (!$data_dangqian && empty($data_dangqian['username'])) {
            $return = array('info' => "账户错误", 'status' => 0, 'url' => "");
            S('bd_' . session('user')['id'], 0);
            die(json_encode($return));
        }

        $username = $_POST['username'];
        $newpwd = $_POST['passwd'];
        if (empty($newpwd) || empty($username)) {
            $return = array('info' => "密码或者电话号码不能为空", 'status' => 0, 'url' => "");
            S('bd_' . session('user')['id'], 0);
            die(json_encode($return));
        }
        $user_bbd = M('user')->where(array('username' => $username))->find();
        if (!$user_bbd||$user_bbd['id']==$data_dangqian['id']) {
            $return = array('info' => "账号不存在或者账号跟绑定账号一样", 'status' => 0, 'url' => "");
            S('bd_' . session('user')['id'], 0);
            die(json_encode($return));
        }
        if ($user_bbd['password'] != md5($newpwd)) {
            $return = array('info' => "密码错误", 'status' => 0, 'url' => "");
            S('bd_' . session('user')['id'], 0);
            die(json_encode($return));
        }

        if ($data_dangqian['id'] > $user_bbd['id']) {
            $data_t = $data_dangqian;
            $data_l = $user_bbd;
            //更新wx表的openid;user_bbd可能没有wx号
            M('wx')->where(array("userid" => $data_t['id']))->setField("userid", $data_l['id']);
            $data_xin['nickname'] = $data_t['nickname'];
            $data_xin['headimgurl'] = $data_t['headimgurl'];
        } else {
            $data_t = $user_bbd;
            $data_l = $data_dangqian;
            //更新用户名和密码,直接更新
            $data_xin['username'] = $data_t['username'];
            $data_xin['password'] = $data_t['password'];
        }
        M()->startTrans();
        $data_xin['points'] = $data_t['points'] + $data_l['points'];
        $data_xin['commission'] = $data_t['commission'] + $data_l['commission'];
        $data_xin['msg'] = $data_l['msg'] . ",合并" . $data_t['id'] . "分:" . $data_t['points'] . "佣:" . $data_t['commission'] . "(" . $data_t['msg'] . ")";
        if (M('user')->where(array('id' => $data_l['id']))->save($data_xin) == false) {
            $return = array('info' => "绑定失败1", 'status' => 0, 'url' => "");
            S('bd_' . session('user')['id'], 0);
            M()->rollback();
            die(json_encode($return));
        }
//        //以前号推荐人要移动过来。
        if (M('user')->where(array('t_id' => $data_t['id']))->count()> 0) {
            if (M('user')->where(array('t_id' => $data_t['id']))->setField('t_id', $data_l['id']) == false) {
                $return = array('info' => "绑定失败2", 'status' => 0, 'url' => "");
                M()->rollback();
                S('bd_' . session('user')['id'], 0);
                die(json_encode($return));
            }
        }

        //代理统计重新计算。todo::
        if (!empty($data_t['d_id'])) {
            if (M('agent')->where(array('id' => $data_t['d_id']))->setDec('tnum') == false || M('agent')->where(array('id' => $data_t['td_id']))->setDec("tnum") == false) {
                M()->rollback();
                $return = array('info' => "绑定失败4", 'status' => 0, 'url' => "");
                S('bd_' . session('user')['id'], 0);
                die(json_encode($return));
            }
            if (!empty($data_t['is_ztui'])) {
                if (M('agent')->where(array('id' => $data_t['d_id']))->setDec('t_num') == false || M('agent')->where(array('id' => $data_t['td_id']))->setDec('t_num') == false) {
                    M()->rollback();
                    $return = array('info' => "绑定失败5", 'status' => 0, 'url' => "");
                    S('bd_' . session('user')['id'], 0);
                    die(json_encode($return));
                }
            }
        }
        if (M('user')->where(array('id' => $data_t['id']))->delete() == false) {
            M()->rollback();
            $return = array('info' => "绑定失败3", 'status' => 0, 'url' => "");
            S('bd_' . session('user')['id'], 0);
            die(json_encode($return));
        }
        M()->commit();
        $return = array('info' => "绑定成功", 'status' => 1, 'url' => "");
        session('user', null);
        S('bd_' . session('user')['id'], 0);
        die(json_encode($return));
    }

    public function yonjindo()
    {
        if (IS_POST) {
            //验证
            $datas = $_POST;
            if (!$datas['jine']) {
                show('金额不能为空，只能为数字', 0);
            }
            if (!$datas['accountnumber']) {
                show('账号不能为空', 0);
            }
            if ($datas['type'] == 'bankcard') {
                if (!$datas['khh']) {
                    show('开户行不能为空', 0);
                }
                if (!$datas['skr']) {
                    show('收款人不能为空', 0);
                }
            }
            if (S('xiafen' . session('user')['id'])) {
                show('请勿频繁操作，稍后再试...', 0);
            }
            S('xiafen' . session('user')['id'], 1, 5);
            //数据库中的金额有没有这么多
            $shengxias = M('user')->where(array('id' => session('user')['id']))->find();

            if (!$shengxias['username']) {
                show('为保障你的资金安全，请绑定手机号', 3);
            }

            if ($datas['jine'] > $shengxias['commission']) {
                show('请输入正确的额度,提取失败', 0);
            }
            if ($datas['type'] == 'bankcard') {
                //如果是银行卡的
                $datass['headimgurl'] = session('user')['headimgurl'];
                $datass['nickname'] = session('user')['nickname'];
                $datass['yue'] = $shengxias['commission'] - $datas['jine'];
                $datass['time'] = time();
                $datass['userid'] = session('user')['id'];
                $datass['typepay'] = 'yignhangka';
                $datass['status'] = 0;
                $datass['type2'] = 4;
                $datass['points'] = $datas['jine'];
                $datass['msg'] = '待审核';
                $datass['accountnumber'] = $datas['accountnumber'];
                $datass['khh'] = $datas['khh'];
                $datass['skr'] = $datas['skr'];
                $datass['bfb'] = $datas['bfb'];
                //判断是否为客服
                $data = M('user')->where(array('id' => $datass['userid']))->find();
                if ($data['iskefu'] == 1) {
                    $datass['is_kefu'] = 1;
                }
                $res = M('money')->add($datass);
                $res2 = M('user')->where(array('id' => session('user')['id']))->setDec('commission', $datas['jine']);
                if ($res && $res2) {
                    if ($shengxias['khh'] !== $datas['khh'] || $shengxias['name'] !== $datas['skr'] || $shengxias['banksou'] !== $datas['accountnumber']) {
                        $ins['khh'] = $datas['khh'];
                        $ins['name'] = $datas['skr'];
                        $ins['banksou'] = $datas['accountnumber'];
                        M('user')->where(array('id' => session('user')['id']))->save($ins);
                    }
                    show('提交，请等待审核', 1);
                } else {
                    show('申请失败，请重试', 0);
                }
            } else {
                $datass['yue'] = $shengxias['points'] - $datas['jine'];
                $datass['headimgurl'] = session('user')['headimgurl'];
                $datass['nickname'] = session('user')['nickname'];
                $datass['time'] = time();
                $datass['userid'] = session('user')['id'];
                $datass['typepay'] = $datas['type'];
                $datass['status'] = 0;
                $datass['type2'] = 4;
                $datass['points'] = $datas['jine'];
                $datass['msg'] = '待审核';
                $datass['accountnumber'] = $datas['accountnumber'];
                $datass['bfb'] = $datas['bfb'];
                //判断是否为客服
                $data = M('user')->where(array('id' => $datass['userid']))->find();
                if ($data['iskefu'] == 1) {
                    $datass['is_kefu'] = 1;
                }
                $res = M('money')->add($datass);
                $res2 = M('user')->where(array('id' => session('user')['id']))->setDec('commission', $datas['jine']);
                if ($res && $res2) {
                    if ($datas['type'] == 'alipay') {
                        if ($shengxias['zhifubao'] !== $datas['accountnumber']) {
                            $ins['zhifubao'] = $datas['accountnumber'];
                            M('user')->where(array('id' => session('user')['id']))->save($ins);
                        }
                    } elseif ($datas['type'] == 'weixin') {
                        if ($shengxias['weixin'] !== $datas['accountnumber']) {
                            $ins['weixin'] = $datas['accountnumber'];
                            M('user')->where(array('id' => session('user')['id']))->save($ins);
                        }
                    }
                    show('提交，请等待审核', 1);
                } else {
                    show('提交失败，请重试', 0);
                }
            }
            show('提交成功', '1');
        }
    }

    public function zuhanhuan()
    {
        $this->display();
    }

    public function zhuanhuando()
    {
        $user = session('user');
        $user = M('user')->where(array('id' => $user['id']))->find();
        $jine = I('jine');
        if (empty($jine)) {
            $date['info'] = "金额不能为空";
            $date['status'] = 0;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        }
        if ($jine < 0 || $jine > $user['commission']) {
            $date['info'] = "金额错误";
            $date['status'] = 0;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        }
        M()->startTrans();
        if (M('user')->where(array('id' => $user['id']))->setDec('commission', $jine) == false || M('user')->where(array('id' => $user['id']))->setInc('points', $jine) == false) {
            M()->rollback();
            $date['info'] = "转换失败";
            $date['status'] = 0;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        }
        M()->commit();
        $data['money']=$jine;
        $data['nickname']=$user['nickname'];
        $data['uid']=$user['id'];
        $data['create_time']=time();
        M('turn')->add($data);
        $date['info'] = "转换成功";
        $date['status'] = 1;
        $date['url'] = "";
        echo json_encode($date);
        exit();
    }

    public function msgsout()
    {
        $date['is_status'] = 1;
        M('msgs')->where(array('userid' => session('user')['id']))->save($date);
        die(json_encode("aaa"));
    }

    public function liuyan()
    {
        $count = M('msgs')->where(array('userid' => session('user')['id']))->count();
        $page = new \Think\Page($count, 10);
        $show = $page->show();
        $list = M('msgs')->where(array('userid' => session('user')['id']))->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->display();
    }
    public function photo(){
        $uid=I('uid');
        $user = M('user')->where(array('id' => $uid))->find();
        $qinniu=C('qiniu_ak');
        if(!C('qiniu_ak') || empty($qinniu)){
            $qinniu='';
        }else{
            $qinniu=2;
        }
        $this->assign('qinniu',$qinniu);
        $this->assign('user',$user);
        $this->display();
    }
    public function photodo(){
        $nickname=I('nickname');
        $uid=I('uid');
        $user=M('user')->where(array('id'=>$uid))->find();
        if (!$user){
            $this->error("会员不存在");
        }
        if(!C('qiniu_ak')){
            $data['nickname']=$nickname;
            if (M('user')->where(array('id'=>$user['id']))->save($data)==false){
                $this->error("错误");
            }
            $this->success("修改成功");
        }else{
            $checkpic = I('checkpic');
            $oldcheckpic = I('oldcheckpic');
            if ($checkpic != $oldcheckpic) {
                $url=$this->qiiuchuan();
                if ($url!=false){
                    $data['headimgurl']=$url['url'];
                }else{
                    $this->error("上传图片错误");
                }
            }
            $data['nickname']=$nickname;
            if (M('user')->where(array('id'=>$user['id']))->save($data)==false){
                $this->error("错误");
            }
            $this->success("修改成功");
        }
    }
    public function bendichuan($checkpic,$oldcheckpic){
        if ($checkpic != $oldcheckpic) {
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 3145728;// 设置附件上传大小
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->rootPath = './Uploads/photo/'; // 设置附件上传根目录
            if (!is_readable($upload->rootPath)) {
                is_file($upload->rootPath) or mkdir($upload->rootPath, 0700);
            }
            $upload->savePath = ''; // 设置附件上传（子）目录
            $upload->saveRule = 'time';
            $info = $upload->upload();
            if ($info) {
                $img_url = '/Uploads/carousel/' . $info[file0][savepath] . $info[file0][savename];//如果上传成功则完成路径拼接
                return $img_url;
            } else {
                return false;
            }
        }else{
            return false;
        }
    }
    public function qiiuchuan(){
        require_once 'vendor/Qiniu/autoload.php';
        $accessKey = C('qiniu_ak');
        $secretKey =C('qiniu_sk');
        $auth = new Auth($accessKey, $secretKey);
        $bucket = C('qiniu_zone');
        $token = $auth->uploadToken($bucket);
        $filePath =$_FILES['file0']['tmp_name'];
        if($this->isImg($filePath) ==false){
            $this->error('上传格式错误');
        };
        $key = time().rand(1,999);
        $uploadMgr = new UploadManager();
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        $data['url'] ='http://'.C('qiniu_url').'/'.$ret['key'];
        return $data;
    }
    function isImg($fileName)
    {
        $file  = fopen($fileName, "rb");
        $bin  = fread($file, 2); // 只读2字节
        fclose($file);
        $strInfo = @unpack("C2chars", $bin);
        $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);
        $fileType = '';
        if($typeCode == 255216 /*jpg*/ || $typeCode == 7173 /*gif*/ || $typeCode == 13780 /*png*/)
        {
            return $typeCode;
        }
        else
        {
            return false;
        }
    }
    public function query(){
        $qihao=I('qihao');
        $game=I('game');
        if (!empty($qihao)){
          $map['periodnumber']=$qihao;
        }
        if (!empty($game)){
            $map['game']=$game;
        }else{
            $map['game']='pk10';
        }
        if ($map['game']=='pk10' ||$map['game']=='jscar'||$map['game']=='fei'){
            $count = M('number')->where($map)->count();
            if ($count>200){
                $count=200;
            }
            $page = new \Think\Page($count, 20);
            $show = $page->show();
            $list = M('number')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
        }elseif($map['game']=='jnd28' ||$map['game']=='bj28'){
            $count = M('dannumber')->where($map)->count();
            if ($count>200){
                $count=200;
            }
            $page = new \Think\Page($count, 20);
            $show = $page->show();
            $list = M('dannumber')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
        }elseif($map['game']=='kuai3'){
            $count = M('kuainumber')->where($map)->count();
            if ($count>200){
                $count=200;
            }
            $page = new \Think\Page($count, 20);
            $show = $page->show();
            $list = M('kuainumber')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
        }elseif($map['game']=='lhc'){
            $count = M('lhcnumber')->where($map)->count();
            if ($count>200){
                $count=200;
            }
            $page = new \Think\Page($count, 20);
            $show = $page->show();
            $list = M('lhcnumber')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
        }elseif($map['game']=='ssc' ||$map['game']=='jsssc'){
            $count = M('sscnumber')->where($map)->count();
            if ($count>200){
                $count=200;
            }
            $page = new \Think\Page($count, 20);
            $show = $page->show();
            $list = M('sscnumber')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order("id DESC")->select();
        }
        if (!empty($show) && !empty($list)){
            $this->assign('show',$show);
            $this->assign('list',$list);
        }
        $game = array(
            'bj28' => "北京28",
            'jnd28' => "加拿大28",
            'pk10' => "北京赛车",
            'ssc' => "时时彩",
            'fei' => "幸运飞艇",
            'kuai3' => "快3",
            'lhc' => "六合彩",
            'jsssc' => "极速时时彩",
            'jscar' => "极速赛车",
        );
        $this->assign('game',$game);
        $this->display();
    }
    public function qiandao(){
        if(C('is_sign')!=1){
            $date['msg'] = "签到未开启";
            $date['status'] = 1;
            echo json_encode($date);
            exit();
        }
        $user=M('user')->where(array('id'=>session('user')['id']))->find();
        if (!$user){
            $date['msg'] = "用户不存在";
            $date['status'] = 1;
            echo json_encode($date);
            exit();
        }
        if (strtotime(date('Y-m-d'))<$user['sign_time']){
            $date['msg'] = "今天你已签到";
            $date['status'] = 1;
            echo json_encode($date);
            M()->rollback();
            exit();
        }
        M()->startTrans();
        if (strtotime(date('Y-m-d',strtotime('-1 day')))<$user['sign_time']){
            if (!M('user')->where(array('id'=>$user['id']))->setInc('sign_number')){
                $date['msg'] = "签到失败";
                $date['status'] = 1;
                echo json_encode($date);
                M()->rollback();
                exit();
            }
        }else{
            if (M('user')->where(array('id'=>$user['id']))->setField('sign_number',1)===false){
                $date['msg'] = "签到失败";
                $date['status'] = 1;
                echo json_encode($date);
                M()->rollback();
                exit();
            }
        }
        if (!M('user')->where(array('id'=>$user['id']))->setField('sign_time',time())){
            $date['msg'] = "签到失败";
            $date['status'] = 1;
            echo json_encode($date);
            M()->rollback();
            exit();
        }
        $data['uid']=$user['id'];
        $data['nickname']=$user['nickname'];
        $data['sgin_time']=time();
        if (!M('sgin')->add($data)){
            $date['msg'] = "签到失败";
            $date['status'] = 1;
            echo json_encode($date);
            M()->rollback();
            exit();
        }
        M()->commit();
        $date['msg'] = "签到成功";
        $date['status'] = 2;
        echo json_encode($date);
        exit();
    }
}