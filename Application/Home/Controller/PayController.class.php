<?php
namespace Home\Controller;
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2018/4/4
 * Time: 21:42
 */
class PayController
{
    public function index(){
        $interfaceurl="http://cs.beebuy.net/initpay/";
        $method 		= "create";		//业务类型 .create 创建订单 .pay 支付 .query 查询
        $app_id 		= "16983";					//商家ID
        $out_trade_no 	= date("YmdHis").rand(100000,999999);//商家订单号
        $way 			= I('tondao');			//通道类型 100001-100027 Bank  100028 ALI  100031 WX
        $amount 		= I('money');	//充值金额
        $buyer_id		= session('user')['id'];		//用户在您网站的惟一编号，或用户名
        $buyer_ip		= $this->get_ip();					//用户的IP，非服务器IP
        $url		 	= C_set('weixin_url')."/home/pay/notify";			//通知URL
        $attach			= "chongzhi";
        $key="hjuk6dzbgt96fdyf";
        $sign_data = "method=$method&app_id=$app_id&out_trade_no=$out_trade_no&way=$way&amount=$amount&key=$key";
        $sign = md5($sign_data);	//签名数据 32位小写的组合加密验证串
        $PostUrl=$interfaceurl."?method=$method&app_id=$app_id&out_trade_no=$out_trade_no&way=$way&amount=$amount&buyer_id=$buyer_id&buyer_ip=$buyer_ip&url=$url&attach=$attach&sign=$sign";
        $result = $this->http($PostUrl);
        die("aaa");
        $content = json_decode($result,true);
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
    function http($url, $data='', $method='GET'){
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        //curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.95 Safari/537.36 SE 2.X MetaSr 1.0');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        if($method=='POST'){
            curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
            if ($data != ''){
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
            }
        }
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据
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
}