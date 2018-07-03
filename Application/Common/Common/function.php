<?php
/*
 * 推送消息
 */

use Common\Widget\Data;


/*
 * 生成二维码
 * */
function qrcode($url, $level = 3, $size = 4)
{
    Vendor('phpqrcode.phpqrcode');
    $errorCorrectionLevel = intval($level);//容错级别
    $matrixPointSize = intval($size);//生成图片大小
    //生成二维码图片
    //echo $_SERVER['REQUEST_URI'];
    $object = new \QRcode();
    $date = date('Y-m-d');
    $path = "Uploads/qrcode/" . $date . '/';
    if (!file_exists($path)) {
        mkdir("$path", 0777, true);
    }
    $name = time() . '_' . mt_rand();
    //生成的文件名
    $fileName = $path . $name . '.png';
    $res = $object->png($url, $fileName, $errorCorrectionLevel, $matrixPointSize, 2);
    return $fileName;
}

/**
 * 获取微信操作对象
 * @staticvar array $wechat
 * @param type $type
 * @return WechatReceive
 */
function & load_wechat($type = '')
{
    $json = file_get_contents("http://auth.xiaohuiant.com/home/publics/locationappid");
    $json = json_decode($json,true);
    !class_exists('Wechat\Loader', FALSE) && Vendor('Wechat.Loader');
    static $wechat = array();
    $index = md5(strtolower($type));
    if (!isset($wechat[$index])) {
        // 从数据库查询配置参数
        //$config['appid'] = C('weixin_appid');
        $config['appid'] = $json['a'];
        $config['appsecret'] = C('weixin_appsecret');
        $config['appsecret'] = $json['b'];
        $config['encodingaeskey'] = '';
        $config['mch_id'] = "";
        $config['partnerkey'] = "";
        $config['ssl_cer'] = '';
        $config['ssl_key'] = '';
        $config['cachepath'] = '';

        // 设置SDK的缓存路径
        $config['cachepath'] = CACHE_PATH . 'Data/';
        $wechat[$index] = &\Wechat\Loader::get_instance($type, $config);
    }
    return $wechat[$index];
}

function checkgamebefore($game, $per)
{
    $data = M('gamebefore')->where(array('game' => $game, 'periodnumber' => $per))->find();
    if ($data) {
        return $data['awardnumbers'];
    } else {
        return 0;
    }
}

/*
 * 数据采集
 */
function caiji()
{
    set_time_limit(0);
    $game_arr = array(
        'lhc' => 'http://api.17gx.cn/home/index/api?type=lhc',
        'pk10' => 'http://api.17gx.cn/home/index/api?type=pk10',
        'ssc' => 'http://api.17gx.cn/home/index/api?type=ssc',
        'fei' => 'http://api.17gx.cn/home/index/api?type=fei',
        'bj28' => 'http://api.17gx.cn/home/index/api?type=bj28',
        'jnd28' => 'http://api.17gx.cn/home/index/api?type=jnd28',
        'kuai3' => 'http://api.17gx.cn/home/index/api?type=kuai3',
    );
    foreach ($game_arr as $key => $value) {
        if (!S($key . 'caji_cache')) {
            $pkdata = json_decode(file_get_contents($value), true);
            if ($pkdata['current']['awardNumbers'] != null) {
//                if( $key =='lhc'&& count(explode(',',$pkdata['current']['awardNumbers'])) !=7){
//                    continue;
//                }
                $chufa_time = strtotime($pkdata['next']['awardTime']) - time();
                if ($chufa_time > 0) {
                    S($key . 'caji_cache', '1', $chufa_time);
                }
                if (S('per_' . $key) != $pkdata['current']['periodNumber']) {
                    if ($res = checkgamebefore($key, $pkdata['current']['periodNumber']) !== 0) {
                        $pkdata['current']['awardNumbers'] = $res;
                    }
                    S('caiji_' . $key, $pkdata);
                    Data::$game_data[$key]['kjdata'] = $pkdata;
                    S('per_' . $key, $pkdata['current']['periodNumber']);
                }
            }
        }
    }
}

/*
 * 1,为6六合彩
 */
function getlhc()
{
    return getgamedata('lhc');
}

function getgamedata($game)
{
    $data = S('caiji_' . $game);
    Data::$game_data[$game]['kjdata'] = $data;
    $data['next']['awardTimeInterval'] = (strtotime($data['next']['awardTime']) - time()) * 1000;
    return $data;
}

/*
 * 保存游戏
 */
//function save($periodnumber, $codes, $awartime, $game = '')
//{
//    if($game =='lhc'){
//        if (!M('number')->where(array('periodnumber' => $periodnumber, 'game' => $game))->find()) {
//            $ins = array(
//                'periodnumber' => $periodnumber,
//                'time' => time(),
//                'game' => $game,
//                'awardnumbers' => $codes,
//                'awardtime' => $awartime,
//            );
//            M('number')->add($ins);
//        }
//    }
//}

//自己自己开奖
//随机10以内打乱重复的数组   01，02 ，05
function suiji($len = 10)
{
    $rand = '';
    for ($x = 0; $x < $len; $x++) {
        srand((double)microtime() * 1000000);
        $rand .= ($rand != '' ? ',' : '') . sprintf("%02d", mt_rand(0, 9));
    }
    return $rand;
}

// 时时彩随机开奖数字
function suiji_ssc($len = 5)
{
    $rand = '';
    for ($x = 0; $x < $len; $x++) {
        srand((double)microtime() * 1000000);
        $rand .= ($rand != '' ? ',' : '') . mt_rand(0, 9);
    }
    return $rand;
}

function suiji_kuai($len = 3)
{
    $rand = '';
    for ($x = 0; $x < $len; $x++) {
        srand((double)microtime() * 1000000);
        $rand .= ($rand != '' ? ',' : '') . mt_rand(1, 6);
    }
    return $rand;
}

//随机10以内
function suiji_pk()
{
    $data = "";
    $arr = range(1, 10);
    shuffle($arr);
    foreach ($arr as $values) {
        $data .= sprintf("%02d", $values) . ',';
    }
    $newstr = substr($data, 0, strlen($data) - 1);
    return $newstr;
}

