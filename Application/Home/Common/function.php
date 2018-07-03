<?php
use QL\QueryList;
/*
 * 竞猜格式检测
 * */

function C_config($data){
    $data_arr =F('peilv_arr');
    if(!$data_arr){
        $config = S('DB_CONFIG_GAME_DATA');
        if (!$config) {
            $config = M('game_config')->where("id", 1)->find();
            S('DB_CONFIG_GAME_DATA', $config);
        }
        $data_arr =array();
        foreach ($config as $key => $value) {
            if ($key != "id") {
                $exp =json_decode($value,true);
                foreach ($exp as $a=>$b){
                    $data_arr[$a]=$b;
                }
            }
        }
        F('peilv_arr',$data_arr);
    }
   return $data_arr[$data];
}

function check_format_pk10($message,$id)
{
    //查询获取数据库中当期中的所有值
    $dankaijiangdata = getPK10();
    $dankaijiangqihao =  $dankaijiangdata['next']['periodNumber'];
    $wheres =array(
        'number' =>$dankaijiangqihao,
        'state'=>1,
        'userid'=>$id,
        'game'=>'pk10',
    );
    //单局最高的金额
    $alljine =M('order')->where($wheres)->sum('del_points');
    $data['error'] = 1;
    //车号大小单双(20-20,000)
    // 双/100 = 1~5车道买双各$100 = 总$500
    if (preg_match('/^(?!\d*?(\d)\d*?\1)\d{1,10}+\/{1}+(大|双|小|单){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>1,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ( $info[2] * strlen($info[0]) >= C_config('pk10_zuidi') &&  $info[2] * strlen($info[0])+$xiazhujinetype1 <= C_config('pk10_dxds_bv') && $info[2] * strlen($info[0]) +$alljine <=C_config('pk10_max_bv')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2] * strlen($info[0]);
            $data['type'] = 1;
        }else {
            $data['error'] = 0;
            $data['money'] = C_config('pk10_zuidi').'-'.C_config('pk10_dxds_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('pk10_max_bv');
        }
    }

    //车号(20-20,000)
    // 12345/89/20 = 1~5车道的8号、9号各买$20 = 总$200
    if (preg_match('/^(?!\d*?(\d)\d*?\1)\d{1,10}+\/{1}+(?!\d*?(\d)\d*?\1)\d{1,10}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>3,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info[2] * strlen($info[0]) * strlen($info[1]) >=  C_config('pk10_zuidi') && $info[2] * strlen($info[0]) * strlen($info[1])+$xiazhujinetype1 <= C_config('pk10_shuzi_bv') &&$info[2] * strlen($info[0]) * strlen($info[1])+$alljine<=C_config('pk10_max_bv')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2] * strlen($info[0]) * strlen($info[1]);
            $data['type'] = 3;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('pk10_zuidi').'-'.C_config('pk10_shuzi_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('pk10_max_bv');
        }
    }

    //组合(20-10,000)
    // 890/大单/50 = 8.9.10车道大单各买$50 = 总$150
    if (preg_match('/^(?!\d*?(\d)\d*?\1)\d{1,10}+\/{1}+(大单|小双|小单|大双){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>2,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info[2] * strlen($info[0]) >=  C_config('pk10_zuidi') &&$info[2] * strlen($info[0])+$xiazhujinetype1 <=  C_config('pk10_zuhe_bv') &&$info[2] * strlen($info[0])+$alljine<=C_config('pk10_max_bv')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2] * strlen($info[0]);
            $data['type'] = 2;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('pk10_zuidi').'-'.C_config('pk10_zuhe_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('pk10_max_bv');
        }
    }

    //龙虎(20-20,000)
    // 123/龙/100 = 1~3车道买龙各$100=总$300
    if (preg_match('/^(?![1-5]*?([1-5])[1-5]*?\1)[1-5]{1,5}+\/{1}+(龙|虎){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>4,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info[2] * strlen($info[0])>=  C_config('pk10_zuidi')  &&$info[2] * strlen($info[0])+$xiazhujinetype1 <= C_config('pk10_longhu_bv')&&$info[2] * strlen($info[0])+$alljine<=C_config('pk10_max_bv')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2] * strlen($info[0]);
            $data['type'] = 4;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('pk10_zuidi').'-'.C_config('pk10_longhu_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('pk10_max_bv');
        }
    }

    //冠亚庄闲(20-20,000)
    // 庄/200 = 冠军大于亚军即中奖
    if (preg_match('/^(庄|闲){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>5,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info[1] >= C_config('pk10_zuidi')  && $info[1]+$xiazhujinetype1 <= C_config('pk10_xz_bv')&&$info[2]+$alljine<=C_config('pk10_max_bv') ) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[1];
            $data['type'] = 5;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('pk10_zuidi').'-'.C_config('pk10_xz_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('pk10_max_bv');
        }
    }

    //冠亚号码(20-5,000)
    // 组/5-6/50 = 5号.6号车在冠亚军(顺序不限) = 总$50
    // 组/1-9.3-7/100 = 1.9号车或3.7号车在冠亚军(顺序不限) = 总$200
    //更改
    if (preg_match('/^组\/{1}+([0-9]{1}-[0-9]{1}.)*([0-9]{1}-[0-9]{1})+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>6,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info[2] >= C_config('pk10_zuidi')&& $info[2]+$xiazhujinetype1 <= C_config('pk10_guanya_bv')&&$info[2]+$alljine<=C_config('pk10_max_bv')) {
            if (strlen($info[1]) > 3) {
                $info2 = explode('.', $info[1]);
                for ($i = 0; $i < count($info2); $i++) {
                    $info3[$i] = explode('-', $info2[$i]);
                    if ($info3[$i][0] == $info3[$i][1]) {
                        $res = 0;
                        return false;
                    } else {
                        $res = 1;
                    }
                    for ($a = 0; $a < $i - 1; $a++) {
                        if ($info2[$i] == $info2[$a]) {
                            $res = 0;
                            return false;
                        } else {
                            $res = 1;
                        }
                        $info3 = explode('-', $info2[$a]);
                        $info4 = $info3[1] . '-' . $info3[0];
                        if ($info2[$i] == $info4) {
                            $res = 0;
                            return false;
                        } else {
                            $res = 1;
                        }
                    }
                }
                if ($res == 1) {
                    $data['start'] = serialize($info2);
                    $data['points'] = $info[2] * count($info2);
                    $data['type'] = 6;
                }
            } else {
                $info1 = explode('-', $info[1]);
                if ($info1[0] != $info1[1]) {
                    $data['start'] = serialize(array('0' => $info[1]));
                    $data['points'] = $info[2];
                    $data['type'] = 6;
                }
            }
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('pk10_zuidi').'-'.C_config('pk10_guanya_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('pk10_max_bv');
        }
    }

    //特码大小单双(20-20,000)
    // 和双100 = 「冠亚和」的双$100
    if (preg_match('/^(和|特){1}(大|小|单|双){1}+\d+$/', $message)) {
        $info = substr($message, 6);
        //单局分类最高金额-----begin-------
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>7,
            'state'=>1,
            'userid'=>$id,
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info >= C_config('pk10_zuidi') && $info+$xiazhujinetype1 <= C_config('pk10_guanyahe_bv')&&$info+$alljine<=C_config('pk10_max_bv')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info;
            $data['type'] = 7;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('pk10_zuidi').'-'.C_config('pk10_guanyahe_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('pk10_max_bv');
        }
    }
    //特码数字
    // 3.4.18.19，含本42倍，限额20-1,000
    // 5.6.16.17，含本21倍，限额20-2,000
    // 7.8.14.15，含本14倍，限额20-3,000
    // 9.10.12.13，含本10倍，限额20-4,000
    // 11，含本8倍，限额20-5,000
    // 和5.6.7/100 = 竞猜「冠亚和」的值为5或6或7各$100 = 总$300
    if (preg_match('/^(和|特){1}(([3-9]|1[0-9]).)*([3-9]|1[0-9])+\/{1}+\d+$/', $message)) {
        $arr = explode('/',$message);
        $getone =explode('和',$arr[0]);
        $sumarr = explode('.',$getone[1]);
        $jichu = 0;
        if(count($sumarr)>1){
            foreach ($sumarr as $key=>$value){
                if ($value>19 ||$value<3){
                    $jichu++;
                }
            }
        }else{
            if ($getone[1] >19 ||$getone[1]<3){
                $jichu++;
            }
        }
        if($jichu==0) {
            $info = explode('/', $message);
            //单局分类最高金额-----begin-------
            $chaxuntiaojian = $info[0];
            $where = array(
                'number' => $dankaijiangqihao,
                'type' => 8,
                'state' => 1,
                'userid' => $id,
                'jincai' => array('like', "%$chaxuntiaojian%"),
            );
            $xiazhujinetype1 = M('order')->where($where)->sum('del_points');
            //------------end------------------
            $start = substr($info[0], 3);
            $ress = explode('.', $start);
            if ($info[1] * count($ress) >= C_config('pk10_zuidi') && $info[1] * count($ress) + $xiazhujinetype1 <= C_config('pk10_guanyahe_bv') && $info[1] * count($ress) + $alljine <= C_config('pk10_max_bv')) {
                if (strlen($start) > 1) {
                    $res = explode('.', $start);
                    if (count($res) == count(array_unique($res))) {
                        $data['start'] = serialize(str_split(substr($info[0], 3)));
                        $data['points'] = $info[1] * count($res);
                        $data['type'] = 8;
                    }
                } else {
                    $data['start'] = serialize(str_split(substr($info[0], 3)));
                    $data['points'] = $info[1];
                    $data['type'] = 8;
                }
            } else {
                $data['error'] = 0;
                $data['money'] = C_config('pk10_zuidi') . '-' . C_config('pk10_guanyahe_bv') . '本期已压注总金额：' . $xiazhujinetype1 . '单局最高' . C_config('pk10_max_bv');
            }
        }
    }
    //abc
    /*if (preg_match('/^(A|B|C){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>9,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info[1] >= C_config('pk10_zuidi')  && $info[1]+$xiazhujinetype1 <= C_config('pk10_tema_abc')&&$info[2]+$alljine<=C_config('pk10_max_bv') ) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[1];
            $data['type'] = 9;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('pk10_zuidi').'-'.C_config('pk10_tema_abc').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('pk10_max_bv');
        }
    }*/
    return $data;
}
function check_format_jscar($message,$id)
{
    //查询获取数据库中当期中的所有值
    $dankaijiangdata = getjscar();
    $dankaijiangqihao =  $dankaijiangdata['next']['periodNumber'];
    $wheres =array(
        'number' =>$dankaijiangqihao,
        'state'=>1,
        'userid'=>$id,
        'game'=>'jscar',
    );
    //单局最高的金额
    $alljine =M('order')->where($wheres)->sum('del_points');
    $data['error'] = 1;
    //车号大小单双(20-20,000)
    // 双/100 = 1~5车道买双各$100 = 总$500
    if (preg_match('/^(?!\d*?(\d)\d*?\1)\d{1,10}+\/{1}+(大|双|小|单){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>1,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ( $info[2] * strlen($info[0]) >= C_config('jscar_zuidi') &&  $info[2] * strlen($info[0])+$xiazhujinetype1 <= C_config('jscar_dxds_bv') && $info[2] * strlen($info[0]) +$alljine <=C_config('jscar_max_bv')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2] * strlen($info[0]);
            $data['type'] = 1;
        }else {
            $data['error'] = 0;
            $data['money'] = C_config('jscar_zuidi').'-'.C_config('jscar_dxds_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('jscar_max_bv');
        }
    }

    //车号(20-20,000)
    // 12345/89/20 = 1~5车道的8号、9号各买$20 = 总$200
    if (preg_match('/^(?!\d*?(\d)\d*?\1)\d{1,10}+\/{1}+(?!\d*?(\d)\d*?\1)\d{1,10}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>3,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info[2] * strlen($info[0]) * strlen($info[1]) >=  C_config('jscar_zuidi') && $info[2] * strlen($info[0]) * strlen($info[1])+$xiazhujinetype1 <= C_config('jscar_shuzi_bv') &&$info[2] * strlen($info[0]) * strlen($info[1])+$alljine<=C_config('jscar_max_bv')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2] * strlen($info[0]) * strlen($info[1]);
            $data['type'] = 3;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('jscar_zuidi').'-'.C_config('jscar_shuzi_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('jscar_max_bv');
        }
    }

    //组合(20-10,000)
    // 890/大单/50 = 8.9.10车道大单各买$50 = 总$150
    if (preg_match('/^(?!\d*?(\d)\d*?\1)\d{1,10}+\/{1}+(大单|小双|小单|大双){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>2,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info[2] * strlen($info[0]) >=  C_config('jscar_zuidi') &&$info[2] * strlen($info[0])+$xiazhujinetype1 <=  C_config('jscar_zuhe_bv') &&$info[2] * strlen($info[0])+$alljine<=C_config('jscar_max_bv')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2] * strlen($info[0]);
            $data['type'] = 2;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('jscar_zuidi').'-'.C_config('jscar_zuhe_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('jscar_max_bv');
        }
    }

    //龙虎(20-20,000)
    // 123/龙/100 = 1~3车道买龙各$100=总$300
    if (preg_match('/^(?![1-5]*?([1-5])[1-5]*?\1)[1-5]{1,5}+\/{1}+(龙|虎){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>4,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info[2] * strlen($info[0])>=  C_config('jscar_zuidi')  &&$info[2] * strlen($info[0])+$xiazhujinetype1 <= C_config('jscar_longhu_bv')&&$info[2] * strlen($info[0])+$alljine<=C_config('jscar_max_bv')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2] * strlen($info[0]);
            $data['type'] = 4;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('jscar_zuidi').'-'.C_config('jscar_longhu_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('jscar_max_bv');
        }
    }

    //冠亚庄闲(20-20,000)
    // 庄/200 = 冠军大于亚军即中奖
    if (preg_match('/^(庄|闲){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>5,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info[1] >= C_config('jscar_zuidi')  && $info[1]+$xiazhujinetype1 <= C_config('jscar_xz_bv')&&$info[2]+$alljine<=C_config('jscar_max_bv') ) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[1];
            $data['type'] = 5;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('jscar_zuidi').'-'.C_config('jscar_xz_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('jscar_max_bv');
        }
    }

    //冠亚号码(20-5,000)
    // 组/5-6/50 = 5号.6号车在冠亚军(顺序不限) = 总$50
    // 组/1-9.3-7/100 = 1.9号车或3.7号车在冠亚军(顺序不限) = 总$200
    //更改
    if (preg_match('/^组\/{1}+([0-9]{1}-[0-9]{1}.)*([0-9]{1}-[0-9]{1})+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>6,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info[2] >= C_config('jscar_zuidi')&& $info[2]+$xiazhujinetype1 <= C_config('jscar_guanya_bv')&&$info[2]+$alljine<=C_config('jscar_max_bv')) {
            if (strlen($info[1]) > 3) {
                $info2 = explode('.', $info[1]);
                for ($i = 0; $i < count($info2); $i++) {
                    $info3[$i] = explode('-', $info2[$i]);
                    if ($info3[$i][0] == $info3[$i][1]) {
                        $res = 0;
                        return false;
                    } else {
                        $res = 1;
                    }
                    for ($a = 0; $a < $i - 1; $a++) {
                        if ($info2[$i] == $info2[$a]) {
                            $res = 0;
                            return false;
                        } else {
                            $res = 1;
                        }
                        $info3 = explode('-', $info2[$a]);
                        $info4 = $info3[1] . '-' . $info3[0];
                        if ($info2[$i] == $info4) {
                            $res = 0;
                            return false;
                        } else {
                            $res = 1;
                        }
                    }
                }
                if ($res == 1) {
                    $data['start'] = serialize($info2);
                    $data['points'] = $info[2] * count($info2);
                    $data['type'] = 6;
                }
            } else {
                $info1 = explode('-', $info[1]);
                if ($info1[0] != $info1[1]) {
                    $data['start'] = serialize(array('0' => $info[1]));
                    $data['points'] = $info[2];
                    $data['type'] = 6;
                }
            }
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('jscar_zuidi').'-'.C_config('jscar_guanya_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('jscar_max_bv');
        }
    }

    //特码大小单双(20-20,000)
    // 和双100 = 「冠亚和」的双$100
    if (preg_match('/^(和|特){1}(大|小|单|双){1}+\d+$/', $message)) {
        $info = substr($message, 6);
        //单局分类最高金额-----begin-------
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>7,
            'state'=>1,
            'userid'=>$id,
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info >= C_config('jscar_zuidi') && $info+$xiazhujinetype1 <= C_config('jscar_guanyahe_bv')&&$info+$alljine<=C_config('jscar_max_bv')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info;
            $data['type'] = 7;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('jscar_zuidi').'-'.C_config('jscar_guanyahe_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('jscar_max_bv');
        }
    }
    //特码数字
    // 3.4.18.19，含本42倍，限额20-1,000
    // 5.6.16.17，含本21倍，限额20-2,000
    // 7.8.14.15，含本14倍，限额20-3,000
    // 9.10.12.13，含本10倍，限额20-4,000
    // 11，含本8倍，限额20-5,000
    // 和5.6.7/100 = 竞猜「冠亚和」的值为5或6或7各$100 = 总$300
    if (preg_match('/^(和|特){1}(([3-9]|1[0-9]).)*([3-9]|1[0-9])+\/{1}+\d+$/', $message)) {
        $arr = explode('/',$message);
        $getone =explode('和',$arr[0]);
        $sumarr = explode('.',$getone[1]);
        $jichu = 0;
        if(count($sumarr)>1){
            foreach ($sumarr as $key=>$value){
                if ($value>19 ||$value<3){
                    $jichu++;
                }
            }
        }else{
            if ($getone[1] >19 ||$getone[1]<3){
                $jichu++;
            }
        }
        if($jichu==0){
            $info = explode('/', $message);
            //单局分类最高金额-----begin-------
            $chaxuntiaojian = $info[0];
            $where  = array(
                'number'=>$dankaijiangqihao,
                'type'=>8,
                'state'=>1,
                'userid'=>$id,
                'jincai'=> array('like', "%$chaxuntiaojian%"),
            );
            $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
            //------------end------------------
            $start = substr($info[0], 3);
            $ress = explode('.', $start);
            if ($info[1]*count($ress) >= C_config('jscar_zuidi') &&$info[1]*count($ress)+$xiazhujinetype1<=C_config('jscar_guanyahe_bv') && $info[1]*count($ress)+$alljine <=C_config('jscar_max_bv')) {
                if (strlen($start) > 1) {
                    $res = explode('.', $start);
                    if (count($res) == count(array_unique($res))) {
                        $data['start'] = serialize(str_split(substr($info[0], 3)));
                        $data['points'] = $info[1] * count($res);
                        $data['type'] = 8;
                    }
                } else {
                    $data['start'] = serialize(str_split(substr($info[0], 3)));
                    $data['points'] = $info[1];
                    $data['type'] = 8;
                }
            } else {
                $data['error'] = 0;
                $data['money'] = C_config('jscar_zuidi').'-'.C_config('jscar_guanyahe_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('jscar_max_bv');
            }
        }


    }
    //abc
    if (preg_match('/^(A|B|C){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>9,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info[1] >= C_config('jscar_zuidi')  && $info[1]+$xiazhujinetype1 <= C_config('jscar_tema_abc')&&$info[2]+$alljine<=C_config('jscar_max_bv') ) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[1];
            $data['type'] = 9;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('jscar_zuidi').'-'.C_config('jscar_tema_abc').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('jscar_max_bv');
        }
    }
    return $data;
}
function check_format_fei($message,$id)
{
    //查询获取数据库中当期中的所有值
    $dankaijiangdata = getfei();
    $dankaijiangqihao =  $dankaijiangdata['next']['periodNumber'];
    $wheres =array(
        'number' =>$dankaijiangqihao,
        'state'=>1,
        'userid'=>$id,
        'game'=>'fei',
    );
    //单局最高的金额
    $alljine =M('order')->where($wheres)->sum('del_points');
    $data['error'] = 1;
    //车号大小单双(20-20,000)
    // 双/100 = 1~5车道买双各$100 = 总$500
    if (preg_match('/^(?!\d*?(\d)\d*?\1)\d{1,10}+\/{1}+(大|双|小|单){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>1,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info[2] * strlen($info[0]) >= C_config('fei_zuidi') && $info[2] * strlen($info[0])+$xiazhujinetype1 <= C_config('fei_dxds_bv') &&$info[2] * strlen($info[0]) +$alljine <=C_config('fei_max_bv')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2] * strlen($info[0]);
            $data['type'] = 1;
        }else {
            $data['error'] = 0;
            $data['money'] = C_config('fei_zuidi').'-'.C_config('fei_dxds_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('fei_max_bv');
        }
    }
    //车号(20-20,000)
    // 12345/89/20 = 1~5车道的8号、9号各买$20 = 总$200
    if (preg_match('/^(?!\d*?(\d)\d*?\1)\d{1,10}+\/{1}+(?!\d*?(\d)\d*?\1)\d{1,10}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>3,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info[2] * strlen($info[0]) * strlen($info[1]) >=  C_config('fei_zuidi') &&$info[2] * strlen($info[0]) * strlen($info[1])+$xiazhujinetype1 <= C_config('fei_shuzi_bv') &&$info[2] * strlen($info[0]) * strlen($info[1])+$alljine<=C_config('fei_max_bv')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2] * strlen($info[0]) * strlen($info[1]);
            $data['type'] = 3;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('fei_zuidi').'-'.C_config('fei_shuzi_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('fei_max_bv');
        }
    }

    //组合(20-10,000)
    // 890/大单/50 = 8.9.10车道大单各买$50 = 总$150
    if (preg_match('/^(?!\d*?(\d)\d*?\1)\d{1,10}+\/{1}+(大单|小双|小单|大双){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>2,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info[2] * strlen($info[0]) >=  C_config('fei_zuidi') && $info[2] * strlen($info[0])+$xiazhujinetype1 <=  C_config('fei_zuhe_bv') &&$info[2] * strlen($info[0])+$alljine<=C_config('fei_max_bv')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2] * strlen($info[0]);
            $data['type'] = 2;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('fei_zuidi').'-'.C_config('fei_zuhe_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('fei_max_bv');
        }
    }

    //龙虎(20-20,000)
    // 123/龙/100 = 1~3车道买龙各$100=总$300
    if (preg_match('/^(?![1-5]*?([1-5])[1-5]*?\1)[1-5]{1,5}+\/{1}+(龙|虎){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>4,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info[2] * strlen($info[0]) >=  C_config('fei_zuidi')  && $info[2] * strlen($info[0])+$xiazhujinetype1 <= C_config('fei_longhu_bv')&&$info[2] * strlen($info[0])+$alljine<=C_config('fei_max_bv')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2] * strlen($info[0]);
            $data['type'] = 4;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('fei_zuidi').'-'.C_config('fei_longhu_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('fei_max_bv');
        }
    }

    //冠亚庄闲(20-20,000)
    // 庄/200 = 冠军大于亚军即中奖
    if (preg_match('/^(庄|闲){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>5,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info[1] >= C_config('fei_zuidi')  && $info[1]+$xiazhujinetype1 <= C_config('fei_xz_bv')&&$info[2]+$alljine<=C_config('fei_max_bv') ) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[1];
            $data['type'] = 5;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('fei_zuidi').'-'.C_config('fei_xz_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('fei_max_bv');
        }
    }

    //冠亚号码(20-5,000)
    // 组/5-6/50 = 5号.6号车在冠亚军(顺序不限) = 总$50
    // 组/1-9.3-7/100 = 1.9号车或3.7号车在冠亚军(顺序不限) = 总$200
    if (preg_match('/^组\/{1}+([0-9]{1}-[0-9]{1}.)*([0-9]{1}-[0-9]{1})+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>6,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info[2] >= C_config('fei_zuidi')&& $info[2]+$xiazhujinetype1 <= C_config('fei_guanya_bv')&&$info[2]+$alljine<=C_config('fei_max_bv')) {
            if (strlen($info[1]) > 3) {
                $info2 = explode('.', $info[1]);
                for ($i = 0; $i < count($info2); $i++) {
                    $info3[$i] = explode('-', $info2[$i]);
                    if ($info3[$i][0] == $info3[$i][1]) {
                        $res = 0;
                        return false;
                    } else {
                        $res = 1;
                    }
                    for ($a = 0; $a < $i - 1; $a++) {
                        if ($info2[$i] == $info2[$a]) {
                            $res = 0;
                            return false;
                        } else {
                            $res = 1;
                        }
                        $info3 = explode('-', $info2[$a]);
                        $info4 = $info3[1] . '-' . $info3[0];
                        if ($info2[$i] == $info4) {
                            $res = 0;
                            return false;
                        } else {
                            $res = 1;
                        }
                    }
                }
                if ($res == 1) {
                    $data['start'] = serialize($info2);
                    $data['points'] = $info[2] * count($info2);
                    $data['type'] = 6;
                }
            } else {
                $info1 = explode('-', $info[1]);
                if ($info1[0] != $info1[1]) {
                    $data['start'] = serialize(array('0' => $info[1]));
                    $data['points'] = $info[2];
                    $data['type'] = 6;
                }
            }
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('fei_guanya_bv').'-'.C_config('fei_guanyahe_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('fei_max_bv');
        }
    }

    //特码大小单双(20-20,000)
    // 和双100 = 「冠亚和」的双$100
    if (preg_match('/^(和|特){1}(大|小|单|双){1}+\d+$/', $message)) {
        $info = substr($message, 6);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>7,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info >= C_config('fei_zuidi') && $info+$xiazhujinetype1 <= C_config('fei_guanyahe_bv')&&$info+$alljine<=C_config('fei_max_bv')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info;
            $data['type'] = 7;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('fei_zuidi').'-'.C_config('fei_guanyahe_bv').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('fei_max_bv');
        }
    }
    //特码数字
    // 3.4.18.19，含本42倍，限额20-1,000
    // 5.6.16.17，含本21倍，限额20-2,000
    // 7.8.14.15，含本14倍，限额20-3,000
    // 9.10.12.13，含本10倍，限额20-4,000
    // 11，含本8倍，限额20-5,000
    // 和5.6.7/100 = 竞猜「冠亚和」的值为5或6或7各$100 = 总$300
    if (preg_match('/^(和|特){1}(([3-9]|1[0-9]).)*([3-9]|1[0-9])+\/{1}+\d+$/', $message)) {
        $arr = explode('/',$message);
        $getone =explode('和',$arr[0]);
        $sumarr = explode('.',$getone[1]);
        $jichu = 0;
        if(count($sumarr)>1){
            foreach ($sumarr as $key=>$value){
                if ($value>19 ||$value<3){
                    $jichu++;
                }
            }
        }else{
            if ($getone[1] >19 ||$getone[1]<3){
                $jichu++;
            }
        }
        if($jichu==0) {
            $info = explode('/', $message);
            //单局分类最高金额-----begin-------
            $chaxuntiaojian = $info[0];
            $where = array(
                'number' => $dankaijiangqihao,
                'type' => 8,
                'state' => 1,
                'userid' => $id,
                'jincai' => array('like', "%$chaxuntiaojian%"),
            );
            $xiazhujinetype1 = M('order')->where($where)->sum('del_points');
            //------------end------------------
            $start = substr($info[0], 3);
            if ($info[1] >= C_config('fei_zuidi') && $info[1] + $xiazhujinetype1 <= C_config('fei_tema_bv') && $info[1] + $alljine <= C_config('fei_max_bv')) {
                if (strlen($start) > 1) {
                    $res = explode('.', $start);
                    if (count($res) == count(array_unique($res))) {
                        $data['start'] = serialize(str_split(substr($info[0], 3)));
                        $data['points'] = $info[1] * count($res);
                        $data['type'] = 8;
                    }
                } else {
                    $data['start'] = serialize(str_split(substr($info[0], 3)));
                    $data['points'] = $info[1];
                    $data['type'] = 8;
                }
            } else {
                $data['error'] = 0;
                $data['money'] = C_config('fei_zuidi') . '-' . C_config('fei_tema_bv') . '本期已压注总金额：' . $xiazhujinetype1 . '单局最高' . C_config('fei_max_bv');
            }
        }
    }
    //abc
    /*if (preg_match('/^(A|B|C){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //单局分类最高金额-----begin-------
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>9,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //------------end------------------
        if ($info[1] >= C_config('fei_zuidi')  && $info[1]+$xiazhujinetype1 <= C_config('fei_tema_abc')&&$info[2]+$alljine<=C_config('fei_max_bv') ) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[1];
            $data['type'] = 9;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('fei_zuidi').'-'.C_config('fei_tema_abc').'本期已压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('fei_max_bv');
        }
    }*/
    return $data;
}

//-----------------------------------------------蛋28验证--------------------------------------------------------
function check_format_bj28($message,$id)
{
    //查询档期的开奖期号
    $dankaijiangdata = getBj28();
    $dankaijiangqihao =  $dankaijiangdata['next']['periodNumber'];
    $wheres =array(
        'number' =>$dankaijiangqihao,
        'state'=>1,
        'userid'=>$id,
        'game'=>'bj28',
    );
    $alljine =M('order')->where($wheres)->sum('del_points');
    //查询
    $data['error'] = 1;
    //单、双、玩法  金额10~~20000     1：2   单/20
    if (preg_match('/^(双|单|大|小){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        //锁定这个是属于大小单双的哪一种
        $chaxuntiaojian = $info[0];
            $where  = array(
                'number'=>$dankaijiangqihao,
                'type'=>1,
                'state'=>1,
                'userid'=>$id,
                'jincai'=> array('like', "%$chaxuntiaojian%"),
            );
            $xiazhujinetype1 =M('order')->where($where)->sum('del_points');
        if(empty($xiazhujinetype1)){
            $xiazhujinetype1 = 0;
        }
        if ($info[1] >= C_config('bj28_zuidi') && $info[1]+$xiazhujinetype1 <= C_config('dan_check_dx') && $info[1]+$alljine < C_config('dan_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 1;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('bj28_zuidi').'-'.C_config('dan_check_dx').'本期压注总金额：'.$xiazhujinetype1.'单局最高'.C_config('dan_all_jine');
//            die(json_encode($data));
        }
    }
    //大单大双 小单小双   1：4    大单/20
    if (preg_match('/^(大双|大单|小双|小单){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>2,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype2 =M('order')->where($where)->sum('del_points');
        if (!$xiazhujinetype2){
            $xiazhujinetype2 = 0;
        }
        if ($info[1] >= C_config('bj28_zuidi') && $info[1]+$xiazhujinetype2<= C_config('dan_check_dxds')&& $info[1]+$alljine < C_config('dan_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 2;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('bj28_zuidi').'-'.C_config('dan_check_dxds').'本期压注总金额：'.$xiazhujinetype2.'单局最高'.C_config('dan_all_jine');
        }
    }
    //极大 极小  1：12     极小/20
    if (preg_match('/^(极大|极小){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>3,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype3 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C_config('bj28_zuidi') && $info[1]+$xiazhujinetype3 <= C_config('dan_check_jz') &&$info[1]+$alljine < C_config('dan_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 3;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('bj28_zuidi').'-'.C_config('dan_check_jz').'本期压注总金额：'.$xiazhujinetype3.'单局最高'.C_config('dan_all_jine');
        }
    }
    //和
    if (preg_match('/^\d{1,2}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>4,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian/%"),
        );
        $xiazhujinetype4 =M('order')->where($where)->sum('del_points');
        $alln = $info[0];
        if ($alln <= 27) {
            if ($info[1] >= C_config('bj28_zuidi') && $info[1]+$xiazhujinetype4 <= C_config('dan_check_hezhi')&&$info[1]+$alljine < C_config('dan_all_jine')) {
                $data['points'] = $info[1];
                $data['type'] = 4;
            }else{
                $data['error'] = 0;
                $data['money'] =  C_config('bj28_zuidi').'-'.'本期压注总金额：'.$xiazhujinetype4.'单局最高'.C_config('dan_all_jine');
            }
        } else {
            $data['error'] = 0;
            $data['money'] =  C_config('bj28_zuidi').'-'.C_config('dan_check_hezhi');
        }

    }

    //豹子判断   999/70
    if (preg_match('/^(豹子){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>5,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype5 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C_config('bj28_zuidi') && $info[1]+$xiazhujinetype5 <= C_config('dan_check_bz') &&$info[1]+$alljine < C_config('dan_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 5;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('bj28_zuidi').'-'.C_config('dan_check_bz').'本期压注总金额：'.$xiazhujinetype5.'单局最高'.C_config('dan_all_jine');
        }
    }
    //对子
    if (preg_match('/^(对子){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>8,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype5 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C_config('bj28_zuidi') && $info[1]+$xiazhujinetype5 <= C_config('dan_check_dz') &&$info[1]+$alljine < C_config('dan_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 8;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('bj28_zuidi').'-'.C_config('dan_check_dz').'本期压注总金额：'.$xiazhujinetype5.'单局最高'.C_config('dan_all_jine');
        }
    }
    // 顺子     123/20
    if (preg_match('/^(顺子){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>6,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype6 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C_config('bj28_zuidi') && $info[1]+$xiazhujinetype6 <= C_config('dan_check_sz')&&$info[1]+$alljine < C_config('dan_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 6;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('bj28_zuidi').'-'.C_config('dan_check_sz').'本期压注总金额：'.$xiazhujinetype6.'单局最高'.C_config('dan_all_jine');
        }
    }
    //大小
    if (preg_match('/^(大|小){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>7,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype1 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C_config('bj28_zuidi') && $info[1]+$jndxiazhujinetype1 <= C_config('jnd_check_dx')&&$info[1]+$alljine < C_config('dan_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 7;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('bj28_zuidi').'-'.C_config('jnd_check_dx').'本期压注总金额：'.$jndxiazhujinetype1.'单局最高'.C_config('dan_all_jine');
        }
    }

    return $data;
}
function check_format_jnd28($message,$id)
{
    $dankaijiangdata = getJnd28();
    $dankaijiangqihao =  $dankaijiangdata['next']['periodNumber'];
    //当局总金额
    $wheres =array(
        'number' =>$dankaijiangqihao,
        'state'=>1,
        'userid'=>$id,
        'game'=>'jnd28',
    );
    $alljine =M('order')->where($wheres)->sum('del_points');
    $data['error'] = 1;
    //单、双、玩法  金额10~~20000     1：2   单/20
    if (preg_match('/^(双|单){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>1,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype1 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C_config('jnd28_zuidi') && $info[1]+$jndxiazhujinetype1 <= C_config('jnd_check_dx')&&$info[1]+$alljine < C_config('jnd_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 1;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('jnd28_zuidi').'-'.C_config('jnd_check_dx').'本期压注总金额：'.$jndxiazhujinetype1.'单局最高'.C_config('jnd_all_jine');
        }
    }
    //大单大双 小单小双   1：4    大单/20---------------------------------------------------------------------
    if (preg_match('/^(大双|大单|小双|小单){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>2,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype2 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C_config('jnd28_zuidi') && $info[1]+$jndxiazhujinetype2 <= C_config('jnd_check_dxds')&&$info[1]+$alljine < C_config('jnd_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 2;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('jnd28_zuidi').'-'.C_config('jnd_check_dxds').'本期压注总金额：'.$jndxiazhujinetype2;
        }
    }
    //极大 极小  1：12     极小/20---------------------------------------------------
    if (preg_match('/^(极大|极小){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>3,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype3 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C_config('jnd28_zuidi') && $info[1]+$jndxiazhujinetype3 <= C_config('jnd_check_jz')&&$info[1]+$alljine < C_config('jnd_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 3;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('jnd28_zuidi').'-'.C_config('jnd_check_jz').'本期压注总金额：'.$jndxiazhujinetype3.'单局最高'.C_config('jnd_all_jine');
        }
    }
    //和
    if (preg_match('/^\d{1,2}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>4,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian/%"),
        );
        $jndxiazhujinetype4 =M('order')->where($where)->sum('del_points');
        $alln = $info[0];
        if ($alln <= 27) {
            if ($info[1] >= C_config('jnd28_zuidi') && $info[1]+$jndxiazhujinetype4 <= C_config('jnd_check_hezhi')&&$info[1]+$alljine < C_config('jnd_all_jine')) {
                $data['points'] = $info[1];
                $data['type'] = 4;
            }else{
                $data['error'] = 0;
                $data['money'] =  C_config('jnd28_zuidi').'-'.C_config('jnd_check_hezhi').'本期压注总金额：'.$jndxiazhujinetype4.'单局最高'.C_config('jnd_all_jine');
            }

        } else {
            $data['error'] = 0;
            $data['money'] =  C_config('jnd28_zuidi').'-'.C_config('jnd_check_hezhi').'本期压注总金额：'.$jndxiazhujinetype4.'单局最高'.C_config('jnd_all_jine');
        }

    }

    //豹子判断   999/70
    if (preg_match('/^(豹子){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>5,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype5 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C_config('jnd28_zuidi') && $info[1]+$jndxiazhujinetype5 <= C_config('jnd_check_bz')&&$info[1]+$alljine < C_config('jnd_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 5;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('jnd28_zuidi').'-'.C_config('jnd_check_bz').'本期压注总金额：'.$jndxiazhujinetype5.'单局最高'.C_config('jnd_all_jine');
        }
    }
    //对子
    //对子
    if (preg_match('/^(对子){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>8,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $xiazhujinetype5 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C_config('jnd28_zuidi') && $info[1]+$xiazhujinetype5 <= C_config('jnd_check_dz') &&$info[1]+$alljine < C_config('dan_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 8;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('jnd28_zuidi').'-'.C_config('jnd_check_dz').'本期压注总金额：'.$xiazhujinetype5.'单局最高'.C_config('dan_all_jine');
        }
    }
    // 顺子     123/20
    if (preg_match('/^(顺子){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>6,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype6 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C_config('jnd28_zuidi') && $info[1]+$jndxiazhujinetype6 <= C_config('jnd_check_sz')&&$info[1]+$alljine < C_config('jnd_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 6;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('jnd28_zuidi').'-'.C_config('jnd_check_sz').'本期压注总金额：'.$jndxiazhujinetype6.'单局最高'.C_config('jnd_all_jine');
        }
    }
    if (preg_match('/^(大|小){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>7,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype1 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C_config('jnd28_zuidi') && $info[1]+$jndxiazhujinetype1 <= C_config('jnd_check_dx')&&$info[1]+$alljine < C_config('jnd_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 7;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('jnd28_zuidi').'-'.C_config('jnd_check_dx').'本期压注总金额：'.$jndxiazhujinetype1.'单局最高'.C_config('jnd_all_jine');
        }
    }
    return $data;
}
//-----------------------------------------------时时彩验证--------------------------------------------------------
function  check_format_ssc($message,$id){
    $dankaijiangdata = getssc();
    $dankaijiangqihao =  $dankaijiangdata['next']['periodNumber'];
    //当局总金额
    $wheres =array(
        'number' =>$dankaijiangqihao,
        'state'=>1,
        'userid'=>$id,
        'game'=>'ssc',
    );
    //当期的总金额
    $alljine =M('order')->where($wheres)->sum('del_points');
    //------------时时彩的开始------------------------------------------------------------------------------------------
    $data['error'] = 1;
    //1/单/600
    if (preg_match('/^\d+\/{1}+(大|双|小|单){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>1,
            'state'=>1,
            'userid'=>$id,
            'game'=>'ssc',
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $sscxiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //如果选择的位数已经超过了五个直接报错：格式不正确
        if ($info[2] * strlen($info[0]) >= C_config('ssc_zuidi') && $info[2] * strlen($info[0])+$sscxiazhujinetype1 <= C_config('ssc_fd_dxds') &&$info[2] * strlen($info[0])+$alljine<=C_config('ssc_djzg')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2] * strlen($info[0]);
            $data['type'] = 1;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('ssc_zuidi').'-'.C_config('ssc_fd_dxds').'本期压注总金额：'.$sscxiazhujinetype1.'单局最高'.C_config('ssc_djzg');
         }
        }
    //1/大单/600     飞鸟，不开奖
//    if (preg_match('/^\d{1}+\/{1}+(大单|小单|大双|小双){1}+\/{1}+\d+$/', $message)) {
//        $info = explode('/', $message);
//        $chaxuntiaojian = $info[1];
//        $where  = array(
//            'number'=>$dankaijiangqihao,
//            'type'=>2,
//            'state'=>1,
//            'userid'=>$id,
//            'jincai'=> array('like', "%$chaxuntiaojian%"),
//        );
//        $sscxiazhujinetype2 =M('order')->where($where)->sum('del_points');
//        //如果选择的位数已经超过了五个直接报错：格式不正确
////        if ($info[0]<=5){
//        if ($info[2] >= C_config('ssc_zuidi') &&  $info[2]+$sscxiazhujinetype2 <= C_config('ssc_fd_zuhe') &&$info[2]+$alljine<=C_config('ssc_djzg')) {
//            $data['start'] = serialize(str_split($info[0]));
//            $data['points'] = $info[2] * strlen($info[0]);
//            $data['type'] = 2;
//        } else {
//            $data['error'] = 0;
//            $data['money'] = C_config('ssc_zuidi').'-'.C_config('ssc_bl_zuhe').'本期压注总金额：'.$sscxiazhujinetype1.'单局最高'.C_config('ssc_djzg');
//        }
////        }else{
////            $data['error'] = 1;
////        }
//    }
    //123/345/600    第123 位置的是为多少
    //猜数字    123/123/500
    if (preg_match('/^\d+\/{1}+\d+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>3,
            'state'=>1,
            'userid'=>$id,
        );
        $sscxiazhujinetype3 =M('order')->where($where)->sum('del_points');
        //如果选择的位数已经超过了五个直接报错：格式不正确
            if ($info[2] * strlen($info[0]) * strlen($info[1]) >= C_config('ssc_zuidi') &&$info[2] * strlen($info[0]) * strlen($info[1])+$sscxiazhujinetype3 <= C_config('ssc_fd_sum') &&$info[2] * strlen($info[0]) * strlen($info[1])+$alljine< C_config('ssc_djzg')) {
                $data['start'] = serialize(str_split($info[0]));
                $data['points'] = $info[2] * strlen($info[0]) * strlen($info[1]);
                $data['type'] = 3;
            } else {
                $data['error'] = 0;
                $data['money'] = C_config('ssc_zuidi').'-'.C_config('ssc_fd_sum').'本期压注总金额：'.$sscxiazhujinetype3.'单局最高'.C_config('ssc_djzg');
            }
    }
    //特码大小单双 总/大/500
    if (preg_match('/^(总){1}+\/{1}+(大|小|单|双)+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian =$info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>4,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $sscxiazhujinetype3 =M('order')->where($where)->sum('del_points');
        //如果选择的位数已经超过了五个直接报错：格式不正确
        if ($info[2] >= C_config('ssc_zuidi') &&$info[2]+$sscxiazhujinetype3 <= C_config('ssc_fd_zhdxds') &&$info[2]+$alljine<C_config('ssc_djzg')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2];
            $data['type'] = 4;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('ssc_zuidi').'-'.C_config('ssc_fd_zhdxds').'本期压注总金额：'.$sscxiazhujinetype3.'单局最高'.C_config('ssc_djzg');
        }
    }
    //特码abc  A/500
    if (preg_match('/^(A|B|C){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian=$info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>5,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $sscxiazhujinetype3 =M('order')->where($where)->sum('del_points');
        //如果选择的位数已经超过了五个直接报错：格式不正确

        if ($info[1] >= C_config('ssc_zuidi') &&$info[1]+$sscxiazhujinetype3 <= C_config('ssc_fd_abc') &&$info[1]+$alljine<C_config('ssc_djzg')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[1];
            $data['type'] = 5;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('ssc_zuidi').'-'.C_config('ssc_fd_abc').'本期压注总金额：'.$sscxiazhujinetype3.'单局最高'.C_config('ssc_djzg');
        }
    }
    //龙虎和
    if (preg_match('/^(龙|虎|和){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian =$info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>6,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $sscxiazhujinetype3 =M('order')->where($where)->sum('del_points');
        //如果选择的位数已经超过了五个直接报错：格式不正确

        if ($info[1] >= C_config('ssc_zuidi') &&$info[1]+$sscxiazhujinetype3 <= C_config('ssc_fd_lh') &&$info[1]+$alljine<C_config('ssc_djzg')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[1];
            $data['type'] = 6;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('ssc_zuidi').'-'.C_config('ssc_fd_lh').'本期压注总金额：'.$sscxiazhujinetype3.'单局最高'.C_config('ssc_djzg');
        }
    }
    //前后中 顺子，半顺，杂六
    if (preg_match('/^(前|中|后){1}+\/{1}+(顺子|半顺|杂六|豹子|对子){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian =$info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>7,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $sscxiazhujinetype3 =M('order')->where($where)->sum('del_points');
        //如果选择的位数已经超过了五个直接报错：格式不正确
        if ($info[1] >= C_config('ssc_zuidi') &&$info[1]+$sscxiazhujinetype3 <= C_config('ssc_fd_qzh') &&$info[1]+$alljine<C_config('ssc_djzg')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2];
            $data['type'] = 7;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('ssc_zuidi').'-'.C_config('ssc_fd_qzh').'本期压注总金额：'.$sscxiazhujinetype3.'单局最高'.C_config('ssc_djzg');
        }
    }
    return $data;
}
//-----------------------------------------------极速彩验证--------------------------------------------------------
function  check_format_jsssc($message,$id){
    $dankaijiangdata = getjsssc();
    $dankaijiangqihao =  $dankaijiangdata['next']['periodNumber'];
    //当局总金额
    $wheres =array(
        'number' =>$dankaijiangqihao,
        'state'=>1,
        'userid'=>$id,
        'game'=>'jsssc',
    );
    //当期的总金额
    $alljine =M('order')->where($wheres)->sum('del_points');
    //------------时时彩的开始------------------------------------------------------------------------------------------
    $data['error'] = 1;
    //1/单/600
    if (preg_match('/^\d+\/{1}+(大|双|小|单){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>1,
            'state'=>1,
            'userid'=>$id,
            'game'=>'jsssc',
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $sscxiazhujinetype1 =M('order')->where($where)->sum('del_points');
        //如果选择的位数已经超过了五个直接报错：格式不正确
        if ($info[2] * strlen($info[0]) >= C_config('jsssc_zuidi') && $info[2] * strlen($info[0])+$sscxiazhujinetype1 <= C_config('jsssc_fd_dxds') &&$info[2] * strlen($info[0])+$alljine<=C_config('jsssc_djzg')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2] * strlen($info[0]);
            $data['type'] = 1;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('jsssc_zuidi').'-'.C_config('jsssc_fd_dxds').'本期压注总金额：'.$sscxiazhujinetype1.'单局最高'.C_config('jsssc_djzg');
        }
    }
    //1/大单/600     飞鸟，不开奖
//    if (preg_match('/^\d{1}+\/{1}+(大单|小单|大双|小双){1}+\/{1}+\d+$/', $message)) {
//        $info = explode('/', $message);
//        $chaxuntiaojian = $info[1];
//        $where  = array(
//            'number'=>$dankaijiangqihao,
//            'type'=>2,
//            'state'=>1,
//            'userid'=>$id,
//            'jincai'=> array('like', "%$chaxuntiaojian%"),
//        );
//        $sscxiazhujinetype2 =M('order')->where($where)->sum('del_points');
//        //如果选择的位数已经超过了五个直接报错：格式不正确
////        if ($info[0]<=5){
//        if ($info[2] >= C_config('ssc_zuidi') &&  $info[2]+$sscxiazhujinetype2 <= C_config('ssc_fd_zuhe') &&$info[2]+$alljine<=C_config('ssc_djzg')) {
//            $data['start'] = serialize(str_split($info[0]));
//            $data['points'] = $info[2] * strlen($info[0]);
//            $data['type'] = 2;
//        } else {
//            $data['error'] = 0;
//            $data['money'] = C_config('ssc_zuidi').'-'.C_config('ssc_bl_zuhe').'本期压注总金额：'.$sscxiazhujinetype1.'单局最高'.C_config('ssc_djzg');
//        }
////        }else{
////            $data['error'] = 1;
////        }
//    }
    //123/345/600    第123 位置的是为多少
    //猜数字    123/123/500
    if (preg_match('/^\d+\/{1}+\d+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>3,
            'state'=>1,
            'userid'=>$id,
            'game'=>'jsssc'
        );
        $sscxiazhujinetype3 =M('order')->where($where)->sum('del_points');
        //如果选择的位数已经超过了五个直接报错：格式不正确
        if ($info[2] * strlen($info[0]) * strlen($info[1]) >= C_config('jsssc_zuidi') &&$info[2] * strlen($info[0]) * strlen($info[1])+$sscxiazhujinetype3 <= C_config('jsssc_fd_sum') &&$info[2] * strlen($info[0]) * strlen($info[1])+$alljine<C_config('jsssc_djzg')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2] * strlen($info[0]) * strlen($info[1]);
            $data['type'] = 3;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('jsssc_zuidi').'-'.C_config('jsssc_fd_sum').'本期压注总金额：'.$sscxiazhujinetype3.'单局最高'.C_config('jsssc_djzg');
        }
    }
    //特码大小单双 总/大/500
    if (preg_match('/^(总){1}+\/{1}+(大|小|单|双)+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian =$info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>4,
            'state'=>1,
            'userid'=>$id,
            'game'=>'jsssc',
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $sscxiazhujinetype3 =M('order')->where($where)->sum('del_points');
        //如果选择的位数已经超过了五个直接报错：格式不正确
        if ($info[2] >= C_config('jsssc_zuidi') &&$info[2]+$sscxiazhujinetype3 <= C_config('jsssc_fd_zhdxds') &&$info[2]+$alljine<C_config('jsssc_djzg')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2];
            $data['type'] = 4;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('jsssc_zuidi').'-'.C_config('jsssc_fd_zhdxds').'本期压注总金额：'.$sscxiazhujinetype3.'单局最高'.C_config('jsssc_djzg');
        }
    }
    //特码abc  A/500
    if (preg_match('/^(A|B|C){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian=$info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>5,
            'state'=>1,
            'userid'=>$id,
            'game'=>'jsssc',
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $sscxiazhujinetype3 =M('order')->where($where)->sum('del_points');
        //如果选择的位数已经超过了五个直接报错：格式不正确

        if ($info[1] >= C_config('jsssc_zuidi') &&$info[1]+$sscxiazhujinetype3 <= C_config('jsssc_fd_abc') &&$info[1]+$alljine<C_config('jsssc_djzg')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[1];
            $data['type'] = 5;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('jsssc_zuidi').'-'.C_config('jsssc_fd_abc').'本期压注总金额：'.$sscxiazhujinetype3.'单局最高'.C_config('jsssc_djzg');
        }
    }
    //龙虎和
    if (preg_match('/^(龙|虎|和){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian =$info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>6,
            'state'=>1,
            'userid'=>$id,
            'game'=>'jsssc',
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $sscxiazhujinetype3 =M('order')->where($where)->sum('del_points');
        //如果选择的位数已经超过了五个直接报错：格式不正确
        if ($info[1] >= C_config('jsssc_zuidi') &&$info[1]+$sscxiazhujinetype3 <= C_config('jsssc_fd_lh') &&$info[1]+$alljine<C_config('jsssc_djzg')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[1];
            $data['type'] = 6;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('jsssc_zuidi').'-'.C_config('jsssc_fd_lh').'本期压注总金额：'.$sscxiazhujinetype3.'单局最高'.C_config('jsssc_djzg');
        }
    }
    //前后中 顺子，半顺，杂六
    if (preg_match('/^(前|中|后){1}+\/{1}+(顺子|半顺|杂六|豹子|对子){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian =$info[1];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>7,
            'state'=>1,
            'userid'=>$id,
            'game'=>'jsssc',
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $sscxiazhujinetype3 =M('order')->where($where)->sum('del_points');
        //如果选择的位数已经超过了五个直接报错：格式不正确
        if ($info[1] >= C_config('jsssc_zuidi') &&$info[1]+$sscxiazhujinetype3 <= C_config('jsssc_fd_qzh') &&$info[1]+$alljine<C_config('jsssc_djzg')) {
            $data['start'] = serialize(str_split($info[0]));
            $data['points'] = $info[2];
            $data['type'] = 7;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('jsssc_zuidi').'-'.C_config('jsssc_fd_qzh').'本期压注总金额：'.$sscxiazhujinetype3.'单局最高'.C_config('jsssc_djzg');
        }
    }
    return $data;
}
//--------------------------------快3验证------------------------
function check_format_kuai3($message,$id)
{
    $dankaijiangdata = getkuai3();
    $dankaijiangqihao =  $dankaijiangdata['next']['periodNumber'];
    //当局总金额
    $wheres =array(
        'number' =>$dankaijiangqihao,
        'state'=>1,
        'userid'=>$id,
        'game'=>'kuai3',
    );
    $alljine =M('order')->where($wheres)->sum('del_points');
    $data['error'] = 1;
    //和
    if (preg_match('/^\d{1,2}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>1,
            'state'=>1,
            'userid'=>$id,
            'game'=>'kuai3',
            'jincai'=> array('like', "%$chaxuntiaojian/%"),
        );
        $jndxiazhujinetype4 =M('order')->where($where)->sum('del_points');
        $alln = $info[0];
        if ($alln <= 18 && $alln>3) {
            if ($info[1] >= C_config('kuai3_zuidi') && $info[1]+$jndxiazhujinetype4 <= C_config('kuai_check_hz')&&$info[1]+$alljine < C_config('kuai_all_jine')) {
                $data['points'] = $info[1];
                $data['type'] = 1;
            }else{
                $data['error'] = 0;
                $data['money'] =  C_config('kuai3_zuidi').'-'.C_config('kuai_check_hz').'本期压注总金额：'.$jndxiazhujinetype4.'单局最高'.C_config('kuai_all_jine');
            }

        } else {
            $data['error'] = 1;
        }
    }
    //豹子
    if (preg_match('/^(豹子){1}+\/{1}+\d+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $checkdx = str_split($info[1]);
        $dpres =0;
        foreach ($checkdx as $value){
            if($value<1 ||$value>6){
                $dpres++;
            }
        }
        if($dpres >0){
            //meiyou fanyin
        }else{
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>3,
            'state'=>1,
            'userid'=>$id,
            'game'=>'kuai3',
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype5 =M('order')->where($where)->sum('del_points');
        if ($info[2] >= C_config('kuai3_zuidi') && $info[2]+$jndxiazhujinetype5 <= C_config('kuai_check_baozi')&&$info[2]+$alljine < C_config('kuai_all_jine')) {
            $data['points'] = $info[2]*strlen($info[1]);
            $data['type'] = 3;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('kuai3_zuidi').'-'.C_config('kuai_check_baozi').'本期压注总金额：'.$jndxiazhujinetype5.'单局最高'.C_config('kuai_all_jine');
        }
        }
    }
    //  短牌/245/500
    if (preg_match('/^(短牌){1}+\/{1}+\d+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $checkdx = str_split($info[1]);
        $dpres =0;
        foreach ($checkdx as $value){
            if($value<1 ||$value>6){
                $dpres++;
            }
        }
        if($dpres >0){
            //meiyou fanyin
        }else{
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>9,
            'state'=>1,
            'game'=>'kuai3',
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype5 =M('order')->where($where)->sum('del_points');
        if ($info[2] >= C_config('kuai3_zuidi') && $info[2]+$jndxiazhujinetype5 <= C_config('kuai_check_dp')&&$info[2]+$alljine < C_config('kuai_all_jine')) {
            $data['points'] = $info[2]*strlen($info[1]);
            $data['type'] = 9;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('kuai3_zuidi').'-'.C_config('kuai_check_dp').'本期压注总金额：'.$jndxiazhujinetype5.'单局最高'.C_config('kuai_all_jine');
        }
        }
    }
    //顺子
    if (preg_match('/^(顺子){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>4,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype5 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C_config('kuai3_zuidi') && $info[1]+$jndxiazhujinetype5 <= C_config('kuai_check_shunzi')&&$info[1]+$alljine < C_config('kuai_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 4;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('kuai3_zuidi').'-'.C_config('kuai_check_shunzi').'本期压注总金额：'.$jndxiazhujinetype5.'单局最高'.C_config('kuai_all_jine');
        }
    }
    //长牌
    if (preg_match('/^(长牌){1}+\/{1}+([\s\S]*?)+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>12,
            'state'=>1,
            'userid'=>$id,
            'game'=>'kuai3',
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype5 =M('order')->where($where)->sum('del_points');
        if ($info[2] >= C_config('kuai3_zuidi') && $info[2]+$jndxiazhujinetype5 <= C_config('kuai_check_cp')&&$info[2]+$alljine < C_config('kuai_all_jine')) {
            $data['points'] = $info[2];
            $data['type'] = 12;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('kuai3_zuidi').'-'.C_config('kuai_check_cp').'本期压注总金额：'.$jndxiazhujinetype5.'单局最高'.C_config('kuai_all_jine');
        }
    }
    //大小单双
    if (preg_match('/^(大|小|单|双){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>11,
            'state'=>1,
            'game'=>'kuai3',
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype5 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C_config('kuai3_zuidi') && $info[1]+$jndxiazhujinetype5 <= C_config('kuai_check_dxds')&&$info[1]+$alljine < C_config('kuai_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] = 11;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('kuai3_zuidi').'-'.C_config('kuai_check_dxds').'本期压注总金额：'.$jndxiazhujinetype5.'单局最高'.C_config('kuai_all_jine');
        }
    }
    //三军/231/50
    if (preg_match('/^(三军){1}+\/{1}+\d+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $checkdx = str_split($info[1]);
        $dpres =0;
        foreach ($checkdx as $value){
            if($value<1 ||$value>6){
                $dpres++;
            }
        }
        if($dpres >0){
            //meiyou fanyin
        }else{
            $chaxuntiaojian = $info[0];
            $where  = array(
                'number'=>$dankaijiangqihao,
                'type'=>13,
                'state'=>1,
                'game'=>'kuai3',
                'userid'=>$id,
                'jincai'=> array('like', "%$chaxuntiaojian%"),
            );
            $jndxiazhujinetype5 =M('order')->where($where)->sum('del_points');
            if ($info[2] >= C_config('kuai3_zuidi') && $info[2]+$jndxiazhujinetype5 <= C_config('kuai_check_sj')&&$info[2]+$alljine < C_config('kuai_all_jine')) {
                $data['points'] = $info[2]*strlen($info[1]);
                $data['type'] = 13;
            } else {
                $data['error'] = 0;
                $data['money'] = C_config('kuai3_zuidi').'-'.C_config('kuai_check_sj').'本期压注总金额：'.$jndxiazhujinetype5.'单局最高'.C_config('kuai_all_jine');
            }
        }
    }
    //全豹
    if (preg_match('/^(全豹){1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where  = array(
            'number'=>$dankaijiangqihao,
            'type'=>15,
            'state'=>1,
            'userid'=>$id,
            'jincai'=> array('like', "%$chaxuntiaojian%"),
        );
        $jndxiazhujinetype5 =M('order')->where($where)->sum('del_points');
        if ($info[1] >= C_config('kuai3_zuidi') && $info[1]+$jndxiazhujinetype5 <= C_config('kuai_check_quanb')&&$info[1]+$alljine < C_config('kuai_all_jine')) {
            $data['points'] = $info[1];
            $data['type'] =15;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('kuai3_zuidi').'-'.C_config('kuai_check_quanb').'本期压注总金额：'.$jndxiazhujinetype5.'单局最高'.C_config('kuai_all_jine');
        }
    }
    return $data;
}

function check_format_lhc($message,$id)
{
   $lhc = getgamedata('lhc');
   $lhcnextper = $lhc['next']['periodNumber'];
    //当局总金额
    $wheres =array(
        'number' =>$lhcnextper,
        'state'=>1,
        'game'=>'lhc',
        'userid'=>$id,
    );
    $alljine =M('order')->where($wheres)->sum('del_points');
    $data['error'] = 1;
    // type=1  特码/10/500
    if (preg_match('/^(特码){1}+\/{1}+([\s\S]*?)+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where = array(
            'number' => $lhcnextper,
            'type' => 1,
            'state' => 1,
            'game' => 'lhc',
            'userid' => $id,
            'jincai' => array('like', "%$chaxuntiaojian/%"),
        );
        $lhc_now_xiazhu = M('order')->where($where)->sum('del_points');
        if ($info[2] >= C_config('lhc_zuidi') && $info[2] + $lhc_now_xiazhu <= C_config('lhc_fending_tema') && $info[2] + $alljine < C_config('lhc_all_jine')) {
            $data['points'] = $info[2];
            $data['type'] = 1;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('lhc_zuidi') . '-' . C_config('lhc_fending_tema') . '本期压注总金额：' . $lhc_now_xiazhu . '单局最高' . C_config('lhc_all_jine');
        }
    }
    //色波  type=2  色波/红/100
    if (preg_match('/^(色波){1}+\/{1}+([\s\S]*?)+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where = array(
            'number' => $lhcnextper,
            'type' => 2,
            'state' => 1,
            'game' => 'lhc',
            'userid' => $id,
            'jincai' => array('like', "%$chaxuntiaojian/%"),
        );
        $lhc_now_xiazhu = M('order')->where($where)->sum('del_points');
        if ($info[2] >= C_config('lhc_zuidi') && $info[2] + $lhc_now_xiazhu <= C_config('lhc_fending_sebo') && $info[2] + $alljine < C_config('lhc_all_jine')) {
            $data['points'] = $info[2];
            $data['type'] = 2;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('lhc_zuidi') . '-' . C_config('lhc_fending_sebo') . '本期压注总金额：' . $lhc_now_xiazhu . '单局最高' . C_config('lhc_all_jine');
        }
    }
    //五行  type=3  五行/金/100    五行/木/100
    if (preg_match('/^(五行){1}+\/{1}+([\s\S]*?)+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where = array(
            'number' => $lhcnextper,
            'type' => 3,
            'state' => 1,
            'game' => 'lhc',
            'userid' => $id,
            'jincai' => array('like', "%$chaxuntiaojian/%"),
        );
        $lhc_now_xiazhu = M('order')->where($where)->sum('del_points');
        if ($info[2] >= C_config('lhc_zuidi') && $info[2] + $lhc_now_xiazhu <= C_config('lhc_fending_wuxing') && $info[2] + $alljine < C_config('lhc_all_jine')) {
            $data['points'] = $info[2];
            $data['type'] =3;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('lhc_zuidi') . '-' . C_config('lhc_fending_wuxing') . '本期压注总金额：' . $lhc_now_xiazhu . '单局最高' . C_config('lhc_all_jine');
        }
    }
    //头尾  type=4  头/5/500
    if (preg_match('/^(头|尾){1}+\/{1}+\d{1}+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where = array(
            'number' => $lhcnextper,
            'type' => 4,
            'state' => 1,
            'game' => 'lhc',
            'userid' => $id,
            'jincai' => array('like', "%$chaxuntiaojian/%"),
        );
        $lhc_now_xiazhu = M('order')->where($where)->sum('del_points');
        if ($info[2] >= C_config('lhc_zuidi') && $info[2] + $lhc_now_xiazhu <= C_config('lhc_fending_touwei') && $info[2] + $alljine < C_config('lhc_all_jine')) {
            $data['points'] = $info[2];
            $data['type'] =4;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('lhc_zuidi') . '-' . C_config('lhc_fending_touwei') . '本期压注总金额：' . $lhc_now_xiazhu . '单局最高' . C_config('lhc_all_jine');
        }
    }
    //生肖  type=5  生肖/狗/500
    if (preg_match('/^(生肖){1}+\/{1}+([\s\S]*?)+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where = array(
            'number' => $lhcnextper,
            'type' => 5,
            'state' => 1,
            'game' => 'lhc',
            'userid' => $id,
            'jincai' => array('like', "%$chaxuntiaojian/%"),
        );
        $lhc_now_xiazhu = M('order')->where($where)->sum('del_points');
        if ($info[2] >= C_config('lhc_zuidi') && $info[2] + $lhc_now_xiazhu <= C_config('lhc_fending_shengxiao') && $info[2] + $alljine < C_config('lhc_all_jine')) {
            $data['points'] = $info[2];
            $data['type'] =5;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('lhc_zuidi') . '-' . C_config('lhc_fending_shengxiao') . '本期压注总金额：' . $lhc_now_xiazhu . '单局最高' . C_config('lhc_all_jine');
        }
    }
    //平特肖 type=6  平特肖/狗/500
    if (preg_match('/^(平特肖){1}+\/{1}+([\s\S]*?)+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0];
        $where = array(
            'number' => $lhcnextper,
            'type' => 6,
            'state' => 1,
            'game' => 'lhc',
            'userid' => $id,
            'jincai' => array('like', "%$chaxuntiaojian/%"),
        );
        $lhc_now_xiazhu = M('order')->where($where)->sum('del_points');
        if ($info[2] >= C_config('lhc_zuidi') && $info[2] + $lhc_now_xiazhu <= C_config('lhc_fending_pingxiao') && $info[2] + $alljine < C_config('lhc_all_jine')) {
            $data['points'] = $info[2];
            $data['type'] =6;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('lhc_zuidi') . '-' . C_config('lhc_fending_pingxiao') . '本期压注总金额：' . $lhc_now_xiazhu . '单局最高' . C_config('lhc_all_jine');
        }
    }
    //两面 type=7  两面/特大/500   两面/合大/500
    if (preg_match('/^(两面){1}+\/{1}+([\s\S]*?)+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $chaxuntiaojian = $info[0].'/'.$info[1];
        $where = array(
            'number' => $lhcnextper,
            'type' => 7,
            'state' => 1,
            'game' => 'lhc',
            'userid' => $id,
            'jincai' => array('like', "%$chaxuntiaojian/%"),
        );
        $lhc_now_xiazhu = M('order')->where($where)->sum('del_points');
        if ($info[2] >= C_config('lhc_zuidi') && $info[2] + $lhc_now_xiazhu <= C_config('lhc_fending_two') && $info[2] + $alljine < C_config('lhc_all_jine')) {
            $data['points'] = $info[2];
            $data['type'] =7;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('lhc_zuidi') . '-' . C_config('lhc_fending_two') . '本期压注总金额：' . $lhc_now_xiazhu . '单局最高' . C_config('lhc_all_jine');
        }
    }

    //    三全中/234/10
    if (preg_match('/^(三全中){1}+\/{1}+([\s\S]*?)+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $select_in = explode('.',$info[1]);
        foreach ($select_in as$value){
            if($value>49 || $value<1){
                return $data;
            }
        }
        if (count($select_in) ==3){
        $chaxuntiaojian = $info[0];
        $where = array(
            'number' => $lhcnextper,
            'type' => 12,
            'state' => 1,
            'game' => 'lhc',
            'userid' => $id,
            'jincai' => array('like', "%$chaxuntiaojian/%"),
        );
        $lhc_now_xiazhu = M('order')->where($where)->sum('del_points');
        if ($info[2] >= C_config('lhc_zuidi') && $info[2] + $lhc_now_xiazhu <= C_config('lhc_fending_sqz') && $info[2] + $alljine < C_config('lhc_all_jine')) {
            $data['points'] = $info[2];
            $data['type'] =12;
        } else {
            $data['error'] = 0;
            $data['money'] = C_config('lhc_zuidi') . '-' . C_config('lhc_fending_sqz') . '本期压注总金额：' . $lhc_now_xiazhu . '单局最高' . C_config('lhc_all_jine');
        }
        }
    }
    //三中二/234/10
    if (preg_match('/^(三中二){1}+\/{1}+([\s\S]*?)+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $select_in = explode('.',$info[1]);
        foreach ($select_in as$value){
            if($value>49 || $value<1){
                return $data;
            }
        }
        if (count($select_in) ==3) {
            $chaxuntiaojian = $info[0];
            $where = array(
                'number' => $lhcnextper,
                'type' => 8,
                'state' => 1,
                'game' => 'lhc',
                'userid' => $id,
                'jincai' => array('like', "%$chaxuntiaojian/%"),
            );
            $lhc_now_xiazhu = M('order')->where($where)->sum('del_points');
            if ($info[2] >= C_config('lhc_zuidi') && $info[2] + $lhc_now_xiazhu <= C_config('lhc_fending_sze') && $info[2] + $alljine < C_config('lhc_all_jine')) {
                $data['points'] = $info[2];
                $data['type'] = 8;
            } else {
                $data['error'] = 0;
                $data['money'] = C_config('lhc_zuidi') . '-' . C_config('lhc_fending_sze') . '本期压注总金额：' . $lhc_now_xiazhu . '单局最高' . C_config('lhc_all_jine');
            }
        }
    }
    //二中特/23/232
    if (preg_match('/^(二中特){1}+\/{1}+([\s\S]*?)+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $select_in = explode('.',$info[1]);
        foreach ($select_in as$value){
            if($value>49 || $value<1){
                return $data;
            }
        }
        if (count($select_in) ==2) {
            $chaxuntiaojian = $info[0];
            $where = array(
                'number' => $lhcnextper,
                'type' => 9,
                'state' => 1,
                'game' => 'lhc',
                'userid' => $id,
                'jincai' => array('like', "%$chaxuntiaojian/%"),
            );
            $lhc_now_xiazhu = M('order')->where($where)->sum('del_points');
            if ($info[2] >= C_config('lhc_zuidi') && $info[2] + $lhc_now_xiazhu <= C_config('lhc_fending_ezt') && $info[2] + $alljine < C_config('lhc_all_jine')) {
                $data['points'] = $info[2];
                $data['type'] = 9;
            } else {
                $data['error'] = 0;
                $data['money'] = C_config('lhc_zuidi') . '-' . C_config('lhc_fending_ezt') . '本期压注总金额：' . $lhc_now_xiazhu . '单局最高' . C_config('lhc_all_jine');
            }
        }
    }
    //二全中
    if (preg_match('/^(二全中){1}+\/{1}+([\s\S]*?)+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $select_in = explode('.',$info[1]);
        foreach ($select_in as $value){
            if($value>49 || $value<1){
                return $data;
            }
        }
        if (count($select_in) ==2) {
            $chaxuntiaojian = $info[0];
            $where = array(
                'number' => $lhcnextper,
                'type' => 10,
                'state' => 1,
                'game' => 'lhc',
                'userid' => $id,
                'jincai' => array('like', "%$chaxuntiaojian/%"),
            );
            $lhc_now_xiazhu = M('order')->where($where)->sum('del_points');
            if ($info[2] >= C_config('lhc_zuidi') && $info[2] + $lhc_now_xiazhu <= C_config('lhc_fending_eqz') && $info[2] + $alljine < C_config('lhc_all_jine')) {
                $data['points'] = $info[2];
                $data['type'] = 10;
            } else {
                $data['error'] = 0;
                $data['money'] = C_config('lhc_zuidi') . '-' . C_config('lhc_fending_eqz') . '本期压注总金额：' . $lhc_now_xiazhu . '单局最高' . C_config('lhc_all_jine');
            }
        }
    }
    //四全中/50/50
    if (preg_match('/^(四全中){1}+\/{1}+([\s\S]*?)+\/{1}+\d+$/', $message)) {
        $info = explode('/', $message);
        $select_in = explode('.',$info[1]);
        foreach ($select_in as $value){
            if($value>49 || $value<1){
                return $data;
            }
        }
        if (count($select_in) ==4) {
            $chaxuntiaojian = $info[0];
            $where = array(
                'number' => $lhcnextper,
                'type' => 11,
                'state' => 1,
                'game' => 'lhc',
                'userid' => $id,
                'jincai' => array('like', "%$chaxuntiaojian/%"),
            );
            $lhc_now_xiazhu = M('order')->where($where)->sum('del_points');
            if ($info[2] >= C_config('lhc_zuidi') && $info[2] + $lhc_now_xiazhu <= C_config('lhc_fending_siqz') && $info[2] + $alljine < C_config('lhc_all_jine')) {
                $data['points'] = $info[2];
                $data['type'] = 11;
            } else {
                $data['error'] = 0;
                $data['money'] = C_config('lhc_zuidi') . '-' . C_config('lhc_fending_siqz') . '本期压注总金额：' . $lhc_now_xiazhu . '单局最高' . C_config('lhc_all_jine');
            }
        }
    }
    //特合 type=7  特合/大/500
//    if (preg_match('/^(特合){1}+\/{1}+([\s\S]*?)+\/{1}+\d+$/', $message)) {
//        $info = explode('/', $message);
//        $chaxuntiaojian = $info[0];
//        $where = array(
//            'number' => $lhcnextper,
//            'type' => 7,
//            'state' => 1,
//            'game' => 'lhc',
//            'userid' => $id,
//            'jincai' => array('like', "%$chaxuntiaojian/%"),
//        );
//        $lhc_now_xiazhu = M('order')->where($where)->sum('del_points');
//        if ($info[2] >= C_config('lhc_zuidi') && $info[2] + $lhc_now_xiazhu <= C_config('lhc_fending_tehe') && $info[2] + $alljine < C_config('lhc_all_jine')) {
//            $data['points'] = $info[2];
//            $data['type'] =7;
//        } else {
//            $data['error'] = 0;
//            $data['money'] = C_config('lhc_zuidi') . '-' . C_config('lhc_fending_tehe') . '本期压注总金额：' . $lhc_now_xiazhu . '单局最高' . C_config('lhc_all_jine');
//        }
//    }
    return $data;
}
//龙虎
function lh($data){
//    echo $data;
    if($data =='龙'){
        echo "lh1";
    }elseif($data =='虎'){
        echo "lh2";
    }elseif($data =='和'){
        echo "s11";
    }

}
//大小
function pk10_dx($data){

    if($data =='小'){
        echo "dx2";
    }else{
        echo "dx1";
    }
}
//单双
function pk10_ds($data){
    if($data =='单'){
        echo "ds2";
    }else{
        echo "ds1";
    }
}
//根据id 获取 名称
function curl_https($url, $data = array(), $header = array(), $timeout = 30)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $response = curl_exec($ch);
    if ($error = curl_error($ch)) {
        die($error);
    }
    curl_close($ch);
    return $response;

}
//php异步操作不关心返回值，直接进行下一步
function _sock($url) {
    $host = parse_url($url,PHP_URL_HOST);
    $port = parse_url($url,PHP_URL_PORT);
    $port = $port ? $port : 80;
    $scheme = parse_url($url,PHP_URL_SCHEME);
    $path = parse_url($url,PHP_URL_PATH);
    $query = parse_url($url,PHP_URL_QUERY);
    if($query) $path .= '?'.$query;
    if($scheme == 'https') {
        $host = 'ssl://'.$host;
    }
    $fp = fsockopen($host,$port,$error_code,$error_msg,1);
    if(!$fp) {
        return array('error_code' => $error_code,'error_msg' => $error_msg);
    }
    else {
        stream_set_blocking($fp,true);//开启了手册上说的非阻塞模式
        stream_set_timeout($fp,1);//设置超时
        $header = "GET $path HTTP/1.1\r\n";
        $header.="Host: $host\r\n";
        $header.="Connection: close\r\n\r\n";//长连接关闭
        fwrite($fp, $header);
        usleep(1000); // 这一句也是关键，如果没有这延时，可能在nginx服务器上就无法执行成功
        fclose($fp);
        return array('error_code' => 0);
    }
}
function updata(){
    _sock('http://car.com/home/api/update');
}
function retrieval($phone,$viey)
{
    $uid=C('sms_id');
    $key=C('sms_key');
    $content="正在进行验证，验证码为：" . $viey . "，请于15分钟内正确输入，如非本人操作，请忽略此短信。";
//    $content="验证码:" . $viey;
    $url = 'http://utf8.sms.webchinese.cn/?Uid='.$uid.'&Key='.$key.'&smsMob='.$phone.'&smsText='.$content;
    if (function_exists('file_get_contents')) {
        $file_contents = file_get_contents($url);
    } else {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $file_contents = curl_exec($ch);
        curl_close($ch);
    }
    return $file_contents;
}
function kuaihezhibl($type){
    $dataarr = explode(',',C_config('kuai3_hezhi_bv'));
    $data = $dataarr[$type];
    $resarr =explode('=',$data);
    echo $resarr[1];
}










?>