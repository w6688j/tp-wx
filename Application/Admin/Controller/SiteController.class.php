<?php

namespace Admin\Controller;

use Think\Controller;
use Think\Model;

class SiteController extends BaseController
{
    public function _initialize()
    {
        C(M('config')->find());
        parent::_initialize();
    }


    public function huigunByOrderId(){
        if($_POST){
//            <option value="1">删除订单(不返钱，记录删除)</option>
//             <option value="2">撤回订单(返钱,同时删除记录)</option>
//            <option value="3">重新结算(未结算状态)</option>
            if(!$_POST['status']){error('请选择操作');}
               $order=M('order')->where(array('id'=>$_POST['id']))->find();
               if(!$order){error('没有该订单信息');}
                switch ($_POST['status']){
                    case 1:
                    $res = M('order')->where(array('id'=>$_POST['id']))->delete();
                    if($res){show('成功');}else{error('失败');}
                        break;
                    case 2:
                        if($order['state'] ==0){
                            error('该订单已经取消了');
                        }
                     $money = $order['del_points']-$order['add_points'];
//                     error($money);
                     $res1=M('user')->where(array('id'=>$order['userid']))->setInc('points',$money);
                     if($res1){
                            $res2=M('order')->where(array('id'=>$_POST['id']))->setField('state',0);
                            if($res2){show('撤销并且删除成功');}else{error('失败');};
                     }
                        break;
                    case 3:
                        if($order['is_add'] ==0){
                            error('已经是没有结算的订单');
                        }
                        $money = $order['del_points']-$order['add_points'];
                        $res1=M('user')->where(array('id'=>$order['userid']))->setInc('points',$money);
                        if($res1){
                            $res = M('order')->where(array('id'=>$_POST['id']))->setField(array('is_add'=>0,'add_points'=>0));
                            if ($res){
                                show('成功');
                            }else{
                                error('失败');
                            }
                        }
                        break;
                }
        }else{
            $this->display();
        }
    }
    /*
 * 保存游戏开始
 */
    public function game_config_pk10()
    {
        $this->assign("config", json_decode(M('game_config')->where(array("id" => 1))->getField("pk10"), true));
        $this->display();
    }

    public function game_config_kuai3()
    {
        $this->assign("config", json_decode(M('game_config')->where(array("id" => 1))->getField("kuai3"), true));
        $this->display();
    }

    public function game_config_bj28()
    {
        $this->assign("config", json_decode(M('game_config')->where(array("id" => 1))->getField("bj28"), true));
        $this->display();

    }

    public function game_config_lhc()
    {
        $this->assign("config", json_decode(M('game_config')->where(array("id" => 1))->getField("lhc"), true));
        $this->display();

    }

    public function game_config_fei()
    {
        $this->assign("config", json_decode(M('game_config')->where(array("id" => 1))->getField("fei"), true));
        $this->display();

    }

    public function game_config_jnd28()
    {

        $this->assign("config", json_decode(M('game_config')->where(array("id" => 1))->getField("jnd28"), true));
        $this->display();

    }

    public function game_config_jsssc()
    {

        $this->assign("config", json_decode(M('game_config')->where(array("id" => 1))->getField("jsssc"), true));
        $this->display();

    }

    public function game_config_jscar()
    {
        $this->assign("config", json_decode(M('game_config')->where(array("id" => 1))->getField("jscar"), true));
        $this->display();

    }

    public function game_config_ssc()
    {
        $this->assign("config", json_decode(M('game_config')->where(array("id" => 1))->getField("ssc"), true));
        $this->display();
    }

    public function save_game_config()
    {
        if (IS_POST) {
            $game = $_POST['game'];
            if (M('game_config')->where(array("id" => 1))->setField($game, json_encode($_POST)) != false) {
                $this->del_cache();
                $this->success('修改成功，跳转中~', U('site/' . 'game_config_' . $game));
            } else {
                $this->error('操作失败', U('site/' . 'game_config_' . $game));
            };
        }
    }

