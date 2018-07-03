<?php

namespace Admin\Controller;

use Think\Controller;

header("Content-type: text/html; charset=utf-8");

class AutoController extends Controller
{
    public function __construct()
    {
        $config = S('DB_CONFIG_GAME_DATA');
        if (!$config) {
            $config = M('game_config')->where("id", 1)->find();
            S('DB_CONFIG_GAME_DATA', $config);
        }
        foreach ($config as $key => $value) {
            if ($key != "id") {
                C(json_decode($value, true)); //添加配置
            }
        }

        parent::__construct();
    }

    protected $type_title = array("ssc" => "重庆时时彩", "fei" => "幸运飞艇", "jnd28" => "加拿大28", "jsssc" => "极速时时彩", "jscar" => "极速赛车", "bj28" => "北京28", "pk10" => "北京赛车", "kuai3" => "快3", 'lhc' => '六合彩');
    //如果要取消游戏，把游戏从数组中减去，降低性能损耗
    //    protected $type_title = array("ssc" => "重庆时时彩", "fei" => "幸运飞艇", "jnd28" => "加拿大28", "jsssc" => "极速时时彩", "jscar" => "极速赛车", "bj28" => "北京28", "pk10" => "北京赛车", "kuai3" => "快3");
    public function index()
    {
        set_time_limit(0);
        if (IS_AJAX) {
            //判断期数不一样储存，在结算
//            $this->kuai3();
//            $this->jnd28js();
//            $this->Bj28js();
//            $this->ssc();
//            $this->jscar();
//            $this->jsssc();
//            $this->jslhc();
//            //结算pk10
//            $this->Bjpk10();
//            echo "结算完成";
        } else {
            $data = $_SERVER["SERVER_NAME"];
            $this->assign('severname', $data);
            $this->display();
        }
    }

    public function nodejs_get_data(){
        $data= json_decode(file_get_contents('php://input'),true);
        if($data['gengxin']){
            //游戏更新余额
            $this->updatepoints($data['game']);
        }else{
            S('caiji_' . $data['game'], $data);
            return json_encode('aaa');
        }
    }
    public function updatepoint(){
        $this->updatepoints('jsssc');
    }
    public function test2(){
        dump(F('test'));
        dump(json_decode(F('test'),true));
    }
    public function update()
    {
        header("Content-type: text/html; charset=utf-8");
        ignore_user_abort(true);
        set_time_limit(0);
        ob_end_clean();
        ob_implicit_flush(1);
        caiji();
        //保存数据
        foreach ($this->type_title as $key => $value) {
            $fun = 'save_' . $key;
            $this->$fun();
        }

        $h = date("G");
        if ($h >= 12 && $h <= 14) {
            $this->fanshui();
            $this->yonjinjs();
            $this->checkredpack();
        }
        die("完成任务");
    }

    public function go_save_fei(){
        if($_SERVER['SERVER_ADDR'] != '127.0.0.1')
        {
            die('未授权的IP不允许访问');
        }
        $data= json_decode(file_get_contents('php://input'),true);
        $s = S('caiji_fei');
        if($data['current']['periodNumber'] == $s['current']['periodNumber'])
        {
            die('fei已保存过了');
        }
        $this->nodejs_get_data();
        $this->save_fei();
        //$this->fei();
        //die("success");
    }
    public function go_save_jsssc(){
        if($_SERVER['SERVER_ADDR'] != '127.0.0.1')
        {
            die('未授权的IP不允许访问');
        }
        $data= json_decode(file_get_contents('php://input'),true);
        $s = S('caiji_jsssc');
        if($data['current']['periodNumber'] == $s['current']['periodNumber'])
        {
            die('jsssc已保存过了');
        }
        $this->nodejs_get_data();
        $this->save_jsssc();
        //var_dump(S('caiji_jsssc'));
        //$this->fei();
        die("success");
    }

    public function go_save_jscar(){
        if($_SERVER['SERVER_ADDR'] != '127.0.0.1')
        {
            die('未授权的IP不允许访问');
        }
        $data= json_decode(file_get_contents('php://input'),true);
        $s = S('caiji_jscar');
        if($data['current']['periodNumber'] == $s['current']['periodNumber'])
        {
            die('jscar已保存过了');
        }
        $this->nodejs_get_data();
        $this->save_jscar();
        //var_dump(S('caiji_jscar'));
        //$this->fei();
        die("success");
    }

    public function go_save_pk10(){
        if($_SERVER['SERVER_ADDR'] != '127.0.0.1')
        {
            die('未授权的IP不允许访问');
        }
        $data= json_decode(file_get_contents('php://input'),true);
        $s = S('caiji_pk10');
        if($data['current']['periodNumber'] == $s['current']['periodNumber'])
        {
            die('pk10已保存过了');
        }
        $this->nodejs_get_data();
        $this->save_pk10();
        //$this->fei();
        die("success");
    }
    //保存数据并结算
    private function save_pk10()
    {
        if (!S('pk10_save')) {
            S('pk10_save', 1, 60);
        } else {
            return false;
        }
        //存期号码
        $waipk10 = getPK10();
        if (F('wai_pk10_data') !== $waipk10['current']['periodNumber']) {
            $this->save_pk10_fei_jscar($waipk10, 'pk10');
            F('wai_pk10_data', $waipk10['current']['periodNumber']);
            $this->Bjpk10();
        }
        S('pk10_save', 0);

    }

    private function save_fei()
    {
        if (!S('fei_save')) {
            S('fei_save', '11', 60);
        } else {
            return false;
        }
        $fei_datas = getfei();
        echo F('fei_periodNumber').'@@'.$fei_datas['current']['periodNumber'];
        if (F('fei_periodNumber') != $fei_datas['current']['periodNumber']) {
            $this->save_pk10_fei_jscar($fei_datas, 'fei');
            F('fei_periodNumber', $fei_datas['current']['periodNumber']);
            $this->fei();
        }
        S('fei_save', null);

    }

    private function save_kuai3()
    {
        if (!S('kuai3_save')) {
            S('kuai3_save', '11', 60);
        } else {
            return false;
        }
        $kuai3_datas = getkuai3();
        if (F('kuai3_periodNumber') != $kuai3_datas['current']['periodNumber']) {
            $res = M('kuainumber')->where("periodnumber = {$kuai3_datas['current']['periodNumber']}")->find();
            if (!$res) {
                $map['awardnumbers'] = $kuai3_datas['current']['awardNumbers'];
                $map['awardtime'] = $kuai3_datas['current']['awardTime'];
                $map['time'] = strtotime($kuai3_datas['current']['awardTime']);
                $map['periodnumber'] = $kuai3_datas['current']['periodNumber'];
                $info = explode(',', $map['awardnumbers']);
                $n1 = $info[0];
                $n2 = $info[1];
                $n3 = $info[2];
                //总和，赋值给总和.
                $alln = $n1 + $n2 + $n3;
                //判断是否为顺子
                $ss = $n1 . $n2 . $n3;
                $shunzi = '';
                if (preg_match('/^(0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)){2}\d$/', $ss)) {
                    $shunzi = "顺子";
                } else {
                    $shunzi = "非顺子";
                }
                //判断是否为豹子
                $bz = '二同号';
                if ($n1 == $n2 && $n1 == $n3 && $n2 == $n3) {
                    $bz = "豹子";
                } elseif ($n1 !== $n2 && $n1 !== $n3 && $n2 !== $n3) {
                    $bz = "三不同";
                }
                //判断对子
                $duizinum = 0;
                if ($n1 == $n2) {
                    $duizinum = $duizinum + 1;
                }
                if ($n1 == $n3) {
                    $duizinum = $duizinum + 1;
                }
                if ($n2 == $n3) {
                    $duizinum = $duizinum + 1;
                }
                $erbutongdan = '二不同';
                $dz = '';
                if ($duizinum == 1) {
                    $dz = "二同号";
                    if ($n1 == $n2 && $n1 !== $n3) {
                        $erbutongdan = $n1;
                    } elseif ($n1 == $n3 && $n1 !== $n2) {
                        $erbutongdan = $n1;
                    } elseif ($n1 !== $n2 && $n1 !== $n3 && $n2 == $n3) {
                        $erbutongdan = $n2;
                    }
                } else {
                    $dz = "二不同";
                }
                if ($alln >= 3 && $alln <= 10) {
                    $dx = '小';
                } elseif ($alln >= 11 && $alln <= 18) {
                    $dx = "大";
                }
                if ($alln % 2 == 0) {
                    $ds = '双';
                } else {
                    $ds = '单';
                }
                $map['ds'] = $ds;
                $map['dx'] = $dx;
                $map['zonghe'] = $alln;
                $map['ertonghao'] = $dz;
                $map['erbutongdan'] = $erbutongdan;
                $map['santonghaotong'] = $bz;
                $map['sz'] = $shunzi;
                $map['game'] = 'kuai3';
                $res1 = M('kuainumber')->add($map);
            }
            F('kuai3_periodNumber', $kuai3_datas['current']['periodNumber']);
            $this->kuai3();
        }
        S('kuai3_save', null);
    }

    private function save_lhc()
    {
        if (!S('lhc_save')) {
            S('lhc_save', '11', 60);
        } else {
            return false;
        }
        $lhc_datas = getlhc();
        if (F('lhc_periodNumber') != $lhc_datas['current']['periodNumber']) {
            $res = M('lhcnumber')->where("periodnumber = {$lhc_datas['current']['periodNumber']}")->find();
            if (!$res) {
                $number1 = explode(',', $lhc_datas['current']['awardNumbers']);
                //当期特码
                $tema = $number1[6];
                $map['awardnumbers'] = $lhc_datas['current']['awardNumbers'];
                $map['awardtime'] = $lhc_datas['current']['awardTime'];
                $map['time'] = strtotime($lhc_datas['current']['awardTime']);
                $map['periodnumber'] = $lhc_datas['current']['periodNumber'];
                $map['game'] = 'lhc';
                $map['tema_sebo'] = get_lhc_sebo($tema);
                $map['tema_wuxing'] = get_lhc_wuxing($tema);
                $map['shengxiao_all'] = json_encode(get_all_shengxiao($lhc_datas['current']['awardNumbers']));
                $tema_shengxiao = seeshengxiao($tema);
                $map['tema_shengxiao'] = $tema_shengxiao;
                $res1 = M('lhcnumber')->add($map);
            }
            F('lhc_periodNumber', $lhc_datas['current']['periodNumber']);
            $this->lhcjs();
        }
        S('lhc_save', null);
    }

    private function save_jnd28()
    {
        if (!S('jnd28_save')) {
            S('jnd28_save', '11', 60);
        } else {
            return false;
        }
        //10.存储加拿大的每期的结果----------------------------------------------------------------------------------------
        $jnd_datas = getJnd28();
        if (F('jnd_periodNumber') != $jnd_datas['current']['periodNumber']) {
            $this->save_jnd28_bj28($jnd_datas, 'jnd28');
            F('jnd_periodNumber', $jnd_datas['current']['periodNumber']);
            $this->jnd28js();
        }
        S('jnd28_save', null);
    }

    private function save_bj28()
    {
        if (!S('bj28_save')) {
            S('bj28_save', '11', 60);
        } else {
            return false;
        }
        //10.存储北京28的每期的结果----------------------------------------------------------------------------------------
        $bj28_datas = getBj28();
        if (F('bj28_periodNumber') != $bj28_datas['current']['periodNumber']) {
            $this->save_jnd28_bj28($bj28_datas, 'bj28');
            F('bj28_periodNumber', $bj28_datas['current']['periodNumber']);
            //结算
            $this->Bj28js();
        }
        S('bj28_save', null);

    }

    private function save_jscar()
    {
        if (!S('jscar_save')) {
            S('jscar_save', '11', 60);
        } else {
            return false;
        }
        //10.存储极速赛车的每期的结果----------------------------------------------------------------------------------------
        $pk10_datas = getjscar();
        if (F('jscar_periodNumber') != $pk10_datas['current']['periodNumber']) {
            $this->save_pk10_fei_jscar($pk10_datas, 'jscar');
            F('jscar_periodNumber', $pk10_datas['current']['periodNumber']);
            $this->jscar();
        }
        S('jscar_save', null);
    }

    private function save_ssc()
    {
        //10.存储时时彩的每期的结果----------------------------------------------------------------------------------------
        if (!S('ssc_save')) {
            S('ssc_save', '11', 60);
        } else {
            return false;
        }
        $ssc_datas = getssc();
        if (F('ssc_periodNumber') != $ssc_datas['current']['periodNumber']) {
            $this->save_ssc_jsssc($ssc_datas, 'ssc');
            F('ssc_periodNumber', $ssc_datas['current']['periodNumber']);
            $this->ssc();
//				$this->zidongjiesuan();//存结果的时候顺便结算
        }
        S('ssc_save', null);

    }

    private function save_jsssc()
    {
        if (!S('jsssc_save')) {
            S('jsssc_save', '11', 60);
        } else {
            return false;
        }
        //10.极速时时彩的每期的结果----------------------------------------------------------------------------------------
        $jsssc_datas = getjsssc();
        if (F('jsssc_periodNumber') != $jsssc_datas['current']['periodNumber']) {
            $this->save_ssc_jsssc($jsssc_datas, 'jsssc');
            F('jsssc_periodNumber', $jsssc_datas['current']['periodNumber']);
            $this->jsssc();
        }
        S('jsssc_save', null);

    }

    private function save_pk10_fei_jscar($waipk10, $game)
    {
        $res = M('number')->where("periodnumber = {$waipk10['current']['periodNumber']}")->where(array('game' => $game))->find();
        if (!$res) {
            $map['awardnumbers'] = $waipk10['current']['awardNumbers'];
            $map['awardtime'] = $waipk10['current']['awardTime'];
            $map['time'] = strtotime($waipk10['current']['awardTime']);
            $map['periodnumber'] = $waipk10['current']['periodNumber'];
            $info = explode(',', $map['awardnumbers']);
            for ($i = 0; $i < count($info); $i++) {
                if ($info[$i] < 10) {
                    if (strlen($info[$i]) > 1) {
                        $info[$i] = substr($info[$i], 1);
                    }
                }
            }
            $map['number'] = json_encode($info);
            if ($info[0] > $info[9]) {
                $lh[0] = '龙';
            } else {
                $lh[0] = '虎';
            }
            if ($info[1] > $info[8]) {
                $lh[1] = '龙';
            } else {
                $lh[1] = '虎';
            }
            if ($info[2] > $info[7]) {
                $lh[2] = '龙';
            } else {
                $lh[2] = '虎';
            }
            if ($info[3] > $info[6]) {
                $lh[3] = '龙';
            } else {
                $lh[3] = '虎';
            }
            if ($info[4] > $info[5]) {
                $lh[4] = '龙';
            } else {
                $lh[4] = '虎';
            }
            $map['lh'] = json_encode($lh);
            $map['tema'] = $info[0] + $info[1];
            if ($map['tema'] % 2 == 0) {
                $map['tema_ds'] = '双';
            } else {
                $map['tema_ds'] = '单';
            }
            if ($map['tema'] >= 12) {
                $map['tema_dx'] = '大';
            } else {
                $map['tema_dx'] = '小';
            }
            if ($map['tema'] >= 3 && $map['tema'] <= 7) {
                $map['tema_dw'] = 'A';
            }
            if ($map['tema'] >= 8 && $map['tema'] <= 14) {
                $map['tema_dw'] = 'B';
            }
            if ($map['tema'] >= 15 && $map['tema'] <= 19) {
                $map['tema_dw'] = 'C';
            }
            if ($info[0] > $info[1]) {
                $map['zx'] = '庄';
            } else {
                $map['zx'] = '闲';
            }
            $map['game'] = $game;
            M('number')->add($map);
        }
    }

    private function save_ssc_jsssc($jsssc_datas, $game)
    {
        $res = M('sscnumber')->where("periodnumber = {$jsssc_datas['current']['periodNumber']}")->where(array('game' => $game))->find();
        if (!$res) {
            $map['awardnumbers'] = $jsssc_datas['current']['awardNumbers'];
            $map['awardtime'] = $jsssc_datas['current']['awardTime'];
            $map['time'] = strtotime($jsssc_datas['current']['awardTime']);
            $map['periodnumber'] = $jsssc_datas['current']['periodNumber'];
            $info = explode(',', $map['awardnumbers']);

            $da = "";
            for ($i = 0; $i < count($info); $i++) {
                if ($info[$i] <= 4) {
                    $da = $da . "小/";
                } else {
                    $da = $da . "大/";
                }
            }
            $dansuan = "";
            for ($b = 0; $b < count($info); $b++) {
                if (($info[$b]) % 2 == 0) {
                    $dansuan = $dansuan . "双/";
                } else {
                    $dansuan = $dansuan . "单/";
                }
            }
            $zuhe = "";
            for ($i = 0; $i < count($info); $i++) {
                $sum = $info[$i];
                if ($sum <= 4) {
                    if ($sum % 2 == 0) {
                        $zuhe = $zuhe . "小双/";
                    } else {
                        $zuhe = $zuhe . "小单/";
                    }
                } else {
                    if ($sum % 2 !== 0) {
                        $zuhe = $zuhe . "大单/";
                    } else {
                        $zuhe = $zuhe . "大双/";
                    }
                }
            }
            //特码大小
            $tema = '';
            for ($i = 0; $i < count($info); $i++) {
                $tema = $tema + $info[$i];
            }
            if ($tema >= 23) {
                $tema_dx = '大';
            } else {
                $tema_dx = '小';
            }
            //特码单双
            if ($tema % 2 == 0) {
                $tema_ds = '双';
            } else {
                $tema_ds = '单';
            }
            if ($tema <= 15) {
                $tema_abc = 'A';
            }
            if ($tema >= 16 && $tema <= 29) {
                $tema_abc = 'B';
            }
            if ($tema >= 30 && $tema <= 45) {
                $tema_abc = 'C';
            }
            //龙虎储存
            if ($info[0] - $info[4] > 0) {
                $ssc_lh = '龙';
            }
            if ($info[0] - $info[4] < 0) {
                $ssc_lh = '虎';
            }
            if ($info[0] - $info[4] == 0) {
                $ssc_lh = '和';
            }
            //前中后的值
            $map['lh'] = $ssc_lh;
            $map['tema_abc'] = $tema_abc;
            $map['tema_ds'] = $tema_ds;
            $map['tema_dx'] = $tema_dx;
            $map['zuhe'] = $zuhe;
            $map['ds'] = $dansuan;
            $map['dx'] = $da;
            $map['game'] = $game;
            $res1 = M('sscnumber')->add($map);
        }
    }

