<?php

namespace Home\Controller;

use Common\Widget\Data;
use Think\Server;
use Workerman\Worker;

header('content-type:text/html;charset=utf-8');

class WorkerController extends Server
{
//    protected $socket = 'websocket://0.0.0.0:8880';
    protected $processes = 1000;
    protected $game_config;
    protected $web_config;
    protected $peilv_config;

    //初始化游戏配置
    public function __construct()
    {
        /* 读取站点配置 */
        $this->web_config=M('config')->find();
        $game = M('game_config')->where(array("id" => 2))->find();
        $peilv = M('game_config')->where(array("id" => 1))->find();
        foreach ($game as $key => $value) {
            $this->game_config[$key] = json_decode($value, true);
        }
        foreach ($peilv as $key => $value) {
            $this->peilv_config[$key] = json_decode($value, true);
        }
        $this->socket='websocket://0.0.0.0:'.C('worker_port');
        parent::__construct();
    }

    /**
     * 添加定时器
     *监控连接状态
     * */
    public function onWorkerStart()
    {

        $this->init_pusish_listen();
        $this->init();
        /*开奖time*/
        \Workerman\Lib\Timer::add(1, function () {
            //5.获取时时彩的缓存
            foreach (Data::$type_data as $key => $value) {
                if ($key == "chat") {
                    continue;
                }
                //6.0获取下一期的时间
                Data::$game_data[$key]['kjdata']['next_time'] = Data::$game_data[$key]['kjdata']['next']['delayTimeInterval'] + strtotime(Data::$game_data[$key]['kjdata']['next']['awardTime']);
                //在38秒的时候提醒一次
                if (Data::$game_data[$key]['kjdata']['next_time'] - time() == $this->game_config[$key]['status_off']+30) {
                    $new_message = array(
                        'type' => 'admin',
                        'send_type' => $key,
                        'head_img_url' => '/Public/main/img/kefu.jpg',
                        'from_client_name' => 'GM管理员',
                        'content' => '期号:' . Data::$game_data[$key]['kjdata']['next']['periodNumber'] . '<br/>' . '--距离封盘还有30秒--' . '<br/>' . '最高中奖100万积分，最低下注' .$this->peilv_config[$key][$key.'_zuidi'] . '积分',
                        'time' => date('H:i:s')
                    );
                    //发给在线用户
                    $this->send_type($key, $new_message);
                    $this->add_message($new_message, $key);
                }

                //8关闭游戏
                if (Data::$game_data[$key]['kjdata']['next_time'] - time() <= $this->game_config[$key]['status_off'] && Data::$game_data[$key]['status'] == 1) {
                    Data::$game_data[$key]['status'] = 0;
                    $new_message = array(
                        'type' => 'admin',
                        'send_type' => $key,
                        'head_img_url' => '/Public/main/img/kefu.jpg',
                        'from_client_name' => 'GM管理员',
                        'content' => '期号:' . Data::$game_data[$key]['kjdata']['next']['periodNumber'] . '<br/>' . '--关闭，请耐心等待--' . '单局最高中奖100万积分，最低下注' . $this->peilv_config[$key][$key.'_zuidi'] . '积分',
                        'time' => date('H:i:s')
                    );
                    $this->send_type($key, $new_message);
                    $this->add_message($new_message, $key);
                }
                //9开启游戏
                /*if($key == 'pk10')
                {
                    var_dump($key);
                    var_dump(Data::$game_data[$key]['status']);
                    var_dump(Data::$game_data[$key]);
                    var_dump("=======" );
                }*/

                if (Data::$game_data[$key]['kjdata']['next_time'] - time() < Data::$type_data[$key]['time'] && Data::$game_data[$key]['kjdata']['next_time'] - time() > $this->game_config[$key]['status_off'] && Data::$game_data[$key]['status'] == 0) {
                    Data::$game_data[$key]['status'] = 1;
                    $new_message = array(
                        'delay' => '8',
                        'type' => 'admin',
                        'send_type' => $key,
                        'head_img_url' => '/Public/main/img/kefu.jpg',
                        'from_client_name' => 'GM管理员',
                        'content' => '期号:' . Data::$game_data[$key]['kjdata']['next']['periodNumber'] . '开放，祝各位中大奖',
                        'time' => date('H:i:s')
                    );
                    $this->send_type($key, $new_message);
                    $this->add_message($new_message, $key);
                }
            }
        });
        //300秒一次的公告------------------------------------------------------------------
//        \Workerman\Lib\Timer::add(300, function () {
//            $new_message = array(
//                'type' => 'admin',
//                'head_img_url' => '/Public/main/img/system.jpg',
//                'from_client_name' => '客服',
//                'content' => '由于各地网络情况不同，开奖动画仅作为参考，可能存在两秒的误差，不影响最终开奖结果！',
//                'time' => date('H:i:s')
//            );
//            $this->send_all($new_message);
//        });
        //10秒更新缓存---------------------------------------------------------------------------------------
        \Workerman\Lib\Timer::add(10, function () {
            //5.更新缓存
            foreach (Data::$type_data as $key => $value) {
                if ($key != 'chat') {
                    $fun = "get" . $key;
                    $fun();
                }
            }
        });

        \Workerman\Lib\Timer::add(1, function () {

            /*if ($this->game_config["pk10"]['robot_on_off'] > 0) {
                if (Data::$game_data["pk10"]['robot_time'] < time()) {
                    Data::$game_data["pk10"]['robot_time'] = time()  + rand(5, $this->game_config["pk10"]['robo_suiji'] * 2);
                }

                if (time() == Data::$game_data["pk10"]['robot_time']) {
                    $mess = $this->robot_message(Data::$type_data["pk10"]['robot_say']);
                    $robot = $this->robot();
                    $new_message = array(
                        'type' => 'say',
                        'send_type' => "pk10",
                        'head_img_url' => '/Uploads' . $robot[0]['headimgurl'],
                        'from_client_name' => $robot[0]['nickname'],
                        'content' => $mess[0]['content'],
                        'time' => date('H:i:s')
                    );
                    if (Data::$game_data["pk10"]['status'] == 1) {
                        $this->send_type("pk10", $new_message);
                        $this->add_message($new_message, "pk10");
                    }
                }
            }
            if ($this->game_config["fei"]['robot_on_off'] > 0) {
                if (Data::$game_data["fei"]['robot_time'] < time()) {
                    Data::$game_data["fei"]['robot_time'] = time()  + rand(5, $this->game_config["fei"]['robo_suiji'] * 2);
                }

                if (time() == Data::$game_data["fei"]['robot_time']) {
                    $mess = $this->robot_message(Data::$type_data["fei"]['robot_say']);
                    $robot = $this->robot();
                    $new_message = array(
                        'type' => 'say',
                        'send_type' => "fei",
                        'head_img_url' => '/Uploads' . $robot[0]['headimgurl'],
                        'from_client_name' => $robot[0]['nickname'],
                        'content' => $mess[0]['content'],
                        'time' => date('H:i:s')
                    );
                    if (Data::$game_data["fei"]['status'] == 1) {
                        $this->send_type("fei", $new_message);
                        $this->add_message($new_message, "fei");
                    }
                }
            }*/


            //5.机器人
            foreach (Data::$type_data as $key => $value) {

                if ($key == 'chat') {
                    continue;
                }

                if ($this->game_config[$key]['robot_on_off'] == 0) {
                    continue;
                }
                /*if($key == 'fei')
                {
                    var_dump($key.':'.Data::$game_data[$key]['robot_time'].':'.time());
                } else {
                    var_dump($key.':'.$this->game_config[$key]['robot_on_off']);
                }*/
                if (Data::$game_data[$key]['robot_time'] < time()) {
                    Data::$game_data[$key]['robot_time'] = time()  + rand(5, $this->game_config[$key]['robo_suiji'] * 2);
                }

                if (time() == Data::$game_data[$key]['robot_time']) {
                    $mess = $this->robot_message(Data::$type_data[$key]['robot_say']);
                    $robot = $this->robot();

                    $new_message = array(
                        'type' => 'say',
                        'send_type' => $key,
                        'head_img_url' => '/Uploads' . $robot[0]['headimgurl'],
                        'from_client_name' => $robot[0]['nickname'],
                        'content' => $mess[0]['content'],
                        'time' => date('H:i:s')
                    );
                    if (Data::$game_data[$key]['status'] == 1) {
                        $this->send_type($key, $new_message);
                        $this->add_message($new_message, $key);
                    }
                    /*if($key == 'fei')
                    {
                        var_dump($robot);
                        var_dump(Data::$game_data[$key]);
                    }*/
                }
            }
        });
    }

