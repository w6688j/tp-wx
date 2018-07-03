<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2017/11/8
 * Time: 20:51
 */

namespace Home\Controller;
use Think\Controller;
use Think\Think;
use Think\Upload;
use think\Model;
use Qiniu\Auth;
// 引入上传类
use Qiniu\Storage\UploadManager;

class HonbaoController extends BaseController
{
    public function linhonbao(){
//        dump($_POST['hid']);
        if ($_POST){
            $hid=$_POST['hid'];
            $uid=$_POST['uid'];
            $rtype =$_POST['rtype'];
            $hon=S($hid."hon");
            if (!empty($hon)){
                $date['msgs']=202;
                $date['date']='系统忙，请稍后再试';
                die(json_encode($date));
            }
            S($hid."hon",1,10);
            $ruser=M('redpacket')->where(array('id'=>$hid))->find();
            if (!$ruser){
                $date['msgs']=202;
                $date['date']='该红包不存在';
                S($hid."hon",0);
                die(json_encode($date));
            }
            if ($ruser['status'] !=0){
                S($hid."hon",0);
                $date['msgs']=202;
                $date['date']='该红包已领完';
                die(json_encode($date));
            }
            if (time()-$ruser['create_time']>86400){
                S($hid."hon",0);
                $date['msgs']=202;
                $date['date']='红包已过期';
                die(json_encode($date));
            }
//            if(!$rtype){
//                $date['msgs']=202;
//                $date['date']='红包错误';
//                die(json_encode($date));
//            }
            $hongbao=M('redpacketed')->where(array('rid'=>$hid,'uid'=>$uid))->find();
            if ($hongbao){
                S($hid."hon",0);
                $date['msgs']=202;
                $date['date']='此红包你已领取';
                die(json_encode($date));
            }
            //条件判断是否领取
            if($rtype ==2){

                $seerdb = M('redpacket')->where(array('id'=>$hid))->find();

                //查看该朋友上分情况
                if($seerdb['daysfen']!=0){
                    $map['userid']=$uid;
                    $start = strtotime( '00:00:00');
                    $end = strtotime( '23:59:59');
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map['status'] =1;
                    $map['type2'] =1;
                    $seefen = M('money')->where($map)->field('sum(points) as points')->select();
                    //查看上分
                    $daysf =$seefen[0]['points'];
                    if($daysf<$seerdb['daysfen'] || $daysf ==null){
                        S($hid."hon",0);
                        $date['msgs']=202;
                        $date['date']='您还没做发到领取此红包的条件哦，联系客服，咨询领取规则。';
                        die(json_encode($date));
                    }
                }
                if($seerdb['dayyli']!=0){
                    $map['userid']=$uid;
                    $start = strtotime( '00:00:00');
                    $end = strtotime( '23:59:59');
                    $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                    $map['is_add'] =1;
                    $map['state'] =1;
                    $seeorder = M('order')->where($map)->field('sum(del_points) as del_points,sum(add_points) as add_points')->select();
                    if($seeorder[0]['del_points']-$seeorder[0]['add_points'] <= $seerdb['dayyli'] ||$seeorder[0]['del_points']-$seeorder[0]['add_points'] ==null){
                        S($hid."hon",0);
                        $date['msgs']=202;
                        $date['date']='您还没做发到领取此红包的条件哦，联系客服，咨询领取规则。';
                        die(json_encode($date));
                    }
                }
                if($seerdb['daylshui']!=0){
                    if(!$seeorder){
                        $map['userid']=$uid;
                        $start = strtotime( '00:00:00');
                        $end = strtotime( '23:59:59');
                        $map['time'] = array(array('egt', $start), array('elt', $end), 'and');
                        $map['is_add'] =1;
                        $map['state'] =1;
                        $seeorder = M('order')->where($map)->field('sum(del_points) as del_points,sum(add_points) as add_points')->select();
                    }
                    //达到多少流水可以领取
                    if($seeorder[0]['del_points'] < $seerdb['daylshui'] ||$seeorder[0]['del_points'] ==null){
                        S($hid."hon",0);
                        $date['msgs']=202;
                        $date['date']='您还没做发到领取此红包的条件哦，联系客服，咨询领取规则。';
                        die(json_encode($date));
                    }
                }

            }
            if($rtype ==3){
            //为指定人才能领取的人领取
                $seerdb = M('redpacket')->where(array('id'=>$hid))->find();
                $type3arr = explode(',',$seerdb['toid']);
                $shifouk =0;
                foreach ($type3arr as $value){
                    if($value ==$uid){
                        $shifouk++;
                    }
                }
                if($shifouk == 0){
                    S($hid."hon",0);
                    $date['msgs']=202;
                    $date['date']='该红包已领完';
                    die(json_encode($date));
                }
            }
            $honid=M('redpacketed')->where(array('rid'=>$hid,'isfou'=>0))->find();
            if (!$honid){
                if (!M('redpacket')->where(array('id'=>$hid))->setInc('status',1)){
                    M()->rollback();
                    S($hid."hon",0);
                    $date['msgs']=202;
                    $date['date']='错误';
                    die(json_encode($date));
                }
                S($hid."hon",0);
                $date['msgs']=202;
                $date['date']='该红包已领完';
                die(json_encode($date));
            }
            $user=M('user')->where(array('id'=>$uid))->find();
            if (!$user){
                S($hid."hon",0);
                $date['msgs']=202;
                $date['date']='用户不存在';
                die(json_encode($date));
            }
            M()->startTrans();
            $datd['uid']=$uid;
            $datd['isfou']=1;
            $datd['time']=time();
            $us=M('redpacketed')->where(array('id'=>$honid['id']))->save($datd);
            if (!$us){
                S($hid."hon",0);
                M()->rollback();
                $date['msgs']=202;
                $date['date']='错误';
                die(json_encode($date));
            }
            $datab['userid'] = $uid;
            $datab['time'] = time();
            $datab['points'] = $honid['money'];
            $datab['type'] = '3';
            $datab['ip'] = get_client_ip();
            $datab['balance'] =$user['points']+$honid['money'];
            $mo['time'] =time();
            $mo['points']=$honid['money'];
            $mo['msg'] ='领取红包';
            $mo['yue'] =$user['points']+$honid['money'];
            $mo['headimgurl'] =$user['headimgurl'];
            if($user['iskefu'] ==1){
                $mo['is_kefu'] =1;
            }
            if (!empty($user['d_id']) &&$user['iskefu']==0){
                M('agent')->where(array('id'=>$user['d_id']))->setInc('hbmoney',$honid['money']);
                M('agent')->where(array('id'=>$user['td_id']))->setInc('hbmoney',$honid['money']);
                $mo['d_id'] =$user['d_id'];
                $mo['td_id'] =$user['td_id'];
            }
            $mo['nickname'] =$user['nickname'];
            $mo['type2'] =3;
            $mo['status'] = 1;
            $mo['userid'] =$uid;
            $mo['typepay'] ='red';
            M('money')->add($mo);
            if(!M('integral')->add($datab) || M('user')->where(array('id'=>$uid))->setInc('points',$honid['money']) ==false||M('redpacket')->where(array('id'=>$hid))->setDec('shengxia',$honid['money'])==false){
                M()->rollback();
                S($hid."hon",0);
                $date['msgs']=202;
                $date['date']='错误';
                die(json_encode($date));
            }
            M()->commit();
            S($hid."hon",0);
            $date['msgs']=200;
            $date['date']='恭喜您，成功领取'.$honid['money'].'元';
            die(json_encode($date));
        }
    }
    public function fornt_send_view(){
        $this->display();
    }

