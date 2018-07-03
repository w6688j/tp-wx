<?php
namespace Home\Controller;

use QL\QueryList;
use Think\Controller;

class TestController extends Controller
{
    public function index()
    {
        $data = QueryList::Query('http://www.pc6777.com/jnd28/', array(
            'time' => array('script:eq(2)', 'text'),
            "currentqihao" => array('.kj_white_line:eq(1)', 'text'),
        ))->data;
        //获取时间
        $str = $data[0]['time'];
        $result = array();
        preg_match_all("/(?:\()(.*)(?:\))/i", $str, $result);
        $allnum = $result[1][0];
        $resdata = explode(',', $allnum);
        $all['time'] = $resdata[0];
        //当前期号
        $nowqihaoalldata = explode("u00a0", $data[0]['currentqihao']);
        $nowqihao1 = explode(' ', $nowqihaoalldata[0]);
        $nowqihao2 = explode("期", $nowqihao1[0]);
        dump($nowqihao2[1]);

        //计算下一期的开奖时间
        $qianmian = explode(']', $nowqihao2[1]);
        $riqi = explode('[', $qianmian[0]);
        $fariqi = $riqi[1];
        $awar = date("Y-m-d");
        $shijianchuo = strtotime("$awar" . "$fariqi") + 200;
        $zhuanhuaderiqi = date('Y:m:d H:i:s', $shijianchuo);

        $all['currentqihao'] = $nowqihao2[0];
        //当前号码
        $nowhaoma = explode("]", $nowqihao1[0]);
        $nowhaomaarr = explode('+', $nowhaoma[1]);
        $testes = json_encode($nowhaomaarr[0]);
        $afa = explode('u00a0', $testes);
        $jjkd = preg_replace('/\D/s', '', $afa[2]);
        $nowhaomaarr1 = $jjkd;
        $nowhaomaarr2 = $nowhaomaarr[1];
        $nowhaomaarr3 = $nowhaomaarr[2];
        $all['currentnumber'] = $nowhaomaarr1 . ',' . $nowhaomaarr2 . ',' . $nowhaomaarr3;
        //下一期
        $all['nextqihao'] = $resdata[1];
        $all['kaijiangshijain'] = 11;
        $all = json_encode($all);
//        return $all;
        echo $all;


    }

    public function number($str)
    {
        return preg_replace('/\D/s', '', $str);
    }

    public function dd($jj)
    {
        echo $jj;
    }

    public function f3($str)
    {
        $result = array();
        preg_match_all("/(?:\()(.*)(?:\))/i", $str, $result);

        return $result[1][0];
    }

    public function test()
    {
        $time = time();
        $olddate = strtotime('-1 days');
        $map['time'] = array('between', "$olddate,$time");
        $res = M('order')->where($map)->field('sum(add_points),userid,sum(type = 2)as zuhetype,sum(type = 2)/count(userid) as zuhebili,sum(del_points),count(userid) as count,sum(del_points)-sum(add_points) as del_data')->group('userid')->select();
        $mycount = count($res);
        dump($res);
        for ($i = 0; $i < $mycount; $i++) {
            //判断是否为大于是十把，&&判断组合比例要大于20%，//小单，大双组合超过75%没有返水。
            if ($res[$i]['count'] >= 10 && $res[$i]['zuhebili'] > 0.2) {
                $data['userid'] = $res[$i]['userid'];
                $data['shuying'] = $res[$i]['del_data'];
                $data['time'] = time();
                $headurldata = M('user')->where(array("id" => $res[$i]['userid']))->select();
                $data['headimgurl'] = $headurldata[0]['headimgurl'];
                $data['nickname'] = $headurldata[0]['nickname'];
                if ($res[$i]['del_data'] >= 2000 && $res[$i]['del_data'] <= 10000) {
                    $fanshuidata = $res[$i]['del_data'] * 0.1;
                    $data['fanshui'] = $fanshuidata;
                    $adduserdata = M('user')->where(array("id" => $res[$i]['userid']))->setInc('points', $fanshuidata);
                    if ($adduserdata) {
                        M('order_day')->add($data);
                    }
                }
                if ($res[$i]['del_data'] >= 10001 && $res[$i]['del_data'] <= 30000) {
                    $fanshuidata = $res[$i]['del_data'] * 0.12;
                    $data['fanshui'] = $fanshuidata;
                    $adduserdata = M('user')->where(array("id" => $res[$i]['userid']))->setInc('points', $fanshuidata);
                    if ($adduserdata) {
                        M('order_day')->add($data);
                    }
                }
                if ($res[$i]['del_data'] >= 30001) {
                    $fanshuidata = $res[$i]['del_data'] * 0.15;
                    $data['fanshui'] = $fanshuidata;
                    $adduserdata = M('user')->where(array("id" => $res[$i]['userid']))->setInc('points', $fanshuidata);
                    if ($adduserdata) {
                        M('order_day')->add($data);
                    }
                }
            }
        }

    }

