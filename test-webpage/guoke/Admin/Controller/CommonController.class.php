<?php
namespace Admin\Controller;
use Think\Controller;
class CommonController extends Controller {

    //功能类似构造方法,率先执行的方法
    public function _initialize(){


        $id = session('uid');
		//检测
		if(empty($id)){
			//没有登录
			$this->error('您还没有登录',U('Admin/Login/index'),3);
		}

		//验证是否具有权限
		// $AUTH = new \Think\Auth();
		// //类库位置应该位于ThinkPHP\Library\Think\
		// if(!$AUTH->check(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME, session('uid'))){
		//            $this->error('没有权限',U('Admin/Index/index'));
		// }
	}
}