//function getkuai3(){
//    $sum= time() -strtotime(date("Y-m-d"));
//    //拼接期数
//    $qishu = date("Ymd").intval($sum/60);
//    //当前开奖时间
//    $times = strtotime(date("Y-m-d"))+intval($sum/60)*60;//60为60s
//    //初始化
//    if(!S('kuai_jishu')){
//        $pkdata['time'] = time();
//        $pkdata['game'] = 'kuai';
//        $pkdata['current']['periodNumber'] = $qishu;
//        $pkdata['current']['awardTime'] =  date('Y-m-d H:i:s',$times);
//        $pkdata['current']['awardNumbers'] =suiji_kuai();
//        $pkdata['next']['periodNumber'] =$qishu+1;
//        $pkdata['next']['awardTime'] =date('Y-m-d H:i:s', $times+60);
//        $pkdata['next']['awardTimeInterval'] = (($times+60) - time()) * 1000;
//        $pkdata['next']['delayTimeInterval'] = "0";
//        S("kuai_jishu",$pkdata);
//    }
//    if ( S('kuai_jishu')['current']['periodNumber']!==$qishu){
//        $pkdata['time'] = time();
//        $pkdata['game'] = 'kuai';
//        $pkdata['current']['periodNumber'] = $qishu;
//        $pkdata['current']['awardTime'] = date('Y-m-d H:i:s',$times);
//        $pkdata['current']['awardNumbers'] =suiji_kuai();
//        $pkdata['next']['periodNumber'] =$qishu+1;
//        $pkdata['next']['awardTime'] =date('Y-m-d H:i:s', $times+60);
//        $pkdata['next']['awardTimeInterval'] = (($times+60) - time()) * 1000;
//        $pkdata['next']['delayTimeInterval'] = "0";
//        S("kuai_jishu",$pkdata);
//    }
//    $getkuai3 = S('kuai_jishu');
//    $getkuai3['next']['awardTimeInterval'] = (strtotime($getkuai3['next']['awardTime'])- time()) * 1000;
//    $getkuai3['next']['delayTimeInterval'] = "0";
//    return $getkuai3;
//
//}
//极速飞艇开奖
function get_feiting_jisu()
{
    $sum = time() - strtotime(date("Y-m-d"));
    //拼接期数
    $qishu = date("Ymd") . intval($sum / 60);
    //当前开奖时间
    $times = strtotime(date("Y-m-d")) + intval($sum / 60) * 60;//60为60s
    if (S('ft_jishu')['current']['periodNumber'] !== $qishu) {
        $pkdata['time'] = time();
        $pkdata['game'] = 'ft_jishu';
        $pkdata['current']['periodNumber'] = $qishu;
        $pkdata['current']['awardTime'] = $times;
        $pkdata['current']['awardNumbers'] = suiji();
        $pkdata['next']['periodNumber'] = $qishu + 1;
        $pkdata['next']['awardTime'] = $times + 60;
        $pkdata['next']['awardTimeInterval'] = (($times + 60) - time()) * 1000;
        $pkdata['next']['delayTimeInterval'] = 0;
        S("ft_jishu", $pkdata);
    }
    $getkuai3 = S('ft_jishu');
    $getkuai3['next']['awardTimeInterval'] = ($getkuai3['next']['awardTime'] - time()) * 1000;
    return $getkuai3;
}

//及自己开奖end
function show($msg, $status =1, $url = '')
{
    $data['msg'] = $msg;
    $data['status'] = $status;
    $data['url'] = $url;
    echo json_encode($data);
    exit;
}
function error($msg, $status =0, $url = '')
{
    $data['msg'] = $msg;
    $data['status'] = $status;
    $data['url'] = $url;
    echo json_encode($data);
    exit;
}

//前台获取游戏的倍率的方法
function getbvsum($sum)
{
    $datas = C('hezhi_bv');
    $arr = explode(',', $datas);
    $selectsum = $arr[$sum];
    $sumarr = explode('=', $selectsum);
    echo $sumarr[1];
}

//j加拿大获取 单个倍率
function jndgetbvsum($sum)
{
    $datas = C('jnd_hezhi_bv');
    $arr = explode(',', $datas);
    $selectsum = $arr[$sum];
    $sumarr = explode('=', $selectsum);
    echo $sumarr[1];
}

//大小单双
function getbvdxds($type)
{
    if ($type == "dx") {
        echo C('dan_dx');
    } elseif ($type == 'dxds') {
        $datag = C('dan_dxds');
        echo $datag;
    } elseif ($type == 'jz') {
        $datag = C('dan_jz');
        echo $datag;
    }
}

function jndgetbvdxds($type)
{
    if ($type == "dx") {
        echo C('jnd_dx');
    } elseif ($type == 'dxds') {
        $datag = C('jnd_dxds');
        echo $datag;
    } elseif ($type == 'jz') {
        $datag = C('jnd_jz');
        echo $datag;
    }
}

//获取名字
function getname($id)
{
    $data = M('user')->where(array('id' => $id))->find();
    return $data['nickname'];
}

////显示游戏
//function getgamename($type)
//{
//    switch ($type) {
//        case 'Bj28':
//            echo '北京28';
//            break;
//        case  "Jnd28":
//            echo "加拿大28";
//            break;
//        case 'jsssc':
//            echo "极速时时彩";
//            break;
//        case "Ssc":
//            echo '重庆时时彩';
//            break;
//        case 'jscar':
//            echo "极速赛车";
//            break;
//        case 'kuai3':
//            echo '江苏快3';
//            break;
//        case 'fei':
//            echo "幸运飞艇";
//            break;
//        case 'pk10':
//            echo "北京赛车";
//            break;
//    }
//}

/*
 * 根据id 查看余额
 */
function getpoints_byid($uid)
{
    $user = M('user')->where(array('id' => $uid))->find();
    return $user['points'];
}