    /*
     * 保存游戏结束
     */

    /*
     * 保存config游戏配置
     */
    public function set_config()
    {
        if (M('config')->where(array("id" => "1"))->save($_POST) != false) {
            $this->del_cache();
            $this->success('修改成功，跳转中~', U('site/' . 'index'));
        } else {
            $this->error('操作失败', U('site/' . 'index'));
        };
    }

    /*
  * 提前开奖
  */
    public function kjbf()
    {
        if (IS_POST) {
            $data = $_POST;
            if (empty($data["game"])) {
                $this->error('请您选择提前开奖的游戏名称');
            }
            if (!is_numeric($data["periodnumber"])) {
                $this->error('开奖期数格式错误');
            }
            if ($data['game'] == 'jscar' || $data['game'] == 'pk10' || $data['game'] == 'fei') {
                if (strlen($data["awardnumbers"]) !== 29) {
                    $this->error('开奖号码格式错误：例子:01,01,01,10,10,10,10,10,10,10');
                }
            }
            if ($data['game'] == 'ssc' || $data['game'] == 'jsssc') {
                if (strlen($data["awardnumbers"]) !== 9) {
                    $this->error('开奖号码格式错误：例子:5,5,5,5,5');
                }
            }
            if ($data['game'] == 'bj28' || $data['game'] == 'jnd28' || $data['game'] == 'kuai3') {
                if (strlen($data["awardnumbers"]) !== 5) {
                    $this->error('开奖号码格式错误：例子:5,5,5');
                }
            }
            if (M('gamebefore')->where(array('periodnumber' => $data["periodnumber"], 'game' => $data['game']))->find()) {
                $this->error('不能手动开奖已经开奖的期号');
            } else {
                $data['time'] = time();
                M('gamebefore')->add($data);
                $this->error('开奖成功', U('site/' . 'kjbf'));
            }
        }
        $list = M('gamebefore')->order('id desc')->select();
        $this->assign('list', $list);
        $this->display();
    }

    public function kjbf_see_edit()
    {
        if ($_POST) {
            if ($_POST['do'] == 'edit') {
                $awr['awardnumbers'] = $_POST['awardnumbers'];
                if ($_POST['game'] == 'jscar') {
                    if (strlen($awr["awardnumbers"]) != 29) {
                        show('开奖号码格式错误：例子:01,01,01,10,10,10,10,10,10,10', 0);
                    }
                }
                if ($_POST['game'] == 'jsssc') {
                    if (strlen($awr["awardnumbers"]) != 9) {
                        show('开奖号码格式错误：例子:5,5,5,5,5', 0);
                    }
                }
                $res = M('gamebefore')->where(array('id' => $_POST['id']))->save($awr);
                if ($res) {
                    show('成功', 1);
                } else {
                    show('失败', 0);
                }
            } elseif ($_POST['do'] == 'del') {
                $res = M('gamebefore')->where(array('id' => $_POST['id']))->delete();
                if ($res) {
                    show('删除成功', 1);
                } else {
                    show('删除失败', 0);
                }
            }
        } else {
            show('提交错误');
        }
    }

    public function postfanshui()
    {
        if (IS_POST) {
            $this->sitesavefs('site.php');
        } else {
            $this->display();
        }
    }

    public function index()
    {
        if (IS_POST) {

        } else {
            $this->display();
        }
    }

    public function index2()
    {
        die(C("kefu"));
    }

    public function setting()
    {
        if (IS_POST) {
            $this->sitesave('route.php');
        } else {
            $this->display();
        }
    }

    public function save_system_setting()
    {
        $res = M('config')->where(array('id' => 1))->save($_POST);
        if ($res) {
            $this->del_cache();
            $this->success('成功');
        } else {
            $this->error('失败');
        }
    }

