<?php
namespace Home\Model;
use Think\Model;
class HonbaoModel extends Model {
    /*
     * 检查是否可以发言   返回1 可以发言，0 ，上分不够
     */
    public function checksay($uid){
        $map['userid']=$uid;
        $setsaycondition_day =C_set('saycondition_day');
        $setsaycondition_shagnfen =C_set('sayconditon_shangfen');
        if($setsaycondition_shagnfen ==0){
            return 1;
        }
        //如果设置为0 ，则为当天上分
        if($setsaycondition_day !=0){
            $start = strtotime('00:00:00') - $setsaycondition_day*86400;
            $end = time();

        }else{
            $start = strtotime( '00:00:00');
            $end = strtotime( '23:59:59');

        }
        $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
        $map['status'] =1;
        $map['type2'] =1;
        $seefen = M('money')->where($map)->field('sum(points) as points')->select();
        //查看上分
        $daysf =$seefen[0]['points'];
        //上分多少
        //  TOdo ..当天发言限制
//        $chart_tody_say_stop =C_set('chart_say_sum');
//        if($chart_tody_say_stop !=0){
//
//            $start = strtotime( '00:00:00');
//            $end = strtotime( '23:59:59');
//            $maps['time'] = array(array('egt', $start), array('elt', $end), 'and');
//            $maps['uid'] =$uid;
//            $char_room_sum= M('chatroom')->where($maps)->count();
//            dump($char_room_sum);
//            if($char_room_sum > $chart_tody_say_stop){
//                return 3;
//            }
//        }
        //todo 结束

        if($daysf<$setsaycondition_shagnfen|| $daysf ==null){
            return 0;
        }else{
            return 1;
        }
    }
    /*
     * 发送红包，用户自己发送红包 ， 数据库红包记录 uid ，和余额 ， 一天没有领取退还
     */
    public function user_send_redpack($uid,$jine,$sum,$content){
            $num = intval($sum);
            if ($num <= 0 || $num > 100) {
                show('数量错误,<br>提示：个数不能大于100',0);
            }
            if ($jine <1) {
                show('金额错误,最低金额为1元',0);
            }
             $ppoints=getpoints_byid($uid);
            if( $ppoints<$jine){
                show('您的余额不足<br>提示：可用余额为:'.$ppoints,0);
            }
            $date = array(
                'jine' => $jine,
                'num' => $num,
                'create_time' => time()
            );
            $date['uid']=$uid;
            $date['type'] =1;
            $date['shengxia'] =$jine;
            if (!$rid = M('redpacket')->add($date)) {
                show('发送红包失败',0);
            }else {
                M('user')->where(array('id'=>$uid))->setDec('points',$jine);
                $ab = $this->honbaochu($rid, $jine, $num,$content,$type='1',$uid);
                if (!$ab) {
                    M('redpacket')->where(array('id' => $rid))->delete();
                    show('发送红包失败',0);
                } else {
                    show('成功',1);
                }
            }
    }
    /*
     * 发送红包的方法
     */
    public function honbaochu($id, $jine, $num,$content,$type,$uid)
    {
        $jinenum = 0;
        $jiness = 0;
        for ($i = 0; $i < $num; $i++) {
            $data[$i] = rand(1, $jine);
            $jinenum += $data[$i];
        }
        for ($i = 0; $i < $num; $i++) {
            $jines[$i] = round($data[$i] / $jinenum * $jine, 2);
            $jiness += $jines[$i];
            $dataList[$i] = array('rid' => $id, 'money' => $jines[$i]);
        }
        if ($jiness != $jine) {
            if ($jiness - $jine > 0) {
                for ($i = 0; $i < $num - 1; $i++) {
                    if ($jines[$i] - $jiness + $jine > 0) {
                        $dataList[$i] = array('rid' => $id, 'money' => $jines[$i] - $jiness + $jine);
                        break;
                    }
                }
            } else {
                $dataList[0] = array('rid' => $id, 'money' => $jines[0] - $jiness + $jine);
            }
        }
        if (!M('redpacketed')->addAll($dataList)) {
            return false;
        } else {
            //获取头像名称
            $user = M('user')->where(array('id'=>$uid))->find();
            //todo：这里推送红包
            $message = array(
                'to'=>'chat',
                'hid' => $id,
                'rtype' =>$type,
                'uid'=>$uid,
                'jine'=>$jine,
                'type' => 'hongbao',
                'head_img_url' => $user['headimgurl'],
                'from_client_name' => $user['nickname'],
                'time' => date('H:i:s'),
                'content' =>$content,
            );
//            M('danmessage')->add($message);
            send_to_web($message);
            $messages = array(
                'uid' => $uid,
                'type'=>$type,
                'uname' =>$user['nickname'],
                'imgurl' => $user['headimgurl'],
                'iskefu' => 0,
                'jine'=>$jine,
                'hid'=>$id,
                'ishon' => 1,
                'content' => $content,
                'time' => time()
            );
            M('chatroom')->add($messages);
            return true;
        }
    }







}