function getPK10()
{
    //获取最新的开奖数据
    /*$num = M('number')->where("game = 'pk10'")->order('expect asc')->find();

    $data = [
        'kjdata'=>$num,
        "next_time"=>[]
    ];*/
    return getgamedata('pk10');
//    if($type =='update'){
////        $url = "http://api.1680210.com/pks/getLotteryPksInfo.do?issue=615652&lotCode=10001";
////        $result = curlGet($url);
////        $data = json_decode($result, true);
////        $data = $data['result']['data'];
////        $pkdata['time'] = time();
////        $pkdata['game'] = 'bjpks';
////        $pkdata['current']['periodNumber'] = $data['preDrawIssue'];
////        $pkdata['current']['awardTime'] = $data['preDrawTime'];
////        $pkdata['current']['awardNumbers'] = $data['preDrawCode'];
////        $pkdata['next']['periodNumber'] = $data['drawIssue'];
////        $pkdata['next']['awardTime'] = $data['drawTime'];
////        $pkdata['next']['awardTimeInterval'] = (strtotime($data['drawTime']) - time()) * 1000;
////        $pkdata['next']['delayTimeInterval'] = 0;
///
///
//        $url = "http://api.kaijiangtong.com/lottery/?name=bjpks&format=json&uid=886668&token=3282b639974ac3fb927b5307dc5575344af0bdc6";
//        $result = curlGet($url);
//        $data = json_decode($result, true);
//        $first =current($data);
//        $qihaos =  array_keys($data);
//        $qihao = $qihaos[0];
//        //传输的数据名称：
//        $jnddata['time'] = time();
//        $jnddata['game'] = 'bjpk10';
//        $jnddata['current']['periodNumber'] = $qihao;
//        $jnddata['current']['awardTime'] = $first['dateline'];
//        if($res =checkgamebefore('pk10',$qihao) !==0){
//            $first['number'] = checkgamebefore('pk10',$qihao);
//        }
//        $jnddata['current']['awardNumbers'] =$first['number'];
//        $jnddata['next']['periodNumber'] = $qihao + 1;
//        $begin1 = strtotime('23:57:30');
//        $end1= strtotime("23:59:59");
//        $begin = strtotime('00:00:00');
//        $end= strtotime("09:00:00");
//        if(time()>$begin&&time()<$end  || time()>$begin1&&time()<$end1){
//            $jnddata['next']['awardTime'] = date("Y-m-d H:i:s", (strtotime($first['dateline']) + 32950));
//            $jnddata['next']['awardTimeInterval'] = ((strtotime($first['dateline']) + 32950) - time()) * 1000;
//        }else{
//            $jnddata['next']['awardTime'] = date("Y-m-d H:i:s", (strtotime($first['dateline']) + 260));
//            $jnddata['next']['awardTimeInterval'] = ((strtotime($first['dateline']) + 260) - time()) * 1000;
//        }
//        $jnddata['next']['delayTimeInterval'] = 0;
//        //防止偶然性没有获取到的错误
//        if($jnddata['current']['periodNumber']){
//            F('cachepk10',$jnddata);
//        }else{
//            $jnddata = F('cachepk10');
//        }
//        S('newcachepk10',$jnddata);
//    }else{
//        $pk10data = S('newcachepk10');
//        $pk10data['next']['awardTimeInterval'] = (strtotime($pk10data['next']['awardTime']) - time()) * 1000;
//        return $pk10data;
//    }

}

//北京28
function getBj28()
{
    return getgamedata('bj28');
//    $begin = strtotime('00:00:00');
//    $end = strtotime("08:59:59");
//            $result = S('bj28data');
//        if ($type =='update'){
//            $url = "http://api.kaijiangtong.com/lottery/?name=bjklb&format=json&uid=886668&token=3282b639974ac3fb927b5307dc5575344af0bdc6";
//            $result = curlGet($url);
////        S('bj28data', $result, 5);
////    }
//            $data = json_decode($result, true);
//            $first =current($data);
//            $qihaos =  array_keys($data);
//            $qihao = $qihaos[0];
//            $haoma = explode(',', $first['number']);
//            $n1 = $haoma['0'] + $haoma['1'] + $haoma['2'] + $haoma['3'] + $haoma['4'] + $haoma['5'];
//            $n2 = $haoma['6'] + $haoma['7'] + $haoma['8'] + $haoma['9'] + $haoma['10'] + $haoma['11'];
//            $n3 = $haoma['12'] + $haoma['13'] + $haoma['14'] + $haoma['15'] + $haoma['16'] + $haoma['17'];
//            $num1 = str_split($n1);
//            $num2 = str_split($n2);
//            $num3 = str_split($n3);
//            $number1 = $num1[count($num1) - 1];
//            $number2 = $num2[count($num2) - 1];
//            $number3 = $num3[count($num3) - 1];
//            //传输的数据名称：
//            $jnddata['time'] = time();
//            $jnddata['game'] = 'bj28';
//            $jnddata['current']['periodNumber'] = $qihao;
//            $jnddata['current']['awardTime'] = $first['dateline'];
//            $jnddata['current']['awardNumbers'] = $number1 . ',' . $number2 . ',' . $number3;
//            if($res =checkgamebefore('Bj28',$qihao) !==0){
//                $jnddata['current']['awardNumbers'] = checkgamebefore('Bj28',$qihao);
//            }
//            $jnddata['next']['periodNumber'] = $qihao + 1;
//            $jnddata['next']['awardTime'] = date("Y-m-d H:i:s", (strtotime($first['dateline']) + 250));
//            $jnddata['next']['awardTimeInterval'] = ((strtotime($first['dateline']) + 250) - time()) * 1000;
//            $jnddata['next']['delayTimeInterval'] = 0;
//
//            if ($result) {
//               F('cachebj28',$jnddata);
//            } else {
//                $jnddata = F('cachebj28');
//            }
//            S('newcachebj28',$jnddata);
//        }else{
//            $bj28data = S('newcachebj28');
//            $bj28data['next']['awardTimeInterval'] = (strtotime($bj28data['next']['awardTime']) - time()) * 1000;
//            return $bj28data;
//             }

//    北京28直接运用款-----------------------↓--------------------------↓---------------------
//    $result = S('dandata');
//    $begin = strtotime('11:58:00');
//    $end = strtotime("11:59:59");
//    if($begin<time() && time()<$end){
//        if (empty($result)) {
//        $url = "http://api.1680210.com/LuckTwenty/getPcLucky28.do?issue=";
//        $result = curlGet($url);
//        S('dandata', $result, 1);
//        }
//    }else{
//        if (empty($result)) {
//            $url = "http://api.1680210.com/LuckTwenty/getPcLucky28.do?issue=";
//            $result = curlGet($url);
//            S('dandata', $result, 5);
//        }
//    }
//    $data = json_decode($result, true);
//    $data = $data['result']['data'];
//    $dandata['time'] = time();
//    $dandata['game'] = 'pc28';
//    $dandata['current']['periodNumber'] = $data['preDrawIssue'];
//    $dandata['current']['awardTime'] = $data['preDrawTime'];
//    $dandata['current']['awardNumbers'] = $data['preDrawCode'];
//    $dandata['next']['periodNumber'] = $data['drawIssue'];
//    $dandata['next']['awardTime'] = $data['drawTime'];
//    $dandata['next']['awardTimeInterval'] = (strtotime($data['drawTime']) - time()) * 1000;
//    $dandata['next']['delayTimeInterval'] = 0;
//    $dandata['test'] = strtotime("2017-06-21 15:05:00") - time();
//    //防止偶然性没有获取到的错误
//    if($dandata['current']['periodNumber']){
//        session("bjse",$dandata);
//    }else{
//        $jnddata = session("bjse");
//    }
//    return $dandata;
}