    private function sitesave($filename)
    {
        if ($this->update_config($_POST, $filename)) {
            $this->success('修改成功，跳转中~', U('site/' . ACTION_NAME));

        } else {

            $this->error('操作失败', U('site/' . ACTION_NAME));
        }
    }

    private function sitesavefs($filename)
    {

        if ($this->update_config($_POST, $filename)) {

            $this->success('修改成功，跳转中~', U('site/' . 'fanshuishezhi'));

        } else {

            $this->error('操作失败', U('site/' . 'fanshuishezhi'));
        }
    }

    public function fanshuishezhi()
    {
        $this->display();
    }

    public function fanshui()
    {
        /*
         * 目前反水为pc蛋蛋不反水，不论输赢反水
         */
        if (IS_POST) {
            $au=new AutoController();
            $renshu=  $au->fanshui();
            $this->success(date('Y年m月d日') .$renshu.'个人,返水手动一次返水10个', U('site/' . ACTION_NAME));
        } else {
            $list = M('order_day')->order('id DESC')->select();
            $this->assign('list', $list);
            $this->display();
        }
    }

    public function pk10sd()
    {
        if (IS_POST) {
            $data = $_POST;
            if (empty($data["awardnumbers"]) || empty($data["periodnumber"])) {
                $this->error('开奖期号或者号码不能为空');
            }
            if (strlen($data["awardnumbers"]) !== 29) {
                $this->error('开奖号码格式错误');
            }
            if (!is_numeric($data["periodnumber"])) {
                $this->error('开奖期数格式错误');
            }
            if (M('number')->where(array('periodnumber' => $data["periodnumber"], 'game' => 'pk10'))->find()) {
                $this->error('不能手动开奖已经开奖的期号');
            }
            $map['awardnumbers'] = $data["awardnumbers"];
            $map['awardtime'] = $data["kaijiangtime"];
//            $map['time'] = time();
            $map['periodnumber'] = $data["periodnumber"];
            $info = explode(',', $data['awardnumbers']);
            for ($i = 0; $i < count($info); $i++) {
                if ($info[$i] < 10) {
                    $info[$i] = substr($info[$i], 1);
                }
            }
            $map['number'] = serialize($info);
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
            $map['lh'] = serialize($lh);
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
            $map['game'] = 'pk10';
            if (M('number')->add($map)) {
                $this->success('修改成功，跳转中~', U('site/' . ACTION_NAME));
            } else {
                $this->success('修改失败~', U('site/' . ACTION_NAME));
            }
        }
        $this->display();
    }

    public function lhcsd()
    {
        if (IS_POST) {
            $data = $_POST;
            if (empty($data["awardnumbers"]) || empty($data["periodnumber"]) || empty($data["kaijiangtime"])) {
                $this->error('数据不能为空');
            }
            if (!is_numeric($data["periodnumber"])) {
                $this->error('开奖期数格式错误');
            }

            if (M('lhcnumber')->where(array('periodnumber' => $data["periodnumber"], 'game' => 'lhc'))->find()) {
                $this->error('不能手动开奖已经开奖的期号');
            }

            $number1 = explode(',', $data["awardnumbers"]);
            //当期特码
            $tema = $number1[6];
            $map['awardnumbers'] = $data["awardnumbers"];
            $map['awardtime'] = $data["kaijiangtime"];
            $map['time'] = strtotime($data["kaijiangtime"]);
            $map['periodnumber'] = $data["periodnumber"];
            $map['game'] = 'lhc';
            $map['tema_sebo'] = get_lhc_sebo($tema);
            $map['tema_wuxing'] = get_lhc_wuxing($tema);
            $map['shengxiao_all'] = json_encode(get_all_shengxiao($data["awardnumbers"]));
            $tema_shengxiao = seeshengxiao($tema);
            $map['tema_shengxiao'] = $tema_shengxiao;
            $res1 = M('lhcnumber')->add($map);
            if ($res1) {
                $this->success('修改成功，跳转中~', U('site/' . ACTION_NAME));
            } else {
                $this->success('修改失败~', U('site/' . ACTION_NAME));
            }
        } else {
            $this->display();

        }

    }

