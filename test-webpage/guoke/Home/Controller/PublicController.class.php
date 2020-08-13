<?php
namespace Home\Controller;
use Think\Controller;

class PublicController extends Controller{


	public function _initialize(){
     		$name = $_COOKIE['HomeUser_User_Nickname'];
			if(!empty($name)){
				$u=M('UserVerify');
				$where="i.`User_Nickname`='".$name."' and a.`User_Pwd`='".$_COOKIE['HomeUser_User_Pwd']."'";
        		$res=$u->alias('a')->join('User_info as i ON a.U_id=i.U_PID')->where($where)->select();
        		// var_dump($res);
        		// die;
        		if($res){
				$this->assign('msgg','true');
        		}
			}

			if(!empty($_SESSION['Home'])){
				$this->assign('msgg','true');
			}


			$u=$_COOKIE['HomeUser_User_Uid'];

			if(empty($u)){
	         $u=$_SESSION['Home']['Uid'];
	     	}
			$Dao=M();
			$touxiang=$Dao->query("select User_pic from User_profile where `U_PID`='".$u."' and State=1");
			$this->assign('tou',$touxiang[0]['User_pic']);
	}



	public function OutUser(){
		$r=cookie(null,'HomeUser_');
		$s=session(null); 
		if($r||$s){
			$this->ajaxReturn('1');
		}
	}


    public function OutUser(){
        $r=cookie(null,'HomeUser_');
        $s=session(null); 
        if($r||$s){
            $this->ajaxReturn('1');
        }
    }

    public function ajaxinformation(){
        $id=I('post.id');
        $j=I('post.j');
        if (empty($j)) {
            $where='';
        } else {
            $j=rtrim($j,',');
            $where=' and sc_comment.id not in ('.$j.')';
        }
        
        $user=M('user_verify');
        $ationres=$user->field('sc_comment.id,sc_comment.Wid,sc_comment.Cm_Content')->join('left join sc_comment on user_verify.U_id = sc_comment.Uid')->where('sc_comment.Pid='.$id.' and sc_comment.Is_Examine=0 and sc_comment.Delete=0'.$where)->select();
        foreach ($ationres as $k => $v) {
            $ationres[$k]['Cm_Content']=strip_tags(htmlspecialchars_decode($v['Cm_Content']));
        }
        echo json_encode($ationres);
    }

    public function ajaxReadInformation(){
        var_dump($_POST);
        $_POST['Is_Examine']=1;
        $comment=M('sc_comment');
        $comment->create();
        $comment->save();                
    }
}