<?php
namespace Home\Controller;
use Think\Controller;
class ReplyController extends PublicController {


	public function index(){
		$this->assign('G_id',$_GET['id']);
		$this->display();
	}

	public function add(){
		// var_dump($_POST);
		// die;
	   $u=$_COOKIE['HomeUser_User_Uid'];

	   if(empty($u)){
	       $u=$_SESSION['Home']['Uid'];
	   }

	   	if(empty($u)){
			redirect('/Home/Login/index');
			die;
	   	}
			
		$_POST['Post_Uid']=$u;
		$_POST['Post_CTime']=time();
		$_POST['Post_Updatetime']=time();
		$_POST['Post_Good']=0;
		$_POST['Post_Display']=0;
		$_POST['Post_Hot']=0;
		
		$m=M('PostInfo');	
		$m->create();
		$res=$m->add();

		$k=M();
		$r=$k->query("update post_Gp set `Group_Hot`=`Group_Hot`+1 where `G_id`=".$_POST['Post_Tid']);
		// echo $k->_sql();
		// die;
		// var_dump($r);
		// die;
		$Gid=$_POST['Post_Tid'];
		if($res){
			redirect('/Home/Reply/infolist/Post_Tid/'.$Gid.'/Post_id/'.$res);
		}	
	}

	public function infolist(){
			date_default_timezone_set('PRC'); 
			// var_dump($_GET);
			// die;
		    $m=M('PostGp');
		    $res=$m->alias('g')->where("`G_id`=".$_GET['Post_Tid'])->find();
		    // echo $m->_sql();
		    $Dao=M();
		    $ren=$Dao->query('select Au_Gid,count(*) from post_audit where Au_Gid='.$_GET['Post_Tid'].' group by Au_Gid');
		    // var_dump($res);
		    // die;
		    $u=$_COOKIE['HomeUser_User_Uid'];

		   	if(empty($u)){
		       $u=$_SESSION['Home']['Uid'];
		  	 }



		    $x=M('postAudit');
		    $where="`Au_Gid`='".$_GET['Post_Tid']."'and `Au_Uid`='$u'";
		    $on=$x->where($where)->find();


		    if(!empty($on)){
		        $this->assign('onn',1);
		    }else{
		        $this->assign('onn',2);
		    }

		    $info=M('postInfo');
		    $where="`Post_Id`='".$_GET['Post_id']."' and t.`State`=1";

		    $content=$info->field('t.User_pic,u.User_Nickname,i.*')->alias('i')->join('user_info as u on u.U_PID=i.Post_Uid')->join('user_profile as t on t.U_PID=i.Post_Uid')->where($where)->find();
		    // var_dump($content);
		    // die;
		    $cha=time()-$content['Post_CTime'];
		    // echo $time;
		    // echo date('Y-m-d H:i:s',$content['Post_CTime']);
		    // die;
		    $minute=floor($cha/60); 
		    $hour=floor($cha/60/60); 
		    $day=floor($cha/60/60/24); 
		    if($minute<60){ 
		    	$content['ji']="已发布 $minute 分钟"; 
		    } 
		    elseif($minute<24*60) { 
		    	$content['ji']="已发布 $hour 小时"; 
		    }else{
		    	 $content['ji']=date('Y-m-d H:i:s',$content['Post_CTime']); 
		    }
		
		    $u=$_COOKIE['HomeUser_User_Uid'];

		    if(empty($u)){
		        $u=$_SESSION['Home']['Uid'];
		    }


		     $x=M('postAudit');
		     $where="`Au_Gid`='".$_GET['Post_Tid']."'and `Au_Uid`='$u'";
		     $on=$x->where($where)->find();
		     // echo $x->_sql();

		     // var_dump($on);
		     // die;

		     switch ($on['Au_State']) {
		       case 3:
		         $this->assign('onn',3);
		         break;
		       case 1:
		         $this->assign('onn',1);
		         break;
		       default:
		          $this->assign('onn',2);
		         break;
		     }
// 开始评论查询
		    $PR=M('PostReply');
		    $where="`Rp_Tid`='".$_GET['Post_Tid']."' and `Rp_Postid`='".$_GET['Post_id']."' and t.`State`=1";
		    $bn=$PR->field('t.User_pic,u.User_Nickname,u.U_PID,i.*')->alias('i')->join('user_info as u on u.U_PID=i.Rp_Id')->join('user_profile as t on t.U_PID=i.Rp_Id')->where($where)->select();
		   	// var_dump($bn);
		   	// die;
		   	$bao=M();
		    $content['Post_Content']= html_entity_decode($content['Post_Content']);
		    foreach ($bn as $key => $val){
		    	$zan=$bao->query("select * from post_ZanRp where Rpid=".$val['id']." and `Uid`='$u' and `Tid`=".$val['Rp_Tid']);
		    	// echo $bao->_sql();
		    	if($zan){
		    		$bn[$key]['zan']='yes';
		    	}
		    	$bn[$key]['Rp_Content']=html_entity_decode($val['Rp_Content']);
			    $fcha=time()-$val['Rp_Ctime'];
			    $minute=floor($fcha/60); 
			    $hour=floor($fcha/60/60); 
			    $day=floor($fcha/60/60/24);

			    if($minute<60){ 
			    	$bn[$key]['ji']="评论了 $minute 分钟"; 
			    } 
			    elseif($minute<24*60){ 
			    	$bn[$key]['ji']="评论已过 $hour 小时"; 
			    }else{
			    	 $bn[$key]['ji']=date('Y-m-d H:i:s',$bn[$key]['Rp_Ctime']); 
			    }

		    }
		    // 'Post_Tid' => string '12' (length=2)
		    // 'Post_id' => string '127' 

		    $o=M();
		    $p=$o->query("select count(*) from `Post_Zan` where `Post_id`=".$_GET['Post_id']." and `Post_Tid`=".$_GET['Post_Tid']);

		    // var_dump($bn);	
		    // die;
		    $this->assign('p',$p[0]['count(*)']);
		    $this->assign('rp',$bn);
		    $this->assign('content',$content);
		    $this->assign('count',$ren[0]['count(*)']);
		    $this->assign('res',$res);
		    $this->display();

		}


