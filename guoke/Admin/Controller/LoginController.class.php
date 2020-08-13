<?php
namespace Admin\Controller;
use Think\Controller;
//管理员管理控制器.
class LoginController extends Controller {
	//管理员显示板块
    public function index(){
    	$this->display();
    }

    //管理员用户处理
    public function action(){
		//管理员账号名    	
    	$Admin_name=I('post.Admin_name');
    	//管理员密码
    	$password=md5(I('post.password'));
    	//创建管理员对象连接
    	$admin=M('AdminUser');

    	$where="`Admin_name`='".$Admin_name."'and `Admin_pwd`='".$password."'";
    	//查询
    	$res=$admin->where($where)->find();
    	//路径跳转
    	if(!empty($res)){
    		session_start();
			$_SESSION['uid'] = $res['A_ID']; 
			$_SESSION['Admin_name']=$res['Admin_name'];
    		$_SESSION['power']=$res['Admin_power'];
    		$this->success('登陆成功!',U("Index/index"),3);
    	}else{
    		$this->error('登录失败!请检查用户名,密码!');
    	}

    }





}