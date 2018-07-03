<?php
namespace Admin\Model;
use Think\Model;
class MemberModel extends Model {

    /*
     * 根据id查看当前充值金额，流水统计，距离上次充值到现在，距离多少天了，最后一次充值的金额为多少  团队下注的总额，充值总额，推广收入
     * return array   allpoints,add_points ,
     * del_points,before_time,before_day,
     * before_points ,team_del_points（团队输赢）,
     * team_add_points（团队输赢）,team_shangfen(团队上分),
     * team_xiafen(团队下分)，t_add(推广收入)
     */
    public function recharge_byid($uid){
        $allpoints = M('money')->where(array('status'=>1,'type2'=>1,'userid'=>$uid))->sum('points');
        $select_order = M('order')->where(array('userid'=>$uid,'state'=>1,'is_add'=>1))->field('sum(del_points) as del_points,sum(add_points) as add_points')->select();
        $add_points =$select_order[0]['add_points'];
        $del_points =$select_order[0]['del_points'];
        $find_moneydb = M('money')->where(array('userid'=>$uid,'type2'=>1,'status'=>1))->order('time desc')->find();
        $before_time =$find_moneydb['time'];
        $before_day =intval((time()-$find_moneydb['time'])/86400);
        $before_points = $find_moneydb['points'];
        //团队的
        $team_shangfen =M('money')->where(array('status'=>1,'type2'=>1,'t_id'=>$uid))->sum('points');
        $team_xiafen =M('money')->where(array('status'=>1,'type2'=>0,'t_id'=>$uid))->sum('points');
        $team_order = M('order')->where(array('t_id'=>$uid,'state'=>1,'is_add'=>1))->field('sum(del_points) as del_points,sum(add_points) as add_points')->select();
        $team_del_points =$team_order[0]['del_points'];
        $team_add_points =$team_order[0]['add_points'];
        $user = M('user')->where(array('id'=>$uid))->find();
        $t_add =$user['t_add'];
        return array(
            'addpoints'=>$allpoints,
            'add_points'=>$add_points,
            'del_points'=>$del_points,
            'before_time'=>$before_time,
            'before_day'=>$before_day,
            'before_points'=>$before_points,
            'team_shangfen'=>$team_shangfen,
            'team_xiafen'=>$team_xiafen,
            'team_del_points'=>$team_del_points,
            'team_add_points'=>$team_add_points,
            't_add'=>$t_add,
            );
    }
    /*
     *根据id 查看团队的成员详情
     * pram uid
     * return array
     */
    public function team_byid($uid){
        $user_db =M('user')->where(array('t_id'=>$uid))->select();
        return $user_db;
    }
    /*
     * 根据uid查看所有(个人)明细
      */
    public function details($uid){

    }
    /*
     * 根据uid 查看团队的所有明细   todo。。。。
     */
    public function team_details($uid){

    }




























}