    public function getimg() {
        if (!empty($_FILES)) {
            if(!C('qiniu_ak')){
                $this->upload();
            }else{
                //如果有文件上传 上传附件
                $this->up_qiniu();
            }

        }
    }
    public function up_qiniu(){
// 需要填写你的 Access Key 和 Secret Key
        require_once 'vendor/Qiniu/autoload.php';
        $accessKey = C('qiniu_ak');
        $secretKey =C('qiniu_sk');

// 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);
// 要上传的空间
        $bucket = C('qiniu_zone');
//        dump($_FILES);exit;
// 生成上传 Token
        $token = $auth->uploadToken($bucket);
// 要上传文件的本地路径
        $filePath =$_FILES['file']['tmp_name'];
// 上传到七牛后保存的文件名
        if($this->isImg($filePath) ==false){
            $this->error('上传格式错误');
        };

//        $filetype = ['jpg', 'jpeg', 'gif', 'bmp', 'png'];
//        if (!in_array($filePath, $filetype)) {
//            echo json_decode("不是图片类型");
//        }
        $key = time().rand(1,999);
// 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();
// 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        $data['url'] ='http://'.C('qiniu_url').'/'.$ret['key'];
        echo json_encode($data);
    }
//判断上传的是不是图片
    function isImg($fileName)
    {
        $file  = fopen($fileName, "rb");
        $bin  = fread($file, 2); // 只读2字节

        fclose($file);
        $strInfo = @unpack("C2chars", $bin);
        $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);
        $fileType = '';

        if($typeCode == 255216 /*jpg*/ || $typeCode == 7173 /*gif*/ || $typeCode == 13780 /*png*/)
        {
            return $typeCode;
        }
        else
        {
            // echo '"仅允许上传jpg/jpeg/gif/png格式的图片！';
            return false;
        }
    }
//    // 文件上传
    public function upload(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功
            $data['url'] = '/Uploads/'.$info['file']['savepath'].$info['file']['savename'];
            echo json_encode($data);
        }
    }
    /*
     * usersend 发送红包
     */
    public function usersend(){
        if(IS_AJAX){
//                如果是后台发送的todo。。。
//            if(){

//            }
            $uid = session('user')['id'];
            //发红包操作
            D('Honbao')->user_send_redpack($uid,$_POST['points'],$_POST['sum'],$_POST['leavemsg']);

        }else{
            $this->error('错误');
        }

    }
}