    private function save_jnd28_bj28($bj28_datas, $game)
    {
        $res = M('dannumber')->where(array('game' => $game))->where("periodnumber = {$bj28_datas['current']['periodNumber']}")->find();
        if (!$res) {
            $map['awardnumbers'] = $bj28_datas['current']['awardNumbers'];
            $map['awardtime'] = $bj28_datas['current']['awardTime'];
            $map['time'] = strtotime($bj28_datas['current']['awardTime']);
            $map['periodnumber'] = $bj28_datas['current']['periodNumber'];
            $info = explode(',', $map['awardnumbers']);
            $n1 = $info[0];
            $n2 = $info[1];
            $n3 = $info[2];
            $alln = $n1 + $n2 + $n3;
            //总和，赋值给总和.
            $map['zonghe'] = $alln;
            //判断单双
            if ($alln % 2 == 0) {
                $jiou = "双";
            } else {
                $jiou = "单";
            }
            $map['danshuang'] = $jiou;
            //判断大小单双
            if ($jiou == "双") {
                if (0 <= $alln && $alln <= 13) {
                    $daxiaodanshuang = "小双";
                } else {
                    $daxiaodanshuang = "大双";
                }
            }
            if ($jiou == "单") {
                if (0 <= $alln && $alln <= 13) {
                    $daxiaodanshuang = "小单";
                } else {
                    $daxiaodanshuang = "大单";
                }
            }
            //储存大小单双到服务器
            $map['dxds'] = $daxiaodanshuang;
            // 判断极值
            $jizhi = "";
            if (0 <= $alln && $alln <= 5) {
                $jizhi = "极小";
            }
            if (5 < $alln && $alln < 22) {
                $jizhi = "非极";
            }
            if (22 <= $alln && $alln <= 27) {
                $jizhi = "极大";
            }
            //判断是否为顺子
            $shunzi = "";
            $ss = $n1 . $n2 . $n3;
            if (preg_match('/^(0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)){2}\d$/', $ss)) {
                $shunzi = "顺子";
            } else {
                $data = Array($n1, $n2, $n3);
                $result = $this->compare($data, 'asc');
                $sum = '';
                foreach ($result as $value) {
                    $sum .= $value;
                }
                if ($sum == '019' || $sum == '089' || $sum == '012') {
                    $shunzi = "顺子";
                } else {
                    $shunzi = "非顺子";
                }
            }
            //判断是否为豹子
            $bz = "";
            if ($n1 == $n2 && $n1 == $n3 && $n2 == $n3) {
                $bz = "豹子";
            } else {
                $bz = "非豹子";
            }
            //判断为的大小
            $dx = "";
            if ($alln <= 13) {
                $dx = "小";
            } else {
                $dx = "大";
            }
            //判断对子
            $dz = "";
            $duizinum = 0;
            if ($n1 == $n2) {
                $duizinum = $duizinum + 1;
            }
            if ($n1 == $n3) {
                $duizinum = $duizinum + 1;
            }
            if ($n2 == $n3) {
                $duizinum = $duizinum + 1;
            }
            if ($duizinum == 1) {
                $dz = "对子";
            } else {
                $dz = "非对子";
            }
            $map['dz'] = $dz;
            $map['dx'] = $dx;
            $map['bz'] = $bz;
            $map['sz'] = $shunzi;
            $map['jz'] = $jizhi;
            $map['number'] = "55";
            $map['game'] = $game;
            $res1 = M('dannumber')->add($map);
        }
    }