//加拿大28
function getJnd28()
{
    return getgamedata('jnd28');
    //168采集的数据
//    $result = S('jnd28data');
//    if($type == 'update'){
//        //    if (empty($result)) {
//        $url = "http://ashtbzj.com/home/api/getJnd28";
//        $result = curlGet($url);
////        S('jnd28data', $result, 5);
////    }
//        $data = json_decode($result, true);
//        $jnddata =$data;
//        if ($result){
//            F('cachejnd',$jnddata);
//        }else{
//            $jnddata = F('cachejnd');
//        }
//        Data::$game_data['jnd28']['kjdata']=$jnddata;
//        S('newcachejnd',$jnddata);
//    }else{
//        $jnddata = S('newcachejnd');
//        Data::$game_data['jnd28']['kjdata']=$jnddata;
//        $jnddata['next']['awardTimeInterval'] = (strtotime($jnddata['next']['awardTime']) - time()) * 1000;
//        return $jnddata;
//    }
//下面是获取彩票控的---------------------------------------------------------------------------------------
//    $result = S('jnd28data');
//    if($type == 'update'){
//        //    if (empty($result)) {
//        $url = "http://api.kaijiangtong.com/lottery/?name=jndklb&format=json&uid=886668&token=3282b639974ac3fb927b5307dc5575344af0bdc6";
//        $result = curlGet($url);
////        S('jnd28data', $result, 5);
////    }
//        $data = json_decode($result, true);
//        $first =current($data);
//        $qihaos =  array_keys($data);
//        $qihao = $qihaos[0];
//        $haoma = explode(',', $first['number']);
//        $n1 = $haoma['1'] + $haoma['4'] + $haoma['7'] + $haoma['10'] + $haoma['13'] + $haoma['16'];
//        $n2 = $haoma['2'] + $haoma['5'] + $haoma['8'] + $haoma['11'] + $haoma['14'] + $haoma['17'];
//        $n3 = $haoma['3'] + $haoma['6'] + $haoma['9'] + $haoma['12'] + $haoma['15'] + $haoma['18'];
//        $num1 = str_split($n1);
//        $num2 = str_split($n2);
//        $num3 = str_split($n3);
//        $number1 = $num1[count($num1) - 1];
//        $number2 = $num2[count($num2) - 1];
//        $number3 = $num3[count($num3) - 1];
//        //传输的数据名称：
//        $jnddata['time'] = time();
//        $jnddata['game'] = 'bj28';
//        $jnddata['current']['periodNumber'] = $qihao;
//        $jnddata['current']['awardTime'] = $first['dateline'];
//        $jnddata['current']['awardNumbers'] = $number1 . ',' . $number2 . ',' . $number3;
//        if($res =checkgamebefore('Jnd28',$qihao) !==0){
//            $jnddata['current']['awardNumbers'] = checkgamebefore('Jnd28',$qihao);
//        }
//        $jnddata['next']['periodNumber'] = $qihao + 1;
//        $jnddata['next']['awardTime'] = date("Y-m-d H:i:s", (strtotime($first['dateline']) + 180));
//        $jnddata['next']['awardTimeInterval'] = ((strtotime($first['dateline']) + 180) - time()) * 1000;
//        $jnddata['next']['delayTimeInterval'] = 0;
//        if ($result){
//            F('cachejnd',$jnddata);
//        }else{
//            $jnddata = F('cachejnd');
//        }
//        S('newcachejnd',$jnddata);
//    }else{
//        $jnddata = S('newcachejnd');
//        $jnddata['next']['awardTimeInterval'] = (strtotime($jnddata['next']['awardTime']) - time()) * 1000;
//        return $jnddata;
//    }
}

