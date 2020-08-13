<?php
namespace Home\Controller;
use Think\Controller;
//用户登录控制器.
class LoginController extends Controller {
	//用户登录首页
    public function index(){
        // var_dump($_COOKIES);
        // var_dump($_SESSION);
    	$this->display();
    }

    public function Emaildo(){
        $m=M('UserVerify');
        $where="`User_Email`='".$_GET['email']."'";
        $res=$m->where($where)->find();
        // var_dump($res);
        if(!$res){
            $this->ajaxReturn(1);
        }
    }

    public function yzm(){
        $config = array(
        'reset' => false // 验证成功后是否重置，这里才是有效的。
        );
        $verify = new \Think\Verify($config);
            if($verify->check(I('post.code'))){
                   $this->ajaxReturn(1);
            }
    }

    public function logindo(){
        // var_dump($_POST);
        $_POST['pass']=md5($_POST['pass']);
        // die;
        $m=M('userVerify');
        $w="`User_Email`='".$_POST['email']."'AND `User_Pwd`='".$_POST['pass']."'";
        $res=$m->where($w)->find();
        // echo $m->_sql();
        // var_dump($res);
        $J=M('userInfo');
        $where="`U_PID`='".$res['U_id']."'";
        $r=$J->field('User_Nickname')->where($where)->find();
        //如果成功之后吧用户的UID返回给ajax
        if($res!=null){
            session_start();
            $_SESSION['Home']['Uid']=$res['U_id'];
            $_SESSION['Home']['Email']=$res['User_Email'];
            $_SESSION['Home']['Nickname']=$r['User_Nickname'];
        if($_POST['checked']=='true'){

            // echo $J->_sql();
            cookie('User_Nickname',$r['User_Nickname'],array('expire'=>10800,'prefix'=>'HomeUser_'));
            cookie('User_Pwd',$res['User_Pwd'],array('expire'=>10800,'prefix'=>'HomeUser_'));
            cookie('User_Uid',$res['U_id'],array('expire'=>10800,'prefix'=>'HomeUser_'));
            
        }
            $this->ajaxReturn($res['U_id']);
        }
    }







}