<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller {

	public function CreateVcode(){
		$config =array(
		'length'=>2,
		'fontSize'=>30,
		'useNoise'=>false,
		);
		$Verify = new \Think\Verify($config);
		$Verify->entry();
	}



}