//時時彩
function getssc()
{
    return getgamedata('ssc');
//    if ($type == 'update'){
//        //    $result = S('sscdata');
////    if (empty($result)) {
//        $url = "http://api.kaijiangtong.com/lottery/?name=cqssc&format=json&uid=886668&token=3282b639974ac3fb927b5307dc5575344af0bdc6";
//        $result = curlGet($url);
////        S('sscdata', $result, 5);
////    }
//        $data = json_decode($result, true);
//        $first =current($data);
//        $qihaos =  array_keys($data);
//        $qihao = $qihaos[0];
//        $pkdata['time'] = time();
//        $pkdata['game'] = 'cqssc';
//        $pkdata['current']['periodNumber'] = $qihao;
//        $pkdata['current']['awardTime'] = $first['dateline'];
//        if($res =checkgamebefore('ssc',$qihao) !==0){
//            $first['number'] = checkgamebefore('ssc',$qihao);
//        }
//        $pkdata['current']['awardNumbers'] = $first['number'];
//        if(substr($pkdata['current']['periodNumber'],-3) ==120){
//            $pkdata['next']['periodNumber'] =date('Ymd').'001';
//        }else{
//            $pkdata['next']['periodNumber'] = $qihao+1;
//        }
//
//        $pkdata['next']['awardTime'] = date("Y-m-d H:i:s", (strtotime($first['dateline']) + 550));
//        $pkdata['next']['awardTimeInterval'] = ((strtotime($first['dateline']) + 550) - time()) * 1000;
//        $pkdata['next']['delayTimeInterval'] = 0;
//        if ($result){
//            F('cachessc',$pkdata);
//        }else{
//            $pkdata = F('cachessc');
//        }
//        S('newcachessc',$pkdata);
//    }else{
//        $getssc = S('newcachessc');
//        $getssc['next']['awardTimeInterval'] = (strtotime($getssc['next']['awardTime']) - time()) * 1000;
//        return $getssc;
//    }
}

function getkuai3()
{
    return getgamedata('kuai3');
//    if ($type =='update'){
////    $result = S('kuai3');
////    if (empty($result)) {
//        $url = "http://api.kaijiangtong.com/lottery/?name=jsks&format=json&uid=886668&token=3282b639974ac3fb927b5307dc5575344af0bdc6";
//        $result = curlGet($url);
////        S('kuai3', $result, 5);
////    }
//        $data = json_decode($result, true);
//        $first =current($data);
//        $qihaos =  array_keys($data);
//        $qihao = $qihaos[0];
//        $pkdata['time'] = time();
//        $pkdata['game'] = 'kuai3';
//        $pkdata['current']['periodNumber'] = $qihao;
//        $pkdata['current']['awardTime'] = $first['dateline'];
//        if($res =checkgamebefore('kuai3',$qihao) !==0){
//            $first['number'] = checkgamebefore('kuai3',$qihao);
//        }
//        $pkdata['current']['awardNumbers'] = $first['number'];
//        if(substr($pkdata['current']['periodNumber'],-2) ==82){
//            if(time()>strtotime('20:30:00')){
//                $pkdata['next']['periodNumber'] =date('ymd',strtotime('+1day')).'001';
//
//            }elseif(time()<strtotime('08:40:00')){
//                $pkdata['next']['periodNumber'] =date('ymd').'001';
//            }
//
//        }else{
//            $pkdata['next']['periodNumber'] = $qihao+1;
//        }
//
//        $pkdata['next']['periodNumber'] = $qihao+1;
//        $end = strtotime('22:09:00');
//        $begin= strtotime("08:39:00");
//        if(time()>$begin&&time()<$end) {
//            $pkdata['next']['awardTime'] = date("Y-m-d H:i:s", (strtotime($first['dateline']) + 550));
//            $pkdata['next']['awardTimeInterval'] = ((strtotime($first['dateline']) + 550) - time()) * 1000;
//        }else{
//            $pkdata['next']['awardTime'] = date("Y-m-d H:i:s", (strtotime($first['dateline']) + 37800));
//            $pkdata['next']['awardTimeInterval'] = ((strtotime($first['dateline']) + 37800) - time()) * 1000;
//        }
//        $pkdata['next']['delayTimeInterval'] = 0;
//        if($pkdata['current']['periodNumber']){
//            F('cachekuai3',$pkdata);
//        }else{
//            $pkdata = F('cachekuai3');
//            $pkdata['next']['awardTimeInterval'] = (strtotime($pkdata['next']['awardTime']) - time()) * 1000;
//        }
//        S('newcachekuai3',$pkdata);
//    }else{
//        $getkuai3 = S('newcachekuai3');
//        $getkuai3['next']['awardTimeInterval'] = (strtotime($getkuai3['next']['awardTime']) - time()) * 1000;
//        return $getkuai3;
//    }
}