	public function Replyadd(){

		$Reply=M('PostReply');
		$u=$_COOKIE['HomeUser_User_Uid'];

		if(empty($u)){
		    $u=$_SESSION['Home']['Uid'];
		}

	   	if(empty($u)){
			redirect('/Home/Login/index');
			die;
	   	}

	   	$_POST['Rp_Id']=$u;
	   	$_POST['Rp_Ctime']=time();
		
		$Reply->create();
		$res=$Reply->add();
		// echo $Reply->_sql();
		// var_dump($_POST);
		// die;
		$k=M();
		$r=$k->query("update post_info set `Post_Good`=`Post_Good`+1 where `Post_Id`=".$_POST['Rp_Postid']);
		$v=$k->query("update post_Gp set `Group_Hot`=`Group_Hot`+1 where `G_id`=".$_POST['Rp_Tid']);
		// echo $k->_sql();
		// die;
		if($res){
			$m=M('PostInfo');
			$arr=array('Post_Updatetime'=>time());
			$where="`Post_Id`='".$_POST['Rp_Postid']."'";
			$bb=$m->where($where)->save($arr);
			$this->ajaxReturn(1);
		}


	}

	public function zan(){
		$u=$_COOKIE['HomeUser_User_Uid'];

		if(empty($u)){
		    $u=$_SESSION['Home']['Uid'];
		}
		// var_dump($_POST);
		$m=M('postZanrp');
		if($_POST['zan']=="jia"){
				
			$_POST['Uid']=$u;
			$m->create();
			$res=$m->add();
			if($res){
				$this->ajaxReturn(1);
			}
		}else{
			 $where="`Tid`='".$_POST['Tid']."' and `Rpid`='".$_POST['Rpid']."' and `Uid`=$u";
       		 $res=$m->where($where)->delete();
       		 // echo $m->_sql();
       		 if($res){
            $this->ajaxReturn(2);
        }
		}
	}

}