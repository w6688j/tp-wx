<?php

namespace Admin\Controller;
use Think\Controller;
use Think\Model;
class MemberController extends BaseController{

	public function index(){
		$nickname = I('nickname');
		$userid = I('userid');
		$member = M('user');
        $type = I('type');
		if ($type==1){
            $data['sf_zu']=1;
            $datas['a.sf_zu']=1;
        }elseif($type==2){
            $data['ls_zu']=1;
            $datas['a.ls_zu']=1;
        }elseif($type==3){
            $data['tj_zu']=1;
            $datas['a.tj_zu']=1;
        }else{
            $data=array();
            $datas=array();
        }
		if(!empty($nickname)){
			$count = $member->where($data)->where("nickname like '%{$nickname}%'")->count();
            $page = new \Think\Page($count,20);
			$show = $page->show();
			$list =M('user  as  a')->join('think_user  as  b  on b.id = a.t_id','LEFT')->join('think_agent  as  c  on c.id = a.d_id','LEFT')->join('think_agent  as  d  on d.id = a.td_id','LEFT')->field('a.*,b.nickname as tname,c.username as dname,d.username as tdname,b.id as bid,c.id as cid,d.id as ddid')->where("a.nickname like '%{$nickname}%'")->where($datas)->limit($page->firstRow.','.$page->listRows)->order("a.id desc")->select();
		}elseif(!empty($userid)){
			$count = $member->where($data)->where(array('id'=>$userid))->count();
			$page = new \Think\Page($count,20);
			$show = $page->show();
			$list = M('user  as  a')->join('think_user  as  b  on b.id = a.t_id','LEFT')->join('think_agent  as  c  on c.id = a.d_id','LEFT')->join('think_agent  as  d  on d.id = a.td_id','LEFT')->field('a.*,b.nickname as tname,c.username as dname,d.username as tdname,b.id as bid,c.id as cid,d.id as ddid')->where(array('a.id'=>$userid))->where($datas)->limit($page->firstRow.','.$page->listRows)->order("a.id desc")->select();
		} else{
			$count = $member->where($data)->count();
			$page = new \Think\Page($count,20);
			$show = $page->show();
			$list = M('user  as  a')->join('think_user  as  b  on b.id = a.t_id','LEFT')->join('think_agent  as  c  on c.id = a.d_id','LEFT')->join('think_agent  as  d  on d.id = a.td_id','LEFT')->field('a.*,b.nickname as tname,c.username as dname,d.username as tdname,b.id as bid,c.id as cid,d.id as ddid')->where($datas)->limit($page->firstRow.','.$page->listRows)->order("a.id desc")->select();
		}
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}
	
	public function disable(){
		$id = I('id');
		$res = M('user')->where("id = $id")->setField('status',0);
        $date=array(
            'ip'=>get_client_ip(),
            'content'=>"禁用账号".$id,
            'create_time'=>time(),
            'uid'=>session('admin')['id']
        );
        M('admin_log')->add($date);
		if($res){
			$this->success('禁用成功！');
		}else{
			$this->error('禁用失败！');
		}
	}


	public function delete(){
		$id = I('id');
		if(empty($id)){
			$this->error('删除失败！');
		}
		$res = M('user')->where(array("id"=>$id))->delete();//用户表
		$res1 = M('order')->where(array("userid" => $id))->delete();//下注order记录表
		$res2 = M('integral')->where(array("userid" => $id))->delete();//上下分记录表
		$res3 = M('wx')->where(array("userid" => $id))->delete();//上下分记录表
        $date=array(
            'ip'=>get_client_ip(),
            'content'=>"删除账号".$id,
            'create_time'=>time(),
            'uid'=>session('admin')['id']
        );
        M('admin_log')->add($date);
		if($res!==false){
			$this->success('删除成功！');
		}else{
			$this->error('删除失败！');
		}
	}
	
	
	public function endisable(){
		$id = I('id');
		$res = M('user')->where("id = $id")->setField('status',1);
        $date=array(
            'ip'=>get_client_ip(),
            'content'=>"启用账号".$id,
            'create_time'=>time(),
            'uid'=>session('admin')['id']
        );
        M('admin_log')->add($date);
		if($res){
			$this->success('启用成功！');
		}else{
			$this->error('启用失败！');
		}
	}