    public function bj28sd()
    {
        if (IS_POST) {
            $data = $_POST;
            if (empty($data["awardnumbers"]) || empty($data["periodnumber"])) {
                $this->error('开奖期号或者号码不能为空');
            }
            if (strlen($data["awardnumbers"]) !== 5) {
                $this->error('开奖号码格式错误，例如：3,5,6');
            }
            if (!is_numeric($data["periodnumber"])) {
                $this->error('开奖期数格式错误');
            }

            if (M('dannumber')->where(array('periodnumber' => $data["periodnumber"], 'game' => 'bj28'))->find()) {
                $this->error('不能手动开奖已经开奖的期号');
            }
            $map['awardnumbers'] = $data["awardnumbers"];
            $map['awardtime'] = $data["kaijiangtime"];
            $map['periodnumber'] = $data["periodnumber"];
            $info = explode(',', $data["awardnumbers"]);
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
                $shunzi = "非顺子";
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
            $map['game'] = 'bj28';
//            $map['time'] = time();
            $map['periodnumber'] = $data["periodnumber"];
            $map['awardtime'] = $data['kaijiangtime'];
            $map['awardnumbers'] = $data['awardnumbers'];

            if (M('dannumber')->add($map)) {
                $this->success('修改成功，跳转中~', U('site/' . ACTION_NAME));
            } else {
                $this->success('修改失败~', U('site/' . ACTION_NAME));
            }
        } else {
            $this->display();
        }
    }

    public function kuai3sd()
    {
        if (IS_POST) {
            $data = $_POST;
            if (empty($data["awardnumbers"]) || empty($data["periodnumber"])) {
                $this->error('开奖期号或者号码不能为空');
            }
            if (strlen($data["awardnumbers"]) !== 5) {
                $this->error('开奖号码格式错误，例如：3,5,6');
            }
            if (!is_numeric($data["periodnumber"])) {
                $this->error('开奖期数格式错误');
            }
            if (M('kuainumber')->where(array('periodnumber' => $data["periodnumber"], 'game' => 'kuai3'))->find()) {
                $this->error('不能手动开奖已经开奖的期号');
            }
            $map['awardnumbers'] = $data["awardnumbers"];
            $map['awardtime'] = $data["kaijiangtime"];
//            $map['time'] = time();
            $map['periodnumber'] = $data["periodnumber"];
            $info = explode(',', $data["awardnumbers"]);
            $n1 = $info[0];
            $n2 = $info[1];
            $n3 = $info[2];
            $alln = $n1 + $n2 + $n3;
            //总和，赋值给总和.
            $map['zonghe'] = $alln;
            //判断单双
            //判断是否为顺子
            $shunzi = "";
            $ss = $n1 . $n2 . $n3;
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
            if (M('kuainumber')->add($map)) {
                $this->success('修改成功，跳转中~', U('site/' . ACTION_NAME));
            } else {
                $this->success('修改失败~', U('site/' . ACTION_NAME));
            }
        } else {
            $this->display();
        }
    }

