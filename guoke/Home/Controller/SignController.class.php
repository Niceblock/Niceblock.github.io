<?php
namespace Home\Controller;
use Think\Controller;
//用户登录控制器.
class SignController extends Controller {
	//用户登录首页
    public function index(){
        
    	$this->display();
    }

    public function signdo(){
        $m=M('UserVerify');
        $where="`User_Email`='".$_GET['email']."'";
        $res=$m->where($where)->find();
        // var_dump($res);
        if(!$res){
            $this->ajaxReturn(1);
        }
    }

    public function Email(){
        $emailsubject = "用户帐号激活";//邮件标题 
        $email = trim($_POST['email']); //邮箱 
        $regtime = time();//注册时间;
        $token = md5($regtime.$email); //创建用于激活识别码 
        $token_exptime = time()+60*60*24;//过期时间为24小时后 
            $user=M('userVerify');
        if(!IS_AJAX){
            $arr=array('User_Email'=>$email,'token'=>$token,'token_time'=>$token_exptime,'regtime'=>$regtime);
        // var_dump($arr);
        $user->create();
        $res=$user->add($arr);
        // var_dump($res);
        }else{
            $regtime = time();//注册时间;
            $token = md5($regtime.$email);
            $arr=array('token'=>$token);
            $where="`User_Email`='".$email."'";
            $res=$user->where($where)->save($arr);
            // echo $user->_sql();
            // var_dump($res);
        }

        $lj = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].U('Home/Sign/Emaildo',array('verify'=>$token));
        $emailbody = "亲爱的".$email."：<br/>感谢您在我站注册了新帐号。<br/>请点击链接激活您的帐号。<br/> 
            <a href=".$lj." target= 
        '_blank'>".$lj."</a><br/> 
            如果以上链接无法点击，请将它复制到你的浏览器地址栏中进入访问，该链接24小时内有效。";  

        $r=sendMail($email,$emailsubject,$emailbody);
        $this->assign('email',$email);
        $this->display();
    }

    public function Emaildo(){
        $verify = stripslashes(trim($_GET['verify'])); 
        $nowtime = time(); 

        $user=M('userVerify');
        $where= "status='0' and `token`='$verify'";
        $res=$user->where($where)->find();
        // echo $user->_sql();
        // var_dump($res);
        $e=$user->field('User_Email')->where("`token`='".$verify."'")->find();
        // echo $user->_sql();
        $email=$e['User_Email'];
        // var_dump($e);
        if($res){
            if($nowtime>$res['token_time']){ 
                   $msg = '您的激活有效期已过，请登录您的帐号重新发送激活邮件.'; 
               }else{ 
                    $where="`U_id`='".$res['U_id']."'";
                    $arr=array('status'=>1);
                    $edit=$user->where($where)->save($arr);
                    // $user->_sql();
                   if(!$edit){
                   $msg = '您的激活发生错误，请给联系976513843@qq.com管理员发邮件'; 
                   $this->assign('msg',$msg);
                   $this->display('error');
                   }else{
                   $id=$res['U_id'];
                   $msg = '激活成功';
                   $t=M('userInfo');
                   $arr=array('U_PID'=>$id);
                   $t->add($arr);
                   $i=M('userProfile');
                   $i->add($arr);
                   $this->assign('po',$id);
                   $this->assign('email',$email);
                   $this->display(); 
                   }
            } 
        }

    }



      public function Verify(){
          $user=M('userInfo');
          $email=I('get.email');
          $where="`User_Nickname`='".$email."'";
          $res=$user->where($where)->find();
          if($res){
              $this->ajaxReturn(1);
          }else{
              $this->ajaxReturn(2);
          }
      }


      public function CreateAdd(){
        $m =M();
         //开启事物
        $m->startTrans();
        $id=$_POST['U_PID'];
        $user=M('userInfo');

        $where="`U_PID`='".$id."'";
        $arr=array('User_Nickname'=>$_POST['User_Nickname']);
        $name=$user->where($where)->save($arr);//插入的是用户信息的pid
        // echo $user->_sql();
        // var_dump($name);


        $_POST['User_Pwd']=md5($_POST['User_Pwd']);
        $u=M('userVerify');
        $arr=array('User_Pwd'=>$_POST['User_Pwd']);
        $where="`U_id`='".$id."'";
        $re=$u->where($where)->save($arr);

        if($name&&$re){
          $m->commit();
          cookie('User_Nickname',$_POST['User_Nickname'],array('expire'=>3600,'prefix'=>'HomeUser_'));
          cookie('User_Uid',$id,array('expire'=>10800,'prefix'=>'HomeUser_'));
          cookie('User_Pwd',$_POST['User_Pwd'],array('expire'=>3600,'prefix'=>'HomeUser_'));

          $this->ajaxReturn(1);
        }else{
          $m->rollback();
        }


      }


      public function xiougai(){
        if(IS_AJAX){

         $emailsubject = "用户修改密码";//邮件标题 
         $email = trim($_POST['email']); //邮箱 
         $regtime = time();//修改时间;
         $token = md5($regtime.$email); //创建用于激活识别码 
         $user=M('userVerify');
         $arr=array('token'=>$token);
         // var_dump($arr);
         $res=$user->where("`User_Email`='$email'")->save($arr);
         // die;
         $lj = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].U('Home/Sign/xioudo',array('verify'=>$token));
         $emailbody = "亲爱的".$email."：<br/>修改密码验证。<br/>请点击链接激活您的帐号。<br/> 
             <a href=".$lj." target= 
         '_blank'>".$lj."</a><br/> 
             如果以上链接无法点击，请将它复制到你的浏览器地址栏中进入访问，该链接24小时内有效。";  

         $r=sendMail($email,$emailsubject,$emailbody);
         // $this->assign('email',$email);
         $this->ajaxReturn(1);
        }else{
         $this->display();
        }
      }


      public function xioudo(){
        if(IS_POST){
          $pwd=md5($_POST['password']);

          $m=M('userVerify');
          $arr=array('User_Pwd'=>$pwd);
          $res=$m->where("`User_Email`='".$_POST['email']."'")->save($arr);
          // var_dump($res);

          if($res){
             $this->redirect('Home/Login/index'); 
          }

        }else{

        $verify = stripslashes(trim($_GET['verify'])); 

        $user=M('userVerify');
        $where= "status='1' and `token`='$verify'";
        $res=$user->where($where)->find();
        // echo $user->_sql();
        // var_dump($res);
        if($res){
            $email=$res['User_Email'];
            $this->assign('email',$email);
            $this->display();
        }
        }
      }



}