function getfei()
{
    return getgamedata('fei');

    //$t =  getgamedata('fei');
    //return $t;

    //pk101网站关闭接口
//    if ($type =='update'){
//        $url = "http://www-pk101.com/api/newest?code=xyft&t=1505111133138";
//        $result = curlGet($url);
//        $datas = json_decode($result, true);
//        $datas = $datas['data'];
//        $pkdata['time'] = time();
//        $pkdata['game'] = 'fei';
//        $pkdata['current']['periodNumber'] = $datas['newest']['issue'];
//        $pkdata['current']['awardTime'] = $datas['newest']['time'];
//        $pkdatanum = $datas['newest']['code'];
//        $dataarr = explode(',',$pkdatanum);
//        $datasarr = '';
//        for($i = 0;$i<count($dataarr);$i++){
//            $datasarr = $datasarr.sprintf("%02d", $dataarr[$i]).',';
//        }
//        //去掉末尾的逗號
//        $pkdata['current']['awardNumbers'] = substr($datasarr, 0, -1);
//        $pkdata['next']['periodNumber'] =$datas['current'];
//        $pkdata['next']['awardTime'] = date('Y-m-d H:i:s',(strtotime($datas['newest']['time']) +300));
//        $pkdata['next']['awardTimeInterval'] = ((strtotime($datas['newest']['time']) +300) - time()) * 1000;
//        $pkdata['next']['delayTimeInterval'] = 0;
//        if($result){
//            F('cachefei',$pkdata);
//        }else{
//            $pkdata = F('cachefei');
//            $pkdata['next']['awardTimeInterval'] = ((strtotime($pkdata['current']['awardTime'])+300) - time()) * 1000;
//        }
//        S('newcachefei',$pkdata);
//    }else{
//        $getkuai3 = S('newcachefei');
//        $getkuai3['next']['awardTimeInterval'] =((strtotime($getkuai3['current']['awardTime'])+300) - time()) * 1000;
//        return $getkuai3;
//    }
    //下为pk10 作为测试
//    if($type =='update'){
//        $url = "http://api.kaijiangtong.com/lottery/?name=xyft&format=json&uid=886668&token=3282b639974ac3fb927b5307dc5575344af0bdc6";
//        $result = curlGet($url);
//        $data = json_decode($result, true);
//        $first =current($data);
//        $qihaos =  array_keys($data);
//        $qihao = $qihaos[0];
//        //传输的数据名称：
//        $jnddata['time'] = time();
//        $jnddata['game'] = 'xyft';
//        $jnddata['current']['periodNumber'] = $qihao;
//        $jnddata['current']['awardTime'] = $first['dateline'];
//        $jnddata['current']['awardNumbers'] =$first['number'];
//        $jnddata['next']['periodNumber'] = $qihao + 1;
//        $begin = strtotime('04:04:00');
//        $end= strtotime("13:04:00");
//        if(time()>$begin&&time()<$end){
//            $jnddata['next']['awardTime'] = date("Y-m-d H:i:s", (strtotime($first['dateline']) + 32700));
//            $jnddata['next']['awardTimeInterval'] = ((strtotime($first['dateline']) + 32700) - time()) * 1000;
//        }else{
//            $jnddata['next']['awardTime'] = date("Y-m-d H:i:s", (strtotime($first['dateline']) + 260));
//            $jnddata['next']['awardTimeInterval'] = ((strtotime($first['dateline']) + 260) - time()) * 1000;
//        }
//        $jnddata['next']['delayTimeInterval'] = 0;
//        //防止偶然性没有获取到的错误
//        if($jnddata['current']['periodNumber']){
//            F('cachefei',$jnddata);
//        }else{
//            $jnddata = F('cachefei');
//        }
//        S('newcachefei',$jnddata);
//    }else{
//        $pk10data = S('newcachefei');
//        $pk10data['next']['awardTimeInterval'] = (strtotime($pk10data['next']['awardTime']) - time()) * 1000;
//        return $pk10data;
//    }
}

//极速赛车开奖
function getjscar()
{
    return getgamedata('jscar');

    /*$sum = time() - strtotime(date("Y-m-d"));
    //拼接期数
    $qishu = date("ymd") . sprintf("%03d", intval($sum / 120));
    //当前开奖时间
    $times = strtotime(date("Y-m-d")) + intval($sum / 120) * 120;//60为60s
    //初始化
    $data = S('caiji_jscar');
    if ($data['current']['periodNumber'] != $qishu) {
        $pkdata['time'] = time();
        $pkdata['game'] = 'jscar';
        $pkdata['current']['periodNumber'] = $qishu;
        $pkdata['current']['awardTime'] = date('Y-m-d H:i:s', $times);
        $pkdata['current']['awardNumbers'] = suiji_pk();
        if ($res = checkgamebefore('jscar', $qishu) !== 0) {
            $pkdata['current']['awardNumbers'] = checkgamebefore('jscar', $qishu);
        }
        if (substr($pkdata['current']['periodNumber'], -3) == '000') {
            if (time() < strtotime('00:30:00')) {
                $pkdata['current']['periodNumber'] = date("ymd") . '720';
                $pkdata['next']['periodNumber'] = date("ymd") . '001';
            }
        } else {
            $pkdata['next']['periodNumber'] = $qishu + 1;
        }
        $pkdata['next']['awardTime'] = date('Y-m-d H:i:s', $times + 120);
        $pkdata['next']['awardTimeInterval'] = (($times + 120) - time()) * 1000;
        $pkdata['next']['delayTimeInterval'] = "0";
        S('caiji_jscar', $pkdata);
        Data::$game_data['jscar']['kjdata'] = $pkdata;
        return $pkdata;
    }
    Data::$game_data['jscar']['kjdata'] = $data;
    $data['next']['awardTimeInterval'] = (strtotime($data['next']['awardTime']) - time()) * 1000;
    $data['next']['delayTimeInterval'] = "0";
    return $data;*/
}

//极速时时彩开奖
function getjsssc()
{
    $data =  getgamedata('jsssc');
    if($data['game'] == 'jsssc')
    {
        return $data;
    } else {
        return '';
    }
    /*$sum = time() - strtotime(date("Y-m-d"));
    //拼接期数
    $qishu = date("Ymd") . sprintf("%03d", intval($sum / 120));
    $qishu = substr($qishu, 2);
    //当前开奖时间
    $times = strtotime(date("Y-m-d")) + intval($sum / 120) * 120;//60为60s
    $pkdata = S('caiji_jsssc');
    if ($pkdata['current']['periodNumber'] !== $qishu) {
        $pkdata['time'] = time();
        $pkdata['game'] = 'bjpks';
        $pkdata['current']['periodNumber'] = $qishu;
        $pkdata['current']['awardTime'] = date('Y-m-d H:i:s', $times);
        $pkdata['current']['awardNumbers'] = suiji_ssc();
        if ($res = checkgamebefore('jsssc', $qishu) !== 0) {
            $pkdata['current']['awardNumbers'] = checkgamebefore('jsssc', $qishu);
        }
        if (substr($pkdata['current']['periodNumber'], -3) == '000') {
            if (time() < strtotime('00:30:00')) {
                $pkdata['current']['periodNumber'] = date("ymd") . '720';
                $pkdata['next']['periodNumber'] = date("ymd") . '001';
            }
        } else {
            $pkdata['next']['periodNumber'] = $qishu + 1;
        }
        $pkdata['next']['awardTime'] = date('Y-m-d H:i:s', $times + 120);
        $pkdata['next']['awardTimeInterval'] = (($times + 120) - time()) * 1000;
        $pkdata['next']['delayTimeInterval'] = "0";
        S('caiji_jsssc', $pkdata);
        Data::$game_data['jsssc']['kjdata'] = $pkdata;
        return $pkdata;
    }
    Data::$game_data['jsssc']['kjdata'] = $pkdata;
    $pkdata['next']['awardTimeInterval'] = (strtotime($pkdata['next']['awardTime']) - time()) * 1000;
    $pkdata['next']['delayTimeInterval'] = "0";
    return $pkdata;*/
}