    public function yongjin(){
        $nickname = I('nickname');
        $userid = I('userid');
        $member = M('commisssion');
        $type=I('type');
        if(empty($type)){
            $date['status']=0;
        }else{
            $date['status']=$type;
        }
        if(!empty($nickname)){
            $count = $member->where("nickname like '%{$nickname}%'")->where($date)->count();
            $page = new \Think\Page($count,20);
            $show = $page->show();
            $list = $member->where("nickname like '%{$nickname}%'")->where($date)->limit($page->firstRow.','.$page->listRows)->order("time DESC")->select();
            //统计开始
            $yizhouqian=mktime(0, 0, 0, date('m'), date('d')-1, date('Y'));//获取一周前的时间戳
            $swt =(date('Y-m-d', strtotime('-15days')));
            $swtstr = strtotime($swt);
            $s =(date('Y-m-d', strtotime('-30days')));
            $mou = strtotime($s);
            $map1['time'] = array(array('EGT', $yizhouqian), array('ELT', time()));
            $map2['time'] = array(array('EGT', $swtstr), array('ELT', time()));
            $map3['time'] = array(array('EGT', $mou), array('ELT', time()));
            $mouth  = $member->where($map3)->where("nickname like '%{$nickname}%'")->where($date)->sum('points');
            $swtian= $member->where($map2)->where("nickname like '%{$nickname}%'")->where($date)->sum('points');
            $qitian = $member->where($map1)->where("nickname like '%{$nickname}%'")->where($date)->sum('points');
        }elseif(!empty($userid)){
            $count = $member->where(array('uid'=>$userid))->where($date)->count();
            $page = new \Think\Page($count,20);
            $show = $page->show();
            $list = $member->where(array('uid'=>$userid))->where($date)->limit($page->firstRow.','.$page->listRows)->order("time DESC")->select();
            //统计开始
            $yizhouqian=mktime(0, 0, 0, date('m'), date('d')-1, date('Y'));//获取一周前的时间戳
            $swt =(date('Y-m-d', strtotime('-15days')));
            $swtstr = strtotime($swt);
            $s =(date('Y-m-d', strtotime('-30days')));
            $mou = strtotime($s);
            $map1['time'] = array(array('EGT', $yizhouqian), array('ELT', time()));
            $map2['time'] = array(array('EGT', $swtstr), array('ELT', time()));
            $map3['time'] = array(array('EGT', $mou), array('ELT', time()));
            $mouth  = $member->where($map3)->where(array('uid'=>$userid))->where($date)->sum('points');
            $swtian= $member->where($map2)->where(array('uid'=>$userid))->where($date)->sum('points');
            $qitian = $member->where($map1)->where(array('uid'=>$userid))->where($date)->sum('points');
        } else{
            $count = $member->where($date)->count();
            $page = new \Think\Page($count,20);
            $show = $page->show();
            $list = $member->where($date)->limit($page->firstRow.','.$page->listRows)->order("time DESC")->select();
            $yizhouqian=mktime(0, 0, 0, date('m'), date('d')-1, date('Y'));//获取一周前的时间戳
            $swt =(date('Y-m-d', strtotime('-15days')));
            $swtstr = strtotime($swt);
            $s =(date('Y-m-d', strtotime('-30days')));
            $mou = strtotime($s);
            $map1['time'] = array(array('EGT', $yizhouqian), array('ELT', time()));
            $map2['time'] = array(array('EGT', $swtstr), array('ELT', time()));
            $map3['time'] = array(array('EGT', $mou), array('ELT', time()));
            $mouth  = $member->where($map3)->where($date)->sum('points');
            $swtian= $member->where($map2)->where($date)->sum('points');
            $qitian = $member->where($map1)->where($date)->sum('points');
        }
        $this->assign('mouth',$mouth);
        $this->assign('swt',$swtian);
        $this->assign('qitian',$qitian);
        $this->assign('show',$show);
        $this->assign('list',$list);
        $this->display();
    }

    public function shenhe(){
        $member = M('money');
        if(I('status')==1){
            $msm['typepay']='alipay';
        }elseif (I('status')==1){
            $msm['typepay']='weixin';
        }elseif (I('status')==1){
            $msm['typepay']='yignhangka';
        }
        $msm['status']=0;
        $msm['type2']=1;
        $count = $member->where($msm)->count();
        $page = new \Think\Page($count,20);
        $show = $page->show();
        $list = $member->limit($page->firstRow.','.$page->listRows)->order("id DESC")->where($msm)->select();
        $points =0;
        foreach ($list as $value){
            $points+=$value['points'];
        }
        $this->assign('points',$points);
        $this->assign('list',$list);
        $this->assign('show',$show);
        $this->display();
    }
    public function shenhex(){
        $member = M('money');
        $count = $member->where(array('status'=>0,'type2'=>0))->count();
        $page = new \Think\Page($count,20);
        $show = $page->show();
        $list = $member->limit($page->firstRow.','.$page->listRows)->order("id DESC")->where(array('status'=>0,'type2'=>0))->select();
        $points =0;
        foreach ($list as $value){
            $points+=$value['points'];
        }
        $this->assign('points',$points);
        $this->assign('list',$list);
        $this->assign('show',$show);
        $this->display();
    }
    public function yonjin(){
        $member = M('money');
        $count = $member->where(array('status'=>0,'type2'=>4))->count();
        $page = new \Think\Page($count,20);
        $show = $page->show();
        $list = $member->limit($page->firstRow.','.$page->listRows)->order("id DESC")->where(array('status'=>0,'type2'=>4))->select();
        $points =0;
        foreach ($list as $value){
            $points+=$value['points'];
        }
        $this->assign('points',$points);
        $this->assign('list',$list);
        $this->assign('show',$show);
        $this->display();
    }
    public function showdldo(){
        $ip =$_POST['ip'];
        $data =GetIpLookup($ip);
        $res =$data['country']."-".$data['province'].'-'.$data['city'].'-'.$data['district'];
        die(json_encode($res));
    }