    public function test2()
    {
        //昨天凌晨的时间戳
        $ago = strtotime('-1 day 00:00:00');
        echo date('Y:m:d H:i:s', $ago);
        echo "<br>";
        //今天凌晨的时间戳
        $today = strtotime(date("Y-m-d"));
        echo date("Y:m:d H:i:s", $today);
    }

    public function caipiaokong()
    {
        $result = S('jjdata');
        if (empty($result)) {
            $url = "http://api.kaijiangtong.com/lottery/?name=jndklb&format=json3&uid=789423&token=1cd714ebb2c93a811fba7533a30d28fed7ccb7e1&num=1";
            $result = curlGet($url);
            S('jjdata', $result, 5);
        }
        $data = json_decode($result, true);

        $haoma = explode(',', $data[0]['cTermResult']);
        $n1 = $haoma['1'] + $haoma['4'] + $haoma['7'] + $haoma['10'] + $haoma['13'] + $haoma['16'];
        $n2 = $haoma['2'] + $haoma['5'] + $haoma['8'] + $haoma['11'] + $haoma['14'] + $haoma['17'];
        $n3 = $haoma['3'] + $haoma['6'] + $haoma['9'] + $haoma['12'] + $haoma['15'] + $haoma['18'];
        $num1 = str_split($n1);
        $num2 = str_split($n2);
        $num3 = str_split($n3);
        $number1 = $num1[count($num1) - 1];
        $number2 = $num2[count($num2) - 1];
        $number3 = $num3[count($num3) - 1];
        //传输的数据名称：
        $jnddata['time'] = time();
        $jnddata['game'] = 'jnd28';
        $jnddata['current']['periodNumber'] = $data[0]['cTerm'];
        $jnddata['current']['awardTime'] = $data[0]['cTermDT'];
        $jnddata['current']['awardNumbers'] = $number1 . ',' . $number2 . ',' . $number3;
        $jnddata['next']['periodNumber'] = $data[0]['cTerm'] + 1;
        $jnddata['next']['awardTime'] = date("Y-m-d H:i:s", (strtotime($data[0]['cTermDT']) + 210));
        $jnddata['next']['awardTimeInterval'] = ((strtotime($data[0]['cTermDT']) + 210) - time()) * 1000;
        $jnddata['next']['delayTimeInterval'] = 0;
        dump($jnddata);
    }

    public function kuaishizhuanhua()
    {
        $url = 'https://www.1399klc.com/lottery/ajax?lotterycode=canada';
        $data = array('name' => 'fdipzone');
        $header = array();
        $response = curl_https($url, $data, $header, 5);
        $result = S('Jnddata');
        if (empty($result)) {
            $result = $response;
            S('Jnddata', $result, 5);
        }
        $data = json_decode($result, true);
        //获取开奖号码：
        $haoma = explode(',', $data['result']);
        $n1 = $haoma['1'] + $haoma['4'] + $haoma['7'] + $haoma['10'] + $haoma['13'] + $haoma['16'];
        $n2 = $haoma['2'] + $haoma['5'] + $haoma['8'] + $haoma['11'] + $haoma['14'] + $haoma['17'];
        $n3 = $haoma['3'] + $haoma['6'] + $haoma['9'] + $haoma['12'] + $haoma['15'] + $haoma['18'];
        $num1 = str_split($n1);
        $num2 = str_split($n2);
        $num3 = str_split($n3);
        $number1 = $num1[count($num1) - 1];
        $number2 = $num2[count($num2) - 1];
        $number3 = $num3[count($num3) - 1];
        //传输的数据名称：
        $jnddata['time'] = time();
        $jnddata['game'] = 'jnd28';
        $jnddata['current']['periodNumber'] = $data['period'];
        $jnddata['current']['awardTime'] = $data['awardTime'];
        $jnddata['current']['awardNumbers'] = $number1 . ',' . $number2 . ',' . $number3;
        $jnddata['next']['periodNumber'] = $data['next_period'];
        $jnddata['next']['awardTime'] = $data['next_awardTime'];
        $jnddata['next']['awardTimeInterval'] = (strtotime($data['next_awardTime']) - time()) * 1000;
        $jnddata['next']['delayTimeInterval'] = 0;
        //返回api接口。
        return $jnddata;
    }

    public function test3()
    {
        $ago = strtotime('-1 day 00:00:00');
        $data['order_time'] = $ago;
        M('order_day')->add($data);
    }

