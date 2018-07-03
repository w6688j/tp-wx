<?php

namespace Home\Controller;

use Think\Server;
use Think\Model;

header('content-type:text/html;charset=utf-8');

class WorkerChartController extends Server
{
    protected $socket = 'websocket://0.0.0.0:8090';
    protected $processes = 1000;
    /**
     * 客户端连接时
     * */
    public function onConnect($connection)
    {
        $connection->onWebSocketConnect = function ($connection, $http_header) {
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
        $user = session('user');
        // 客户端传递的是json数据
        $message_data = json_decode($data, true);
        if (!$message_data) {
            return;
        }
        // 1:表示执行登陆操作 2:表示执行说话操作 3:表示执行退出操作
        // 根据类型执行不同的业务
        switch ($message_data['type']) {
            case 'login':
                // 把昵称放到session中
                $client_name = htmlspecialchars($message_data['client_name']);
                /* 保存uid到connection的映射，这样可以方便的通过uid查找connection， */
                $connection->uid = $message_data['client_id'];
                $this->worker->uidConnections[$message_data['type_name']][$connection->uid] = $connection;
//                session($connection->uid,$client_name);
                $new_message = array(
                    'type' => 'admin',
                    'send_type' => $message_data['type_name'],
                    'head_img_url' => '/Public/main/img/kefu.jpg',
                    'from_client_name' => 'GM管理员',
                    'content' => '欢迎莅临',
                    'time' => date('H:i:s')
                );
                $connection->send(json_encode($new_message));//单人发送
                $clients_list = $this->worker->connections;
                $num = count($clients_list);
                $new_message1 = array(
                    'type' => 'ping',
                    'content' => $num,
                    'time' => date('H:i:s')
                );
                $connection->send(json_encode($new_message1));//单人发送
                break;
            case 'say_chat':
                $userid = $connection->uid;
                $user = M('user')->where(array('id' => $userid))->find();
                if ($user['isgag'] == 1) {
                    $time_message = array(
                        'uid' => $connection->uid,
                        'type' => 'admin',
                        'send_type' => 'chat',
                        'isgag' => 1,
                        'head_img_url' => '/Public/Home/img/wnsr.png',
                        'from_client_name' => 'GM管理员GM管理员',
                        'content' => "你已被禁言,请联系管理员",
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($time_message));
                } elseif (D('Honbao')->checksay($connection->uid) == 0) {
                    $time_message = array(
                        'uid' => $connection->uid,
                        'type' => 'admin',
                        'send_type' => 'chat',
                        'isgag' => 1,
                        'head_img_url' => '/Public/Home/img/wnsr.png',
                        'from_client_name' => 'GM管理员',
                        'content' => "你没有发言资格，请咨询在线客服",
                        'time' => date('H:i:s')
                    );
                    $connection->send(json_encode($time_message));
                } else {
                    //判断是否聊天室禁言
                    $time_message = array(
                        'uid' => $connection->uid,
                        'type' => 'admin',
                        'send_type' => 'chat',
                        'isimg' => $message_data['isimg'],
                        'head_img_url' => $message_data['headimgurl'],
                        'from_client_name' => $message_data['client_name'],
                        'content' => $message_data['content'],
                        'time' => date('H:i:s')
                    );
                    $messages = array(
                        'uid' => $connection->uid,
                        'uname' => $message_data['client_name'],
                        'imgurl' => $message_data['headimgurl'],
                        'iskefu' => 2,
                        'ishon' => 0,
                        'isimg' => $message_data['isimg'],
                        'content' => $message_data['content'],
                        'time' => time()
                    );
                    M('chatroom')->add($messages);
                    foreach ($this->worker->connections as $conn) {
                        $conn->send(json_encode($time_message));
                    }
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
//        $user = session($connection->id);
//        foreach ($this->worker->uidConnections as $con) {
//            if (!empty($user)) {
//                $new_message = array(
//                    'type' => 'logout',
//                    'from_client_name' => $user,
//                    'time' => date('H:i:s')
//                );
//                $con->send(json_encode($new_message));
//            }
//        }
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