    public function feisd()
    {
        if (IS_POST) {
            $data = $_POST;
            if (empty($data["awardnumbers"]) || empty($data["periodnumber"])) {
                $this->error('开奖期号或者号码不能为空');
            }
            if (strlen($data["awardnumbers"]) !== 29) {
                $this->error('开奖号码格式错误');
            }
            if (!is_numeric($data["periodnumber"])) {
                $this->error('开奖期数格式错误');
            }
            if (M('number')->where(array('periodnumber' => $data["periodnumber"], 'game' => 'fei'))->find()) {
                $this->error('不能手动开奖已经开奖的期号');
            }
            $map['awardnumbers'] = $data["awardnumbers"];
            $map['awardtime'] = $data["kaijiangtime"];
//            $map['time'] = time();
            $map['periodnumber'] = $data["periodnumber"];
            $info = explode(',', $data['awardnumbers']);
            for ($i = 0; $i < count($info); $i++) {
                if ($info[$i] < 10) {
                    $info[$i] = substr($info[$i], 1);
                }
            }
            $map['number'] = serialize($info);
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
            $map['lh'] = serialize($lh);
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
            $map['game'] = 'fei';
            if (M('number')->add($map)) {
                $this->success('修改成功，跳转中~', U('site/' . ACTION_NAME));
            } else {
                $this->success('修改失败~', U('site/' . ACTION_NAME));
            }
        } else {
            $this->display();
        }
    }

    public function jnd28sd()
    {
        if (IS_POST) {
            $data = $_POST;
            if (empty($data["awardnumbers"]) || empty($data["periodnumber"])) {
                $this->error('开奖期号或者号码不能为空');
            }
            if (strlen($data["awardnumbers"]) !== 5) {
                $this->error('开奖号码格式错误，例如：3,5,6');
            }
            if (!is_numeric($data["periodnumber"])) {
                $this->error('开奖期数格式错误');
            }
            if (M('dannumber')->where(array('periodnumber' => $data["periodnumber"], 'game' => 'jnd28'))->find()) {
                $this->error('不能手动开奖已经开奖的期号');
            }
            $map['awardnumbers'] = $data["awardnumbers"];
            $map['awardtime'] = $data["kaijiangtime"];
//            $map['time'] = time();
            $map['periodnumber'] = $data["periodnumber"];
            $info = explode(',', $data["awardnumbers"]);
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
                $shunzi = "非顺子";
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
            $map['game'] = 'jnd28';
//            $map['time'] = time();
            $map['periodnumber'] = $data["periodnumber"];
            $map['awardtime'] = $data['kaijiangtime'];
            $map['awardnumbers'] = $data['awardnumbers'];

            if (M('dannumber')->add($map)) {
                $this->success('修改成功，跳转中~', U('site/' . ACTION_NAME));
            } else {
                $this->success('修改失败~', U('site/' . ACTION_NAME));
            }
        } else {
            $this->display();
        }
    }