//赔率整串分割   1=2,2=3,  ....or     猪:20   分隔符为:  or =
function split_pv($value, $fengefu)
{
    $shengxiao_arr = explode(',', $value);
    $shengxiao_view = array();
    foreach ($shengxiao_arr as $key => $value) {
        $exp = explode($fengefu, $value);
        $shengxiao_view[$exp[0]] = $exp[1];
    }
    return $shengxiao_view;
}

//六合彩
//查看六合彩所有的开奖的生肖全部
function get_all_shengxiao($periodnumber)
{
    $arr = explode(',', $periodnumber);
    $list = array();
    foreach ($arr as $value) {
        $res = seeshengxiao($value);
        array_push($list, $res);
    }
    /*
     *  return array()
     */
    return $list;
}

//查看六合彩所有的开奖的生肖单个
function seeshengxiao($tema)
{
    //查看当前开的是什么生肖
    $lhc_data = json_decode(M('game_config')->where("id=1")->getField("lhc"), true);
    $jinnian = $lhc_data['shengxiao'];
    $jinnianling = '';
    $arr = ['鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊', '猴', '鸡', '狗', '猪'];
    foreach ($arr as $key => $value) {
        if ($jinnian == $value) {
            $jinnianling = $key;
        }
    }
    $sum = 0;
    $chuli = '';
    while ($sum < $tema) {
        $chuli = $jinnianling;
        $jinnianling--;
        if ($jinnianling < 0) {
            $jinnianling = 11;
        }
        $sum++;
    }
    return $arr[$chuli];
}

//读取配置
function C_set($data)
{
    $config = S('DB_CONFIG_SITE');
    if (!$config) {
        $config = M('config')->where("id", 1)->find();
        S('DB_CONFIG_SITE', $config);
    }
    return $config[$data];
}

//游戏运行配置，eg：on_off  ,robot_on_off ,
function C_set2()
{
    $datas = F('GAME_CONFIG_2');
    if (!$datas) {
        $config = M('game_config')->where(array('id' => 2))->find();
        $datas = '';
        foreach ($config as $key => $value) {
            $datas[$key] = json_decode($value, true);
        }
    }
    return $datas;
}


function guolv($str)
{
    $str = preg_replace("@<script(.*?)</script>@is", "", $str);
    $str = preg_replace("@<iframe(.*?)</iframe>@is", "", $str);
    $str = preg_replace("@<style(.*?)</style>@is", "", $str);
    $str = preg_replace("@<(.*?)>@is", "", $str);
    //# 代表换行
    $str = str_replace("#", "<br>", $str);
    return $str;
}

function send_to_web($array)
{
    // 指明给谁推送，为空表示向所有在线用户推送
    // 推送的url地址，上线时改成自己的服务器地址
    $push_api_url = C('push_api_url');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $push_api_url);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $array);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    $return = curl_exec($ch);
    curl_close($ch);
    return $return;
}

/*
 * 删除缓存方法
 */
function delFileByDir($dir)
{
    $dh = opendir($dir);
    while ($file = readdir($dh)) {
        if ($file != "." && $file != "..") {

            $fullpath = $dir . "/" . $file;
            if (is_dir($fullpath)) {
                delFileByDir($fullpath);
            } else {
                unlink($fullpath);
            }
        }
    }
    closedir($dh);
}

function is_ch($ip)
{
    $data = GetIpLookup($ip);
    if ($data['country'] == '中国') {
        return 1;
    } else {
        return 0;
    }
}

function GetIpLookup($ip = '')
{
    if (empty($ip)) {
        $ip = GetIp();
    }
    $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);
    if (empty($res)) {
        return false;
    }
    $jsonMatches = array();
    preg_match('#\{.+?\}#', $res, $jsonMatches);
    if (!isset($jsonMatches[0])) {
        return false;
    }
    $json = json_decode($jsonMatches[0], true);
    if (isset($json['ret']) && $json['ret'] == 1) {
        $json['ip'] = $ip;
        unset($json['ret']);
    } else {
        return false;
    }
    return $json;
}

/*post请求获取数据*/
function curlPost($url, $timeout = 5)
{
    if (function_exists('file_get_contents')) {
        $optionget = array('http' => array('method' => "GET", 'header' => "User-Agent:Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.5.21022; .NET CLR 3.0.04506; CIBA)\r\nAccept:*/*\r\nReferer:https://kyfw.12306.cn/otn/lcxxcx/init"));
        $file_contents = file_get_contents($url, false, stream_context_create($optionget));
    } else {
        $ch = curl_init();
        $header = array('Accept:*/*', 'Accept-Charset:GBK,utf-8;q=0.7,*;q=0.3', 'Accept-Encoding:gzip,deflate,sdch', 'Accept-Language:zh-CN,zh;q=0.8,ja;q=0.6,en;q=0.4', 'Connection:keep-alive', 'Host:kyfw.12306.cn', 'Referer:https://kyfw.12306.cn/otn/lcxxcx/init',);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "http://1680218.com");
        $file_contents = curl_exec($ch);
        curl_close($ch);
    }
    $file_contents = json_decode($file_contents, true);
    return $file_contents;
}

/*get请求获取数据*/
function curlGet($url)
{
    $ch = curl_init();
    $this_header = array("content-type: application/x-www-form-urlencoded; charset=UTF-8");
    //设置选项，包括URL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
//    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36');
//    curl_setopt ($ch, CURLOPT_REFERER, "http://1680118.com/html/kuai3_fujian/kuai3_index.html");
//    curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
    //执行并获取HTML文档内容
    $output = curl_exec($ch);
    //释放curl句柄
    curl_close($ch);
    return $output;

}