    public function getbjdata()
    {

        $url = "http://api.1680210.com/LuckTwenty/getBaseLuckTewnty.do?issue=&lotCode=10014";
        $result = curlGet($url);
        S('klbjdata', $result, 5);
        $data = json_decode($result, true);
        $data = $data['result']['data'];
        //获取开奖号码
        $haoma = explode(',', $data['preDrawCode']);
        $caisan = array_chunk($haoma, 6);
        $num1all = array_sum($caisan[0]);
        $num2all = array_sum($caisan[1]);
        $num3all = array_sum($caisan[2]);
        $num1 = str_split($num1all);
        $num2 = str_split($num2all);
        $num3 = str_split($num3all);
        $number1 = $num1[count($num1) - 1];
        $number2 = $num2[count($num2) - 1];
        $number3 = $num3[count($num3) - 1];
        //数组合并
        $jnddata['time'] = time();
        $klbj28data['game'] = 'bj28';
        $klbj28data['current']['periodNumber'] = $data['preDrawIssue'];
        $klbj28data['current']['awardTime'] = $data['preDrawTime'];
        $klbj28data['current']['awardNumbers'] = $number1 . ',' . $number2 . ',' . $number3;
        $klbj28data['next']['periodNumber'] = $data['drawIssue'];
        $klbj28data['next']['awardTime'] = $data['drawTime'];
        $klbj28data['next']['awardTimeInterval'] = (strtotime($data['drawTime']) - time()) * 1000;
        $klbj28data['next']['delayTimeInterval'] = 0;
       return $klbj28data;
//            $n1 = $haoma['1'] + $haoma['4'] + $haoma['7']+$haoma['10']+$haoma['13']+$haoma['16'];
//            $n2 = $haoma['2'] + $haoma['5'] + $haoma['8']+$haoma['11']+$haoma['14']+$haoma['17'];
//            $n3 = $haoma['3'] + $haoma['6'] + $haoma['9']+$haoma['12']+$haoma['15']+$haoma['18'];
//            $num1 = str_split($n1);
//            $num2 = str_split($n2);
//            $num3 = str_split($n3);
//            $number1 =  $num1[count($num1)-1];
//            $number2 =  $num2[count($num2)-1];
//            $number3 =  $num3[count($num3)-1];
//            //传输的数据名称：
//            $jnddata['time'] = time();
//            $jnddata['game'] = 'jnd28';
//            $jnddata['current']['periodNumber'] = $data['period'];
//            $jnddata['current']['awardTime'] = $data['awardTime'];
//            $jnddata['current']['awardNumbers'] = $number1.','.$number2.','.$number3;
//            $jnddata['next']['periodNumber'] = $data['next_period'];
//            $jnddata['next']['awardTime'] = $data['next_awardTime'];
//            $jnddata['next']['awardTimeInterval'] = (strtotime($data['next_awardTime']) - time()) * 1000;
//            $jnddata['next']['delayTimeInterval'] = 0;
//            //返回api接口。
//            print_r($jnddata);
    }
    public function duizi(){
        $n1 = 6;
        $n2 = 6;
        $n3 = 6;
        $duizinum = 0;
        if ($n1 ==$n2){
            $duizinum=$duizinum+1;
        }
        if ($n1 ==$n3){
            $duizinum=$duizinum+1;
        }
        if($n2 ==$n3){
            $duizinum =$duizinum+1;
        }
        if($duizinum ==0){
            echo '非豹子，非顺子';
        }
       if($duizinum == 1){
            echo "对子";
       }
       if($duizinum ==3){
           echo '豹子';
       }
    }
    public function ceshi(){
        $sessiondata = session('user');
        $userid = $sessiondata['id'];
        $dankaijiangdata = getBj28();
        $dankaijiangqihao =  $dankaijiangdata['next']['periodNumber'];
        $where  = array(
          'number'=>$dankaijiangqihao,
            'type'=>1,
            'userid'=>$userid,
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        if(!$xiazhujinetype1){
            $xiazhujinetype1 = 0;
        }
        echo "期号";
        dump($dankaijiangqihao);
        dump($xiazhujinetype1);
    }
    public function group(){
//        $map['time'] = array('between', "$olddate,$time");
        $res = M('order')->field('sum(add_points),userid,sum(type = 2)as zuhetype,sum(type = 2)/count(userid) as zuhebili,sum(del_points),count(userid) as count,sum(del_points)-sum(add_points) as del_data')->group('userid')->select();
        dump($res);
    }
    public function cache(){
        dump(F('kuai3_status'));
    }
    public function test1(){
        $sum = '7,6,7';
        $arr = explode(',',$sum);
        $n1 = $arr[0];
        $n2 = $arr[1];
        $n3 = $arr[2];
        if($n1 ==$n2 &&$n1 !==$n3){
            echo $n1 .'='.$n2;
        }elseif ($n1 ==$n3 &&$n1 !==$n2 ){
            echo $n1 .'='.$n3;
        }elseif ($n1 !==$n2 && $n1!==$n3 &&$n2 ==$n3){
            echo $n2 .'='.$n3;
        }
    }
    public function test5(){
F('fei_periodNumber',0);

    }
    public function showtest1(){
    }
    public function test4(){
//      dump(F('fff'));
        echo "k0";
    }






}