    public function sscsd()
    {
        if (IS_POST) {
            $data = $_POST;
            if (empty($data["awardnumbers"]) || empty($data["periodnumber"])) {
                $this->error('开奖期号或者号码不能为空');
            }
            if (strlen($data["awardnumbers"]) !== 9) {
                $this->error('开奖号码格式错误，例如：3,5,6');
            }
            if (!is_numeric($data["periodnumber"])) {
                $this->error('开奖期数格式错误');
            }
            if (M('sscnumber')->where(array('periodnumber' => $data["periodnumber"], 'game' => 'ssc'))->find()) {
                $this->error('不能手动开奖已经开奖的期号');
            }
            $map['awardnumbers'] = $data["awardnumbers"];
            $map['awardtime'] = $data["kaijiangtime"];
//            $map['time'] = time();
            $map['periodnumber'] = $data["periodnumber"];
            $info = explode(',', $data['awardnumbers']);
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
            if ($tema >= 15) {
                $tema_dx = '大';
            } else {
                $tema_dx = '小';
            }
            //特码单双
            if ($tema % 2 == 2) {
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
            $map['game'] = 'ssc';
            if (M('sscnumber')->add($map)) {
                $this->success('修改成功，跳转中~', U('site/' . ACTION_NAME));
            } else {
                $this->success('修改失败~', U('site/' . ACTION_NAME));
            }
        } else {
            $this->display();
        }
    }

    public function jssscsd()
    {
        if (IS_POST) {
            $data = $_POST;
            if (empty($data["awardnumbers"]) || empty($data["periodnumber"])) {
                $this->error('开奖期号或者号码不能为空');
            }
            if (strlen($data["awardnumbers"]) !== 9) {
                $this->error('开奖号码格式错误');
            }
            if (!is_numeric($data["periodnumber"])) {
                $this->error('开奖期数格式错误');
            }
            if (M('sscnumber')->where(array('periodnumber' => $data["periodnumber"], 'game' => 'jsssc'))->find()) {
                $this->error('不能手动开奖已经开奖的期号');
            }
            $map['awardnumbers'] = $data["awardnumbers"];
            $map['awardtime'] = $data["kaijiangtime"];
//            $map['time'] = time();
            $map['periodnumber'] = $data["periodnumber"];
            $info = explode(',', $data['awardnumbers']);
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
            if ($tema >= 15) {
                $tema_dx = '大';
            } else {
                $tema_dx = '小';
            }
            //特码单双
            if ($tema % 2 == 2) {
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
            $map['game'] = 'jsssc';
            if (M('sscnumber')->add($map)) {
                $this->success('修改成功，跳转中~', U('site/' . ACTION_NAME));
            } else {
                $this->success('修改失败~', U('site/' . ACTION_NAME));
            }
        } else {
            $this->display();
        }
    }

    public function jscarsd()
    {
        if (IS_POST) {
            $data = $_POST;
            if (empty($data["awardnumbers"]) || empty($data["periodnumber"])) {
                $this->error('开奖期号或者号码不能为空');
            }
            if (strlen($data["awardnumbers"]) !== 29) {
                $this->error('开奖号码格式错误，');
            }
            if (!is_numeric($data["periodnumber"])) {
                $this->error('开奖期数格式错误');
            }
            if (M('number')->where(array('periodnumber' => $data["periodnumber"], 'game' => 'jscar'))->find()) {
                $this->error('不能手动开奖已经开奖的期号');
            }
            $map['awardnumbers'] = $data["awardnumbers"];
            $map['awardtime'] = $data["kaijiangtime"];
//            $map['time'] = time();
            $map['periodnumber'] = $data["periodnumber"];
            $info = explode(',', $data['awardnumbers']);
            for ($i = 0; $i < count($info); $i++) {
                if ($info[$i] < 10) {
                    $info[$i] = substr($info[$i], 1);
                }
            }
            $map['number'] = serialize($info);
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
            $map['lh'] = serialize($lh);
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
            $map['game'] = 'jscar';
            if (M('number')->add($map)) {
                $this->success('修改成功，跳转中~', U('site/' . ACTION_NAME));
            } else {
                $this->success('修改失败~', U('site/' . ACTION_NAME));
            }
        } else {
            $this->display();
        }
    }


    public function honbao()
    {
        $this->display();
    }

    public function fahonbao()
    {
        if ($_POST) {
            $jine = $_POST['jine'];
            $num = intval($_POST['num']);
            if ($num <= 0 || $num > 100) {
                $this->error('数量错误');
            }
            if ($jine<1) {
                $this->error('金额错误');
            }
            $date = array(
                'jine' => $jine,
                'num' => $num,
                'create_time' => time()
            );
            $content = I('content');
            $date['type'] = 1;
            $date['shengxia'] = $jine;
            if (!$rid = M('redpacket')->add($date)) {
                $this->error("数据错误");
            } else {
                $ab = $this->honbaochu($rid, $jine, $num, $content, $type = '1');
                if (!$ab) {
                    M('redpacket')->where(array('id' => $rid))->delete();
                    $this->error("红包发送失败");
                } else {
                    $this->success("发送成功");
                }
            }
        } else {
            $this->error("数据错误");
        }
    }

    /*
     * 红包发送ytpe2  条件发送
     */
    public function fahonbaot2()
    {
        if ($_POST) {
            $jine = $_POST['jine'];
            $num = intval($_POST['num']);
            if ($num <= 0 || $num > 100) {
                $this->error('数量错误');
            }
            if ($jine <= 1) {
                $this->error('金额错误');
            }
            $date = array(
                'jine' => $jine,
                'num' => $num,
                'create_time' => time()
            );
            $content = I('content');
            $date['type'] = 2;
            $date['daylshui'] = $_POST['daylshui'];
            $date['daysfen'] = $_POST['daysfen'];
            $date['dayyli'] = $_POST['dayyli'];
            if (!$rid = M('redpacket')->add($date)) {
                $this->error("数据错误");
            } else {

                $ab = $this->honbaochu($rid, $jine, $num, $content, $type = 2);
                if (!$ab) {
                    M('redpacket')->where(array('id' => $rid))->delete();
                    $this->error("红包发送失败");
                } else {
                    $this->success("发送成功");
                }
            }
        } else {
            $this->error("数据错误");
        }
    }

    public function liuyan()
    {
        if (IS_POST) {
            $id = $_POST['id'];
            $del = M('chatroom')->where(array('id' => $id))->delete();
            if ($del) {
                $this->success('成功');
            } else {
                $this->error('失败');
            }
        } else {
            $count = M('chatroom')->count();
            $page = new \Think\Page($count, 20);
            $show = $page->show();
            $list = M('chatroom')->limit($page->firstRow . ',' . $page->listRows)->order("time Desc")->select();
            $this->assign('list', $list);
            $this->assign('show', $show);
            $this->display();
        }
    }

    public function fahonbaot3()
    {
        if ($_POST) {
            $jine = $_POST['jine'];
            $num = intval($_POST['num']);
            if ($num <= 0 || $num > 500) {
                $this->error('数量错误');
            }
            if ($jine <= 1) {
                $this->error('金额错误');
            }
            if (!$_POST['toid']) {
                $this->error('请填写领取的id');
            }
            if (!$num) {
                $rtoidarr = explode(',', $_POST['toid']);
                $num = count($rtoidarr);
            }
            $date = array(
                'jine' => $jine,
                'num' => $num,
                'create_time' => time()
            );
            $date['toid'] = $_POST['toid'];
            $date['type'] = 3;
            $content = I('content');
            $date['content'] = $content;
            if (!$rid = M('redpacket')->add($date)) {
                $this->error("数据错误");
            } else {
                $ab = $this->honbaochu($rid, $jine, $num, $content, $type = 3);
                if (!$ab) {
                    M('redpacket')->where(array('id' => $rid))->delete();
                    $this->error("红包发送失败");
                } else {
                    $this->success("发送成功");
                }
            }
        } else {
            $this->error("数据错误");
        }
    }

    /*
     * 发布红包，指定的人可以领取
     */
    public function honbaochu($id, $jine, $num, $content, $type)
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
            //todo：这里推送红包
            $message = array(
                'hid' => $id,
                'rtype' => $type,
                'type' => 'hongbao',
                'jine' => $content,
                'head_img_url' => '/Public/main/img/kefu.jpg',
                'from_client_name' => '客服',
                'time' => date('H:i:s'),
                'content' => $content,
            );
//            M('danmessage')->add($message);
            send_to_web($message);
            $messages = array(
                'uid' => $id,
                'type' => $type,
                'uname' => '客服',
                'imgurl' => '/Public/main/img/kefu.jpg',
                'iskefu' => 1,
                'jine' => $content,
                'ishon' => 1,
                'hid' => $id,
                'content' => $content,
                'time' => time()
            );
            M('chatroom')->add($messages);
            return true;
        }
    }

