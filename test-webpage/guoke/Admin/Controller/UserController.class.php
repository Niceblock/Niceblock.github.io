<?php
namespace Admin\Controller;
use Think\Controller;
//后台的用户管理控制器.
class UserController extends CommonController {
	//用户列表显示板块


    public function index(){
        
        $p=I('get.page');
        if(empty($p)){
            $p='5';
        }

        if(!empty($_GET['keyword'])){
            //有关键字
            $where = "User_Email like '%".$_GET['keyword']."%' and User_profile.State=1";
        }else{
            $where = 'User_profile.State=1';
        }


        $user=M('userVerify');
        $count = $user->where($where)->count();
        $Page=new \Think\Page($count,$p);
        $PageShow=$Page->show();


        $res=$user->alias('a')->join('User_info as i ON a.U_id=i.U_PID')->join('User_profile ON a.U_id=User_profile.U_Pid')->limit($Page->firstRow.','.$Page->listRows)->where($where)->select();
        $this->assign('res',$res);
        $this->assign('pages',$PageShow);
    	$this->display();
    }



    public function Verify(){
        $user=M('userVerify');
        $email=I('get.email');
        $where="`User_Email`='".$email."'";
        $res=$user->where($where)->find();
        if($res){
            $this->ajaxReturn(1);
        }else{
            $this->ajaxReturn(2);
        }
    }



    //用户添加显示板块
    public function add(){
    	if(IS_POST){
            // var_dump($_POST);
            //连接数据库userVerify            
            $user=M('userVerify');
            //md5加密
            $_POST['User_Pwd']=md5($_POST['User_Pwd']);
            $user->create();

            $res=$user->add();

            $userinfo=M('UserInfo');

            $_POST['U_PID']=$res;
            $userinfo->create();
            $id=$userinfo->add();
            //头像关联
            $Tx=M('userProfile');
            Uploads('myfile');
            $_POST['User_pic']=$_POST['myfile'];
            unset($_POST['myfile']);
            $Tx->create();
            $Tx->add();
            //关联到用户的信息表INFO中  pid
            if($id){
                $this->success('用户'.$res.'添加成功!', U('Admin/User/index'),3);
            }else{
                $this->success('用户添加失败!', U('Admin/User/index'),3);
            }

    	}else{
    	//用户的添加页面显示
        	$this->display();

    	}
    }




    //用户的删除 使用了ajax
     public function delete(){
        // var_dump($_POST);
        $m =M();
         //开启事物
        $m->startTrans();
        //3个表的实例化
        $user=M('userVerify');
        $info=M('userInfo');
        $Tx=M('userProfile');

        //先删除其他2个表的数据
        $d1=$user->delete($_POST['id']);
        $where="`U_PID`='".$_POST['id']."'";
        $d2=$info->where($where)->delete();
        //把头像路径拿出来
        $p=$Tx->where($where)->find();
        $pic=$p['User_pic'];
        //删除头像的信息
        $d3=$Tx->where($where)->delete();
        //如果头像的信息删除成功后,删除头像
        if($d3&&$d2&&$d1){
             $m->commit();
             $pic = './Public'.$pic;
             $r=unlink($pic);
             $this->ajaxReturn($r);
             //成功之后返回ajax数据
         }else{
             $m->rollback();
         }

       

    }


    //使用AjAX修改信息
    
    public function edit(){
        $p=I('get.page');
        if(empty($p)){
            $p='5';
        }

        if(!empty($_GET['keyword'])){
            //有关键字
            $where = "User_Email like '%".$_GET['keyword']."%' and User_profile.State=1";
        }else{
            $where = 'User_profile.State=1';
        }


        $user=M('userVerify');
        $count = $user->where($where)->count();
        $Page=new \Think\Page($count,$p);
        $PageShow=$Page->show();



        $res=$user->alias('a')->join('User_info as i ON a.U_id=i.U_PID')->join('User_profile ON a.U_id=User_profile.U_Pid')->limit($Page->firstRow.','.$Page->listRows)->where($where)->select();
        $this->assign('res',$res);
        $this->assign('pages',$PageShow);
        $this->display();
    }

    public function update(){
      // var_dump($_POST);
       $_POST['User_Pwd']=md5($_POST['User_Pwd']);
       //修改emil 和 密码
       $u=M('UserVerify');
       $u->create();
       $ress=$u->save();
       // echo $u->_sql();
       // var_dump($ress);
       
       //修改用户的信息
       $i=M('UserInfo');
       $i->create();
       $where="`U_PID`='".$_POST['U_PID']."'";
       $res=$i->where($where)->save();
       // var_dump($res);
       if($res||$ress){
        $this->ajaxReturn(1);
       }
    }

}