    public function init_pusish_listen()
    {
        $inner_http_worker = new Worker('http://0.0.0.0:'.C('push_port'));
        // 当http客户端发来数据时触发
        $inner_http_worker->onMessage = function ($http_connection, $data) {
            $_POST = $_POST ? $_POST : $_GET;
            $new_message = $_POST;
            $to = @$_POST['to'];
            // 有指定uid则向uid所在socket组发送数据
            if ($to) {
                $this->send_type($to, $new_message);
                // 否则向所有uid推送数据
            } else {
                $this->send_all($new_message);
            }
            // http接口返回，如果用户离线socket返回fail
            if ($to && !isset($this->worker->connections)) {
                return $http_connection->send('offline');
            } else {
                return $http_connection->send('ok');
            }
            return $http_connection->send('fail');
        };
        // 执行监听
        $inner_http_worker->listen();
    }

    public function init()
    {
        //获取已经开启的游戏的开关
        foreach (Data::$type_data as $key => $value) {
            if (empty(Data::$game_data[$key]['kjdata']) && $key != 'chat') {
                $fun = "get" . $key;
                $fun();
            }
            //2.极速时时彩下一场的时间的值
            /*Data::$game_data[$key]['next_time'] = Data::$game_data[$key]['kjdata']['next']['delayTimeInterval'] + strtotime(Data::$game_data[$key]['kjdata']['next']['awardTime']);*/
            //3.极速赛车是否开盘
            /*if (Data::$game_data[$key]['next_time'] - time() > 20 && Data::$game_data[$key]['next_time'] - time() < $value['time'] && $this->game_config[$key]['on_off'] == 1) {
                Data::$game_data[$key]['status'] = 1;
            } else {
                Data::$game_data[$key]['status'] = 0;
            }*/
        }
    }

    public function send_type($type, $new_message)
    {
        foreach ($this->worker->uidConnections[$type] as $conn) {
            $conn->send(json_encode($new_message));
        }
    }

    public function send_all($new_message)
    {
        foreach ($this->worker->connections as $conn) {
            $conn->send(json_encode($new_message));
        }
    }