	public function gagdo(){
        if (I('status')==1){
            $id = I('id');
            $res = M('user')->where("id = $id")->setField('isgag',1);
            $date=array(
                'ip'=>get_client_ip(),
                'content'=>"禁言账号".$id,
                'create_time'=>time(),
                'uid'=>session('admin')['id']
            );
            M('admin_log')->add($date);
            if($res){
                $this->success('禁言成功！');
            }else{
                $this->error('禁言失败！');
            }
        }else{
            $id = I('id');
            $res = M('user')->where("id = $id")->setField('isgag',0);
            if($res){
                $this->success('取消禁言成功！');
            }else{
                $this->error('取消禁言失败！');
            }
        }
    }

    public function kefudo(){
        if (I('status')==1){
            $id = I('id');
            $res = M('user')->where("id = $id")->setField('iskefu',1);
            $date=array(
                'ip'=>get_client_ip(),
                'content'=>"设置客服账号".$id,
                'create_time'=>time(),
                'uid'=>session('admin')['id']
            );
            M('admin_log')->add($date);
            if($res){
                $this->success('设置成功！');
            }else{
                $this->error('设置失败！');
            }
        }else{
            $id = I('id');
            $res = M('user')->where("id = $id")->setField('iskefu',0);
            $date=array(
                'ip'=>get_client_ip(),
                'content'=>"取消客服账号".$id,
                'create_time'=>time(),
                'uid'=>session('admin')['id']
            );
            M('admin_log')->add($date);
            if($res){
                $this->success('取消客服成功！');
            }else{
                $this->error('取消客服失败！');
            }
        }
    }

    public function message(){

        $count = M('liuyan')->where(array('is_kefu'=>0))->count();
        $page = new \Think\Page($count,20);
        $show = $page->show();
        $list = M()->query("SELECT a.*,b.headimgurl,b.nickname FROM think_liuyan a LEFT JOIN think_user b on a.uid = b.id WHERE a.is_kefu =0 ORDER BY a.id desc limit $page->firstRow,$page->listRows");
        $this->assign('list',$list);
        $this->assign('show',$show);
        $this->display();
    }
    public function huifudo(){
        $id=I('uid');
        $text=I('text');
        $user=M('liuyan')->where(array('id'=>$id))->find();
        M('liuyan')->where(array('id'=>$id))->setField('see',1);
        $data=array(
            'is_kefu'=>1,
            'time'=>time(),
            'content'=>$text,
            'see'=>0,
            'uid'=>$user['uid']
        );
        if (!M('liuyan')->add($data)){
            $this->error('失败！');
        }else{
            $this->success('成功！');
        }

    }