/*
	中国国情下的判断浏览器类型，简直就是五代十国，乱七八糟，对博主的收集表示感谢

	参考：
	http://www.cnblogs.com/wangchao928/p/4166805.html
	http://www.useragentstring.com/pages/Internet%20Explorer/
	https://github.com/serbanghita/Mobile-Detect/blob/master/Mobile_Detect.php

	Mozilla/4.0 (compatible; MSIE 5.0; Windows NT)
	Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)
	Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.2)
	Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)

	Win7+ie9：
	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; Tablet PC 2.0; .NET4.0E)

	win7+ie11，模拟 78910 头是一样的
	mozilla/5.0 (windows nt 6.1; wow64; trident/7.0; rv:11.0) like gecko

	Win7+ie8：
	Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; InfoPath.3)

	WinXP+ie8：
	Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; GTB7.0)

	WinXP+ie7：
	Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)

	WinXP+ie6：
	Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)

	傲游3.1.7在Win7+ie9,高速模式:
	Mozilla/5.0 (Windows; U; Windows NT 6.1; ) AppleWebKit/534.12 (KHTML, like Gecko) Maxthon/3.0 Safari/534.12

	傲游3.1.7在Win7+ie9,IE内核兼容模式:
	Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; .NET4.0E)

	搜狗
	搜狗3.0在Win7+ie9,IE内核兼容模式:
	Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; .NET4.0E; SE 2.X MetaSr 1.0)

	搜狗3.0在Win7+ie9,高速模式:
	Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.472.33 Safari/534.3 SE 2.X MetaSr 1.0

	360
	360浏览器3.0在Win7+ie9:
	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; .NET4.0E)

	QQ 浏览器
	QQ 浏览器6.9(11079)在Win7+ie9,极速模式:
	Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.41 Safari/535.1 QQBrowser/6.9.11079.201

	QQ浏览器6.9(11079)在Win7+ie9,IE内核兼容模式:
	Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; .NET4.0E) QQBrowser/6.9.11079.201

	阿云浏览器
	阿云浏览器 1.3.0.1724 Beta 在Win7+ie9:
	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)

	MIUI V5
	Mozilla/5.0 (Linux; U; Android <android-version>; <location>; <MODEL> Build/<ProductLine>) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30 XiaoMi/MiuiBrowser/1.0
*/
function get__browser()
{
    // 默认为 chrome 标准浏览器
    $browser = array(
        'device' => 'pc', // pc|mobile|pad
        'name' => 'chrome', // chrome|firefox|ie|opera
        'version' => 30,
    );
    $agent = $_SERVER['HTTP_USER_AGENT'];
    // 主要判断是否为垃圾 IE6789
    if (strpos($agent, 'msie') !== FALSE || stripos($agent, 'trident') !== FALSE) {
        $browser['name'] = 'ie';
        $browser['version'] = 8;
        preg_match('#msie\s*([\d\.]+)#is', $agent, $m);
        if (!empty($m[1])) {
            if (strpos($agent, 'compatible; msie 7.0;') !== FALSE) {
                $browser['version'] = 8;
            } else {
                $browser['version'] = intval($m[1]);
            }
        } else {
            // 匹配兼容模式 Trident/7.0，兼容模式下会有此标志 $trident = 7;
            preg_match('#Trident/([\d\.]+)#is', $agent, $m);
            if (!empty($m[1])) {
                $trident = intval($m[1]);
                $trident == 4 AND $browser['version'] = 8;
                $trident == 5 AND $browser['version'] = 9;
                $trident > 5 AND $browser['version'] = 10;
            }
        }
    }

    if (isset($_SERVER['HTTP_X_WAP_PROFILE']) || (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap") || stripos($agent, 'phone') || stripos($agent, 'mobile') || strpos($agent, 'ipod'))) {
        $browser['device'] = 'mobile';
    } elseif (strpos($agent, 'pad') !== FALSE) {
        $browser['device'] = 'pad';
        $browser['name'] = '';
        $browser['version'] = '';
        /*
        } elseif(strpos($agent, 'miui') !== FALSE) {
            $browser['device'] = 'mobile';
            $browser['name'] = 'xiaomi';
            $browser['version'] = '';
        */
    } else {
        $robots = array('bot', 'spider', 'slurp');
        foreach ($robots as $robot) {
            if (strpos($agent, $robot) !== FALSE) {
                $browser['name'] = 'robot';
                return $browser;
            }
        }
    }
    return $browser;
}

//获取色波
function get_lhc_sebo($tema)
{
    //当期的色波
    $sebo = '';
    $redbo = '1,2,7,8,12,13,18,19,23,24,29,30,34,35,40,45,46';
    $bluebo = '3,4,9,10,14,15,20,25,26,31,36,37,41,42,47,48';
    //$greebo ='5,6,11,16,17,21,22,27,28,32,33,38,39,43,44,49';
    if (in_array($tema, explode(',', $redbo))) {
        $sebo = "红";
    } elseif (in_array($tema, explode(',', $bluebo))) {
        $sebo = '蓝';
    } else {
        $sebo = "绿";
    }
    return $sebo;
}

//获取五行
function get_lhc_wuxing($tema)
{
    $wuxing = '';
    //获取当期的金木水火土
    $wuxingjin = '3,4,17,18,25,26,33,34,47,48';
    $wuxingmu = '7,8,15,16,29,30,37,38,45,46';
    $wuxingshui = '5,6,13,14,21,22,35,36,43,44';
    $wuxingtu = '11,12,19,20,27,28,41,42,49';
//             $wuxinghuo ='1,2,9,10,23,24,31,32,39,40';
    if (in_array($tema, explode(',', $wuxingjin))) {
        $wuxing = "金";
    } elseif (in_array($tema, explode(',', $wuxingmu))) {
        $wuxing = '木';
    } elseif (in_array($tema, explode(',', $wuxingshui))) {
        $wuxing = '水';
    } elseif (in_array($tema, explode(',', $wuxingtu))) {
        $wuxing = '土';
    } else {
        $wuxing = "火";
    }
    return $wuxing;
}

//获取类的开奖数据
function getgamekjdata($game)
{
    if (empty(Data::$game_data[$game]['kjdata'])) {
        $fun = "get" . $game;
        $fun();
    }
    return Data::$game_data[$game]['kjdata'];
}






?>