    /**
     * 客户端连接时
     * */
    public function onConnect($connection)
    {
        $connection->onWebSocketConnect = function ($connection, $http_header) {
            /* 读取站点配置 */
            $data = M('config')->find();
            if($data != $this->web_config)
            {
                $this->web_config = $data;
            }

            $game = M('game_config')->where(array("id" => 2))->find();
            $peilv = M('game_config')->where(array("id" => 1))->find();
            foreach ($game as $key => $value) {
                if($this->game_config[$key] != json_decode($value, true))
                {
                    $this->game_config[$key] = json_decode($value, true);
                }
            }
            foreach ($peilv as $key => $value) {
                if($this->peilv_config[$key] != json_decode($value, true))
                {
                    $this->peilv_config[$key] = json_decode($value, true);
                }
            }
            if ($_SERVER['HTTP_ORIGIN'] != 'http://pk.aicaiyou.com') {
                //$connection->close();
            }
        };
    }

    /**
     * onMessage
     * @access public
     * 转发客户端消息
     * @param  void
     * @param  void
     * @return void
     */
    public function onMessage($connection, $data)
    {
//        $user = session('user');
        // 客户端传递的是json数据
        $message_data = json_decode($data, true);
        if (!$message_data) {
            return;
        }
        switch ($message_data['type']) {
            case 'login':
                // 把昵称放到session中
//                $client_name = htmlspecialchars($message_data['client_name']);
                /* 保存uid到connection的映射，这样可以方便的通过uid查找connection， */
                $connection->uid = $message_data['client_id'];
                $this->worker->uidConnections[$message_data['type_name']][$connection->uid] = $connection;
//                session($connection->uid,$client_name);
                $new_message = array(
                    'type' => 'admin',
                    'say_game'=>$message_data['type_name'],
                    'send_type' => $message_data['type_name'],
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '欢迎莅临' . Data::$type_data[$message_data['type_name']]['title'] . '，' .$this->web_config['welcome'],
                    'time' => date('H:i:s')
                );
                $connection->send(json_encode($new_message));//单人发送
                $clients_list = $this->worker->connections;
                $num = count($clients_list);
                $new_message = array(
                    'type' => 'ping',
                    'content' => $num,
                    'time' => date('H:i:s')
                );
                $connection->send(json_encode($new_message));//单人发送
                break;
            case 'say':
                $userid = $connection->uid;
                /*是否竞猜时间*/
                $key = $message_data['type_name'];
                if(strpos($message_data['content'],'上') !== false) {
                    $num=explode('上',$message_data['content'])[1];
                    if(preg_match("/^\d*$/",$num)){
                        $person=M('user')->where(array('id'=>$userid))->find();
                        $datass['headimgurl'] = $person['headimgurl'];
                        $datass['nickname'] =$person['nickname'];
                        $datass['yue'] = $person['points'];
                        $datass['time']=time();
                        $datass['userid'] = $person['id'];
                        $datass['typepay']='weixin';
                        $datass['status']=0;
                        $datass['accountnumber'] ='';
                        $datass['type2']=1;
                        $datass['points']=$num;
                        $datass['msg']='待审核';
                        //判断是否为客服
                        if($person['iskefu'] ==1){
                            $datass['is_kefu'] =1;
                        }
                        if($person['t_id'] !=0){
                            $datass['t_id'] =$person['t_id'];
                        }
                        if(M('money')->add($datass)){
                            $format_error_message = array(
                                'uid' => $connection->uid,
                                'type' => 'say',
                                'send_type' => $key,
                                'head_img_url' => $message_data['headimgurl'],
                                'from_client_name' => $message_data['client_name'],
                                'content' => $message_data['content'],
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($format_error_message));
                            $format_error_message['type'] = 'say_error';
                            $this->add_message($format_error_message, $key);/*添加信息*/

                            $new_message3 = array(
                                'uid' => $connection->uid,
                                'type' => 'admin',
                                'send_type' => $key,
                                'head_img_url' => '/Public/main/img/kefu.jpg',
                                'from_client_name' => 'GM管理员',
                                'content' =>  '提交成功，等待财务审核',
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($new_message3));
                            $new_message3['type'] = 'error';
                            $this->add_message($new_message3, $key);/*添加信息*/
                            return;
                        }else{
                            $format_error_message = array(
                                'uid' => $connection->uid,
                                'type' => 'say',
                                'send_type' => $key,
                                'head_img_url' => $message_data['headimgurl'],
                                'from_client_name' => $message_data['client_name'],
                                'content' => $message_data['content'],
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($format_error_message));
                            $format_error_message['type'] = 'say_error';
                            $this->add_message($format_error_message, $key);/*添加信息*/

                            $new_message3 = array(
                                'uid' => $connection->uid,
                                'type' => 'admin',
                                'send_type' => $key,
                                'head_img_url' => '/Public/main/img/kefu.jpg',
                                'from_client_name' => 'GM管理员',
                                'content' =>  '上分失败',
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($new_message3));
                            $new_message3['type'] = 'error';
                            $this->add_message($new_message3, $key);/*添加信息*/
                            return;
                        }
                    }else{
                        $format_error_message = array(
                            'uid' => $connection->uid,
                            'type' => 'say',
                            'send_type' => $key,
                            'head_img_url' => $message_data['headimgurl'],
                            'from_client_name' => $message_data['client_name'],
                            'content' => $message_data['content'],
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($format_error_message));
                        $format_error_message['type'] = 'say_error';
                        $this->add_message($format_error_message, $key);/*添加信息*/

                        $new_message3 = array(
                            'uid' => $connection->uid,
                            'type' => 'admin',
                            'send_type' => $key,
                            'head_img_url' => '/Public/main/img/kefu.jpg',
                            'from_client_name' => 'GM管理员',
                            'content' =>  '格式不正确,上分失败',
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($new_message3));
                        $new_message3['type'] = 'error';
                        $this->add_message($new_message3, $key);/*添加信息*/
                        return;
                    }
                    break;
                }
                if(strpos($message_data['content'],'下') !== false) {
                    $num=explode('下',$message_data['content'])[1];
                    if(preg_match("/^\d*$/",$num)){
                        $person=M('user')->where(array('id'=>$userid))->find();
                        if ($person['points']-$num<0){
                            $format_error_message = array(
                                'uid' => $connection->uid,
                                'type' => 'say',
                                'send_type' => $key,
                                'head_img_url' => $message_data['headimgurl'],
                                'from_client_name' => $message_data['client_name'],
                                'content' => $message_data['content'],
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($format_error_message));
                            $format_error_message['type'] = 'say_error';
                            $this->add_message($format_error_message, $key);/*添加信息*/

                            $new_message3 = array(
                                'uid' => $connection->uid,
                                'type' => 'admin',
                                'send_type' => $key,
                                'head_img_url' => '/Public/main/img/kefu.jpg',
                                'from_client_name' => 'GM管理员',
                                'content' =>  '余额不足，下分失败',
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($new_message3));
                            $new_message3['type'] = 'error';
                            $this->add_message($new_message3, $key);/*添加信息*/
                            return;
                        }
                        $datass['yue'] =$person['points']-$num;
                        $datass['headimgurl'] = $person['headimgurl'];
                        $datass['nickname'] =$person['nickname'];
                        $datass['time']=time();
                        $datass['userid'] =$person['id'];
                        $datass['typepay'] ="weixin";
                        $datass['status']=0;
                        $datass['type2']=0;
                        $datass['points']=$num;
                        $datass['msg']='待审核';
                        $datass['accountnumber'] = '';
                        $datass['bfb'] = 0;
                        //判断是否为客服
                        if($person['iskefu'] ==1){
                            $datass['is_kefu'] =1;
                        }
                        $res =M('money')->add($datass);
                        $res2 =M('user')->where(array('id'=>$connection->uid))->setDec('points',$num);
                        if($res &&$res2){
                            $format_error_message = array(
                                'uid' => $connection->uid,
                                'type' => 'say',
                                'send_type' => $key,
                                'head_img_url' => $message_data['headimgurl'],
                                'from_client_name' => $message_data['client_name'],
                                'content' => $message_data['content'],
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($format_error_message));
                            $format_error_message['type'] = 'say_error';
                            $this->add_message($format_error_message, $key);/*添加信息*/

                            $new_message3 = array(
                                'uid' => $connection->uid,
                                'type' => 'admin',
                                'send_type' => $key,
                                'head_img_url' => '/Public/main/img/kefu.jpg',
                                'from_client_name' => 'GM管理员',
                                'content' =>  '提交成功，等待财务审核',
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($new_message3));
                            $new_message3['type'] = 'error';
                            $this->add_message($new_message3, $key);/*添加信息*/
                            return;
                        }else{
                            $format_error_message = array(
                                'uid' => $connection->uid,
                                'type' => 'say',
                                'send_type' => $key,
                                'head_img_url' => $message_data['headimgurl'],
                                'from_client_name' => $message_data['client_name'],
                                'content' => $message_data['content'],
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($format_error_message));
                            $format_error_message['type'] = 'say_error';
                            $this->add_message($format_error_message, $key);/*添加信息*/

                            $new_message3 = array(
                                'uid' => $connection->uid,
                                'type' => 'admin',
                                'send_type' => $key,
                                'head_img_url' => '/Public/main/img/kefu.jpg',
                                'from_client_name' => 'GM管理员',
                                'content' =>  '下分失败',
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($new_message3));
                            $new_message3['type'] = 'error';
                            $this->add_message($new_message3, $key);/*添加信息*/
                            return;
                        }
                    }else{
                        $format_error_message = array(
                            'uid' => $connection->uid,
                            'type' => 'say',
                            'send_type' => $key,
                            'head_img_url' => $message_data['headimgurl'],
                            'from_client_name' => $message_data['client_name'],
                            'content' => $message_data['content'],
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($format_error_message));
                        $format_error_message['type'] = 'say_error';
                        $this->add_message($format_error_message, $key);/*添加信息*/

                        $new_message3 = array(
                            'uid' => $connection->uid,
                            'type' => 'admin',
                            'send_type' => $key,
                            'head_img_url' => '/Public/main/img/kefu.jpg',
                            'from_client_name' => 'GM管理员',
                            'content' =>  '格式错误，下分失败',
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($new_message3));
                        $new_message3['type'] = 'error';
                        $this->add_message($new_message3, $key);/*添加信息*/
                        return;
                    }
                    break;
                }
                  if(strpos($message_data['content'],'查') !== false) {
                    $num=explode('查',$message_data['content'])[1];
                    if(preg_match("/^\d*$/",$num)){
                        $person=M('user')->where(array('id'=>$userid))->find();
                        $datass['headimgurl'] = $person['headimgurl'];
                        $datass['nickname'] =$person['nickname'];
                        $datass['yue'] = $person['points'];
                        $datass['time']=time();
                        $datass['userid'] = $person['id'];
                        $datass['typepay']='weixin';
                        $datass['status']=0;
                        $datass['accountnumber'] ='';
                        $datass['type2']=1;
                        $datass['points']=$num;
                        $datass['msg']='待审核';
                        //判断是否为客服
                        if($person['iskefu'] ==1){
                            $datass['is_kefu'] =1;
                        }
                        if($person['t_id'] !=0){
                            $datass['t_id'] =$person['t_id'];
                        }
                        if(M('money')->add($datass)){
                            $format_error_message = array(
                                'uid' => $connection->uid,
                                'type' => 'say',
                                'send_type' => $key,
                                'head_img_url' => $message_data['headimgurl'],
                                'from_client_name' => $message_data['client_name'],
                                'content' => $message_data['content'],
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($format_error_message));
                            $format_error_message['type'] = 'say_error';
                            $this->add_message($format_error_message, $key);/*添加信息*/

                            $new_message3 = array(
                                'uid' => $connection->uid,
                                'type' => 'admin',
                                'send_type' => $key,
                                'head_img_url' => '/Public/main/img/kefu.jpg',
                                'from_client_name' => 'GM管理员',
                                'content' =>  '提交成功，等待财务审核',
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($new_message3));
                            $new_message3['type'] = 'error';
                            $this->add_message($new_message3, $key);/*添加信息*/
                            return;
                        }else{
                            $format_error_message = array(
                                'uid' => $connection->uid,
                                'type' => 'say',
                                'send_type' => $key,
                                'head_img_url' => $message_data['headimgurl'],
                                'from_client_name' => $message_data['client_name'],
                                'content' => $message_data['content'],
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($format_error_message));
                            $format_error_message['type'] = 'say_error';
                            $this->add_message($format_error_message, $key);/*添加信息*/

                            $new_message3 = array(
                                'uid' => $connection->uid,
                                'type' => 'admin',
                                'send_type' => $key,
                                'head_img_url' => '/Public/main/img/kefu.jpg',
                                'from_client_name' => 'GM管理员',
                                'content' =>  '上分失败',
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($new_message3));
                            $new_message3['type'] = 'error';
                            $this->add_message($new_message3, $key);/*添加信息*/
                            return;
                        }
                    }else{
                        $format_error_message = array(
                            'uid' => $connection->uid,
                            'type' => 'say',
                            'send_type' => $key,
                            'head_img_url' => $message_data['headimgurl'],
                            'from_client_name' => $message_data['client_name'],
                            'content' => $message_data['content'],
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($format_error_message));
                        $format_error_message['type'] = 'say_error';
                        $this->add_message($format_error_message, $key);/*添加信息*/

                        $new_message3 = array(
                            'uid' => $connection->uid,
                            'type' => 'admin',
                            'send_type' => $key,
                            'head_img_url' => '/Public/main/img/kefu.jpg',
                            'from_client_name' => 'GM管理员',
                            'content' =>  '格式不正确,上分失败',
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($new_message3));
                        $new_message3['type'] = 'error';
                        $this->add_message($new_message3, $key);/*添加信息*/
                        return;
                    }
                    break;
                }
                if(strpos($message_data['content'],'回') !== false) {
                    $num=explode('回',$message_data['content'])[1];
                    if(preg_match("/^\d*$/",$num)){
                        $person=M('user')->where(array('id'=>$userid))->find();
                        if ($person['points']-$num<0){
                            $format_error_message = array(
                                'uid' => $connection->uid,
                                'type' => 'say',
                                'send_type' => $key,
                                'head_img_url' => $message_data['headimgurl'],
                                'from_client_name' => $message_data['client_name'],
                                'content' => $message_data['content'],
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($format_error_message));
                            $format_error_message['type'] = 'say_error';
                            $this->add_message($format_error_message, $key);/*添加信息*/

                            $new_message3 = array(
                                'uid' => $connection->uid,
                                'type' => 'admin',
                                'send_type' => $key,
                                'head_img_url' => '/Public/main/img/kefu.jpg',
                                'from_client_name' => 'GM管理员',
                                'content' =>  '余额不足，下分失败',
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($new_message3));
                            $new_message3['type'] = 'error';
                            $this->add_message($new_message3, $key);/*添加信息*/
                            return;
                        }
                        $datass['yue'] =$person['points']-$num;
                        $datass['headimgurl'] = $person['headimgurl'];
                        $datass['nickname'] =$person['nickname'];
                        $datass['time']=time();
                        $datass['userid'] =$person['id'];
                        $datass['typepay'] ="weixin";
                        $datass['status']=0;
                        $datass['type2']=0;
                        $datass['points']=$num;
                        $datass['msg']='待审核';
                        $datass['accountnumber'] = '';
                        $datass['bfb'] = 0;
                        //判断是否为客服
                        if($person['iskefu'] ==1){
                            $datass['is_kefu'] =1;
                        }
                        $res =M('money')->add($datass);
                        $res2 =M('user')->where(array('id'=>$connection->uid))->setDec('points',$num);
                        if($res &&$res2){
                            $format_error_message = array(
                                'uid' => $connection->uid,
                                'type' => 'say',
                                'send_type' => $key,
                                'head_img_url' => $message_data['headimgurl'],
                                'from_client_name' => $message_data['client_name'],
                                'content' => $message_data['content'],
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($format_error_message));
                            $format_error_message['type'] = 'say_error';
                            $this->add_message($format_error_message, $key);/*添加信息*/

                            $new_message3 = array(
                                'uid' => $connection->uid,
                                'type' => 'admin',
                                'send_type' => $key,
                                'head_img_url' => '/Public/main/img/kefu.jpg',
                                'from_client_name' => 'GM管理员',
                                'content' =>  '提交成功，等待财务审核',
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($new_message3));
                            $new_message3['type'] = 'error';
                            $this->add_message($new_message3, $key);/*添加信息*/
                            return;
                        }else{
                            $format_error_message = array(
                                'uid' => $connection->uid,
                                'type' => 'say',
                                'send_type' => $key,
                                'head_img_url' => $message_data['headimgurl'],
                                'from_client_name' => $message_data['client_name'],
                                'content' => $message_data['content'],
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($format_error_message));
                            $format_error_message['type'] = 'say_error';
                            $this->add_message($format_error_message, $key);/*添加信息*/

                            $new_message3 = array(
                                'uid' => $connection->uid,
                                'type' => 'admin',
                                'send_type' => $key,
                                'head_img_url' => '/Public/main/img/kefu.jpg',
                                'from_client_name' => 'GM管理员',
                                'content' =>  '下分失败',
                                'time' => date('H:i:s')
                            );
                            $connection->send(json_encode($new_message3));
                            $new_message3['type'] = 'error';
                            $this->add_message($new_message3, $key);/*添加信息*/
                            return;
                        }
                    }else{
                        $format_error_message = array(
                            'uid' => $connection->uid,
                            'type' => 'say',
                            'send_type' => $key,
                            'head_img_url' => $message_data['headimgurl'],
                            'from_client_name' => $message_data['client_name'],
                            'content' => $message_data['content'],
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($format_error_message));
                        $format_error_message['type'] = 'say_error';
                        $this->add_message($format_error_message, $key);/*添加信息*/

                        $new_message3 = array(
                            'uid' => $connection->uid,
                            'type' => 'admin',
                            'send_type' => $key,
                            'head_img_url' => '/Public/main/img/kefu.jpg',
                            'from_client_name' => 'GM管理员',
                            'content' =>  '格式错误，下分失败',
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($new_message3));
                        $new_message3['type'] = 'error';
                        $this->add_message($new_message3, $key);/*添加信息*/
                        return;
                    }
                    break;
                }
                
				if (Data::$game_data[$key]['status'] == 0) {
                    $time_error_message = array(
                        'uid' => $connection->uid,
                        'type' => 'say',
                        'send_type' => $key,
                        'head_img_url' => $message_data['headimgurl'],
                        'from_client_name' => $message_data['client_name'],
                        'content' => $message_data['content'],
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($time_error_message));//单人发送
                    $time_error_message['type'] = 'say_error';
                    $this->add_message($time_error_message, $key);/*添加信息*/
                    $time_message = array(
                        'uid' => $connection->uid,
                        'type' => 'admin',
                        'send_type' => $key,
                        'head_img_url' => '/Public/main/img/kefu.jpg',
                        'from_client_name' => 'GM管理员',
                        'content' => '「' . $message_data['content'] . '」' . '非竞猜时间，竞猜失败',
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($time_message));//单人发送
                    $time_message['type'] = 'error';
                    $this->add_message($time_message, $key);/*添加信息*/
                    return;
                }
                //var_dump($message_data);
                //检测是否是1大123
               $zh =  preg_match_all("/^(\d+)([\x{4e00}-\x{9fa5}])(\d+)$/u",$message_data['content'],$preg);
                //检测是否包含"/"
                if($zh){

                    $message_data['content'] = $preg[1][0]."/".$preg[2][0].'/'.$preg[3][0];
                }

                //检测是否是大213
                $zh =  preg_match_all("/^([\x{4e00}-\x{9fa5}])(\d+)$/u",$message_data['content'],$preg);
                //检测是否包含"/"
                if($zh){
                    $message_data['content'] = "1/".$preg[1][0]."/".$preg[2][0];
                }

                /*检测格式和金额*/
                $check = Data::$type_data[$key]['check'];
                $res = $check($message_data['content'], $connection->uid);
                if ($res['error'] == 0) {
                    $error_message = array(
                        'uid' => $connection->uid,
                        'type' => 'say',
                        'send_type' => $key,
                        'head_img_url' => $message_data['headimgurl'],
                        'from_client_name' => $message_data['client_name'],
                        'content' => $message_data['content'],
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($error_message));//单人发送
                    $error_message['type'] = 'say_error';
                    $this->add_message($error_message, $key);/*添加信息*/
                    $new_message = array(
                        'uid' => $connection->uid,
                        'type' => 'admin',
                        'send_type' => $key,
                        'head_img_url' => '/Public/main/img/kefu.jpg',
                        'from_client_name' => 'GM管理员',
                        'content' => '「' . $message_data['content'] . '」' . '单笔点数' . $res['money'] . '竞猜失败',
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($new_message));//单人发送
                    $new_message['type'] = 'error';
                    $this->add_message($new_message, $key);/*添加信息*/
                    return;
                }
                if (!$res['type']) {
                    $format_error_message = array(
                        'uid' => $connection->uid,
                        'type' => 'say',
                        'send_type' => $key,
                        'head_img_url' => $message_data['headimgurl'],
                        'from_client_name' => $message_data['client_name'],
                        'content' => $message_data['content'],
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($format_error_message));
                    $format_error_message['type'] = 'say_error';
                    $this->add_message($format_error_message, $key);/*添加信息*/

                    $new_message3 = array(
                        'uid' => $connection->uid,
                        'type' => 'admin',
                        'send_type' => $key,
                        'head_img_url' => '/Public/main/img/kefu.jpg',
                        'from_client_name' => 'GM管理员',
                        'content' => '「' . $message_data['content'] . '」' . '格式不正确,竞猜失败',
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($new_message3));
                    $new_message3['type'] = 'error';
                    $this->add_message($new_message3, $key);/*添加信息*/
                    return;
                }
                /*查询积分*/
                $user = M('user')->where(array('id' => $userid))->find();
                if ($user['points'] < $res['points']) {
                    $points_error = array(
                        'uid' => $connection->uid,
                        'type' => 'say',
                        'send_type' => $key,
                        'head_img_url' => $message_data['headimgurl'],
                        'from_client_name' => $message_data['client_name'],
                        'content' => $message_data['content'],
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($points_error));
                    $points_error['type'] = 'say_error';
                    $this->add_message($points_error, $key);/*添加信息*/

                    $points_tips = array(
                        'uid' => $connection->uid,
                        'type' => 'admin',
                        'send_type' => $key,
                        'head_img_url' => '/Public/main/img/kefu.jpg',
                        'from_client_name' => 'GM管理员',
                        'content' => '「' . $message_data['content'] . '」' . '点数不足，竞猜失败',
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($points_tips));//单人发送
                    $points_tips['type'] = 'error';
                    $this->add_message($points_tips, $key);/*添加信息*/
                    return;
                }
                //成功竞猜
                $map['userid'] = $userid;
                $map['type'] = $res['type'];
                $map['state'] = 1;
                $map['is_add'] = 0;
                $map['time'] = time();
                $map['number'] = Data::$game_data[$key]['kjdata']['next']['periodNumber'];
                $map['jincai'] = $message_data['content'];
                $map['del_points'] = $res['points'];
                $map['balance'] = $user['points'] - $map['del_points'];
                $map['nickname'] = $message_data['client_name'];
                $map['game'] = $key;
                /*添加竞猜*/
                M()->startTrans();
                if ($this->add_order($map)) {
                    /*减分*/
                    $res_points = M('user')->where("id = $userid")->setDec('points', $map['del_points']);
                    if ($res_points) {
                        $points_del = array(
                            'type' => 'points',
                            'content' => $res['points'],
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($points_del));
                    } else {
                        M()->rollback();
                        return;
                    }
                    $new_message = array(
                        'uid' => $connection->uid,
                        'type' => 'say',
                        'send_type' => $key,
                        'head_img_url' => $message_data['headimgurl'],
                        'from_client_name' => $message_data['client_name'],
                        'content' => $message_data['content'],
                        'time' => date('H:i:s')
                    );
                    $this->send_type($key, $new_message);
                    $add_return = $this->add_message($new_message, $key);/*添加信息*/
                    $jifen = M('user')->where("id = $userid")->find();
                    if ($add_return) {
                        $new_message = array(
                            'uid' => $connection->uid,
                            'type' => 'admin',
                            'send_type' => $key,
                            'updatepoints' => $jifen['points'],
                            'head_img_url' => '/Public/main/img/kefu.jpg',
                            'from_client_name' => 'GM管理员',
                            'content' => '@' . $user['nickname'] . '「' . $message_data['content'] . '」' . ',竞猜成功',
                            'time' => date('H:i:s')
                        );
                        $connection->send(json_encode($new_message));
                        $new_message1['type'] = 'error';
                        $type = $key;
                        $this->add_message($new_message1, $type);/*添加信息*/
                        M()->commit();
                        return;
                    } else {
                        M()->rollback();
                        return;
                    }
                } else {
                    M()->rollback();
                    var_dump(M()->getDbError());
                    return;
                }
                break;
            case 'say_chat':
                $userid = $connection->uid;
                $user = M('user')->where(array('id' => $userid))->find();
                $checksay = D('Honbao')->checksay($connection->uid);
                if ($user['isgag'] == 1) {
                    $time_message = array(
                        'uid' => $connection->uid,
                        'type' => 'admin',
                        'send_type' => 'chat',
                        'isgag' => 1,
                        'head_img_url' => '/Public/main/img/kefu.jpg',
                        'from_client_name' => 'GM管理员GM管理员',
                        'content' => "你已被禁言,请联系管理员",
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($time_message));
                } elseif ($checksay == 0) {
                    $time_message = array(
                        'uid' => $connection->uid,
                        'type' => 'admin',
                        'send_type' => 'chat',
                        'isgag' => 1,
                        'head_img_url' => '/Public/main/img/kefu.jpg',
                        'from_client_name' => 'GM管理员',
                        'content' => "你没有发言资格，请咨询在线客服",
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($time_message));
                }/*elseif($checksay == 3){  todo..当天限制多少条数据， 开启该功能->model Honbao开启todo,后台开启发言限制
                    $time_message = array(
                        'uid' => $connection->uid,
                        'type' => 'admin',
                        'send_type' => 'chat',
                        'isgag' => 1,
                        'head_img_url' => '/Public/Home/img/wnsr.png',
                        'from_client_name' => 'GM管理员',
                        'content' => "你的今日发言数已达到上限",
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($time_message));
                }*/else {
                    //todo::判断是否聊天室禁言
                    $content=guolv($message_data['content']);
                    if(empty($content)){
                        return;
                    }
                    $time_message = array(
                        'uid' => $connection->uid,
                        'type' => 'admin',
                        'send_type' => 'chat',
                        'isimg' => $message_data['isimg'],
                        'head_img_url' => $message_data['headimgurl'],
                        'from_client_name' => $message_data['client_name'],
                        'content' => $content,
                        'time' => date('H:i:s')
                    );
                    $messages = array(
                        'uid' => $connection->uid,
                        'uname' => $message_data['client_name'],
                        'imgurl' => $message_data['headimgurl'],
                        'iskefu' => 2,
                        'ishon' => 0,
                        'isimg' => $message_data['isimg'],
                        'content' => $content,
                        'time' => time()
                    );
                    M('chatroom')->add($messages);
                    $this->send_type('chat',$time_message);
                }
                break;
        }
    }