    public function liushui(){
        $id=I('uid');
        $user=M('user')->where(array('id'=>$id))->find();
        $xia=M('money')->where(array('userid'=>$id,'type2'=>0,'status'=>1))->count('points');
        $shang=M('money')->where(array('userid'=>$id,'type2'=>1,'status'=>1))->count('points');
        $xiazhu=M('order')->where(array('userid'=>$id,'state'=>1))->count("del_points");
        $money=$shang-$xia;
        $baifen=$xiazhu/$money*100;
        die($baifen."%");
    }
    public function liushuibili(){
        $id=I('uid');
        $user=M('user')->where(array('id'=>$id))->find();
        if ($user){
            M('user')->where(array('id'=>$id))->setField('liushui',I('num'));
            $date['msg']="成功";
            die(json_encode($date));
        }else{
            $date['msg']="账号不存在";
            die(json_encode($date));
        }
    }
    public function beizhu(){
        if(IS_AJAX){
            $userid = $_POST['userid'];
            $content = $_POST['content'];
            $res = M('user')->where(array('id'=>$userid))->setField('msg',$content);
            if($res){
                $this->success('成功','/admin/member/index');
            }else{
                $this->error('失败','/admin/member/index');
            }
        }else{
            $id=$_GET['id'];
            $data['id']  =$id;
            $this->assign('userinfo',$data);
            $this->display();
        }
    }
    public function liuyan(){
        if(IS_AJAX){
            $userid = $_POST['userid'];
            $content = $_POST['content'];
            $date=array(
                'userid'=>$userid,
                'content'=>$content,
                'create_time'=>time()
            );
            $res = M('msgs')->add($date);
            if($res){
                $this->success('成功','/admin/member/index');
            }else{
                $this->error('失败','/admin/member/index');
            }
        }else{
            $id=$_GET['id'];
            $data['id']  =$id;
            $this->assign('userinfo',$data);
            $this->display();
        }
    }
    public function team_list(){
        $id =$_GET['id'];
        $user = M('user');
        $count = $user->where(array('t_id'=>$id))->count();
        $page = new \Think\Page($count,20);
        $show = $page->show();
        $list = $user->limit($page->firstRow.','.$page->listRows)->order("id DESC")->where(array('t_id'=>$id))->select();
        $this->assign('list',$list);
        $this->assign('show',$show);
        $this->display();
    }
    public function team_yongjin(){
        $id =$_GET['id'];
        $user = M('commisssion');
        $count = $user->where(array('uid'=>$id))->count();
        $page = new \Think\Page($count,20);
        $show = $page->show();
        $list = M('commisssion')->where(array('uid'=>$id))->join('think_user on think_commisssion.id_add = think_user.id ')->order("time DESC")->limit($page->firstRow.','.$page->listRows)->select();
        $this->assign('list',$list);
        $this->assign('show',$show);
        $this->display();
    }
    public function shangfen_zu(){
        $status=I('status');
        $id=I('id');
        if ($status==1){
            if (M('user')->where(array('id'=>$id))->setField('sf_zu',1)){
                $this->success("成功");
            }else{
                $this->error("失败");
            }
        }else{
            if (M('user')->where(array('id'=>$id))->setField('sf_zu',0)){
                $this->success("成功");
            }else{
                $this->error("失败");
            }
        }
    }
    public function liushui_zu(){
        $status=I('status');
        $id=I('id');
        if ($status==1){
            if (M('user')->where(array('id'=>$id))->setField('ls_zu',1)){
                $this->success("成功");
            }else{
                $this->error("失败");
            }
        }else{
            if (M('user')->where(array('id'=>$id))->setField('ls_zu',0)){
                $this->success("成功");
            }else{
                $this->error("失败");
            }
        }
    }
    public function tuijian_zu(){
        $status=I('status');
        $id=I('id');
        if ($status==1){
            if (M('user')->where(array('id'=>$id))->setField('tj_zu',1)){
                $this->success("成功");
            }else{
                $this->error("失败");
            }
        }else{
            if (M('user')->where(array('id'=>$id))->setField('tj_zu',0)){
                $this->success("成功");
            }else{
                $this->error("失败");
            }
        }
    }
    public function liuyanlist(){
        $user = M('msgs');
        $count = $user->count();
        $page = new \Think\Page($count,20);
        $show = $page->show();
        $list = M('msgs')->join('think_user on think_msgs.userid = think_user.id ')->order("think_msgs.id DESC")->limit($page->firstRow.','.$page->listRows)->select();
        $this->assign('list',$list);
        $this->assign('show',$show);
        $this->display();
    }
    public function setmessage(){
        if (IS_AJAX){
            $user=M('user')->where(array('id'=>$_POST['id']))->find();
            $password=I('password');
            if (!empty($password)){
                $_POST['password']=md5($password);
            }else{
                $_POST['password']=$user['password'];
            }
            $points = I('t_id');
            $info = M('user')->where(array('id'=>$points))->find();
            if (!$info && $points !=0) {
                $this->error('推荐人不存在');
            }
            if(M('user')->where(array('id'=>$_POST['id']))->save($_POST)){
                $this->success("成功",U('admin/member/index'));
            }else{
                $this->error("失败");
            }
        }else{
            $user=M('user')->where(array('id'=>I('id')))->find();
            $this->assign('userinfo',$user);
            $this->display();
        }
    }
    public function qiandao(){
        $uid=I('uid');
        $count = M('sgin')->where(array('uid'=>$uid))->count();
        $page = new \Think\Page($count,20);
        $show = $page->show();
        $list = M('sgin')->where(array('uid'=>$uid))->limit($page->firstRow.','.$page->listRows)->order("id DESC")->select();
        $this->assign('list',$list);
        $this->assign('show',$show);
        $this->display();
    }
}

?>