    private function lhcjs()
    {

        if (!(S('lhcjsjiesuan'))) {
            S('lhcjsjiesuan', "1", 60);
        } else {
            return false;
        }

        $list = M('order')->where(array("state" => 1, "game" => 'lhc', "is_add" => 0))->order("time ASC")->select();
        if ($this) {
            for ($i = 0; $i < count($list); $i++) {
                $id = $list[$i]['id'];
                $userid = $list[$i]['userid'];
                //获取开奖号码当期的
                $current_number = M('lhcnumber')->where(array("game" => 'lhc', "periodnumber" => $list[$i]['number']))->find();
                if (!$current_number) {
                    continue;
                }
                //获取当前的开奖号码
                $number1 = explode(',', $current_number['awardnumbers']);
                //当期特码
                $tema = $number1[6];
                //当期的色波
                $sebo = $current_number['tema_sebo'];
                $wuxing = $current_number['tema_wuxing'];
                $shengxiao = $current_number['tema_shengxiao'];
                //当期的总金额
                $dangqianqihao = $current_number['periodnumber'];
                $dangqianalldatas = M('order')->where(array('number' => $dangqianqihao, 'userid' => $userid))->alias('o')->field('sum(add_points)as a ,sum(del_points)as d')->select();
                $dangqianalldata = $dangqianalldatas[0]['d'];
                $addjine = $dangqianalldatas[0]['a'];
                switch ($list[$i]['type']) {
                    //特码/2/50  测试完成
                    case 1:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[1] == $tema) {
                            $peilv = split_pv(C('lhc_tema_bv'), '=');
                            $points1 = $start1[2] * $peilv[$start1[1]];
                            $res1 = $this->add_points($id, $userid, $points1, $addjine);
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;
                    //色波 色波/红/500  测试完成
                    case 2:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[1] == $sebo) {
                            $peilv = split_pv(C('lhc_sebo_bv'), ':');
                            $points1 = $start1[2] * $peilv[$start1[1]];
                            $res1 = $this->add_points($id, $userid, $points1, $addjine);
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;
                    //五行/金/500
                    case 3:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[1] == $wuxing) {
                            $points1 = $start1[2] * C('lhc_wuxing_bv');
                            $res1 = $this->add_points($id, $userid, $points1, $addjine);
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;
                    //头/0/50  测试
                    case 4:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '头') {
                            if ($start1[1] == 0) {
                                if ($tema < 10) {
                                    $points1 = $start1[2] * C('lhc_tou0_bv');
                                    $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                } else {
                                    $res1 = $this->del_points($id);
                                }
                            } else {
                                if ($start1[1] == str_split($tema)[0]) {
                                    $points1 = $start1[2] * C('lhc_tou1_bv');
                                    $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                } else {
                                    $res1 = $this->del_points($id);
                                }
                            }

                        } elseif ($start1[0] == '尾') {
                            $w = '';
                            if ($tema < 10) {
                                $w = $tema;
                            } else {
                                $w = substr($tema, 1);
                            }
                            if ($start1[1] == 0) {
                                if ($start1[1] == $w) {
                                    $points1 = $start1[2] * C('lhc_wei0_bv');
                                    $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                } else {
                                    $res1 = $this->del_points($id);
                                }
                            } else {
                                if ($start1[1] == $w) {
                                    $points1 = $start1[2] * C('lhc_wei1_bv');
                                    $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                } else {
                                    $res1 = $this->del_points($id);
                                }
                            }
                        }
                        break;
                    //生肖/狗/500  测试完成
                    case 5:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($shengxiao == $start1[1]) {
                            $peilvsx = split_pv(C('lhc_shengxiao_bv'), ':');
                            $points1 = $start1[2] * $peilvsx[$start1[1]];
                            $res1 = $this->add_points($id, $userid, $points1, $addjine);
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;
                    //平特肖/狗/5000
                    case 6:
                        $start1 = explode('/', $list[$i]['jincai']);
                        $all_shengxiao_array = json_decode($current_number['shengxiao_all'], true);
                        $xiazhu = $start1[1];
                        $jishu = 0;
                        foreach ($all_shengxiao_array as $value) {
                            if ($value == $xiazhu) {
                                $jishu++;
                            }
                        }
                        if ($jishu > 0) {
                            $peitx = split_pv(C('lhc_pingtexiao_bv'), ':');
                            $points1 = $start1[2] * $peitx[$start1[1]];
                            $res1 = $this->add_points($id, $userid, $points1, $addjine);
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;
                    //两面/特大/500    两面/合大/500 测试
                    case 7:
                        $start1 = explode('/', $list[$i]['jincai']);
                        //特码：
                        if ($tema >= 25 && $tema <= 49) {
                            $te_dx = '特大';
                        } else {
                            $te_dx = '特小';
                        }
                        if ($tema % 2 == 0) {
                            $te_ds = '特双';
                        } else {
                            $te_ds = '特单';
                        }
                        //合码：
                        $dataed = sprintf("%02d", $tema);
                        $hemas = str_split($dataed);
                        $hema = $hemas[0] + $hemas[1];
                        if ($hema >= 7) {
                            $he_dx = '合大';
                        } else {
                            $he_dx = '合小';
                        }
                        if ($hema % 2 == 0) {
                            $he_ds = '合双';
                        } else {
                            $he_ds = '合单';
                        }
                        //两面的赔率
                        $lmplv = split_pv(C('lhc_liangmian_bv'), ':');

                        if ($start1[1] == '特大' || $start1[1] == '特小') {
                            //特码大小单双
                            if ($start1[1] == $te_dx) {
                                $points1 = $start1[2] * $lmplv[$start1[1]];
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                            } else {
                                $res1 = $this->del_points($id);
                            }

                        } elseif ($start1[1] == '特单' || $start1[1] == '特双') {
                            //特码大小单双
                            if ($start1[1] == $te_ds) {
                                $points1 = $start1[2] * $lmplv[$start1[1]];
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                            } else {
                                $res1 = $this->del_points($id);
                            }

                        } elseif ($start1[1] == '合大' || $start1[1] == '合小') {
                            //特码的合码大小单双
                            if ($start1[1] == $he_dx) {
                                $points1 = $start1[2] * $lmplv[$start1[1]];
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        } elseif ($start1[1] == '合单' || $start1[1] == '合双') {
                            //特码的合码大小单双
                            if ($start1[1] == $he_ds) {
                                $points1 = $start1[2] * $lmplv[$start1[1]];
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //三中二/234/10
                    case 8:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '三中二' && $this->checkslhc($start1[1], $current_number['awardnumbers']) >= 2) {
                            $points1 = $start1[2] * C('lhc_sze_bv');
                            $res1 = $this->add_points($id, $userid, $points1, $addjine);
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;
                    // 三全中/234/10
                    case 12:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '三全中' && $this->checkslhc($start1[1], $current_number['awardnumbers']) == 3) {
                            $points1 = $start1[2] * C('lhc_sqz_bv');
                            $res1 = $this->add_points($id, $userid, $points1, $addjine);
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;
                    //二中特/23/232
                    case 9:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '二中特' && $this->checkslhc($start1[1], $tema) == 1) {
                            $points1 = $start1[2] * C('lhc_ezt_bv');
                            $res1 = $this->add_points($id, $userid, $points1, $addjine);
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;
                    //二全中
                    case 10:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '二全中' && $this->checkslhc($start1[1], $current_number['awardnumbers']) == 2) {
                            $points1 = $start1[2] * C('lhc_eqz_bv');
                            $res1 = $this->add_points($id, $userid, $points1, $addjine);
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;
                    //四全中/50/50
                    case 11:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '四全中' && $this->checkslhc($start1[1], $current_number['awardnumbers']) >= 4) {
                            $points1 = $start1[2] * C('lhc_siqz_bv');
                            $res1 = $this->add_points($id, $userid, $points1, $addjine);
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;
                }

            }
        }
        if ($res1) {
            $this->updatepoints('lhc');
        }
        S('lhcjsjiesuan', null);
    }

    public function jnd28js()
    {
        $value = S('jnd28jsjiesuan');
        if (empty($value)) {
            S('jnd28jsjiesuan', "1", 60);
        } else {
            return false;
        }
        //自动结算之前没结算的//查找状态为 is_add = 0 的标识没有记录的，is_add为存入，但是没有处理的。
        $list = M('order')->where(array("state" => 1, "game" => 'jnd28', "is_add" => 0))->order("time ASC")->select();
        if ($list) {
            for ($i = 0; $i < count($list); $i++) {
                $id = $list[$i]['id'];
                $userid = $list[$i]['userid'];
                //获取开奖号码当期的
                $current_number = M('dannumber')->where(array("game" => 'jnd28', "periodnumber" => $list[$i]['number']))->find();
                if (!$current_number) {
                    continue;
                }
                //获取当前的开奖号码
                $number1 = explode(',', $current_number['awardnumbers']);
                //获取当前号码开奖的单双情况
                $number['bz'] = $current_number['bz'];
                $number['dx'] = $current_number['dx'];
                $number['ds'] = $current_number['danshuang'];
                $number['dxds'] = $current_number['dxds'];
                $number['jz'] = $current_number['jz'];
                $number['zonghe'] = $current_number['zonghe'];
                //当期的总金额
                $dangqianqihao = $current_number['periodnumber'];
                $dangqianalldatas = M('order')->where(array('number' => $dangqianqihao, 'userid' => $userid))->alias('o')->field('sum(add_points)as a ,sum(del_points)as d')->select();
                $dangqianalldata = $dangqianalldatas[0]['d'];
                $addjine = $dangqianalldatas[0]['a'];
                //当期的赔付的总金额，是在结算这每一单的时候前的总金额
                //------------------------------分情况-----------------------------------
                //------------------------------分情况-----------------------------------
                //------------------------------分情况-----------------------------------
                switch ($list[$i]['type']) {
                    //第一种为单双判断     单/20    如果判断正确   *   倍数；  ---------- 测试成功-------------------------------------------
                    //第一种为单双判断     单/20    如果判断正确
                    case 1:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '单' || $start1[0] == '双') {
                            //如果这局不是等于13 ， 14 那么就按照正常的程序去走
                            if ($number['zonghe'] != 13 && $number['zonghe'] != 14) {
                                if ($number['ds'] == $start1[0]) {
                                    $points1 = $start1[1] * C('jnd_dx');
                                    $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                } else {
                                    $res1 = $this->del_points($id);
                                }

                                //如果这局开的是13 或者14 那么就按照13 ，14 处理
                            } else {
                                if ($number['ds'] == $start1[0]) {
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    //当局的总金额
                                    if ($dangqianalldata <= C('jnd_ds_jq_1')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_x1_bl');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                    }
                                    if ($dangqianalldata > C('jnd_ds_jq_1') && $dangqianalldata < C('jnd_ds_jq_2')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_1_bl');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                        if ($res1) {
                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata > C('jnd_ds_jq_2') && $dangqianalldata < C('jnd_ds_jq_3')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_2_bl');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                        if ($res1) {
                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata > C('jnd_ds_jq_3')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_3_bl');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                        if ($res1) {
                                        }
                                    }
                                } else {
                                    $res1 = $this->del_points($id);
                                }
                            }
                        }
                        break;
                    //type = 2 时 ， 判断大小单双。 // ------------------测试成功------------------------------------
                    case 2:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '大单' || $start1[0] == '大双' || $start1[0] == '小单' || $start1[0] == '小双') {
                            if ($number['dxds'] == $start1[0]) {
                                //判断是不是13 ， 或者14
                                //分算法
                                switch (C('jnd_dxds_swich')) {
                                    case 1:
                                        //同一算法 13 14 特别
                                        if ($number['zonghe'] != 13 && $number['zonghe'] != 14) {
                                            //如果开奖为豹子
                                            if ($number['bz'] == "豹子") {
                                                $points1 = $start1[1] * 1;
                                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                            } else {
                                                //如果不是为豹子.
                                                $points1 = $start1[1] * C('jnd_dxds');
                                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                            }

                                        } else {
                                            //如果用户投的是  大小单双正确，且是综合为13,14的就按照特殊情况处理
                                            $points1 = $start1[1] * C('jnd_dxds_13_14');
                                            $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                        }
                                        break;
                                    case 2:
                                        //第二种算法，看金额的大小去计算倍率
                                        if ($number['zonghe'] != 13 && $number['zonghe'] != 14) {
                                            if ($start1[0] == '大双') {
                                                $points1 = $start1[1] * C('jnd_dxds_dsxd');
                                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                            }
                                            if ($start1[0] == '小单') {
                                                $points1 = $start1[1] * C('jnd_dxds_dsxdxiaodan');
                                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                            }
                                            if ($start1[0] == '小双') {
                                                $points1 = $start1[1] * C('jnd_dxds_xsdd');
                                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                            }
                                            if ($start1[0] == '大单') {
                                                $points1 = $start1[1] * C('jnd_dxds_xsdddss');
                                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                            }
                                        } else {
                                            //如果开的值为13,14的时候，后台设置，赔率为多少。
                                            if ($dangqianalldata <= C('jnd_dxds_jq_1')) {
                                                $points1 = $start1[1] * C('jnd_dxds_jq_x1_bl');
                                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                            }
                                            if ($dangqianalldata > C('jnd_dxds_jq_1') && $dangqianalldata < C('jnd_dxds_jq_2')) {
                                                $points1 = $start1[1] * C('jnd_dxds_jq_1_bl');
                                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                            }
                                            if ($dangqianalldata > C('jnd_dxds_jq_2') && $dangqianalldata < C('jnd_dxds_jq_3')) {
                                                $points1 = $start1[1] * C('jnd_dxds_jq_3_bl');
                                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                            }
                                            if ($dangqianalldata > C('jnd_dxds_jq_3')) {
                                                $points1 = $start1[1] * C('jnd_dxds_jq_3_bl');
                                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                            }
                                        }
                                        break;
                                }
                            } else {
                                $this->del_points($id);
                            }
                        }
                        break;
                    //type  = 3 极大值，极小值，判断 -----------------------------测试成功----------------------------
                    case 3:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '极大' || $start1[0] == '极小') {
                            if ($number['jz'] == $start1[0]) {
                                $points1 = $start1[1] * C('jnd_jz');
                                $this->add_points($id, $userid, $points1, $addjine);
                            } else {
                                $this->del_points($id);
                            }
                        }
                        break;
                    // type= 4 和值的判断 9/10 --------------------------- 测试成功-------------------
                    case 4:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if (0 <= $start1[0] || $start1[0] <= 27) {
                            if ($number['zonghe'] == $start1[0]) {
                                $data = explode(',', C('jnd_hezhi_bv'));
                                $touzhushuzi = $start1[0];
                                $dd = $data[$touzhushuzi];
                                $chaifendeshuzi = explode('=', $dd);
                                $he_res = $chaifendeshuzi[1];
                                //乘配置文件的数据
                                $points1 = $start1[1] * $he_res;
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //豹子  type = 5  999/20    -----------------------------测试成功-----------------------------
                    case 5:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '豹子') {
                            if ($number['bz'] == $start1[0]) {
                                $points1 = $start1[1] * C('jnd_bz');
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //  顺子判断   type = 6    顺子 ---------------------------------测试成功---------------------------
                    case 6:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '顺子') {
                            if ($number['sz'] == $start1[0]) {
                                $points1 = $start1[1] * C('jnd_sz');
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //判断大小，-------------------------------未测试------------------------------
                    case 7:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '大' || $start1[0] == '小') {
                            //如果输入的值不是13 ， 14 那么走正常的程序
                            if ($number['zonghe'] != 13 && $number['zonghe'] != 14) {
                                //如果这期是豹子
                                if ($number['dz'] == "豹子") {
                                    if ($number['dz'] == "豹子") {
                                        $points1 = $start1[1] * 1;
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                    } else {
                                        $res1 = $this->del_points($id);
                                    }
                                } else {
                                    //这期不是豹子
                                    if ($number['dx'] == $start1[0]) {
                                        $points1 = $start1[1] * C('jnd_dx');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                    } else {
                                        $res1 = $this->del_points($id);
                                    }
                                }
                            } else {

                                if ($number['dx'] == $start1[0]) {

                                    if ($dangqianalldata < C('jnd_ds_jq_1')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_x1_bl');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                        if ($res1) {
                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata >= C('jnd_ds_jq_1') && $dangqianalldata < C('jnd_ds_jq_2')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_1_bl');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                        if ($res1) {

                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata >= C('jnd_ds_jq_2') && $dangqianalldata < C('jnd_ds_jq_3')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_2_bl');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                        if ($res1) {

                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata >= C('jnd_ds_jq_3')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_3_bl');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                        if ($res1) {

                                        }
                                    }
                                    //否者如果玩者输入的不对，删除投注的金额。
                                } else {
                                    $res1 = $this->del_points($id);
                                }
                            }

                        }
                        break;
                    case 8:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '对子') {
                            if ($number['dz'] == $start1[0]) {
                                $points1 = $start1[1] * C('jnd_dz');
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                if ($res1) {
                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                }
            }
            if ($res1) {
                $this->updatepoints('jnd28');
            }

        }

        S('jnd28jsjiesuan', null);
    }

    public function Bj28js()
    {
        $value = S('Bj28jsjiesuan');
        if (empty($value)) {
            S('Bj28jsjiesuan', "1", 60);
        } else {
            return false;
        }
        //自动结算之前没结算的//查找状态为 is_add = 0 的标识没有记录的，is_add为存入，但是没有处理的。
        $list = M('order')->where(array("game" => 'bj28', "state" => 1, "is_add" => 0))->order("time ASC")->select();
        if ($list) {
            for ($i = 0; $i < count($list); $i++) {
                $id = $list[$i]['id'];
                $userid = $list[$i]['userid'];
                //获取开奖号码当期的
                $current_number = M('dannumber')->where(array("game" => 'bj28', "periodnumber" => $list[$i]['number']))->find();
                if (!$current_number) {
                    continue;
                }
                //获取当前的开奖号码
                $number1 = explode(',', $current_number['awardnumbers']);
                //获取当前号码开奖的单双情况
                $number['bz'] = $current_number['bz'];
                $number['dx'] = $current_number['dx'];
                $number['ds'] = $current_number['danshuang'];
                $number['dxds'] = $current_number['dxds'];
                $number['jz'] = $current_number['jz'];
                $number['zonghe'] = $current_number['zonghe'];
                //当期的总金额
                $dangqianqihao = $current_number['periodnumber'];
                $dangqianalldatas = M('order')->where(array('number' => $dangqianqihao, 'userid' => $userid))->alias('o')->field('sum(add_points)as a ,sum(del_points)as d')->select();
                $dangqianalldata = $dangqianalldatas[0]['d'];
                $addjine = $dangqianalldatas[0]['a'];
                //------------------------------分情况-----------------------------------
                //------------------------------分情况-----------------------------------
                //------------------------------分情况-----------------------------------
                switch ($list[$i]['type']) {
                    //第一种为单双判断     单/20    如果判断正确   *   倍数；  ---------- 测试成功-------------------------------------------
                    //第一种为单双判断     单/20    如果判断正确
                    case 1:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '单' || $start1[0] == '双') {
                            //如果这局不是等于13 ， 14 那么就按照正常的程序去走
                            if ($number['zonghe'] != 13 && $number['zonghe'] != 14) {
                                if ($number['ds'] == $start1[0]) {
                                    $points1 = $start1[1] * C('dan_dx');
                                    $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                    if ($res1) {
                                    }
                                } else {
                                    $res1 = $this->del_points($id);
                                }
                                //如果这局开的是13 或者14 那么就按照13 ，14 处理
                            } else {
                                if ($number['ds'] == $start1[0]) {
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata <= C('dan_ds_jq_1')) {
                                        $points1 = $start1[1] * C('dan_ds_jq_x1_bl');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                        if ($res1) {

                                        }
                                    }
                                    if ($dangqianalldata > C('dan_ds_jq_1') && $dangqianalldata < C('dan_ds_jq_2')) {
                                        $points1 = $start1[1] * C('dan_ds_jq_1_bl');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                        if ($res1) {

                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata > C('dan_ds_jq_2') && $dangqianalldata < C('dan_ds_jq_3')) {
                                        $points1 = $start1[1] * C('dan_ds_jq_2_bl');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                        if ($res1) {

                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata > C('dan_ds_jq_3')) {
                                        $points1 = $start1[1] * C('dan_ds_jq_3_bl');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                        if ($res1) {

                                        }
                                    }

                                } else {
                                    $res1 = $this->del_points($id);
                                }
                            }
                        }
                        break;
                    //type = 2 时 ， 判断大小单双。 // ------------------测试成功------------------------------------
                    case 2:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '大单' || $start1[0] == '大双' || $start1[0] == '小单' || $start1[0] == '小双') {
                            if ($number['dxds'] == $start1[0]) {
                                //判断是不是13 ， 或者14
                                //分算法
                                switch (C('dan_dxds_swich')) {
                                    case 1:
                                        //同一算法 13 14 特别
                                        if ($number['zonghe'] != 13 && $number['zonghe'] != 14) {
                                            $points1 = $start1[1] * C('dan_dxds');
                                            $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                            if ($res1) {
                                            }
                                        } else {
                                            //如果用户投的是  大小单双正确，且是综合为13,14的就按照特殊情况处理
                                            $points1 = $start1[1] * C('dan_dxds_13_14');
                                            $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                            if ($res1) {

                                            }
                                        }
                                        break;
                                    case 2:
                                        //第二种算法，看金额的大小去计算倍率
                                        if ($number['zonghe'] != 13 && $number['zonghe'] != 14) {
                                            if ($start1[0] == '大双') {
                                                $points1 = $start1[1] * C('dan_dxds_ds');
                                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                            }
                                            if ($start1[0] == '小双') {
                                                $points1 = $start1[1] * C('dan_dxds_xs');
                                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                            }
                                            if ($start1[0] == '大单') {
                                                $points1 = $start1[1] * C('dan_dxds_dd');
                                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                            }
                                            if ($start1[0] == '小单') {
                                                $points1 = $start1[1] * C('dan_dxds_xd');
                                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                            }

                                        } else {
                                            //如果开的值为13,14的时候，后台设置，赔率为多少。
                                            if ($dangqianalldata <= C('dan_dxds_jq_1')) {
                                                $points1 = $start1[1] * C('dan_dxds_jq_x1_bl');
                                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                            }
                                            if ($dangqianalldata > C('dan_dxds_jq_1') && $start1[1] < C('dan_dxds_jq_2')) {
                                                $points1 = $start1[1] * C('dan_dxds_jq_1_bl');
                                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                            }
                                            if ($dangqianalldata > C('dan_dxds_jq_2') && $start1[1] < C('dan_dxds_jq_3')) {
                                                $points1 = $start1[1] * C('dan_dxds_jq_3_bl');
                                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                            }
                                            if ($dangqianalldata > C('dan_dxds_jq_3')) {
                                                $points1 = $start1[1] * C('dan_dxds_jq_3_bl');
                                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                            }
                                            if ($res1) {

                                            }
                                        }
                                        break;
                                }

                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //type  = 3 极大值，极小值，判断 -----------------------------测试成功----------------------------
                    case 3:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '极大' || $start1[0] == '极小') {
                            if ($number['jz'] == $start1[0]) {
                                $points1 = $start1[1] * C('dan_jz');
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    // type= 4 和值的判断 9/10 --------------------------- 测试成功-------------------
                    case 4:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if (0 <= $start1[0] || $start1[0] <= 27) {
                            if ($number['zonghe'] == $start1[0]) {
                                $data = explode(',', C('hezhi_bv'));
                                $touzhushuzi = $start1[0];
                                $dd = $data[$touzhushuzi];
                                $chaifendeshuzi = explode('=', $dd);
                                $he_res = $chaifendeshuzi[1];
                                //乘配置文件的数据
                                $points1 = $start1[1] * $he_res;
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //豹子  type = 5  999/20    -----------------------------测试成功-----------------------------
                    case 5:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '豹子') {
                            if ($number['bz'] == $start1[0]) {
                                $points1 = $start1[1] * C('dan_bz');
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //  顺子判断   type = 6    顺子 ---------------------------------测试成功---------------------------
                    case 6:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '顺子') {
                            if ($number['sz'] == $start1[0]) {
                                $points1 = $start1[1] * C('dan_sz');
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //判断大小，-------------------------------未测试------------------------------
                    case 7:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '大' || $start1[0] == '小') {
                            //如果输入的值不是13 ， 14 那么走正常的程序
                            if ($number['zonghe'] != 13 && $number['zonghe'] != 14) {
                                if ($number['dx'] == $start1[0]) {
                                    $points1 = $start1[1] * C('dan_dx');
                                    $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                    if ($res1) {

                                    }
                                } else {
                                    $res1 = $this->del_points($id);
                                }
                            } else {
                                if ($number['dx'] == $start1[0]) {
                                    if ($dangqianalldata <= C('dan_ds_jq_1')) {
                                        $points1 = $start1[1] * C('dan_ds_jq_x1_bl');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                        if ($res1) {

                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata >= C('dan_ds_jq_1') && $dangqianalldata < C('dan_ds_jq_2')) {
                                        $points1 = $start1[1] * C('dan_ds_jq_1_bl');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                        if ($res1) {

                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata >= C('dan_ds_jq_2') && $dangqianalldata < C('dan_ds_jq_3')) {
                                        $points1 = $start1[1] * C('dan_ds_jq_2_bl');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                        if ($res1) {

                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata >= C('dan_ds_jq_3')) {
                                        $points1 = $start1[1] * C('dan_ds_jq_3_bl');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                        if ($res1) {

                                        }
                                    }
                                    //否者如果玩者输入的不对，删除投注的金额。
                                } else {
                                    $res1 = $this->del_points($id);
                                }

                            }

                        }
                        break;
                    case 8:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '对子') {
                            if ($number['dz'] == $start1[0]) {
                                $points1 = $start1[1] * C('dan_dz');
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                if ($res1) {

                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;

                }
//                if ($res1) {
//                    //是否有人推荐
//                    $start1 = explode('/', $list[$i]['jincai']);
//                    $uid = $userid;
//                    $chaxunshuju = M('user')->where(array('id' => $uid))->find();
//                    //判断有没有推荐人
//                    $gettid = $chaxunshuju['t_id'];
//                    if ($gettid !== '0') {
//                        //是否设置佣金比率
//                        $tjr = M('user')->where(array('id' => $chaxunshuju['t_id']))->find();
//                        //如果没有设置比率为默认的比率
//                        M('user')->where("id = {$chaxunshuju['t_id']}")->setInc('points', $start1[1] * C('fenxiao') * 0.001);
//                        M('user')->where("id = {$chaxunshuju['t_id']}")->setInc('t_add', $start1[1] * C('fenxiao') * 0.001);
//                        $this->commission($chaxunshuju['t_id'], $start1[1] * C('fenxiao') * 0.001, $uid, $tjr['headimgurl'], $tjr['nickname'], $list[$i]['number']);
//                        if ($tjr['t_id']) {
//                            $tjrn = M('user')->where(array('id' => $tjr['t_id']))->find();
//                            M('user')->where("id = {$chaxunshuju['t_id']}")->setInc('points', $start1[1] * C('fenxiaosan') * 0.001);
//                            M('user')->where("id = {$chaxunshuju['t_id']}")->setInc('t_add', $start1[1] * C('fenxiaosan') * 0.001);
//                            $this->commission($tjr['t_id'], $start1[1] * C('fenxiaosan') * 0.001, $uid, $tjrn['headimgurl'], $tjrn['nickname'], $list[$i]['number']);
//                        }
//                    }
//                }

            }
            //发送已经结算了， 更新前台的金钱
            if ($res1) {
                $this->updatepoints('bj28');
            }

        }
        S('Bj28jsjiesuan', null);

    }

    protected function commission($uid, $points, $id_add, $url, $nickname, $qihao)
    {
        $datas = M('user')->where(array('id' => $uid))->find();
        if (!empty($datas['d_id'])) {
            $data['d_id'] = $datas['d_id'];
            $data['td_id'] = $datas['td_id'];
        }
        $money = $datas['points'];
        $data['time'] = time();
        $data['points'] = $points;
        $data['id_add'] = $id_add;
        $data['uid'] = $uid;
        $data['headimgurl'] = $url;
        $data['nickname'] = $nickname;
        $data['money'] = $money;
        $data['qihao'] = $qihao;
        M('commisssion')->add($data);
    }

    public function Bjpk10()
    {
        $value = S('Bjpk10jiesuan');
        if (empty($value)) {
            S('Bjpk10jiesuan', "1", 60);
        } else {
            return false;
        }

//        if (empty($value)) {
//            S('pkjiesuan', "1", 10);
//        } else {
//            return false;
//        }
        //自动结算之前没结算的
        $list = M('order')->where(array("state" => 1, "game" => 'pk10', "is_add" => 0))->order("time ASC")->select();
        if ($list) {
            for ($i = 0; $i < count($list); $i++) {
                $id = $list[$i]['id'];
                $userid = $list[$i]['userid'];
                //获取开奖号码当期的
                $current_number = M('number')->where(array("game" => 'pk10', "periodnumber" => $list[$i]['number']))->find();
                if (!$current_number) {
                    continue;
                }
                $dangqianqihao = $current_number['periodnumber'];
                $number1 = explode(',', $current_number['awardnumbers']);
                $dangqianalldatas = M('order')->where(array('number' => $dangqianqihao, 'userid' => $userid))->alias('o')->field('sum(add_points)as a ,sum(del_points)as d')->select();
                //当前
                $dangqianalldata = $dangqianalldatas[0]['d'];
                $addjine = $dangqianalldatas[0]['a'];
                $lh = json_decode($current_number['lh']);
                for ($y = 0; $y < count($number1); $y++) {
                    if ($number1[$y] % 2 == 0) {
                        $number[$y]['ds'] = '双';
                        if ($number1[$y] >= 6) {
                            $number[$y]['zuhe'] = '大双';
                        } else {
                            $number[$y]['zuhe'] = '小双';
                        }
                    } else {
                        $number[$y]['ds'] = '单';
                        if ($number1[$y] >= 6) {
                            $number[$y]['zuhe'] = '大单';
                        } else {
                            $number[$y]['zuhe'] = '小单';
                        }
                    }
                    if ($number1[$y] >= 6) {
                        $number[$y]['dx'] = '大';
                    } else {
                        $number[$y]['dx'] = '小';
                    }
                }

                //分类
                switch ($list[$i]['type']) {
                    //车号大小单双(12345/双/100)
                    case 1:
                        $start1 = explode('/', $list[$i]['jincai']);
                        $num1 = 0;
                        $starts1 = str_split($start1[0]);
                        if ($start1[1] == '单' || $start1[1] == '双') {
                            for ($a = 0; $a < count($starts1); $a++) {
                                if ($starts1[$a] == 0) {
                                    $hao1 = '9';
                                } else {
                                    $hao1 = $starts1[$a] - 1;
                                }
                                if ($number[$hao1]['ds'] == $start1[1]) {
                                    $num1++;
                                }
                            }
                        } else {
                            for ($a = 0; $a < count($starts1); $a++) {
                                if ($starts1[$a] == 0) {
                                    $hao1 = '9';
                                } else {
                                    $hao1 = $starts1[$a] - 1;
                                }
                                if ($number[$hao1]['dx'] == $start1[1]) {
                                    $num1++;
                                }
                            }
                        }
                        if ($num1 > 0) {
                            $points1 = $num1 * $start1[2] * C('dxds');
                            $res1 = $this->add_points($id, $userid, $points1, $addjine);
                            if ($res1) {


                            }
                        } else {

                            $res1 = $this->del_points($id);
                        }
                        break;

                    //车号(12345/89/20)
                    case 3:
                        $start2 = explode('/', $list[$i]['jincai']);
                        $chehao2 = str_split($start2[1]);
                        $starts2 = str_split($start2[0]);
                        $num2 = 0;
                        for ($s = 0; $s < count($chehao2); $s++) {
                            for ($a = 0; $a < count($starts2); $a++) {
                                if ($starts2[$a] == 0) {
                                    $hao2 = '9';
                                } else {
                                    $hao2 = $starts2[$a] - 1;
                                }
                                if ($chehao2[$s] == 0) {
                                    $chehao2[$s] = 10;
                                }
                                if ($chehao2[$s] == $number1[$hao2]) {
                                    $num2++;
                                }
                            }
                        }
                        if ($num2 > 0) {
                            $points2 = $num2 * $start2[2] * C('chehao');
                            $res2 = $this->add_points($id, $userid, $points2);
                            if ($res2) {
//                                $this->send_msg('pointsadd', $points2, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //组合(890/大单/50)
                    case 2:
                        $start3 = explode('/', $list[$i]['jincai']);
                        $starts3 = str_split($start3[0]);
                        $num3 = 0;
                        for ($a = 0; $a < count($starts3); $a++) {
                            if ($starts3[$a] == 0) {
                                $hao3 = '9';
                            } else {
                                $hao3 = $starts3[$a] - 1;
                            }
                            if ($number[$hao3]['zuhe'] == $start3[1]) {
                                $num3++;
                            }
                        }
                        if ($num3 > 0) {
                            if ($start3[1] == '大单' || $start3[1] == '小双') {
                                $points3 = $num3 * $start3[2] * C('zuhe_1');
                            } else {
                                $points3 = $num3 * $start3[2] * C('zuhe_2');
                            }
                            $res3 = $this->add_points($id, $userid, $points3);
                            if ($res3) {
//                                $this->send_msg('pointsadd', $points3, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //龙虎(123/龙/100)
                    case 4:
                        $start4 = explode('/', $list[$i]['jincai']);
                        $starts4 = str_split($start4[0]);
                        $num4 = 0;
                        for ($a = 0; $a < count($starts4); $a++) {
                            if ($starts4[$a] == 0) {
                                $hao4 = '9';
                            } else {
                                $hao4 = $starts4[$a] - 1;
                            }
                            if ($lh[$hao4] == $start4[1]) {
                                $num4++;
                            }
                        }
                        if ($num4 > 0) {
                            $points4 = $num4 * $start4[2] * C('lh');
                            $res4 = $this->add_points($id, $userid, $points4);
                            if ($res4) {
//                                $this->send_msg('pointsadd', $points4, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //冠亚庄闲(庄/200)
                    case 5:
                        $start5 = explode('/', $list[$i]['jincai']);
                        if ($current_number['zx'] == $start5[0]) {
                            $points5 = $start5[1] * C('zx');
                            $res5 = $this->add_points($id, $userid, $points5);
                            if ($res5) {
//                                $this->send_msg('pointsadd', $points5, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //冠亚号码(组/1-9.3-7/100)
                    case 6:
                        $start6 = explode('/', $list[$i]['jincai']);
                        if (strlen($start6[1]) > 3) {
                            $zu = explode('.', $start6[1]);
                            for ($a = 0; $a < count($zu); $a++) {
                                $gy = explode('-', $zu[$a]);
                                if ($gy[0] == 0) {
                                    $gy[0] = 10;
                                }
                                if ($gy[1] == 0) {
                                    $gy[1] = 10;
                                }
                                if ($gy[0] == $number1[0] && $gy[1] == $number1[1] || $gy[0] == $number1[1] && $gy[1] == $number1[0]) {
                                    $num6 = 1;
                                }
                            }
                        } else {
                            $gy = explode('-', $start6[1]);
                            if ($gy[0] == 0) {
                                $gy[0] = 10;
                            }
                            if ($gy[1] == 0) {
                                $gy[1] = 10;
                            }
                            if ($gy[0] == $number1[0] && $gy[1] == $number1[1] || $gy[0] == $number1[1] && $gy[1] == $number1[0]) {
                                $num6 = 1;
                            }
                        }
                        if ($num6 > 0) {
                            $points6 = $num6 * $start6[2] * C('gy');
                            $res6 = $this->add_points($id, $userid, $points6);
                            if ($res6) {
//                                $this->send_msg('pointsadd', $points6, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //特码大小单双(和双100)
                    case 7:
                        $start7 = substr($list[$i]['jincai'], 3, 3);
                        $starts7 = substr($list[$i]['jincai'], 6);
                        $num7 = 0;
                        if ($start7 == '大' || $start7 == '小') {
                            if ($current_number['tema_dx'] == $start7) {
                                $num7 = 1;
                            }
                        } else {
                            if ($current_number['tema_ds'] == $start7) {
                                $num7 = 1;
                            }
                        }
                        if ($num7 > 0) {
                            if ($start7 == '大' || $start7 == '双') {
                                $points7 = $starts7 * C('tema_1');
                            } else {
                                $points7 = $starts7 * C('tema_2');
                            }
                            $res7 = $this->add_points($id, $userid, $points7);
                            if ($res7) {
//                                $this->send_msg('pointsadd', $points7, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //特码数字(和5.6.7/100)
                    case 8:
                        $tema1 = array('03', '04', '18', '19');
                        $tema2 = array('5', '6', '16', '17');
                        $tema3 = array('7', '8', '14', '15');
                        $tema4 = array('9', '10', '12', '13');
                        $tema5 = array('11');

                        $start8 = explode('/', $list[$i]['jincai']);
                        $starts8 = substr($start8[0], 3);
                        $num8 = 0;
                        if (strlen($starts8) > 1) {
                            $tlist = explode('.', $starts8);
                            for ($a = 0; $a < count($tlist); $a++) {
                                if ($current_number['tema'] == $tlist[$a]) {
                                    if (in_array($tlist[$a], $tema1)) {
                                        $points8 = $start8[1] * C('tema_sz_1');
                                    }
                                    if (in_array($tlist[$a], $tema2)) {
                                        $points8 = $start8[1] * C('tema_sz_2');
                                    }
                                    if (in_array($tlist[$a], $tema3)) {
                                        $points8 = $start8[1] * C('tema_sz_3');
                                    }
                                    if (in_array($tlist[$a], $tema4)) {
                                        $points8 = $start8[1] * C('tema_sz_4');
                                    }
                                    if (in_array($tlist[$a], $tema5)) {
                                        $points8 = $start8[1] * C('tema_sz_5');
                                    }
                                    $num8 = 1;
                                }
                            }
                        } else {
                            if ($current_number['tema'] == $starts8) {
                                if (in_array($starts8, $tema1)) {
                                    $points8 = $start8[1] * C('tema_sz_1');
                                }
                                if (in_array($starts8, $tema2)) {
                                    $points8 = $start8[1] * C('tema_sz_2');
                                }
                                if (in_array($starts8, $tema3)) {
                                    $points8 = $start8[1] * C('tema_sz_3');
                                }
                                if (in_array($starts8, $tema4)) {
                                    $points8 = $start8[1] * C('tema_sz_4');
                                }
                                if (in_array($starts8, $tema5)) {
                                    $points8 = $start8[1] * C('tema_sz_5');
                                }
                                $num8 = 1;
                            }
                        }
                        if ($num8 > 0) {
                            $res8 = $this->add_points($id, $userid, $points8);
                            if ($res8) {
//                                $this->send_msg('pointsadd', $points8, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //特码区段(BC/100)
                    case 9:
                        $start9 = explode('/', $list[$i]['jincai']);
                        $num9 = 0;
                        if (strlen($start9[0]) > 1) {
                            $starts9 = str_split($start9[0]);
                            for ($a = 0; $a < count($starts9); $a++) {
                                if ($current_number['tema_dw'] == $starts9[$a]) {
                                    if ($starts9[$a] == 'A' || $starts9[$a] == 'C') {
                                        $points9 = $start9[1] * C('tema_qd_1');
                                    } else {
                                        $points9 = $start9[1] * C('tema_qd_2');
                                    }
                                    $num9 = 1;
                                }
                            }
                        } else {
                            if ($current_number['tema_dw'] == $start9[0]) {
                                if ($start9[0] == 'A' || $start9[0] == 'C') {
                                    $points9 = $start9[1] * C('tema_qd_1');
                                } else {
                                    $points9 = $start9[1] * C('tema_qd_2');
                                }
                                $num9 = 1;
                            }
                        }
                        if ($num9 > 0 && $points9) {
                            $res9 = $this->add_points($id, $userid, $points9);
                            if ($res9) {
//                                $this->send_msg('pointsadd', $points9, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;
                }
            }
            if ($res1) {
                $this->updatepoints('pk10');
            }
        }
        S('Bjpk10jiesuan', null);
    }

    public function jscar()
    {
        $value = S('jscarjiesuan');
        if (empty($value)) {
            S('jscarjiesuan', "1", 60);
        } else {
            return false;
        }
        //自动结算之前没结算的
        $list = M('order')->where(array("state" => 1, "is_add" => 0, "game" => 'jscar'))->order("time ASC")->select();
        if ($list) {
            for ($i = 0; $i < count($list); $i++) {
                switch ($list[$i]['type']) {
                    case "pk10": {
                    }
                }
                $id = $list[$i]['id'];
                $userid = $list[$i]['userid'];
                //获取开奖号码当期的
                $current_number = M('number')->where(array("game" => 'jscar', "periodnumber" => $list[$i]['number']))->find();
                if (!$current_number) {
                    continue;
                }
                $dangqianqihao = $current_number['periodnumber'];
                $number1 = explode(',', $current_number['awardnumbers']);
                $dangqianalldatas = M('order')->where(array('number' => $dangqianqihao, 'userid' => $userid))->alias('o')->field('sum(add_points)as a ,sum(del_points)as d')->select();
                //当前
                $dangqianalldata = $dangqianalldatas[0]['d'];
                $addjine = $dangqianalldatas[0]['a'];
                $lh = json_decode($current_number['lh']);
                for ($y = 0; $y < count($number1); $y++) {
                    if ($number1[$y] % 2 == 0) {
                        $number[$y]['ds'] = '双';
                        if ($number1[$y] >= 6) {
                            $number[$y]['zuhe'] = '大双';
                        } else {
                            $number[$y]['zuhe'] = '小双';
                        }
                    } else {
                        $number[$y]['ds'] = '单';
                        if ($number1[$y] >= 6) {
                            $number[$y]['zuhe'] = '大单';
                        } else {
                            $number[$y]['zuhe'] = '小单';
                        }
                    }
                    if ($number1[$y] >= 6) {
                        $number[$y]['dx'] = '大';
                    } else {
                        $number[$y]['dx'] = '小';
                    }
                }

                //分类
                switch ($list[$i]['type']) {
                    //车号大小单双(12345/双/100)
                    case 1:
                        $start1 = explode('/', $list[$i]['jincai']);
                        $num1 = 0;
                        $starts1 = str_split($start1[0]);
                        if ($start1[1] == '单' || $start1[1] == '双') {
                            for ($a = 0; $a < count($starts1); $a++) {
                                if ($starts1[$a] == 0) {
                                    $hao1 = '9';
                                } else {
                                    $hao1 = $starts1[$a] - 1;
                                }
                                if ($number[$hao1]['ds'] == $start1[1]) {
                                    $num1++;
                                }
                            }
                        } else {
                            for ($a = 0; $a < count($starts1); $a++) {
                                if ($starts1[$a] == 0) {
                                    $hao1 = '9';
                                } else {
                                    $hao1 = $starts1[$a] - 1;
                                }
                                if ($number[$hao1]['dx'] == $start1[1]) {
                                    $num1++;
                                }
                            }
                        }
                        if ($num1 > 0) {
                            $points1 = $num1 * $start1[2] * C('jscar_dxds');
                            $res1 = $this->add_points($id, $userid, $points1, $addjine);
                            if ($res1) {


                            }
                        } else {

                            $res1 = $this->del_points($id);
                        }
                        break;

                    //车号(12345/89/20)
                    case 3:
                        $start2 = explode('/', $list[$i]['jincai']);
                        $chehao2 = str_split($start2[1]);
                        $starts2 = str_split($start2[0]);
                        $num2 = 0;
                        for ($s = 0; $s < count($chehao2); $s++) {
                            for ($a = 0; $a < count($starts2); $a++) {
                                if ($starts2[$a] == 0) {
                                    $hao2 = '9';
                                } else {
                                    $hao2 = $starts2[$a] - 1;
                                }
                                if ($chehao2[$s] == 0) {
                                    $chehao2[$s] = 10;
                                }
                                if ($chehao2[$s] == $number1[$hao2]) {
                                    $num2++;
                                }
                            }
                        }
                        if ($num2 > 0) {
                            $points2 = $num2 * $start2[2] * C('jscar_chehao');
                            $res2 = $this->add_points($id, $userid, $points2);
                            if ($res2) {
//                                $this->send_msg('pointsadd', $points2, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //组合(890/大单/50)
                    case 2:
                        $start3 = explode('/', $list[$i]['jincai']);
                        $starts3 = str_split($start3[0]);
                        $num3 = 0;
                        for ($a = 0; $a < count($starts3); $a++) {
                            if ($starts3[$a] == 0) {
                                $hao3 = '9';
                            } else {
                                $hao3 = $starts3[$a] - 1;
                            }
                            if ($number[$hao3]['zuhe'] == $start3[1]) {
                                $num3++;
                            }
                        }
                        if ($num3 > 0) {
                            if ($start3[1] == '大单' || $start3[1] == '小双') {
                                $points3 = $num3 * $start3[2] * C('jscar_zuhe_1');
                            } else {
                                $points3 = $num3 * $start3[2] * C('jscar_zuhe_2');
                            }
                            $res3 = $this->add_points($id, $userid, $points3);
                            if ($res3) {
//                                $this->send_msg('pointsadd', $points3, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //龙虎(123/龙/100)
                    case 4:
                        $start4 = explode('/', $list[$i]['jincai']);
                        $starts4 = str_split($start4[0]);
                        $num4 = 0;
                        for ($a = 0; $a < count($starts4); $a++) {
                            if ($starts4[$a] == 0) {
                                $hao4 = '9';
                            } else {
                                $hao4 = $starts4[$a] - 1;
                            }
                            if ($lh[$hao4] == $start4[1]) {
                                $num4++;
                            }
                        }
                        if ($num4 > 0) {
                            $points4 = $num4 * $start4[2] * C('jscar_lh');
                            $res4 = $this->add_points($id, $userid, $points4);
                            if ($res4) {
//                                $this->send_msg('pointsadd', $points4, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //冠亚庄闲(庄/200)
                    case 5:
                        $start5 = explode('/', $list[$i]['jincai']);
                        if ($current_number['zx'] == $start5[0]) {
                            $points5 = $start5[1] * C('jscar_zx');
                            $res5 = $this->add_points($id, $userid, $points5);
                            if ($res5) {
//                                $this->send_msg('pointsadd', $points5, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //冠亚号码(组/1-9.3-7/100)
                    case 6:
                        $start6 = explode('/', $list[$i]['jincai']);
                        if (strlen($start6[1]) > 3) {
                            $zu = explode('.', $start6[1]);
                            for ($a = 0; $a < count($zu); $a++) {
                                $gy = explode('-', $zu[$a]);
                                if ($gy[0] == 0) {
                                    $gy[0] = 10;
                                }
                                if ($gy[1] == 0) {
                                    $gy[1] = 10;
                                }
                                if ($gy[0] == $number1[0] && $gy[1] == $number1[1] || $gy[0] == $number1[1] && $gy[1] == $number1[0]) {
                                    $num6 = 1;
                                }
                            }
                        } else {
                            $gy = explode('-', $start6[1]);
                            if ($gy[0] == 0) {
                                $gy[0] = 10;
                            }
                            if ($gy[1] == 0) {
                                $gy[1] = 10;
                            }
                            if ($gy[0] == $number1[0] && $gy[1] == $number1[1] || $gy[0] == $number1[1] && $gy[1] == $number1[0]) {
                                $num6 = 1;
                            }
                        }
                        if ($num6 > 0) {
                            $points6 = $num6 * $start6[2] * C('jscar_gy');
                            $res6 = $this->add_points($id, $userid, $points6);
                            if ($res6) {
//                                $this->send_msg('pointsadd', $points6, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //特码大小单双(和双100)
                    case 7:
                        $start7 = substr($list[$i]['jincai'], 3, 3);
                        $starts7 = substr($list[$i]['jincai'], 6);
                        $num7 = 0;
                        if ($start7 == '大' || $start7 == '小') {
                            if ($current_number['tema_dx'] == $start7) {
                                $num7 = 1;
                            }
                        } else {
                            if ($current_number['tema_ds'] == $start7) {
                                $num7 = 1;
                            }
                        }
                        if ($num7 > 0) {
                            if ($start7 == '大' || $start7 == '双') {
                                $points7 = $starts7 * C('jscar_tema_1');
                            } else {
                                $points7 = $starts7 * C('jscar_tema_2');
                            }
                            $res7 = $this->add_points($id, $userid, $points7);
                            if ($res7) {
//                                $this->send_msg('pointsadd', $points7, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //特码数字(和5.6.7/100)
                    case 8:
                        $tema1 = array('03', '04', '18', '19');
                        $tema2 = array('5', '6', '16', '17');
                        $tema3 = array('7', '8', '14', '15');
                        $tema4 = array('9', '10', '12', '13');
                        $tema5 = array('11');

                        $start8 = explode('/', $list[$i]['jincai']);
                        $starts8 = substr($start8[0], 3);
                        $num8 = 0;
                        if (strlen($starts8) > 1) {
                            $tlist = explode('.', $starts8);
                            for ($a = 0; $a < count($tlist); $a++) {
                                if ($current_number['tema'] == $tlist[$a]) {
                                    if (in_array($tlist[$a], $tema1)) {
                                        $points8 = $start8[1] * C('jscar_tema_sz_1');
                                    }
                                    if (in_array($tlist[$a], $tema2)) {
                                        $points8 = $start8[1] * C('jscar_tema_sz_2');
                                    }
                                    if (in_array($tlist[$a], $tema3)) {
                                        $points8 = $start8[1] * C('jscar_tema_sz_3');
                                    }
                                    if (in_array($tlist[$a], $tema4)) {
                                        $points8 = $start8[1] * C('jscar_tema_sz_4');
                                    }
                                    if (in_array($tlist[$a], $tema5)) {
                                        $points8 = $start8[1] * C('jscar_tema_sz_5');
                                    }
                                    $num8 = 1;
                                }
                            }
                        } else {
                            if ($current_number['tema'] == $starts8) {
                                if (in_array($starts8, $tema1)) {
                                    $points8 = $start8[1] * C('jscar_tema_sz_1');
                                }
                                if (in_array($starts8, $tema2)) {
                                    $points8 = $start8[1] * C('jscar_tema_sz_2');
                                }
                                if (in_array($starts8, $tema3)) {
                                    $points8 = $start8[1] * C('jscar_tema_sz_3');
                                }
                                if (in_array($starts8, $tema4)) {
                                    $points8 = $start8[1] * C('jscar_tema_sz_4');
                                }
                                if (in_array($starts8, $tema5)) {
                                    $points8 = $start8[1] * C('jscar_tema_sz_5');
                                }
                                $num8 = 1;
                            }
                        }
                        if ($num8 > 0) {
                            $res8 = $this->add_points($id, $userid, $points8);
                            if ($res8) {
//                                $this->send_msg('pointsadd', $points8, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //特码区段(BC/100)
                    case 9:
                        $start9 = explode('/', $list[$i]['jincai']);
                        $num9 = 0;
                        if (strlen($start9[0]) > 1) {
                            $starts9 = str_split($start9[0]);
                            for ($a = 0; $a < count($starts9); $a++) {
                                if ($current_number['tema_dw'] == $starts9[$a]) {
                                    if ($starts9[$a] == 'A' || $starts9[$a] == 'C') {
                                        $points9 = $start9[1] * C('jscar_tema_qd_1');
                                    } else {
                                        $points9 = $start9[1] * C('jscar_tema_qd_2');
                                    }
                                    $num9 = 1;
                                }
                            }
                        } else {
                            if ($current_number['tema_dw'] == $start9[0]) {
                                if ($start9[0] == 'A' || $start9[0] == 'C') {
                                    $points9 = $start9[1] * C('jscar_tema_qd_1');
                                } else {
                                    $points9 = $start9[1] * C('jscar_tema_qd_2');
                                }
                                $num9 = 1;
                            }
                        }
                        if ($num9 > 0 && $points9) {
                            $res9 = $this->add_points($id, $userid, $points9);
                            if ($res9) {
//                                $this->send_msg('pointsadd', $points9, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;
                }
            }
            if ($res1) {
                $this->updatepoints('jscar');
            }
        }
        S('jscarjiesuan', null);
    }

    public function fei()
    {
        $value = S('feijiesuan');
        var_dump(empty($value));
        if (empty($value)) {
            S('feijiesuan', "1", 60);
        } else {
            return false;
        }
        //自动结算之前没结算的
        $list = M('order')->where(array("state" => 1, "game" => 'fei', "is_add" => 0))->order("time ASC")->select();
        if ($list) {
            for ($i = 0; $i < count($list); $i++) {
                switch ($list[$i]['type']) {
                    case "pk10": {

                    }
                }

                $id = $list[$i]['id'];
                $userid = $list[$i]['userid'];
                //获取开奖号码当期的
                $current_number = M('number')->where(array("game" => 'fei', "periodnumber" => $list[$i]['number']))->find();
                if (!$current_number) {
                    continue;
                }
                $dangqianqihao = $current_number['periodnumber'];
                $number1 = explode(',', $current_number['awardnumbers']);
                $dangqianalldatas = M('order')->where(array('number' => $dangqianqihao, 'userid' => $userid))->alias('o')->field('sum(add_points)as a ,sum(del_points)as d')->select();
                //当前
                $dangqianalldata = $dangqianalldatas[0]['d'];
                $addjine = $dangqianalldatas[0]['a'];
                $lh = json_decode($current_number['lh']);
                for ($y = 0; $y < count($number1); $y++) {
                    if ($number1[$y] % 2 == 0) {
                        $number[$y]['ds'] = '双';
                        if ($number1[$y] >= 6) {
                            $number[$y]['zuhe'] = '大双';
                        } else {
                            $number[$y]['zuhe'] = '小双';
                        }
                    } else {
                        $number[$y]['ds'] = '单';
                        if ($number1[$y] >= 6) {
                            $number[$y]['zuhe'] = '大单';
                        } else {
                            $number[$y]['zuhe'] = '小单';
                        }
                    }
                    if ($number1[$y] >= 6) {
                        $number[$y]['dx'] = '大';
                    } else {
                        $number[$y]['dx'] = '小';
                    }
                }

                //分类
                switch ($list[$i]['type']) {
                    //车号大小单双(12345/双/100)
                    case 1:
                        $start1 = explode('/', $list[$i]['jincai']);
                        $num1 = 0;
                        $starts1 = str_split($start1[0]);
                        if ($start1[1] == '单' || $start1[1] == '双') {
                            for ($a = 0; $a < count($starts1); $a++) {
                                if ($starts1[$a] == 0) {
                                    $hao1 = '9';
                                } else {
                                    $hao1 = $starts1[$a] - 1;
                                }
                                if ($number[$hao1]['ds'] == $start1[1]) {
                                    $num1++;
                                }
                            }
                        } else {
                            for ($a = 0; $a < count($starts1); $a++) {
                                if ($starts1[$a] == 0) {
                                    $hao1 = '9';
                                } else {
                                    $hao1 = $starts1[$a] - 1;
                                }
                                if ($number[$hao1]['dx'] == $start1[1]) {
                                    $num1++;
                                }
                            }
                        }
                        if ($num1 > 0) {
                            $points1 = $num1 * $start1[2] * C('dxds_fei');
                            $res1 = $this->add_points($id, $userid, $points1, $addjine);
                            if ($res1) {


                            }
                        } else {

                            $res1 = $this->del_points($id);
                        }
                        break;

                    //车号(12345/89/20)
                    case 3:
                        $start2 = explode('/', $list[$i]['jincai']);
                        $chehao2 = str_split($start2[1]);
                        $starts2 = str_split($start2[0]);
                        $num2 = 0;
                        for ($s = 0; $s < count($chehao2); $s++) {
                            for ($a = 0; $a < count($starts2); $a++) {
                                if ($starts2[$a] == 0) {
                                    $hao2 = '9';
                                } else {
                                    $hao2 = $starts2[$a] - 1;
                                }
                                if ($chehao2[$s] == 0) {
                                    $chehao2[$s] = 10;
                                }
                                if ($chehao2[$s] == $number1[$hao2]) {
                                    $num2++;
                                }
                            }
                        }
                        if ($num2 > 0) {
                            $points2 = $num2 * $start2[2] * C('chehao_fei');//2
                            $res2 = $this->add_points($id, $userid, $points2);
                            if ($res2) {
//                                $this->send_msg('pointsadd', $points2, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //组合(890/大单/50)
                    case 2:
                        $start3 = explode('/', $list[$i]['jincai']);
                        $starts3 = str_split($start3[0]);
                        $num3 = 0;
                        for ($a = 0; $a < count($starts3); $a++) {
                            if ($starts3[$a] == 0) {
                                $hao3 = '9';
                            } else {
                                $hao3 = $starts3[$a] - 1;
                            }
                            if ($number[$hao3]['zuhe'] == $start3[1]) {
                                $num3++;
                            }
                        }
                        if ($num3 > 0) {
                            if ($start3[1] == '大单' || $start3[1] == '小双') {
                                $points3 = $num3 * $start3[2] * C('zuhe_1_fei');
                            } else {
                                $points3 = $num3 * $start3[2] * C('zuhe_2_fei');//4
                            }
                            $res3 = $this->add_points($id, $userid, $points3);
                            if ($res3) {
//                                $this->send_msg('pointsadd', $points3, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //龙虎(123/龙/100)
                    case 4:
                        $start4 = explode('/', $list[$i]['jincai']);
                        $starts4 = str_split($start4[0]);
                        $num4 = 0;
                        for ($a = 0; $a < count($starts4); $a++) {
                            if ($starts4[$a] == 0) {
                                $hao4 = '9';
                            } else {
                                $hao4 = $starts4[$a] - 1;
                            }
                            if ($lh[$hao4] == $start4[1]) {
                                $num4++;
                            }
                        }
                        if ($num4 > 0) {
                            $points4 = $num4 * $start4[2] * C('lh_fei');//5
                            $res4 = $this->add_points($id, $userid, $points4);
                            if ($res4) {
//                                $this->send_msg('pointsadd', $points4, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //冠亚庄闲(庄/200)
                    case 5:
                        $start5 = explode('/', $list[$i]['jincai']);
                        if ($current_number['zx'] == $start5[0]) {
                            $points5 = $start5[1] * C('zx_fei');//6
                            $res5 = $this->add_points($id, $userid, $points5);
                            if ($res5) {
//                                $this->send_msg('pointsadd', $points5, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //冠亚号码(组/1-9.3-7/100)
                    case 6:
                        $start6 = explode('/', $list[$i]['jincai']);
                        if (strlen($start6[1]) > 3) {
                            $zu = explode('.', $start6[1]);
                            for ($a = 0; $a < count($zu); $a++) {
                                $gy = explode('-', $zu[$a]);
                                if ($gy[0] == 0) {
                                    $gy[0] = 10;
                                }
                                if ($gy[1] == 0) {
                                    $gy[1] = 10;
                                }
                                if ($gy[0] == $number1[0] && $gy[1] == $number1[1] || $gy[0] == $number1[1] && $gy[1] == $number1[0]) {
                                    $num6 = 1;
                                }
                            }
                        } else {
                            $gy = explode('-', $start6[1]);
                            if ($gy[0] == 0) {
                                $gy[0] = 10;
                            }
                            if ($gy[1] == 0) {
                                $gy[1] = 10;
                            }
                            if ($gy[0] == $number1[0] && $gy[1] == $number1[1] || $gy[0] == $number1[1] && $gy[1] == $number1[0]) {
                                $num6 = 1;
                            }
                        }
                        if ($num6 > 0) {
                            $points6 = $num6 * $start6[2] * C('gy_fei');//7
                            $res6 = $this->add_points($id, $userid, $points6);
                            if ($res6) {
//                                $this->send_msg('pointsadd', $points6, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //特码大小单双(和双100)
                    case 7:
                        $start7 = substr($list[$i]['jincai'], 3, 3);
                        $starts7 = substr($list[$i]['jincai'], 6);
                        $num7 = 0;
                        if ($start7 == '大' || $start7 == '小') {
                            if ($current_number['tema_dx'] == $start7) {
                                $num7 = 1;
                            }
                        } else {
                            if ($current_number['tema_ds'] == $start7) {
                                $num7 = 1;
                            }
                        }
                        if ($num7 > 0) {
                            if ($start7 == '大' || $start7 == '双') {
                                $points7 = $starts7 * C('tema_1_fei');
                            } else {
                                $points7 = $starts7 * C('tema_2_fei');
                            }
                            $res7 = $this->add_points($id, $userid, $points7);
                            if ($res7) {
//                                $this->send_msg('pointsadd', $points7, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //特码数字(和5.6.7/100)
                    case 8:
                        $tema1 = array('03', '04', '18', '19');
                        $tema2 = array('5', '6', '16', '17');
                        $tema3 = array('7', '8', '14', '15');
                        $tema4 = array('9', '10', '12', '13');
                        $tema5 = array('11');

                        $start8 = explode('/', $list[$i]['jincai']);
                        $starts8 = substr($start8[0], 3);
                        $num8 = 0;
                        if (strlen($starts8) > 1) {
                            $tlist = explode('.', $starts8);
                            for ($a = 0; $a < count($tlist); $a++) {
                                if ($current_number['tema'] == $tlist[$a]) {
                                    if (in_array($tlist[$a], $tema1)) {
                                        $points8 = $start8[1] * C('tema_sz_1_fei');
                                    }
                                    if (in_array($tlist[$a], $tema2)) {
                                        $points8 = $start8[1] * C('tema_sz_2_fei');
                                    }
                                    if (in_array($tlist[$a], $tema3)) {
                                        $points8 = $start8[1] * C('tema_sz_3_fei');
                                    }
                                    if (in_array($tlist[$a], $tema4)) {
                                        $points8 = $start8[1] * C('tema_sz_4_fei');
                                    }
                                    if (in_array($tlist[$a], $tema5)) {
                                        $points8 = $start8[1] * C('tema_sz_5_fei');//14
                                    }
                                    $num8 = 1;
                                }
                            }
                        } else {
                            if ($current_number['tema'] == $starts8) {
                                if (in_array($starts8, $tema1)) {
                                    $points8 = $start8[1] * C('tema_sz_1_fei');//15
                                }
                                if (in_array($starts8, $tema2)) {
                                    $points8 = $start8[1] * C('tema_sz_2_fei');
                                }
                                if (in_array($starts8, $tema3)) {
                                    $points8 = $start8[1] * C('tema_sz_3_fei');
                                }
                                if (in_array($starts8, $tema4)) {
                                    $points8 = $start8[1] * C('tema_sz_4_fei');
                                }
                                if (in_array($starts8, $tema5)) {
                                    $points8 = $start8[1] * C('tema_sz_5_fei');
                                }
                                $num8 = 1;
                            }
                        }
                        if ($num8 > 0) {
                            $res8 = $this->add_points($id, $userid, $points8);
                            if ($res8) {
//                                $this->send_msg('pointsadd', $points8, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;

                    //特码区段(BC/100)
                    case 9:
                        $start9 = explode('/', $list[$i]['jincai']);
                        $num9 = 0;
                        if (strlen($start9[0]) > 1) {
                            $starts9 = str_split($start9[0]);
                            for ($a = 0; $a < count($starts9); $a++) {
                                if ($current_number['tema_dw'] == $starts9[$a]) {
                                    if ($starts9[$a] == 'A' || $starts9[$a] == 'C') {
                                        $points9 = $start9[1] * C('tema_qd_1_fei');//20
                                    } else {
                                        $points9 = $start9[1] * C('tema_qd_2_fei');
                                    }
                                    $num9 = 1;
                                }
                            }
                        } else {
                            if ($current_number['tema_dw'] == $start9[0]) {
                                if ($start9[0] == 'A' || $start9[0] == 'C') {
                                    $points9 = $start9[1] * C('tema_qd_1_fei');
                                } else {
                                    $points9 = $start9[1] * C('tema_qd_2_fei');//23
                                }
                                $num9 = 1;
                            }
                        }
                        if ($num9 > 0 && $points9) {
                            $res9 = $this->add_points($id, $userid, $points9);
                            if ($res9) {
//                                $this->send_msg('pointsadd', $points9, $userid);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;
                }
            }
            if ($res1) {
                $this->updatepoints('fei');
            }
        }
        S('feijiesuan', null);
    }

    public function ssc()
    {
        header("Content-type: text/html; charset=utf-8");
        $value = S('sscjiesuan');
        if (empty($value)) {
            S('sscjiesuan', "1", 60);
        } else {
            return false;
        }

        //自动结算之前没结算的//查找状态为 is_add = 0 的标识没有记录的，is_add为存入，但是没有处理的。
        $list = M('order')->where(array("state" => 1, "is_add" => 0, "game" => 'ssc'))->order("time ASC")->select();
        if ($list) {
            for ($i = 0; $i < count($list); $i++) {
                $id = $list[$i]['id'];
                $userid = $list[$i]['userid'];
                //获取开奖号码当期的
                $current_number = M('sscnumber')->where(array("game" => 'ssc', "periodnumber" => $list[$i]['number']))->find();
                if (!$current_number) {
                    continue;
                }
                //获取当前的开奖号码
                $number1 = explode(',', $current_number['awardnumbers']);
                //获取当前号码开奖的单双情况
                $number['awardnumbers'] = $current_number['awardnumbers'];
                $number['ds'] = $current_number['ds'];
                $number['dx'] = $current_number['dx'];
                $number['zuhe'] = $current_number['zuhe'];
                $number['dxds'] = $current_number['dxds'];
                $number['jz'] = $current_number['jz'];
                $number['zonghe'] = $current_number['zonghe'];
                $number['tema_dx'] = $current_number['tema_dx'];
                $number['tema_ds'] = $current_number['tema_ds'];
                $number['tema_abc'] = $current_number['tema_abc'];
                $number['lh'] = $current_number['lh'];

                //当期的总金额
                $dangqianqihao = $current_number['periodnumber'];
                $dangqianalldatas = M('order')->where(array('number' => $dangqianqihao, 'userid' => $userid))->alias('o')->field('sum(add_points)as a ,sum(del_points)as d')->select();
                //当前下注了总金额
                $dangqianalldata = $dangqianalldatas[0]['d'];
                $addjine = $dangqianalldatas[0]['a'];


                //------------------------------分情况-----------------------------------
                //------------------------------分情况-----------------------------------
                //------------------------------分情况-----------------------------------
                switch ($list[$i]['type']) {
                    case 1:
                        $start1 = explode('/', $list[$i]['jincai']);
                        //再次判断是否为单双正确的，和数据库里ds 判断
                        if ($start1[1] == '单' || $start1[1] == '双') {
                            $ds = explode('/', $number['ds']);
                            $starts4 = str_split($start1[0]);
                            $num4 = 0;
                            for ($a = 0; $a < count($starts4); $a++) {
                                $hao4 = $starts4[$a] - 1;
                                if ($ds[$hao4] == $start1[1]) {
                                    $num4++;
                                }
                            }
                            if ($num4 > 0) {
                                $points4 = $num4 * $start1[2] * C('ssc_bl_dxds');
                                $res4 = $this->add_points($id, $userid, $points4);
                            } else {
                                $res1 = $this->del_points($id);
                            }

                        }
                        //如果为大小的单独和数据库字段dx 的判断
                        if ($start1[1] == '大' || $start1[1] == '小') {
                            //拆分ds字段的數組。
                            $dx = explode('/', $number['dx']);
                            $starts4 = str_split($start1[0]);
                            $num4 = 0;
                            for ($a = 0; $a < count($starts4); $a++) {
                                $hao4 = $starts4[$a] - 1;
                                if ($dx[$hao4] == $start1[1]) {
                                    $num4++;
                                }
                            }
                            if ($num4 > 0) {
                                $points4 = $num4 * $start1[2] * C('ssc_bl_dxds');
                                $res4 = $this->add_points($id, $userid, $points4);
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }

                        break;
                    //type = 2 时 ， 判断大小单双。 // ------------------测试成功---------------------------------------
                    case 2:
                        $start1 = explode('/', $list[$i]['jincai']);
                        //再次判断是否为单双正确的，和数据库里ds 判断
                        if ($start1[1] == '大单' || $start1[1] == '大双' || $start1[1] == '小双' || $start1[1] == '小单') {
                            //判断第几个数字
                            $selectsum = $start1[0] - 1;
                            //拆分ds字段的數組。
                            $zuhe = explode('/', $number['zuhe']);
                            if ($zuhe[$selectsum] == $start1[1]) {
                                $points1 = $start1[2] * C('ssc_bl_zuhe');
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //type  = 3 时候， 判断数字的大小。
                    //12/56/500  在万位和千位的是为5，6 球四柱
                    case 3:
                        //获取用户下注的数字。
                        $start1 = explode('/', $list[$i]['jincai']);
                        $chehao2 = str_split($start1[1]);
                        $starts2 = str_split($start1[0]);
                        $num2 = 0;
                        for ($s = 0; $s < count($chehao2); $s++) {
                            for ($a = 0; $a < count($starts2); $a++) {
                                //第$i个位置有 没有
                                $hao2 = $starts2[$a] - 1;
                                if ($chehao2[$s] == $number1[$hao2]) {
                                    $num2++;
                                }
                            }
                        }
                        if ($num2 > 0) {
                            //猜数字的倍率
                            $points2 = $num2 * $start1[2] * C('ssc_bl_sum');
                            $res1 = $this->add_points($id, $userid, $points2, $addjine);
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;
                    case 4:
                        //总/大/50
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[1] == '大' || $start1[1] == '小') {
                            if ($start1[1] == $number['tema_dx']) {
                                //大小
                                $pointsdx = $start1[2] * C('ssc_zonghe_dxds');
                                $this->add_points($id, $userid, $pointsdx, $addjine);
                            } else {
                                $this->del_points($id);
                            }
                        }
                        if ($start1[1] == '单' || $start1[1] == '双') {
                            if ($start1[1] == $number['tema_ds']) {
                                //单双
                                $pointsdx = $start1[2] * C('ssc_zonghe_dxds');
                                $this->add_points($id, $userid, $pointsdx, $addjine);
                            } else {
                                $this->del_points($id);
                            }
                        }
                        break;
                    case 5://测试成功
                        //abc
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == 'B' || $start1[0] == 'A' || $start1[0] == 'C') {
                            if ($start1[0] == $number['tema_abc']) {
                                if ($start1[0] == 'A') {
                                    $pointsdx = $start1[1] * C('ssc_a_dxds');
                                    $this->add_points($id, $userid, $pointsdx, $addjine);
                                }
                                if ($start1[0] == 'B') {
                                    $pointsdx = $start1[1] * C('ssc_b_dxds');
                                    $this->add_points($id, $userid, $pointsdx, $addjine);
                                }
                                if ($start1[0] == 'C') {
                                    $pointsdx = $start1[1] * C('ssc_c_dxds');
                                    $this->add_points($id, $userid, $pointsdx, $addjine);
                                }


                            } else {
                                $this->del_points($id);
                            }
                        }
                        break;

                    case 6://测试成功
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '龙' || $start1[0] == '虎' || $start1[0] == '和') {
                            if ($start1[0] == $number['lh']) {
                                if ($start1[0] == '龙') {
                                    $pointsdx = $start1[1] * C('ssc_lh_sum');
                                    $this->add_points($id, $userid, $pointsdx, $addjine);
                                }
                                if ($start1[0] == '虎') {
                                    $pointsdx = $start1[1] * C('ssc_laohu_sum');
                                    $this->add_points($id, $userid, $pointsdx, $addjine);
                                }
                                if ($start1[0] == '和') {
                                    $pointsdx = $start1[1] * C('ssc_he_sum');
                                    $this->add_points($id, $userid, $pointsdx, $addjine);
                                }

                            } else {
                                $this->del_points($id);
                            }
                        }
                        break;

                    case 7:  //测试成功
                        //前/对子/50
                        $start1 = explode('/', $list[$i]['jincai']);
                        //前三个值
                        if ($start1[0] == '前') {
                            $checksum = $number1[0] . ',' . $number1[1] . ',' . $number1[2];
                            $qiansan = $this->checkqzh($checksum);
                            if ($qiansan == $start1[1]) {
                                $pointsdx = 0;
                                if ($start1[1] == '豹子') {
                                    $pointsdx = $start1[2] * C('ssc_baozi_sqsum');
                                }
                                if ($qiansan == '顺子') {
                                    $pointsdx = $start1[2] * C('ssc_sz_sqsum');
                                }
                                if ($qiansan == '对子') {
                                    $pointsdx = $start1[2] * C('ssc_duizi_sqsum');
                                }
                                if ($qiansan == '半顺') {
                                    $pointsdx = $start1[2] * C('ssc_banshun_sqsum');
                                }
                                if ($qiansan == '杂六') {
                                    $pointsdx = $start1[2] * C('ssc_liu_sqsum');
                                }
                                $this->add_points($id, $userid, $pointsdx, $addjine);

                            } else {
                                $this->del_points($id);
                            }
                        }
                        if ($start1[0] == '中') {
                            $checksum = $number1[1] . ',' . $number1[2] . ',' . $number1[3];
                            $qiansan = $this->checkqzh($checksum);
                            if ($qiansan == $start1[1]) {
                                $pointsdx = 0;
                                if ($start1[1] == '豹子') {
                                    $pointsdx = $start1[2] * C('ssc_baozi_sqsum');
                                }
                                if ($qiansan == '顺子') {
                                    $pointsdx = $start1[2] * C('ssc_sz_sqsum');
                                }
                                if ($qiansan == '对子') {
                                    $pointsdx = $start1[2] * C('ssc_duizi_sqsum');
                                }
                                if ($qiansan == '半顺') {
                                    $pointsdx = $start1[2] * C('ssc_banshun_sqsum');
                                }
                                if ($qiansan == '杂六') {
                                    $pointsdx = $start1[2] * C('ssc_liu_sqsum');
                                }
                                $this->add_points($id, $userid, $pointsdx, $addjine);

                            } else {
                                $this->del_points($id);
                            }
                        }
                        if ($start1[0] == '后') {
                            $checksum = $number1[2] . ',' . $number1[3] . ',' . $number1[4];
                            $qiansan = $this->checkqzh($checksum);
                            if ($qiansan == $start1[1]) {
                                $pointsdx = 0;
                                if ($start1[1] == '豹子') {
                                    $pointsdx = $start1[2] * C('ssc_baozi_sqsum');
                                }
                                if ($qiansan == '顺子') {
                                    $pointsdx = $start1[2] * C('ssc_sz_sqsum');
                                }
                                if ($qiansan == '对子') {
                                    $pointsdx = $start1[2] * C('ssc_duizi_sqsum');
                                }
                                if ($qiansan == '半顺') {
                                    $pointsdx = $start1[2] * C('ssc_banshun_sqsum');
                                }
                                if ($qiansan == '杂六') {
                                    $pointsdx = $start1[2] * C('ssc_liu_sqsum');
                                }
                                $this->add_points($id, $userid, $pointsdx, $addjine);

                            } else {
                                $this->del_points($id);
                            }
                        }
                        break;
                }
            }
            if ($res1) {
                $this->updatepoints('ssc');
            }
        }
        S('sscjiesuan', null);

    }

    public function kuai3()
    {
        $value = S('kuaijiesuan');

        if (empty($value)) {
            S('kuaijiesuan', "1", 60);
        } else {
            return false;
        }
        //自动结算之前没结算的//查找状态为 is_add = 0 的标识没有记录的，is_add为存入，但是没有处理的。
        $list = M('order')->where(array("state" => 1, "is_add" => 0, 'game' => 'kuai3'))->order("time ASC")->select();
        if ($list) {
            for ($i = 0; $i < count($list); $i++) {
                $id = $list[$i]['id'];
                $userid = $list[$i]['userid'];
                //获取开奖号码当期的
                $current_number = M('kuainumber')->where(array("game" => 'kuai3', "periodnumber" => $list[$i]['number']))->find();
                if (!$current_number) {
                    continue;
                }
                //获取当前的开奖号码
                $number1 = explode(',', $current_number['awardnumbers']);
                //获取当前号码开奖的单双情况
                $number['erbutongdan'] = $current_number['erbutongdan'];
                $number['bz'] = $current_number['bz'];
                $number['dx'] = $current_number['dx'];
                $number['ds'] = $current_number['ds'];
                $number['ertonghao'] = $current_number['ertonghao'];
                $number['sz'] = $current_number['sz'];
                $number['santonghaotong'] = $current_number['santonghaotong'];
                $number['zonghe'] = $current_number['zonghe'];
                //当期的总金额
                $dangqianqihao = $current_number['periodnumber'];
                $dangqianalldatas = M('order')->where(array('number' => $dangqianqihao, 'userid' => $userid))->alias('o')->field('sum(add_points)as a ,sum(del_points)as d')->select();
                //当前下注的总金额
                $dangqianalldata = $dangqianalldatas[0]['d'];
                //当前用户赚了多少钱
                $addjine = $dangqianalldatas[0]['a'];
                //当期的赔付的总金额，是在结算这每一单的时候前的总金额
                //------------------------------分情况-----------------------------------
                //------------------------------分情况-----------------------------------
                //------------------------------分情况-----------------------------------
                switch ($list[$i]['type']) {
                    //和值
                    case 1:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if (4 <= $start1[0] || $start1[0] <= 17) {
                            if ($number['zonghe'] == $start1[0]) {
                                $data = explode(',', C('kuai3_hezhi_bv'));
                                $touzhushuzi = $start1[0];
                                $dd = $data[$touzhushuzi];
                                $chaifendeshuzi = explode('=', $dd);
                                $he_res = $chaifendeshuzi[1];
                                //乘配置文件的数据
                                $points1 = $start1[1] * $he_res;
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                if ($res1) {
                                }
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;
                    //三不同通选
                    case 2:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '三不同') {
                            if ($number['santonghaotong'] == $start1[0]) {
                                $points1 = $start1[1] * C('kuai3_sbt');
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                if ($res1) {
                                }
                            } else {
                                $this->del_points($id);
                            }
                        }
                        break;
                    //豹子/123/600
                    case 3:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == $number['santonghaotong']) {
                            //这期什么豹
                            $wahtbao = $number1[0];
                            $starts4 = str_split($start1[1]);
                            $cshih = 0;
                            for ($bz = 0; $bz < count($starts4); $bz++) {
                                if ($starts4[$bz] == $wahtbao) {
                                    $cshih++;
                                }
                            }
                            if ($cshih > 0) {
                                $points1 = $start1[2] * C('kuai_bz') * $cshih;
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                            } else {
                                $this->del_points($id);
                            }
                        } else {
                            $this->del_points($id);
                        }
                        break;
                    //三连号：
                    case 4:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '顺子') {
                            if ($number['sz'] == $start1[0]) {
                                $points1 = $start1[1] * C('kuai3_sz_bv');
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                            } else {
                                $this->del_points($id);
                            }
                        } else {
                            $this->del_points($id);
                        }
                        break;
                    //二相同通选
                    case 5:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '二同号') {
                            if ($number['ertonghao'] == $start1[0]) {
                                $points1 = $start1[1] * C('kuai3_ertonghaotong_bv');
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                if ($res1) {
                                }
                            } else {
                                $this->del_points($id);
                            }
                        } else {
                            $this->del_points($id);
                        }
                        break;
                    //二不同通选
                    case 6:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '二不同') {
                            if ($number['ertonghao'] == $start1[0]) {
                                $points1 = $start1[1] * C('kuai3_erbutongtong_bv');
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                            } else {
                                $this->del_points($id);
                            }
                        } else {
                            $this->del_points($id);
                        }
                        break;
                    //三同号单选：
                    case 7:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '大' || $start1[0] == '小') {
                            //如果输入的值不是13 ， 14 那么走正常的程序
                            if ($number['zonghe'] != 13 && $number['zonghe'] != 14) {
                                //如果这期是豹子
                                if ($number['dz'] == "豹子") {
                                    if ($number['dz'] == "豹子") {
                                        $points1 = $start1[1] * 1;
                                        $this->add_points($id, $userid, $points1, $addjine);
                                    } else {
                                        $this->del_points($id);
                                    }
                                } else {
                                    //这期不是豹子
                                    if ($number['dx'] == $start1[0]) {
                                        $points1 = $start1[1] * C('jnd_dx');
                                        $this->add_points($id, $userid, $points1, $addjine);
                                    } else {
                                        $this->del_points($id);
                                    }
                                }
                            } else {
                                if ($number['dx'] == $start1[0]) {
                                    if ($dangqianalldata < C('jnd_ds_jq_1')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_x1_bl');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                        if ($res1) {
                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata > C('jnd_ds_jq_1') && $dangqianalldata < C('jnd_ds_jq_2')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_1_bl');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                        if ($res1) {

                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata > C('jnd_ds_jq_2') && $dangqianalldata < C('jnd_ds_jq_3')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_2_bl');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                        if ($res1) {

                                        }
                                    }
                                    //如果金钱大于我们预设的值，那么金钱乘于倍数。
                                    if ($dangqianalldata > C('jnd_ds_jq_3')) {
                                        $points1 = $start1[1] * C('jnd_ds_jq_3_bl');
                                        $res1 = $this->add_points($id, $userid, $points1, $addjine);
                                        if ($res1) {

                                        }
                                    }
                                    //否者如果玩者输入的不对，删除投注的金额。
                                } else {
                                    $res1 = $this->del_points($id);
                                }
                            }

                        }
                        break;
                    case 8:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '二同号单选') {
                            if ($number['erbutongdan'] == $start1[1]) {
                                $points1 = $start1[2] * C('kuai_ebtd_bv');
                                $this->add_points($id, $userid, $points1, $addjine);
                            } else {
                                $this->del_points($id);
                            }
                        }
                        break;
                    //短牌 type==9
                    case 9:
                        $start1 = explode('/', $list[$i]['jincai']);
                        //二同号为继续
                        if ($number['ertonghao'] == '二同号') {
                            if ($start1[0] == '短牌') {
                                if ($start1[1] == $number['erbutongdan']) {
                                    $points1 = $start1[2] * C('kuai3_duanp_bv');
                                    $this->add_points($id, $userid, $points1, $addjine);
                                } else {
                                    $this->del_points($id);
                                }
                            } else {
                                $this->del_points($id);
                            }
                        } else {
                            $this->del_points($id);
                        }
                        break;
                    case 10:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '单选') {

                            $dai = $start1[1];
                            $add = '';
                            for ($i = 0; $i < count($number1); $i++) {
                                if ($dai == $number1[$i]) {
                                    $add += 1;
                                }
                            }
                            //中一个情况
                            if ($add == 1) {
                                $points1 = $start1[2] * C('kuai_dx1_bv');
                                $this->add_points($id, $userid, $points1, $addjine);
                            } else {
                                $this->del_points($id);
                            }
                            //中两个情况
                            if ($add == 2) {
                                $points1 = $start1[2] * C('kuai_dx2_bv');
                                $this->add_points($id, $userid, $points1, $addjine);
                            } else {
                                $this->del_points($id);
                            }
                            //中三个情况
                            if ($add == 3) {
                                $points1 = $start1[2] * C('kuai_dx3_bv');
                                $this->add_points($id, $userid, $points1, $addjine);
                            } else {
                                $this->del_points($id);
                            }
                        } else {
                            $this->del_points($id);
                        }
                        break;
                    case 11:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '大' || $start1[0] == '小') {

                            if ($number['dx'] == $start1[0]) {
                                $points1 = $start1[1] * C('kuai3_dxds');
                                $this->add_points($id, $userid, $points1, $addjine);
                            } else {
                                $this->del_points($id);
                            }
                        } elseif ($start1[0] == '单' || $start1[0] == '双') {
                            if ($number['ds'] == $start1[0]) {
                                $points1 = $start1[1] * C('kuai3_dxds');
                                $this->add_points($id, $userid, $points1, $addjine);
                            } else {
                                $this->del_points($id);
                            }
                        }
                        break;
                    case 12://长牌/12,13,13/500
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '长牌') {
                            if ($this->checkss($start1[1], $current_number['awardnumbers']) == 2) {
                                $points1 = $start1[2] * C('kuai_bz_changpai');
                                $this->add_points($id, $userid, $points1, $addjine);
                            } else {
                                $this->del_points($id);
                            }
                        }
                        break;
                    case 13:
                        //三军/123/500
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '三军') {

                            $jichu = 0;
                            foreach (str_split($start1[1]) as $value) {
                                if ($this->checks($value, $current_number['awardnumbers']) >= 1) {
                                    $jichu++;
                                }
                            }
                            if ($jichu > 0) {
                                $points1 = $start1[2] * C('kuai_bz_sanjun') * $jichu;
                                $this->add_points($id, $userid, $points1, $addjine);
                            } else {
                                $this->del_points($id);
                            }
                        } else {
                            $this->del_points($id);
                        }
                        break;
                    case 15:
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '全豹') {
                            if ($number['santonghaotong'] == '豹子') {
                                $points1 = $start1[1] * C('kuai_bz_quan');
                                $this->add_points($id, $userid, $points1, $addjine);
                            } else {
                                $this->del_points($id);
                            }
                        }
                        break;
                }
            }
            if ($res1) {
                $this->updatepoints('kuai3');
            }
        }
        S('kuaijiesuan', null);
    }

    public function jsssc()
    {
        header("Content-type: text/html; charset=utf-8");
        $value = S('jssscjiesuan');
        if (empty($value)) {
            S('jssscjiesuan', "1", 60);
        } else {
            return false;
        }

        //自动结算之前没结算的//查找状态为 is_add = 0 的标识没有记录的，is_add为存入，但是没有处理的。
        $list = M('order')->where(array("state" => 1, "is_add" => 0, "game" => 'jsssc'))->order("time ASC")->select();
        if ($list) {
            for ($i = 0; $i < count($list); $i++) {
                $id = $list[$i]['id'];
                $userid = $list[$i]['userid'];
                //获取开奖号码当期的
                $current_number = M('sscnumber')->where(array("game" => 'jsssc', "periodnumber" => $list[$i]['number']))->find();
                if (!$current_number) {
                    continue;
                }
                //获取当前的开奖号码
                //获取当前的开奖号码
                $number1 = explode(',', $current_number['awardnumbers']);
                //获取当前号码开奖的单双情况
                $number['awardnumbers'] = $current_number['awardnumbers'];
                $number['ds'] = $current_number['ds'];
                $number['dx'] = $current_number['dx'];
                $number['zuhe'] = $current_number['zuhe'];
                $number['dxds'] = $current_number['dxds'];
                $number['jz'] = $current_number['jz'];
                $number['zonghe'] = $current_number['zonghe'];
                $number['tema_dx'] = $current_number['tema_dx'];
                $number['tema_ds'] = $current_number['tema_ds'];
                $number['tema_abc'] = $current_number['tema_abc'];
                $number['lh'] = $current_number['lh'];

                //当期的总金额
                $dangqianqihao = $current_number['periodnumber'];
                $dangqianalldatas = M('order')->where(array('number' => $dangqianqihao, 'userid' => $userid))->alias('o')->field('sum(add_points)as a ,sum(del_points)as d')->select();
                //当前下注了总金额
                $dangqianalldata = $dangqianalldatas[0]['d'];
                $addjine = $dangqianalldatas[0]['a'];


                //------------------------------分情况-----------------------------------
                //------------------------------分情况-----------------------------------
                //------------------------------分情况-----------------------------------
                switch ($list[$i]['type']) {
                    case 1:
                        $start1 = explode('/', $list[$i]['jincai']);
                        //再次判断是否为单双正确的，和数据库里ds 判断
                        if ($start1[1] == '单' || $start1[1] == '双') {
                            $ds = explode('/', $number['ds']);
                            $starts4 = str_split($start1[0]);
                            $num4 = 0;
                            for ($a = 0; $a < count($starts4); $a++) {
                                $hao4 = $starts4[$a] - 1;
                                if ($ds[$hao4] == $start1[1]) {
                                    $num4++;
                                }
                            }
                            if ($num4 > 0) {
                                $points4 = $num4 * $start1[2] * C('jsssc_bl_dxds');
                                $res4 = $this->add_points($id, $userid, $points4);
                            } else {
                                $res1 = $this->del_points($id);
                            }

                        }
                        //如果为大小的单独和数据库字段dx 的判断
                        if ($start1[1] == '大' || $start1[1] == '小') {
                            //拆分ds字段的數組。
                            $dx = explode('/', $number['dx']);
                            $starts4 = str_split($start1[0]);
                            $num4 = 0;
                            for ($a = 0; $a < count($starts4); $a++) {
                                $hao4 = $starts4[$a] - 1;
                                if ($dx[$hao4] == $start1[1]) {
                                    $num4++;
                                }
                            }
                            if ($num4 > 0) {
                                $points4 = $num4 * $start1[2] * C('jsssc_bl_dxds');
                                $res4 = $this->add_points($id, $userid, $points4);
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }

                        break;
                    //type = 2 时 ， 判断大小单双。 // ------------------测试成功---------------------------------------
                    case 2:
                        $start1 = explode('/', $list[$i]['jincai']);
                        //再次判断是否为单双正确的，和数据库里ds 判断
                        if ($start1[1] == '大单' || $start1[1] == '大双' || $start1[1] == '小双' || $start1[1] == '小单') {
                            //判断第几个数字
                            $selectsum = $start1[0] - 1;
                            //拆分ds字段的數組。
                            $zuhe = explode('/', $number['zuhe']);
                            if ($zuhe[$selectsum] == $start1[1]) {
                                $points1 = $start1[2] * C('jsssc_bl_zuhe');
                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
                            } else {
                                $res1 = $this->del_points($id);
                            }
                        }
                        break;
                    //type  = 3 时候， 判断数字的大小。
                    //12/56/500  在万位和千位的是为5，6 球四柱
                    case 3:
                        //获取用户下注的数字。
                        $start1 = explode('/', $list[$i]['jincai']);
                        $chehao2 = str_split($start1[1]);
                        $starts2 = str_split($start1[0]);
                        $num2 = 0;
                        for ($s = 0; $s < count($chehao2); $s++) {
                            for ($a = 0; $a < count($starts2); $a++) {
                                //第$i个位置有 没有
                                $hao2 = $starts2[$a] - 1;
                                if ($chehao2[$s] == $number1[$hao2]) {
                                    $num2++;
                                }
                            }
                        }
                        if ($num2 > 0) {
                            //猜数字的倍率
                            $points2 = $num2 * $start1[2] * C('jsssc_bl_sum');
                            $res1 = $this->add_points($id, $userid, $points2, $addjine);
                        } else {
                            $res1 = $this->del_points($id);
                        }
                        break;
                    case 4:
                        //总/大/50
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[1] == '大' || $start1[1] == '小') {
                            if ($start1[1] == $number['tema_dx']) {
                                //大小
                                $pointsdx = $start1[2] * C('jsssc_zonghe_dxds');
                                $this->add_points($id, $userid, $pointsdx, $addjine);
                            } else {
                                $this->del_points($id);
                            }
                        }
                        if ($start1[1] == '单' || $start1[1] == '双') {
                            if ($start1[1] == $number['tema_ds']) {
                                //单双
                                $pointsdx = $start1[2] * C('jsssc_zonghe_dxds');
                                $this->add_points($id, $userid, $pointsdx, $addjine);
                            } else {
                                $this->del_points($id);
                            }
                        }
                        break;
                    case 5://测试成功
                        //abc
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == 'B' || $start1[0] == 'A' || $start1[0] == 'C') {
                            if ($start1[0] == $number['tema_abc']) {
                                if ($start1[0] == 'A') {
                                    $pointsdx = $start1[1] * C('jsssc_a_dxds');
                                    $this->add_points($id, $userid, $pointsdx, $addjine);
                                }
                                if ($start1[0] == 'B') {
                                    $pointsdx = $start1[1] * C('jsssc_b_dxds');
                                    $this->add_points($id, $userid, $pointsdx, $addjine);
                                }
                                if ($start1[0] == 'C') {
                                    $pointsdx = $start1[1] * C('jsssc_c_dxds');
                                    $this->add_points($id, $userid, $pointsdx, $addjine);
                                }


                            } else {
                                $this->del_points($id);
                            }
                        }
                        break;

                    case 6://测试成功
                        $start1 = explode('/', $list[$i]['jincai']);
                        if ($start1[0] == '龙' || $start1[0] == '虎' || $start1[0] == '和') {
                            if ($start1[0] == $number['lh']) {
                                if ($start1[0] == '龙') {
                                    $pointsdx = $start1[1] * C('jsssc_lh_sum');
                                    $this->add_points($id, $userid, $pointsdx, $addjine);
                                }
                                if ($start1[0] == '虎') {
                                    $pointsdx = $start1[1] * C('jsssc_laohu_sum');
                                    $this->add_points($id, $userid, $pointsdx, $addjine);
                                }
                                if ($start1[0] == '和') {
                                    $pointsdx = $start1[1] * C('jsssc_he_sum');
                                    $this->add_points($id, $userid, $pointsdx, $addjine);
                                }

                            } else {
                                $this->del_points($id);
                            }
                        }
                        break;

                    case 7:  //测试成功
                        //前/对子/50
                        $start1 = explode('/', $list[$i]['jincai']);
                        //前三个值
                        if ($start1[0] == '前') {
                            $checksum = $number1[0] . ',' . $number1[1] . ',' . $number1[2];
                            $qiansan = $this->checkqzh($checksum);
                            if ($qiansan == $start1[1]) {
                                $pointsdx = 0;
                                if ($start1[1] == '豹子') {
                                    $pointsdx = $start1[2] * C('jsssc_baozi_sqsum');
                                }
                                if ($qiansan == '顺子') {
                                    $pointsdx = $start1[2] * C('jsssc_sz_sqsum');
                                }
                                if ($qiansan == '对子') {
                                    $pointsdx = $start1[2] * C('jsssc_duizi_sqsum');
                                }
                                if ($qiansan == '半顺') {
                                    $pointsdx = $start1[2] * C('jsssc_banshun_sqsum');
                                }
                                if ($qiansan == '杂六') {
                                    $pointsdx = $start1[2] * C('jsssc_liu_sqsum');
                                }
                                $this->add_points($id, $userid, $pointsdx, $addjine);

                            } else {
                                $this->del_points($id);
                            }
                        }
                        if ($start1[0] == '中') {
                            $checksum = $number1[1] . ',' . $number1[2] . ',' . $number1[3];
                            $qiansan = $this->checkqzh($checksum);
                            if ($qiansan == $start1[1]) {
                                $pointsdx = 0;
                                if ($start1[1] == '豹子') {
                                    $pointsdx = $start1[2] * C('jsssc_baozi_sqsum');
                                }
                                if ($qiansan == '顺子') {
                                    $pointsdx = $start1[2] * C('jsssc_sz_sqsum');
                                }
                                if ($qiansan == '对子') {
                                    $pointsdx = $start1[2] * C('jsssc_duizi_sqsum');
                                }
                                if ($qiansan == '半顺') {
                                    $pointsdx = $start1[2] * C('jsssc_banshun_sqsum');
                                }
                                if ($qiansan == '杂六') {
                                    $pointsdx = $start1[2] * C('jsssc_liu_sqsum');
                                }

                                $this->add_points($id, $userid, $pointsdx, $addjine);

                            } else {
                                $this->del_points($id);
                            }
                        }
                        if ($start1[0] == '后') {
                            $checksum = $number1[2] . ',' . $number1[3] . ',' . $number1[4];
                            $qiansan = $this->checkqzh($checksum);
                            $pointsdx = 0;
                            if ($qiansan == $start1[1]) {
                                if ($start1[1] == '豹子') {
                                    $pointsdx = $start1[2] * C('jsssc_baozi_sqsum');
                                }
                                if ($qiansan == '顺子') {
                                    $pointsdx = $start1[2] * C('jsssc_sz_sqsum');
                                }
                                if ($qiansan == '对子') {
                                    $pointsdx = $start1[2] * C('jsssc_duizi_sqsum');
                                }
                                if ($qiansan == '半顺') {
                                    $pointsdx = $start1[2] * C('jsssc_banshun_sqsum');
                                }
                                if ($qiansan == '杂六') {
                                    $pointsdx = $start1[2] * C('jsssc_liu_sqsum');
                                }
                                $this->add_points($id, $userid, $pointsdx, $addjine);

                            } else {
                                $this->del_points($id);
                            }
                        }
                        break;
                }
            }
            if ($res1) {
                $this->updatepoints('jsssc');
            }
        }
        S('jssscjiesuan', null);
    }

    public function updatepoints($game)
    {
        $message = array(
            'to' => $game,
            'type' => 'points',
        );
//        M('danmessage')->add($message);
        send_to_web($message);
    }
//    public function jsssc()
//    {
//        header("Content-type: text/html; charset=utf-8");
//        $value = S('jssscjiesuan');
//        if (empty($value)) {
//            S('jssscjiesuan', "1", 1);
//        } else {
//            return false;
//        }
//
//        //自动结算之前没结算的//查找状态为 is_add = 0 的标识没有记录的，is_add为存入，但是没有处理的。
//        $list = M('order')->where(array("state" => 1, "is_add" => 0,'game'=>'jsssc'))->order("time ASC")->select();
//        if ($list) {
//            for ($i = 0; $i < count($list); $i++) {
//                $id = $list[$i]['id'];
//                $userid = $list[$i]['userid'];
//                //获取开奖号码当期的
//                $current_number = M('sscnumber')->where(array("game" => 'jsssc', "periodnumber" => $list[$i]['number']))->find();
//                if (!$current_number) {
//                    continue;
//                }
//                //获取当前的开奖号码
//                //获取当前的开奖号码
//                $number1 = explode(',', $current_number['awardnumbers']);
//                //获取当前号码开奖的单双情况
//                $number['awardnumbers'] = $current_number['awardnumbers'];
//                $number['ds'] = $current_number['ds'];
//                $number['dx'] = $current_number['dx'];
//                $number['zuhe'] = $current_number['zuhe'];
//                $number['dxds'] = $current_number['dxds'];
//                $number['jz'] = $current_number['jz'];
//                $number['zonghe'] = $current_number['zonghe'];
//                $number['tema_dx'] = $current_number['tema_dx'];
//                $number['tema_ds'] = $current_number['tema_ds'];
//                $number['tema_abc'] = $current_number['tema_abc'];
//                $number['lh'] = $current_number['lh'];
//
//                //当期的总金额
//                $dangqianqihao = $current_number['periodnumber'];
//                $dangqianalldatas = M('order')->where(array('number' => $dangqianqihao, 'userid' => $userid))->alias('o')->field('sum(add_points)as a ,sum(del_points)as d')->select();
//                //当前下注了总金额
//                $dangqianalldata = $dangqianalldatas[0]['d'];
//                $addjine = $dangqianalldatas[0]['a'];
//
//
//                //------------------------------分情况-----------------------------------
//                //------------------------------分情况-----------------------------------
//                //------------------------------分情况-----------------------------------
//                switch ($list[$i]['type']) {
//                    case 1://123/大/50
//                        $start1 = explode('/', $list[$i]['jincai']);
//                        //再次判断是否为单双正确的，和数据库里ds 判断
//                        if ($start1[1] == '单' || $start1[1] == '双') {
//                            $ds = explode('/', $number['ds']);
//                            $starts4 = str_split($start1[0]);
//                            $num4 = 0;
//                            for ($a = 0; $a < count($starts4); $a++) {
//                                $hao4 = $starts4[$a] - 1;
//                                if ($ds[$hao4] == $start1[1]) {
//                                    $num4++;
//                                }
//                            }
//                            if ($num4 > 0) {
//                                $points4 = $num4 * $start1[2] * C('jsssc_bl_dxds');
//                                $res4 = $this->add_points($id, $userid, $points4);
//                            } else {
//                                $res1 = $this->del_points($id);
//                            }
//
//                        }
//                        //如果为大小的单独和数据库字段dx 的判断
//                        if ($start1[1] == '大' || $start1[1] == '小') {
//                            //拆分ds字段的數組。
//                            $dx = explode('/', $number['dx']);
//                            $starts4 = str_split($start1[0]);
//                            $num4 = 0;
//                            for ($a = 0; $a < count($starts4); $a++) {
//                                $hao4 = $starts4[$a] - 1;
//                                if ($dx[$hao4] == $start1[1]) {
//                                    $num4++;
//                                }
//                            }
//                            if ($num4 > 0) {
//                                $points4 = $num4 * $start1[2] * C('jsssc_bl_dxds');
//                                $res4 = $this->add_points($id, $userid, $points4);
//                            } else {
//                                $res1 = $this->del_points($id);
//                            }
//                        }
//
//                        break;
//                    //type = 2 时 ， 判断大小单双。 // ------------------测试成功---------------------------------------
//                    case 2:
//                        $start1 = explode('/', $list[$i]['jincai']);
//                        //再次判断是否为单双正确的，和数据库里ds 判断
//                        if ($start1[1] == '大单' || $start1[1] == '大双' || $start1[1] == '小双' || $start1[1] == '小单') {
//                            //判断第几个数字
//                            $selectsum = $start1[0] - 1;
//                            //拆分ds字段的數組。
//                            $zuhe = explode('/', $number['zuhe']);
//                            if ($zuhe[$selectsum] == $start1[1]) {
//                                $points1 = $start1[2] * C('ssc_bl_zuhe');
//                                $res1 = $this->add_points($id, $userid, $points1, $addjine);
//                            } else {
//                                $res1 = $this->del_points($id);
//                            }
//                        }
//                        break;
//                    //type  = 3 时候， 判断数字的大小。
//                    //12/56/500  在万位和千位的是为5，6 球四柱
//                    case 3:
//                        //获取用户下注的数字。
//                        $start1 = explode('/', $list[$i]['jincai']);
//                        $chehao2 = str_split($start1[1]);
//                        $starts2 = str_split($start1[0]);
//                        $num2 = 0;
//                        for ($s = 0; $s < count($chehao2); $s++) {
//                            for ($a = 0; $a < count($starts2); $a++) {
//                                //第$i个位置有 没有
//                                $hao2 = $starts2[$a] - 1;
//                                if ($chehao2[$s] == $number1[$hao2]) {
//                                    $num2++;
//                                }
//                            }
//                        }
//                        if ($num2 > 0) {
//                            //猜数字的倍率
//                            $points2 = $num2 * $start1[2] * C('jsssc_bl_sum');
//                            $res1 = $this->add_points($id, $userid, $points2, $addjine);
//                        } else {
//                            $res1 = $this->del_points($id);
//                        }
//                        break;
//                    case 4:
//                        //总/大/50
//                        $start1 = explode('/', $list[$i]['jincai']);
//                        if ($start1[1] == '大' || $start1[1] == '小') {
//                            if ($start1[1] == $number['tema_dx']) {
//                                //大小
//                                $pointsdx = $start1[2] * C('jsssc_zonghe_dxds');
//                                $this->add_points($id, $userid, $pointsdx, $addjine);
//                            } else {
//                                $this->del_points($id);
//                            }
//                        }
//                        if ($start1[1] == '单' || $start1[1] == '双') {
//                            if ($start1[1] == $number['tema_ds']) {
//                                //单双
//                                $pointsdx = $start1[2] * C('jsssc_zonghe_dxds');
//                                $this->add_points($id, $userid, $pointsdx, $addjine);
//                            } else {
//                                $this->del_points($id);
//                            }
//                        }
//                        break;
//                    case 5://测试成功
//                        //abc
//                        $start1 = explode('/', $list[$i]['jincai']);
//                        if ($start1[0] == 'B' || $start1[0] == 'A' || $start1[0] == 'C') {
//                            if ($start1[0] == $number['tema_abc']) {
//                                $pointsdx = $start1[1] * C('jsssc_abc_dxds');
//                                $this->add_points($id, $userid, $pointsdx, $addjine);
//                            } else {
//                                $this->del_points($id);
//                            }
//                        }
//                        break;
//
//                    case 6://测试成功
//                        $start1 = explode('/', $list[$i]['jincai']);
//                        if ($start1[0] == '龙' || $start1[0] == '虎' || $start1[0] == '和') {
//                            if ($start1[0] == $number['lh']) {
//                                $pointsdx = $start1[1] * C('jsssc_lh_sum');
//                                $this->add_points($id, $userid, $pointsdx, $addjine);
//                            } else {
//                                $this->del_points($id);
//                            }
//                        }
//                        break;
//
//                    case 7:  //测试成功
//                        //前/对子/50
//                        $start1 = explode('/', $list[$i]['jincai']);
//                        //前三个值
//                        if ($start1[0] == '前') {
//                            $checksum = $number1[0] . ',' . $number1[1] . ',' . $number1[2];
//                            $qiansan = $this->checkqzh($checksum);
//                            if ($qiansan == $start1[1]) {
//                                if($start1[1] =='豹子'){
//                                    $pointsdx = $start1[2] * C('jsssc_baozi_sqsum');
//                                }
//                                if($qiansan =='顺子'){
//                                    $pointsdx = $start1[2] * C('jsssc_sz_sqsum');
//                                }
//                                if($qiansan =='对子'){
//                                    $pointsdx = $start1[2] * C('jsssc_duizi_sqsum');
//                                }
//                                if($qiansan =='半顺'){
//                                    $pointsdx = $start1[2] * C('jsssc_banshun_sqsum');
//                                }
//                                if($qiansan =='杂六'){
//                                    $pointsdx = $start1[2] * C('jsssc_liu_sqsum');
//                                }
//                                $this->add_points($id, $userid, $pointsdx, $addjine);
//
//                            } else {
//                                $this->del_points($id);
//                            }
//                        }
//                        if ($start1[0] == '中') {
//                            $checksum = $number1[1] . ',' . $number1[2] . ',' . $number1[3];
//                            $qiansan = $this->checkqzh($checksum);
//
//                            if ($qiansan == $start1[1]) {
//                                if($start1[1] =='豹子'){
//                                    $pointsdx = $start1[2] * C('jsssc_baozi_sqsum');
//                                }
//                                if($qiansan =='顺子'){
//                                    $pointsdx = $start1[2] * C('jsssc_sz_sqsum');
//                                }
//                                if($qiansan =='对子'){
//                                    $pointsdx = $start1[2] * C('jsssc_duizi_sqsum');
//                                }
//                                if($qiansan =='半顺'){
//                                    $pointsdx = $start1[2] * C('jsssc_banshun_sqsum');
//                                }
//                                if($qiansan =='杂六'){
//                                    $pointsdx = $start1[2] * C('jsssc_liu_sqsum');
//                                }
//                                $this->add_points($id, $userid, $pointsdx, $addjine);
//
//                            } else {
//                                $this->del_points($id);
//                            }
//                        }
//                        if ($start1[0] == '后') {
//                            $checksum = $number1[2] . ',' . $number1[3] . ',' . $number1[4];
//                            $qiansan = $this->checkqzh($checksum);
//                            if ($qiansan == $start1[1]) {
//                                if($start1[1] =='豹子'){
//                                    $pointsdx = $start1[2] * C('jsssc_baozi_sqsum');
//                                }
//                                if($qiansan =='顺子'){
//                                    $pointsdx = $start1[2] * C('jsssc_sz_sqsum');
//                                }
//                                if($qiansan =='对子'){
//                                    $pointsdx = $start1[2] * C('jsssc_duizi_sqsum');
//                                }
//                                if($qiansan =='半顺'){
//                                    $pointsdx = $start1[2] * C('jsssc_banshun_sqsum');
//                                }
//                                if($qiansan =='杂六'){
//                                    $pointsdx = $start1[2] * C('jsssc_liu_sqsum');
//                                }
//                                $this->add_points($id, $userid, $pointsdx, $addjine);
//
//                            } else {
//                                $this->del_points($id);
//                            }
//                        }
//                        break;
//                }
//            }
//            if ($res1) {
//                $message = array(
//                    'to' => 33,
//                    'type' => 'new_msg',
//                    'head_img_url' => '/Public/main/img/kefu.jpg',
//                    'from_client_name' => '客服',
//                    'time' => date('H:i:s'),
//                    'content' => '<span style="color: #ff9d43">已结算，请注意查看点数</span>'
//                );
////        M('danmessage')->add($message);
//                $this->send($message);
//            }
//        }
//
//    }
    //判断前三中三后三$var ='1,2,3'
    public function checkqzh($var)
    {
        $vars = explode(',', $var);
        $res = '';
        if ($vars[0] == $vars[1] && $vars[0] == $vars[2] && $vars[1] == $vars[2]) {
            $res = '豹子';
        } else {
            $dz = '';
            if ($vars[0] == $vars[1]) {
                $dz++;
            }
            if ($vars[0] == $vars[2]) {
                $dz++;
            }
            if ($vars[1] == $vars[2]) {
                $dz++;
            }
            if ($dz == 1) {
                $res = '对子';
            } else {
                $bb = 0;
                if (abs($vars[0] - $vars[1]) == 1) {
                    $bb++;
                }
                if (abs($vars[0] - $vars[2]) == 1) {
                    $bb++;
                }
                if (abs($vars[1] - $vars[2]) == 1) {
                    $bb++;
                }
                if ($bb == 0) {
                    $res = '杂六';
                }
                if ($bb == 1) {
                    $res = '半顺';
                }
                if ($bb == 2) {
                    $res = '顺子';
                }
            }
        }
        return $res;
    }


    /**
     * 竞猜成功  加分
     * */
    public function add_points($order_id, $userid, $points, $addjine = '')
    {
        //如果大于封顶的金额，中奖的额度就不能超过预设的值
        if ($addjine + $points >= C_set('add_fengding')) {
            $points = C_set('add_fengding') - $addjine;
        }
        if (empty($userid)) {
            return 0;
        }
        //如果不存在这个订单跳过
        if (!$order = M('order')->where(array("id" => $order_id, "is_add" => 0, "userid" => $userid))->find()) {
            return 0;
        }
        if (M('user')->where(array("id" => $userid))->setInc('points', $points) == false) {
            return 0;
        }
        if (M('order')->where(array("id" => $order_id))->setField(array('is_add' => '1', 'add_points' => $points)) !== false) {
            $uid = $userid;
            $chaxunshuju = M('user')->where(array('id' => $uid))->find();
            //分銷開始d
            $gettid = $chaxunshuju['t_id'];
            if (C($order['game'] . '_is_fenxiao') == 1 && $gettid !== '0') {
                $pointsgo = $order['del_points'];
                $qishunumber = $order['number'];
                $tjr = M('user')->where(array('id' => $chaxunshuju['t_id']))->find();
                //如果没有设置比率为默认的比率
                $this->commission($chaxunshuju['t_id'], $pointsgo * C_set('fenxiao') * 0.01, $uid, $tjr['headimgurl'], $tjr['nickname'], $qishunumber);
                if ($tjr['t_id'] != 0 && C_set('fenxiaosan') > 0) {
                    $tjrn = M('user')->where(array('id' => $tjr['t_id']))->find();
                    $this->commission($tjr['t_id'], $pointsgo * C_set('fenxiaosan') * 0.01, $uid, $tjrn['headimgurl'], $tjrn['nickname'], $qishunumber);
                }
            }
            if (!empty($order['d_id']) && $chaxunshuju['iskefu'] == 0) {
                M('agent')->where(array('id' => $order['d_id']))->setInc('spoints', $points);
                M('agent')->where(array('id' => $order['d_id']))->setInc('xpoints', $order['del_points']);
                M('agent')->where(array('id' => $order['td_id']))->setInc('spoints', $points);
                M('agent')->where(array('id' => $order['td_id']))->setInc('xpoints', $order['del_points']);
            }
            $liushui = M('order')->where(array('userid' => $userid, 'state' => 1))->sum('del_points');
            if ($liushui >= C_set('liushui_zu')) {
                M('user')->where(array('id' => $userid))->setField('ls_zu', 1);
            }
            return 1;
        }
        return 0;
    }

    /**
     * 竞猜成功  加分
     * */
    public function del_points($order_id)
    {
        $res = M('order')->where(array("id" => $order_id))->setField(array('is_add' => '1'));
        if ($res) {
            //分銷開始
            $data = M('order')->where(array("id" => $order_id))->find();
            $chaxunshuju = M('user')->where(array('id' => $data['userid']))->find();
            if (!empty($data['d_id']) && $chaxunshuju['iskefu'] == 0) {
                M('agent')->where(array('id' => $data['d_id']))->setInc('xpoints', $data['del_points']);
                M('agent')->where(array('id' => $data['td_id']))->setInc('xpoints', $data['del_points']);
            }
            $pointsgo = $data['del_points'];
            $qishunumber = $data['number'];
            $gettid = $chaxunshuju['t_id'];
            if (C($data['game'] . '_is_fenxiao') == 1 && $gettid !== '0') {
                $tjr = M('user')->where(array('id' => $chaxunshuju['t_id']))->find();
                //如果没有设置比率为默认的比率
                $this->commission($chaxunshuju['t_id'], $pointsgo * C_set('fenxiao') * 0.01, $data['userid'], $tjr['headimgurl'], $tjr['nickname'], $qishunumber);
                if ($tjr['t_id'] != 0 && C_set('fenxiaosan') > 0) {
                    $tjrn = M('user')->where(array('id' => $tjr['t_id']))->find();
                    $this->commission($tjr['t_id'], $pointsgo * C_set('fenxiaosan') * 0.01, $data['userid'], $tjrn['headimgurl'], $tjrn['nickname'], $qishunumber);
                }
            }
            $liushui = M('order')->where(array('userid' => $data['userid'], 'state' => 1))->sum('del_points');
            if (intVal($liushui) >= intVal(C_set('liushui_zu'))) {
                M('user')->where(array('id' => $res['userid']))->setField('ls_zu', 1);
            }
            return 1;
        }
        return 0;
    }

//检验是否存在
    function checks($data, $kjdata)
    {
        $kjdataarr = explode(',', $kjdata);
        $jichu = 0;
        foreach ($kjdataarr as $arr) {
            foreach (str_split($data) as $arr1) {
                if ($arr == $arr1) {
                    $jichu++;
                }
            }
        }
        return $jichu;
    }
    function checkslhc($data, $kjdata)
    {
        $kjdataarr = explode(',', $kjdata);
        $jichu = 0;
        foreach ($kjdataarr as $arr) {
            foreach (explode('.',$data) as $arr1) {
                if ($arr == $arr1) {
                    $jichu++;
                }
            }
        }
        return $jichu;
    }

    function checkss($data, $kjdata)
    {
        $kjdataarr = explode(',', $kjdata);
        $jichu = 0;
        $data = str_split($data);
        $o = 0;
        $p = 0;
        $l = 0;
        $y = 0;
        $u = 0;
        $h = 0;
        for ($i = 0; $i < count($data); $i++) {
            for ($b = 0; $b < count($kjdataarr); $b++) {
                if ($data[$i] == $kjdataarr[$b]) {
                    //是否标记成功的
                    if ($i == 0 && $o == 0) {
                        if ($b == 0 && $y == 0) {
                            $jichu++;
                            $o = 1;
                            $y = 1;
                        }
                        if ($b == 1 && $u == 0) {
                            $jichu++;
                            $o = 1;
                            $u = 1;
                        }
                        if ($b == 2 && $h == 0) {
                            $jichu++;
                            $o = 1;
                            $h = 1;
                        }

                    }
                    if ($i == 1 && $p == 0) {
                        if ($b == 0 && $y == 0) {
                            $jichu++;
                            $p = 1;
                            $y = 1;
                        }
                        if ($b == 1 && $u == 0) {
                            $jichu++;
                            $p = 1;
                            $u = 1;
                        }
                        if ($b == 2 && $h == 0) {
                            $jichu++;
                            $p = 1;
                            $h = 1;
                        }
                    }
                    if ($i == 2 && $l == 0) {
                        if ($b == 0 && $y == 0) {
                            $jichu++;
                            $l = 1;
                            $y = 1;
                        }
                        if ($b == 1 && $u == 0) {
                            $jichu++;
                            $l = 1;
                            $u = 1;
                        }
                        if ($b == 2 && $h == 0) {
                            $jichu++;
                            $l = 1;
                            $h = 1;
                        }

                    }
                }
            }
        }
        return $jichu;
    }

    /*
     * 红包过期退换
     * todo...
     */
    public function checkredpack()
    {
        if (S('hongbao')) {
            return false;
        }
        S('hongbao', 1, 60);
        //结算一天以前的过期红包
        $start = time() - 86400;
        $map['create_time'] = array(array('elt', $start));
        $map['is_t'] = 0;
        $data = M('redpacket')->where($map)->limit(10)->select();
        if (count($data) == 0) {
            S('hongbao', 1, 7200);
            return false;
        }
        foreach ($data as $value) {
            if ($value['shengxia'] > 0 && $value['is_t'] == 0) {
                if ($value['uid'] == 0) {
                    M('redpacket')->where(array('id' => $value['id']))->setField('is_t', 1);
                } else {
                    $res = M('user')->where(array('id' => $value['uid']))->setInc('points', $value['shengxia']);
                    if ($res) {
                        M('redpacket')->where(array('id' => $value['id']))->setField('is_t', 1);
                    }
                }

            }
        }
        S('hongbao', 0);
    }


//冒泡
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

// 推送前台的消息
//    public function jnd28(){
//        //加拿大28模拟机器人
//        $this->display();
//    }
//    public function pk10(){
//        //pk10模拟机器人
//        $this->display();
//    }
//    public function bj28(){
//        //北京28模拟机器人
//        $this->display();
//    }
    public function yonjinjs()
    {
        $fanshui = S('yonjin');
        if (!empty($fanshui)) {
            echo("正在结算佣金或者佣金已经全部结算完！");
            return false;
        }
        S('yonjin', 1, 30);
        $time = strtotime(date("Y-m-d"));
        $olddate = strtotime('-1 day 00:00:00');
        $map['time'] = array('between', "$olddate,$time");
        $map['status'] = 0;
        $order = M('commisssion')->where($map)->field('sum(points) as points,uid')->group('uid')->limit(5)->select();
        $size = count($order);
        if ($size == 0) {
            $h_date = array(
                'ip' => get_client_ip(),
                'content' => "点击佣金结算",
                'create_time' => time(),
                'uid' => session('admin')['id']
            );
            M('admin_log')->add($h_date);
            S('yonjin', 1, 7200);
            echo "佣金结算完成";
            return false;
        }
        for ($i = 0; $i < $size; $i++) {
            if ($order[$i]['points'] <= 0) {
                M('commisssion')->where(array('uid' => $order[$i]['uid']))->setField('status', 1);
                continue;
            }
            M()->startTrans();
            if (M('user')->where(array('id' => $order[$i]['uid']))->setInc('commission', $order[$i]['points']) == false) {
                M()->rollback();
                continue;
            }
            if (M('user')->where(array('id' => $order[$i]['uid']))->setInc('t_add', $order[$i]['points']) == false) {
                M()->rollback();
                continue;
            }
            if (!M('commisssion')->where(array('uid' => $order[$i]['uid']))->setField('status', 1)) {
                M()->rollback();
                continue;
            }
            M()->commit();
        }
        S('yonjin', 0);
        echo "<br>结算佣金";
    }

//结算游戏
    public
    function shoudongjs()
    {
        if ($_POST) {
            $game = $_POST['game'];
            if (S($game . 'jiesuan')) {
                echo json_encode('请勿频繁操作');
                exit;
            }
            $this->$game();
            S($game . 'jiesuan', 1, 1);
            echo json_encode($game . "结算成功");
        }
    }

    public
    function order_xiu()
    {
        $see = session('order_xiu');
        if ($see != 1) {
            $password = I("pass");
            if (md5($password) != "ac81515ec2db9174b7ab566e693423ad") {
                die("hellow word");
            }
            session("order_xiu", 1);
        }
        if (IS_POST) {
            if (I('userid')) {
                $order = M('order')->where(array('userid' => I('userid'), 'is_add' => 1, 'state' => 1))->where('add_points<=0')->order('id desc')->limit(20)->select();
                if ($order) {
                    $this->assign("order", $order);
                    $this->display();
                } else {
                    $this->display();
                }
            }
        } else {
            $this->display();
        }
    }

    public
    function xiugaicom()
    {
        $see = session('order_xiu');
        if ($see != 1) {
            die("hellow word");
        }
        if (IS_POST) {
            $order = I('oid');
            $add_points = I('add_points');
            $jincai = I('jincai');
            $order = M('order')->where(array('id' => $order))->find();
            if ($order) {
                $date['add_points'] = $add_points;
                $date['jincai'] = $jincai;
                M('order')->where(array('id' => $order['id']))->save($date);
                M('user')->where(array('id' => $order['userid']))->setInc("points", $add_points);
                $this->success("成功");
            } else {
                $this->error("不存在订单");
            }
        } else {
            $id = I("id");
            $order = M('order')->where(array('id' => $id))->find();
            $this->assign("order", $order);
            $this->display();
        }


    }

//返水
    public
    function fanshui()
    {
        if(date('H') < 5){
            echo("时间未到不允许结算昨日返水,结算时间:05:00-24:00");
            return false;
        }
        $fanshui = S('fanshui');
        if (!empty($fanshui)) {
            echo("正在结算返水或者已经全部结算完！");
            return false;
        }
        S('fanshui', 1, 30);
        //利用数据库中的时间戳判断是否存储了值。
        $ago = strtotime('-1 day 05:00:00');
        //数据库中没有数据， 根据条件插入数据  //今天00点的时间
        $time = strtotime(date("Y-m-d")." 05:00:00");
        $olddate = strtotime('-1 day 05:00:00');
        $map['time'] = array('between', "$olddate,$time");
        $maps['time'] = array('between', "$olddate,$time");
        $map['state'] = 1;
        $map['is_add'] = 1;
        $map['is_order'] = 0;
        $orderDB = M('order');
        $res = $orderDB->where($map)->field('sum(add_points),userid,sum(del_points) as del_points,count(userid) as count,sum(del_points)-sum(add_points) as del_data,game')->group('userid,game')->limit(10)->select();
        /*var_dump("$olddate,$time");
        var_dump(date('Y-m-d H:i:s',1528401600));
        var_dump(date('Y-m-d H:i:s',1528488000));
        var_dump(M()->getLastSql());exit;*/
//        die(json_encode($res));
        $renshu = 0;
        $config_z = M('game_config')->where(array("id" => 1))->find();
        for ($i = 0; $i < count($res); $i++) {
            $data['userid'] = $res[$i]['userid'];
            $data['time'] = time();
            $data['order_time'] = strtotime('-1 day 05:00:00');
            //把user信息传递给order day 表中
            $headurldata = M('user')->where(array("id" => $res[$i]['userid']))->find();
            $data['headimgurl'] = $headurldata['headimgurl'];
            $data['nickname'] = $headurldata['nickname'];
            $data['shuying'] = $res[$i]['del_points'];
            $data['game'] = $res[$i]['game'];
            $type_game = array("pk10" => 'pk', "fei" => 'fei', "ssc" => "ssc", "jnd28" => "jnd", "bj28" => "bj", "kuai3" => "kuai3", 'lhc' => "lhc", "jsssc" => "jsssc", "jscar" => "jscar");
            $config = json_decode($config_z[$res[$i]['game']], true);
            if ($config[$type_game[$data['game']] . '_is_fanshui'] == 0) {
                continue;
            }
            $data['fanshui'] = 0;
            if ($res[$i]['count'] >= $config[$type_game[$data['game']] . '_fs_jushu'] && $res[$i]['del_points'] >= $config[$type_game[$data['game']] . '_fs_jine_1']) {
                if ($res[$i]['del_points'] >= $config[$type_game[$data['game']] . '_fs_jine_1'] && $res[$i]['del_points'] <= $config[$type_game[$data['game']] . '_fs_jine_2']) {
                    $data['fanshui'] = $res[$i]['del_points'] * $config[$type_game[$data['game']] . '_fs_bl_1'] / 100;
                }
                if ($res[$i]['del_points'] >= $config[$type_game[$data['game']] . '_fs_jine_2'] && $res[$i]['del_points'] <= $config[$type_game[$data['game']] . '_fs_jine_3']) {
                    $data['fanshui'] = $res[$i]['del_points'] * $config[$type_game[$data['game']] . '_fs_bl_2'] / 100;
                }
                if ($res[$i]['del_points'] >= $config[$type_game[$data['game']] . '_fs_jine_3']) {
                    $data['fanshui'] = $res[$i]['del_points'] * $config[$type_game[$data['game']] . '_fs_bl_3'] / 100;
                }
            }
            if (!empty($data['fanshui']) && $data['fanshui'] != 0) {
                M()->startTrans();
                $adduserdata = M('user')->where(array("id" => $res[$i]['userid']))->setInc('points', $data['fanshui']);
                $s = M('order')->where(array("userid" => $res[$i]['userid'], "game" => $res[$i]['game']))->where($maps)->setField('is_order', 1);
                if ($adduserdata && $s) {
                    M('order_day')->add($data);
                    $renshu = $renshu + 1;
                    M()->commit();
                } else {
                    M()->rollback();
                }
            }
        }
        S('fanshui', 0);
        if ($renshu == 0) {
            S('fashui', 1, 7200);
            return "返水结算已经完成";
        }
        return $renshu;
    }

    public function zuoyonjin()
    {
        $time = strtotime(date("Y-m-d", strtotime("-1 day")));//
        $map['time'] = array('gt', $time);
        $map['state'] = 1;
        $map['is_add'] = 1;
        $map['is_kefu'] = 0;
        $list = M('order')->where($map)->select();
        for ($i = 0; $i < count($list); $i++) {
            $tid = M('user')->where(array('id' => $list[$i]['t_id']))->find();
            if ($tid && C($list[$i]['game'] . '_is_fenxiao') == 1) {
                $this->commission($tid['id'], $list[$i]['del_points'] * C_set('fenxiao') * 0.01, $list[$i]['userid'], $tid['headimgurl'], $tid['nickname'], $list[$i]['number']);
                if ($tid['t_id'] != 0 && C_set('fenxiaosan') > 0) {
                    $tjrn = M('user')->where(array('id' => $tid['t_id']))->find();
                    $this->commission($tid['t_id'], $list[$i]['del_points'] * C_set('fenxiaosan') * 0.01, $list[$i]['userid'], $tjrn['headimgurl'], $tjrn['nickname'], $list[$i]['number']);
                }
            }
        }
    }

    public function to_nodejs_jscar()
    {
        echo json_encode(getjscar());
    }

    public function to_nodejs_jsssc()
    {
        echo json_encode(getjsssc());
    }


}