    public function robot_message($type)
    {
        $count = M('robot_message')->where(array('type' => $type))->count();
        $rand = mt_rand(0, $count - 1); //产生随机数。
        $limit = $rand . ',' . '1';
        $data = M('robot_message')->where(array('type' => $type))->limit($limit)->select();
        return $data;
    }

    public function robot()
    {
        $count = M('robot')->count();
        $rand = mt_rand(0, $count - 1); //产生随机数。
        $limit = $rand . ',' . '1';
        $data = M('robot')->limit($limit)->select();
        return $data;
    }

    /**
     * onClose
     * 关闭连接
     * @access public
     * @param  void
     * @return void
     */
    public function onClose($connection)
    {
        if (isset($connection->uid)) {
            // 连接断开时删除映射
            foreach ($this->worker->uidConnections as $grouid) {
                unset($grouid[$connection->uid]);
            }
        }
    }

    /**
     * 存竞猜记录和信息
     * */
    protected function add_order($mew_message)
    {
        $userid = $mew_message['userid'];
        $data = M('user')->where(array('id' => $userid))->find();
        if ($data['iskefu'] == 1) {
            $mew_message['is_kefu'] = 1;
        }
        if ($data['t_id'] !== 0) {
            $mew_message['t_id'] = $data['t_id'];
        }
        if (!empty($data['d_id'])) {
            $mew_message['d_id'] = $data['d_id'];
            $mew_message['td_id'] = $data['td_id'];
        }
        $res = M('order')->add($mew_message);
        return $res;
    }

