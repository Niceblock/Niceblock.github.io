<?php
namespace Home\Controller;
use Think\Controller;
//用户站内信控制器.
class EmailController extends Controller {
	//用户站内信
    public function index(){

        // 获取当前登入用户id
        $uid=$_SESSION['Home']['Uid'];
        $state=empty($_GET['num'])?1:$_GET['num'];
        if($state==1){
            $w='';
            $wh='';
        }elseif($state==2){
            $w=' and state=1';
            $wh=' and e.state=1';
        }elseif($state==3){
            $w=' and state=0';
            $wh=' and e.state=0';
        }
        $email=M('email');
        // 查询满足条件总记录数
        $count=$email->where('pid="'.$uid.'" and is_display="1"'.$w)->count();
        //实例化分页类
        $page=new \Think\Page($count,5);
        // 获取limit参数
        $limit=$page->firstRow.','.$page->listRows;
        // 查询数据
        $res=$email->alias('e')->join('User_info as i ON i.U_PID=e.uid')->join('User_profile as p ON p.U_PID=e.uid')->where('e.pid="'.$uid.'" and e.is_display="1"'.$wh)->order('e.time desc')->limit($limit)->select();
        // var_dump($res);die;
        $pages=$page->show();

        $this->assign('res',$res);
        $this->assign('num',$state);
        $this->assign('pages',$pages);
        $this->display();
    }

    public function add(){
        // 接收pid值
        $pid=$_GET['pid'];
        $user=M('user_info');
        if(!empty($pid)){
            $res=$user->where('U_PID='.$pid)->select()[0];
            $this->assign('res',$res);
        }
        $this->display();
    }

    // 站内信息插入
    public function insert(){
        // var_dump($_POST);
        // 获取当前登入用户id
        $data['uid']=$_SESSION['Home']['Uid'];
        // 获取状态
        $state=$_POST['state'];
        // 获取内容
        $data['msg']=$_POST['msg'];
        // 设置当前时间
        $data['time']=date('Y-m-d H:i:s');
        $email=M('email');
        $name=$_POST['uname'];
        $info=M('user_info');
        if(!empty($name)){
            $id=$info->where('User_Nickname="'.$name.'"')->select()[0]['U_PID'];
        }
        // 判断状态
        if($state==0){
            $data['pid']='1';
            $email->add($data);
            header('location:'.U('Home/Email/been'));
        }else{
            $dat['uid']=$data['uid'];
            $dat['pid']=$id;
            $dat['time']=date('Y-m-d H:i:s');
            $dat['msg']=$data['msg'];
            $email->add($data);
            $this->success('发送成功');
        }
    }

    // 已发送信箱显示模块
    public function been(){
        // 获取当前登入用户id
        $uid=$_SESSION['Home']['Uid'];

        $email=M('email');
        // 查询满足条件总记录数
        $count=$email->where('uid="'.$uid.'" and is_display="1"')->count();
        //实例化分页类
        $page=new \Think\Page($count,5);
        // 获取limit参数
        $limit=$page->firstRow.','.$page->listRows;
        // 查询数据
        $res=$email->alias('e')->join('User_info as i ON i.U_PID=e.pid')->join('User_profile as p ON p.U_PID=e.uid')->where('e.uid="'.$uid.'" and e.is_display="1"')->order('e.time desc')->limit($limit)->select();
        // var_dump($res);die;
        $pages=$page->show();
        $this->assign('res',$res);
        $this->assign('pages',$pages);
        $this->display();
    }
    // 已发送信息
    public function waitdel(){
        // 接收post
        $data['id']=$_POST['id'];

        // 设置隐藏
        $data['is_display']="0";
        $email=M('email');

        $res=$email->save($data);
        if($res){
            echo 1;
        }else{
            echo 0;
        }

    }

    // 查看信息
    public function see(){
        // 接收id
        $data['id']=$_GET['id'];
        $data['state']='1';
        $email=M('email');
        $email->save($data);
        $res=$email->alias('e')->join('User_info as i ON i.U_PID=e.uid')->join('User_profile as p ON p.U_PID=e.uid')->where('e.id='.$data['id'])->select()[0];
        $this->assign('res',$res);
        $this->display();

    }
}