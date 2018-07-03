<?php
namespace Home\Controller;
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2018/4/4
 * Time: 21:42
 */
use Think\Controller;

header("Content-type:text/html;charset=utf-8");
class PaydoController extends Controller
{
    public function index(){
        $partner= '612434933268508';
        $user_seller='217523';
        $out_order_no=date("YmdHis") . rand(1000000, 9999999);
        $subject="ghjksd";
        $total_fee=I('money');
        $body="2e2exccxvcuhhvh";
        $notify_url="http://" . $_SERVER["HTTP_HOST"] . "/home/paydo/notify";
        $return_url="http://" . $_SERVER["HTTP_HOST"] . "/home/select/toprecord";
        $parameter = array(
            //pid号
            'partner' => $partner,
            //商户号
            'user_seller' => $user_seller,
            //订单号
            'out_order_no' => $out_order_no,
            //订单名称
            'subject' => $subject,
            //金额
            'total_fee' => $total_fee,
            //商品描述
            'body' => $body,
            //异步返回结果地址
            'notify_url' => $notify_url,
            //同步返回结果地址
            'return_url' => $return_url,
            //加密字符串
            'sign' => $this->signparame('i8TYuzj5e5WQvesA6k7nxVeJIhmCkba6',$body,$notify_url,$out_order_no,$partner,$return_url,$subject,$total_fee,$user_seller),
            'pay_type' => '1',//预留字段
            'banktype' => '',//预留字段
        );
        $list=$this->send_post("http://www.globalspay.com/PayOrder/payorder",$parameter);
        die($list);
        $content['data']=urldecode($content['data']);
        if($content['code'] == 200){			//200  连接正常
            $status = $content['status'];
            $code_type	= $content['code_type'];	//展示方式,#qr 将data数据生成二维码,#url 当前浏览器跳转到data地址,#fac 当前页面输出data内容
            $data		= $content['data'];			//当code_type为'qr'时,将此数据生成二维码给客户.当code_type为'url'时,浏览器跳转到此地址.当code_type为'fac'时,当前页面输出此内容
            $body		= $content['body'];			//订单说明
            $timeout	= $content['timeout'];		//订单有效期,客户需要在此时间内完成支付
            $trade_no	= $content['trade_no'];		//系统订单号
            $date['points']=$amount;
            $date['status']=0;
            $date['type2']=1;
            $date['typepay']="online";
            $date['orderid']=$out_trade_no;
            $date['userid']=$buyer_id;
            $date['time']=time();
            $date['xtorderid']=$trade_no;
            $date['nickname']=session('user')['nickname'];
            $date['headimgurl']=session('user')['headimgurl'];
            $date['yue']=session('user')['points'];
            $date['t_id']=session('user')['t_id'];
            $d_id=session('user')['d_id'];
            if (!empty($d_id)){
                $date['d_id']=session('user')['d_id'];
                $date['td_id']=session('user')['td_id'];
            }
            M('money')->add($date);
            if($status=='success'){
                die('订单支付成功');
            }else if($status=='closed'){
                die('订单超时,请返回重试!');
            }else if($status=='erro'){
                die($body);
            }else if($status=='waitpay'){		//下单成功,等待客户支付.
                if($code_type=='qr'){
                    $qrcode = 	$data;			//code_type为'qr'时,将此数据生成二维码,用于客户扫码支付!
                    header('Location: '.$qrcode);die;
                }else if($code_type=='url'){
                    //echo($data);
                    header('Location: '.$data);	//code_type为'url'时,浏览器跳转到此地址
                    die();
                }else if($code_type=='fac'){
                    echo($data);				//code_type为'fac'时,当前页面输出data内容
                    die();
                }else{
                    die('qr信息有误!');
                }
            }else{
                die('订单信息有误,请返回重试!'.$status);
            }
        }
    }

    public function notify(){
        file_put_contents("test.txt", json_encode($_REQUEST), FILE_APPEND);
        $key="hjuk6dzbgt96fdyf";
        $trade_no		= $_REQUEST["trade_no"];
        $app_id			= $_REQUEST["app_id"];
        $out_trade_no	= $_REQUEST["out_trade_no"];
        $status			= $_REQUEST["status"];
        $way			= $_REQUEST["way"];
        $amount			= $_REQUEST["amount"];
        $attach			= $_REQUEST["attach"];
        $sign			= $_REQUEST["sign"];
        $sign_data = "trade_no=$trade_no&app_id=$app_id&out_trade_no=$out_trade_no&status=$status&way=$way&amount=$amount&key=$key";
        $md5_sign = md5($sign_data);
        echo "ok";
        if ($md5_sign==$sign) {
            if ($status == "success")
                echo ",ok,amount:" . $amount;
                $order = M('money')->where(array('status' => 0, 'orderid' => $trade_no))->find();
                if ($order){
                    $dats['confirm_time']=time();
                    $dats['status']=1;
                    M()->startTrans();
                    if (!M('money')->where(array('status' => 0, 'orderid' => $trade_no))->save($dats)){
                        M()->rollback();
                        return false;
                    }
                    if(M('user')->where(array('id'=>$order['uid']))->setInc('points',$order['points'])){
                        M()->rollback();
                        return false;
                    }
                    M()->commit();
                }
            }else{
                echo ",no";
        }
    }

    function get_ip(){
        $arr_ip_header = array(
            'HTTP_CDN_SRC_IP',
            'HTTP_PROXY_CLIENT_IP',
            'HTTP_WL_PROXY_CLIENT_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR',
        );
        $client_ip = 'unknown';
        foreach ($arr_ip_header as $key){
            if (!empty($_SERVER[$key]) && strtolower($_SERVER[$key]) != 'unknown')
            {
                $client_ip = $_SERVER[$key];
                break;
            }
        }
        return $client_ip;
    }

    public function signparame($key,$body,$notify_url,$out_order_no,$partner,$return_url,$subject,$total_fee,$user_seller) {
        //key码
        $p = array(
            'body' => $body,
            'notify_url' => $notify_url,
            'out_order_no' => $out_order_no,
            'partner' => $partner,
            'return_url' => $return_url,
            'subject' => $subject,
            'total_fee' => $total_fee,
            'user_seller' => $user_seller,
        );

        //拼接字符串
        if($p['body']==''){
            $para = 'notify_url=' . $p['notify_url'] .
                '&out_order_no=' . $p['out_order_no'] .
                '&partner=' . $p['partner'] .
                '&return_url=' . $p['return_url'] .
                '&subject=' . $p['subject'] .
                '&total_fee=' . $p['total_fee'] .
                '&user_seller=' . $p['user_seller'];
        }else{
            $para = 'body=' . $p['body'] .
                '&notify_url=' . $p['notify_url'] .
                '&out_order_no=' . $p['out_order_no'] .
                '&partner=' . $p['partner'] .
                '&return_url=' . $p['return_url'] .
                '&subject=' . $p['subject'] .
                '&total_fee=' . $p['total_fee'] .
                '&user_seller=' . $p['user_seller'];
        }
        // return $para;*
        return md5($para.$key);
    }
    function send_post($url, $post_data) {
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
    }
}