    protected function add_message($new_message, $type)
    {
//        switch ($type){
//            case 'jsssc':
//                $new_message['game'] ='jsssc';
//                $res = M('sscmessage')->add($new_message);
//                break;
//            case 'jscar':
//                $new_message['game'] = 'jscar';
//                $res = M('message')->add($new_message);
//                break;
//            case 'fei':
//                $new_message['game'] = 'fei';
//                $res = M('message')->add($new_message);
//                break;
//            case 'kuai3':
//                $res = M('kuai3message')->add($new_message);
//                break;
//            case 'pk10':
//                $new_message['game'] = 'pk10';
//                $res = M('message')->add($new_message);
//                break;
//            case 'bj28':
//                $new_message['game'] = 'Bj28';
//                $res = M('danmessage')->add($new_message);
//                break;
//            case 'jnd28':
//                $new_message['game'] = 'Jnd28';
//                $res = M('jndmessage')->add($new_message);
//                break;
//            case 'ssc':
//                $new_message['game'] ="ssc";
//                $res = M('sscmessage')->add($new_message);
//                break;
//        }

        return true;
    }

    /**
     * 竞猜成功  加分
     * */
    public function add_points($order_id, $userid, $points)
    {
        if (empty($userid)) {
            return 0;
        }
        if (!M('order')->where(array("id" => $order_id, "is_add" => 0, "userid" => $userid))->find()) {
            return 0;
        }
        $res = M('user')->where(array("id" => $userid))->setInc('points', $points);
        if ($res) {
            $res1 = M('order')->where(array("id" => $order_id))->setField(array('is_add' => '1', 'add_points' => $points));
        }
        if ($res && $res1) {
            return 1;
        }
    }

    /**
     * 竞猜成功  加分
     * */
    public function del_points($order_id)
    {
        $res = M('order')->where(array("id" => $order_id))->setField(array('is_add' => '1'));
        if ($res) {
            return 1;
        }
    }

    /**
     * 竞猜成功通知
     * */
    public function send_msg($type, $points, $userid)
    {
        $message_points = array(
            'type' => $type,
            'points' => $points,
            'time' => date('H:i:s')
        );
        if (isset($this->worker->uidConnections[$userid])) {
            $connection = $this->worker->uidConnections[$userid];
            $connection->send(json_encode($message_points));
        }
    }

    /*
     * 冒泡
     */
    function compare($data, $order = 'asc')
    {
        if (empty($data))
            return;
        $count = count($data);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $tmp = $data[$i];
                if ($order == 'desc') {
                    if ($data[$i] < $data[$j]) {
                        $data[$i] = $data[$j];
                        $data[$j] = $tmp;
                    }
                } else {
                    if ($data[$i] > $data[$j]) {
                        $data[$i] = $data[$j];
                        $data[$j] = $tmp;
                    }
                }
            }
        }
        return $data;
    }
}

?>