<?php
namespace Home\Controller;
use Think\Controller;
class JubaoController extends Controller {

	public function add(){
		// var_dump($_POST);
		// die;
	$u=$_COOKIE['HomeUser_User_Uid'];

 	if(empty($u)){
     $u=$_SESSION['Home']['Uid'];
	}

	$_POST['RF_Ren']=$u;
	$m=M('PostRf');
	$m->create();
	$res=$m->add();
	// var_dump($res);
	if($res){
		$this->ajaxReturn(1);
	}

	}



}