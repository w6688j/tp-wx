<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2017/11/20
 * Time: 11:44
 */

namespace Home\Controller;


use Think\Controller;

class PublicsController extends Controller
{
    public function __construct(){
        /* 读取站点配置 */
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $config =  M('config')->find();
            S('DB_CONFIG_DATA',$config);
        }
        C($config); //添加配置
        parent::__construct();
    }

    public function getWXYM(){
        die(C('weixin_url'));
    }

    public function login()
    {
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
        if (session('user')) {
            redirect("/home/select/index");
        }
        $url=M('config')->where('id=1')->find();
        $this->assign('config',$url);
        $this->assign('url',$url['zhuche']);
        $this->display();
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

    function check_verify($code, $id = '')
    {
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
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
        /*if (!$this->check_verify($code)) {
            $data['status'] = 0;
            $data['info'] = "验证码错误";
            $data['url'] = "";
            echo json_encode($data);
            exit();
        }*/
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
        M('user')->where(array('id'=>$user['id']))->setField('last_time',time());
        M('user')->where(array('id'=>$user['id']))->setField('last_ip',get_client_ip());
        session('user', $user);
        $data['status'] = 1;
        $data['info'] = "登录成功";
        $data['url'] = "/home/select/index";
        echo json_encode($data);
        exit();
    }

    public function verlist()
    {
        $phone = I('username');
        if (empty($phone)) {
            $data['status'] = 202;
            $data['msg'] = "手机号码不能为空";
            die(json_encode($data));
        }
        $verify = rand(1000, 9999);
        $user = M('user')->where(array('username' => $phone))->order('id desc')->find();
        if (!$user) {
            $data['status'] = 202;
            $data['msg'] = "用户不存在";
            die(json_encode($data));
        }
        $x = M('verifys')->where(array('username' => $user['nickname']))->order('id desc')->find();
        if (time() - $x['vtime'] < 60) {
            $data['status'] = 202;
            $data['msg'] = "一分钟后再获取";
            die(json_encode($data));
        }
        $date = array(
            'uname' => $user['nickname'],
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
    public function verlists()
    {
        $phone = I('username');
        if (empty($phone)) {
            $data['status'] = 202;
            $data['msg'] = "手机号码不能为空";
            die(json_encode($data));
        }
        $verify = rand(1000, 9999);
        $x = M('verifys')->where(array('username' =>$phone))->order('id desc')->find();
        if (time() - $x['vtime'] < 60) {
            $data['status'] = 202;
            $data['msg'] = "一分钟后再获取";
            die(json_encode($data));
        }
        $date = array(
            'uname' => $phone,
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
    public function passworddo()
    {
        $username = I('username');
        $verlist = I('verlist');
        $passwd = I('passwd');
        if (empty($username) || empty($verlist) || empty($passwd)) {
            $data['status'] = 202;
            $data['msg'] = "不能为空";
            die(json_encode($data));
        }
        $user = M('user')->where(array('username' => $username))->order('id desc')->find();
        if (!$user) {
            $date['info'] = "用户不存在";
            $date['status'] = 0;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        }
        $x = M('verifys')->where(array('uname' => $user['nickname']))->order('id desc')->find();
        if ($verlist != $x['verify'] || time() - $x['vtime'] > 15 * 60) {
            $date['info'] = "验证码错误";
            $date['status'] = 0;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        }
        $dats['password'] = md5($passwd);
        $ys = M('user')->where(array('id' => $user['id']))->save($dats);
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
    public function register(){
        $this->display();
    }
    public function registerdo(){
        $username = I('username');
        $verlist = I('verlist');
        $passwd = I('passwd');
        if (empty($username) || empty($verlist) || empty($passwd)) {
            $data['status'] = 202;
            $data['msg'] = "不能为空";
            die(json_encode($data));
        }
        $x = M('verifys')->where(array('uname' => $username))->order('id desc')->find();
        /*if ($verlist != $x['verify'] || time() - $x['vtime'] > 15 * 60) {
            $date['info'] = "验证码错误";
            $date['status'] = 0;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        }*/
        $user = M('user')->where(array('username' => $username))->order('id desc')->find();
        if ($user) {
            $date['info'] = "用户已存在";
            $date['status'] = 0;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        }
        if (session('d_id')){
            $shang=M('agent')->where(array('id'=>session('d_id')))->find();
            if ($shang){
                $data['d_id'] = session('d_id');
                $data['td_id'] = $shang['t_id'];
                $data['is_ztui'] = 1;
            }
        }
        $t_id = session('tid');
        //$t_id = 65;
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
        $data['username']=$username;
        $data['nickname']="";
        $data['reg_time']=time();
        $data['logins']=1;
        $data['sex']=1;
        $data['country']="";
        $data['points']=0;
        $data['user_agent']="";
        $data['t_add']=0;
        $data['qrcode']="";
        $data['last_ip']="";
        $data['last_time']="";
        $data['city']="";
        $data['province']="";
        $data['reg_ip']=get_client_ip();
        $data['password']=md5($passwd);
        $ss=rand(1,3);
        $data['headimgurl']="/public/carhome/images/".$ss.".png";
        if (M('user')->add($data)){
            $user=M('user')->where(array('username'=>$username))->find();
            M('user')->where(array('id'=>$user['id']))->setField('nickname',$user['id']);
            if (session('d_id')) {
                M('agent')->where(array('id'=>session('d_id')))->setInc('tnum');
                M('agent')->where(array('id'=>$data['td_id']))->setInc('tnum');
            }
            if (!empty($user['d_id'])){
                M('agent')->where(array('id'=>$user['d_id']))->setInc('t_num');
                M('agent')->where(array('id'=>$user['td_id']))->setInc('t_num');
            }
            session('user', $user);
            $date['info'] = "注册成功";
            $date['status'] = 1;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        }else{
            $date['info'] = "账号未注册";
            $date['status'] = 0;
            $date['url'] = "";
            echo json_encode($date);
            exit();
        }
    }


    public function auth()
    {
        //1. 将timestamp , nonce , token 按照字典排序
        $timestamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];
        $token = "4533ee45d16f2cca";
        //$token =C("weixin_appid");
        $signature = $_GET['signature'];
        $array = array($timestamp,$nonce,$token);
        sort($array);

        //2.将排序后的三个参数拼接后用sha1加密
        $tmpstr = implode('',$array);
        $tmpstr = sha1($tmpstr);

        //3. 将加密后的字符串与 signature 进行对比, 判断该请求是否来自微信
        if($tmpstr == $signature)
        {
            echo $_GET['echostr'];
            exit;
        } else {
            echo 'error';
        }
    }


    public function addTime()
    {
        //幸运飞艇
        $int_expect = 1;
        $time = '13:09:00';
        $l_id = 27;

        M('game_date')->where(['l_id'=>$l_id])->delete();
        $exists = M('game_date')->where(['l_id'=>$l_id])->find();
        //检测是否增加过
        if($exists)
        {
            return '已经增加过';
        }
        $arr = [];
        for($i = 1;$i<181;$i++)
        {

            $arr[] = [
                'l_id'=>$l_id,
                'expect'=>str_pad($int_expect,3,0,STR_PAD_LEFT),
                'draw_time' => $time
            ];
            $int_expect++;
            $interval = 60 * 5;

            $time = date("H:i:s",strtotime($time)+$interval);
            if($int_expect == 132)
            {
                $time = '00:04:00';
            }
        }
        M('game_date')->addAll($arr);
        echo '新云飞艇时间添加成功';
    }
    //急速赛车
    public function addTimeJsCar()
    {
        //急速赛车
        $int_expect = 1;
        $time = '07:29:15';
        $l_id = 28;

        M('game_date')->where(['l_id'=>$l_id])->delete();
        $exists = M('game_date')->where(['l_id'=>$l_id])->find();
        //检测是否增加过
        if($exists)
        {
            return '已经增加过';
        }
        $arr = [];
        for($i = 1;$i<985;$i++)
        {

            $arr[] = [
                'l_id'=>$l_id,
                'expect'=>str_pad($int_expect,3,0,STR_PAD_LEFT),
                'draw_time' => $time
            ];
            $int_expect++;
            $interval = 75;

            $time = date("H:i:s",strtotime($time)+$interval);
            if($int_expect == 132)
            {
                $time = '00:04:00';
            }
        }
        M('game_date')->addAll($arr);
        echo '新云飞艇时间添加成功';
    }


    /**
     * 本方法运行sql升级
     */
    public function update()
    {
        $default = [
            "ALTER TABLE think_config ADD safecode varchar(64) NULL COMMENT '安全码'",
            "ALTER TABLE think_config ADD version int DEFAULT 0 NULL",
            "ALTER TABLE think_config MODIFY handicap_money varchar(64) COMMENT '盘口余额'"
        ];
        //ALTER TABLE think_user MODIFY t_add decimal(14,4) NOT NULL COMMENT '分销积分';
        //dump($version);exit;
        //方法升级
        $config  = M('config')->find();
        $version = $config['version'];
        if(is_null($version))
        {
            $Model = new \Think\Model();
            foreach ($default as $v)
            {
                $res = $Model->execute($v);
                dump($res);
            }
            dump($Model->getDbError());
        } else {
            $sql = [
                "ALTER TABLE think_user MODIFY t_add decimal(14,4) NOT NULL COMMENT '分销积分'",
                "ALTER TABLE think_user MODIFY headimgurl varchar(255)",
                "ALTER TABLE think_order ENGINE = InnoDB",
                "ALTER TABLE think_order ENGINE = InnoDB",
            ];
            $Model = new \Think\Model();
            for ( $i=$version ; $i < count($sql) ; $i++ )
            {
                foreach ($default as $v)
                {
                    $res = $Model->execute($sql[$i]);
                    dump($res);
                }
                //更新version
                dump($Model->getDbError());
            }
            $r = M('config')->where('id=1')->save(['version'=>count($sql)]);
            dump($r);
        }

    }

}