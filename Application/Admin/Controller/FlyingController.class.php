<?php

namespace Admin\Controller;

use Think\Controller;
use Think\Model;

/**
 * 飞单管理控制器
 * Class FlyingController
 * @package Admin\Controller
 */
class FlyingController extends BaseController
{
    public function _initialize()
    {
        C(M('config')->find());
        parent::_initialize();
    }

    public function index()
    {
        if (IS_POST) {

        } else {
            $this->display();
        }
    }

    public function config(){
        //$data = M('config')->find();
        //var_dump($data);
        $res = [
            "HandicapUrl"=>C('handicap_url'),
            "Handicap"=>C('Handicap'),
            "HandicapPass"=>C('Handicap_pass'),
            "HandicapUser"=>C('Handicap_user'),
            "Safecode"=>C('Safecode'),
        ];
        echo json_encode($res);
    }

    public function PKExpectTimeAdd(){
        //->where("l_id = 4 and draw_time < ?",[date("H:i:s")])
        $time = date('H:i:s');
        $Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
        $data = $Model->query("select * from think_game_date where l_id=4 and draw_time < '{$time}' order by expect desc limit 1");
        $data = $data[0];
        //获取系统封单时间
        $game = M("game_config")->where("id = 2")->find();
        $pk10 = json_decode($game['pk10']);
        //var_dump($pk10->status_off);
        $subTime = time()+$pk10->status_off;
        /*echo time();
        echo '<br />';
        echo strtotime(date('Y-m-d ').$data['draw_time'])+330 - $pk10->status_off;
        echo '<br />';*/
        $res =  time() > strtotime(date('Y-m-d ').$data['draw_time'])+330 - $pk10->status_off;
        echo json_encode($res);
    }
    //FeiExpectTimeAdd
    public function FeiExpectTimeAdd(){
        //->where("l_id = 4 and draw_time < ?",[date("H:i:s")])
        $time = date('H:i:s');
        $Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
        $data = $Model->query("select * from think_game_date where l_id=27 and draw_time < '{$time}' order by expect desc limit 1");
        $data = $data[0];
        //dump($data);
        //获取系统封单时间
        $game = M("game_config")->where("id = 2")->find();
        $pk10 = json_decode($game['fei']);

        //var_dump($pk10->status_off);
        $subTime = time()+$pk10->status_off;
        /*echo time();
        echo '<br />';
        echo strtotime(date('Y-m-d ').$data['draw_time'])+330 - $pk10->status_off;
        echo '<br />';*/
        $res =  time() > (strtotime(date('Y-m-d ').$data['draw_time'])+330 - $pk10->status_off);
        echo json_encode($res);
    }


    //获取PK10的订单
    public function getOrder()
    {
        //$data = getPk10();
        //$number = $data['next']['periodNumber'];
        $game = $_POST['game'];
        switch ($game){
            case "pk10":
                $data = getPk10();
                break;
            case "fei":
                $data = getfei();
                break;
        }
        $number = $data['next']['periodNumber'];
        $Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
        $data = $Model->query("select * from think_order where number = '{$number}' and game = '{$game}' and state = 1 and is_add = 0");
        echo json_encode($data);
    }

    public function saveBet()
    {
        $bet = M('flying_bet');
        $map = $_POST;
        echo $bet->add($map);
    }

    /**
     * 查询订单是否已经保存住了
     */
    public function orderExists()
    {
        $id = intval($_POST['id']);
        if($id < 1)
        {
            echo 'false';
            return;
        }
        $Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
        $data = $Model->query("select * from think_flying_bet where order_id = %d and status = 0 limit 1",$id);
        if($data){
            echo "true";
        } else {
            echo "false";
        }
    }

    /**
     * 获取PK10的当前期号
     */
    public function PKExpect(){
        $data = getPk10();
        echo $data['next']['periodNumber'];
    }
    public function FeiExpect(){
        $data = getfei();
        echo $data['next']['periodNumber'];
    }

    /**
     * 飞单列表
     */
    public function lists()
    {
        $flying = M('flying_bet');
        $map = [];
        if($map)
        {
            $flying->where($map);
        }
        //$count = $flying->where($map)->count();
        $count = $flying->count();
        $page = new \Think\Page($count,20);
        $show = $page->show();
        $list = $flying->where($map)->limit($page->firstRow.','.$page->listRows)->order("id Desc")->select();
        $this->assign('show',$show);
        $this->assign('list',$list);
        $this->display();
    }
}