    //推送
    protected function send($content)
    {
        // 指明给谁推送，为空表示向所有在线用户推送
        $to_uid = "";
        // 推送的url地址，上线时改成自己的服务器地址
        $push_api_url = C('push_api_url');
        $post_data = array(
            "type" => "publish",
            "content" => json_encode($content),
            "to" => $to_uid,
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $push_api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;
    }

    public function lunbotu()
    {
        $list = M('carousel')->select();
        $this->assign('list', $list);
        $this->display();
    }

    public function addlunbotu()
    {
        $this->display();
    }

    public function addlunbotudo()
    {
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 3145728;// 设置附件上传大小
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath = './Uploads/lunbourl/'; // 设置附件上传根目录
        $upload->savePath = ''; // 设置附件上传（子）目录
        $upload->saveRule = 'time';
        $info = $upload->upload();
        if ($info) {
            $img_url = '/Uploads/lunbourl/' . $info['file0']['savepath'] . $info['file0']['savename'];//如果上传成功则完成路径拼接
        } else {
            $this->error($upload->getError());//否则就是上传错误，显示错误原因
        }
        $data['imgurl'] = $img_url;
        $data['create_time'] = time();
        if ($data) {
            $res = M('carousel')->add($data);
            if ($res) {
                $this->success('添加成功!', U('Admin/site/lunbotu'));
            } else {
                $this->error('添加失败');
            }
        }
    }

    public function dellunbo()
    {
        $id = I('tid');
        if (!M('carousel')->where(array('id' => $id))->delete()) {
            $this->error('删除失败');
        } else {
            $this->success("删除成功");
        }
    }

    public function seedo()
    {
        $uid = $_GET['id'];
        $db = M('user')->where(array('id' => $uid))->find();
        $select = M('user')->select();
        $jichu = 0;
        foreach ($select as $value) {
            if ($value['t_id'] == $uid) {
                $jichu++;
            }
        }
        $xiangqing = D('Member')->recharge_byid($uid);
        $this->assign('xiangqing', $xiangqing);
        $this->assign('t_sum', $jichu);
        $this->assign('userinfo', $db);
        $this->display();
    }

    public function huodong()
    {
        if (IS_POST) {
            $data['activity_title'] = I('activity_title');
            $data['activity_content'] = I('activity_content');
            if (M('config')->where(array('id' => 1))->save($data)) {
                $this->del_cache();
                $this->success('修改成功，跳转中~', U('site/' . 'huodong'));
            } else {
                $this->error('操作失败', U('site/' . 'huodong'));
            }
        } else {
            $this->display();
        }
    }

    public function config_set()
    {
        if (IS_POST) {
            $date = array('pk10', 'jnd28', 'bj28', 'ssc', 'kuai3', 'fei', 'jsssc', 'jscar', 'lhc');
            foreach ($date as $key => $value) {
                $data[$value] = json_encode(array(
                    'on_off' => I('a_' . ($key + 1)),
                    'robot_on_off' => I('b_' . ($key + 1)),
                    'status_off' => I('c_' . ($key + 1)),
                    'robo_suiji' => I('d_' . ($key + 1))
                ));
            }
            if (M('game_config')->where(array("id" => 2))->save($data) != false) {
                $this->del_cache();
                $this->success('修改成功，跳转中~', U('site/' . 'config_set'));
            } else {
                $this->error('操作失败', U('site/' . 'config_set'));
            };
        } else {
            $list = M('game_config')->where(array("id" => 2))->find();
            $this->assign("ssc", json_decode($list['ssc'], true));
            $this->assign("jsssc", json_decode($list['jsssc'], true));
            $this->assign("pk10", json_decode($list['pk10'], true));
            $this->assign("fei", json_decode($list['fei'], true));
            $this->assign("jnd28", json_decode($list['jnd28'], true));
            $this->assign("jscar", json_decode($list['jscar'], true));
            $this->assign("bj28", json_decode($list['bj28'], true));
            $this->assign("kuai3", json_decode($list['kuai3'], true));
            $this->assign("lhc", json_decode($list['lhc'], true));
            $this->display();
        }
    }

    public function del_cache()
    {
        delFileByDir(APP_PATH . 'Runtime/');
    }

    public function shoudongjs()
    {
        $this->display();
    }
}
