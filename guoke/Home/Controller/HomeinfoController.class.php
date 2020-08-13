<?php
namespace Home\Controller;
use Think\Controller;
class HomeinfoController extends PublicController{

	public function index(){

	$u=$_COOKIE['HomeUser_User_Uid'];

    if(empty($u)){
       $u=$_SESSION['Home']['Uid'];
    }

    if(empty($_GET['u'])){
        $where='g.Post_Display=0 and Post_Uid='.$u;
        $u=$_SESSION['Home']['Uid'];
    }else{
        $where='g.Post_Display=0 and Post_Uid='.$_GET['u'];
        $u=$_GET['u'];
    }


    $rp=M('postInfo');
    //瀑布流开始
    $m=M();
    $count=$m->query("select count(*) from post_info g where $where");
    // echo $m->_sql();
    // var_dump($count);
    // die;
    $count=$count[0]['count(*)'];
    // die;
    $Page=new \Think\Page($count,5);
    $PageShow=$Page->show();


    //瀑布流结束
    $tiezi=$rp->alias('g')->field('g.*,i.User_Nickname,p.Group_Name,i.User_Roleid')->join('user_info as i on g.Post_Uid=i.U_PID')->join('post_gp as p on p.G_id=g.Post_Tid')->where($where)->order('Post_Hot desc')->limit($Page->firstRow.','.$Page->listRows)->select();
    // echo $rp->_sql();
    // var_dump($tiezi);
    // die;
    $Dao=M();
     $touxiang=$Dao->query("select User_pic from User_profile where `U_PID`='".$u."' and State=1");
    foreach ($tiezi as $key => $value) {
    	$tiezi[$key]['Post_Content']=strip_tags(html_entity_decode($value['Post_Content']));
    	$tiezi[$key]['Post_CTime']=date('Y:m:d H:i:s',$value['Post_CTime']);
    }
    // var_dump($tiezi);
    // die;	
    // 瀑布流
    	if(IS_AJAX){
    		if($tiezi){
    		$this->ajaxReturn($tiezi);
    		}else{
    		$this->ajaxReturn(1);
    		}
    	}else{

    	$count=count($tiezi);
        $this->assign('count',$countnt);
    	$this->assign('touxiang',$touxiang[0]['User_pic']);
    	$this->assign('tiezi',$tiezi);
		$this->display();
    	}
	}


    public function userinfo(){
        $u=$_COOKIE['HomeUser_User_Uid'];

        if(empty($u)){
           $u=$_SESSION['Home']['Uid'];
        }
        // $_POST['U_PID']=$u;

            $m=M('UserInfo');
        if(IS_AJAX){
            // var_dump($_POST);
            $m->create();
            $res=$m->where("U_PID=$u")->save();
            // echo $m->_sql();
            // var_dump($res);
            if($res){
                $this->ajaxReturn(1);
            }
        }else{
        $res=$m->where("U_PID=$u")->find();
         // $m->_sql();
        // var_dump($res);
        // die;
        $this->assign('res',$res);
        $this->display();
        }
    }


    public function Touxiang(){

            $u=$_COOKIE['HomeUser_User_Uid'];

            if(empty($u)){
               $u=$_SESSION['Home']['Uid'];
            }

            $m=M('userProfile');
        if(IS_AJAX){
            $_POST['U_PID']=$u;
            $where="`U_PID`=$u";
            $arr=array('State'=>0);
            $edit=$m->where($where)->save($arr);
            // var_dump($edit); 
            $m->create();
            $xin=$m->add();
            // var_dump($xin);
            if($xin){
                $this->ajaxReturn(1);
            }
        }else{

            $where="State=0 and U_PID=$u";
            $res=$m->where($where)->select();

        $this->assign('res',$res);
        $this->display();
        }
    }


    public function lishi(){
        $u=$_COOKIE['HomeUser_User_Uid'];

        if(empty($u)){
           $u=$_SESSION['Home']['Uid'];
        }
        
        $m=M('userProfile');
        $_POST['U_PID']=$u;
        $where="`U_PID`=$u";
        $arr=array('State'=>0);
        $edit=$m->where($where)->save($arr);
        $w="`P_ID`=".$_POST['P_ID'];
        
        $arr=array('State'=>1);
        $res=$m->where($w)->save($arr);
        // var_dump($res);
        if($res){
            $this->ajaxReturn(1);
        }

    }


    public function anquan(){
        if(IS_AJAX){
            // var_dump($_POST);
            $u=$_COOKIE['HomeUser_User_Uid'];

            if(empty($u)){
               $u=$_SESSION['Home']['Uid'];
            }

            $m=M('userVerify');
            $where="`U_id`='".$u."'";
            $res=$m->field('User_Pwd')->where($where)->find();
            // var_dump($res);
            if($res['User_Pwd']==md5($_POST['pwd'])){
                // $this->ajaxReturn(1);
                $arr=array('User_Pwd'=>md5($_POST['User_Pwd']));
                $new=$m->where($where)->save($arr);
                if($new){
                    $r=cookie(null,'HomeUser_');
                    $s=session(null); 
                    $this->ajaxReturn('1');
                }
            }else{
                $this->ajaxReturn(2);   
            }
        }else{
        $this->display();
        }
    }

    public function gzhu(){

    }

    public function Group(){
        $gp=M('postGp');

        $u=$_COOKIE['HomeUser_User_Uid'];

        if(empty($u)){
           $u=$_SESSION['Home']['Uid'];
        }

        if(empty($u)){

        $where="`Group_state`=0";
        }else{
            
            $where = "`Group_state`=0 And `Group_Uid`=$u";
        }

        $res=$gp->alias('g')->field('g.*,u.User_Nickname')->join('User_Info as u on u.U_PID=g.Group_Uid')->where($where)->find();
        // echo $gp->_sql();
        // var_dump($res);
        // die; 


        $rp=M();
        $ret=$rp->query("select * from post_info where Post_Tid=".$res['G_id']);
        
        // var_dump($ret);
        // die;
        $count=count($ret);

        $this->assign('count',$count);
        $this->assign('tiezi',$res);
        $this->assign('info',$ret);
        $this->display();
    }

    public function tiezidel(){
        // var_dump($_GET);
        $infoModel=M('postInfo');
        $where="`Post_Id`=".$_GET['id'];
        $res=$infoModel->where($where)->delete();
        // var_dump($res);
        $where="`Rp_Postid`=".$_GET['id'];
        $rp=M('PostReply');
        $r=$rp->where($where)->delete();
        // var_dump($r);
        if($r||$res){
            $this->success('删除成功', U('Home/Homeinfo/Group'),3);
        }

    }

    public function shezhi(){

        if(IS_POST){

        $m=M();
        $m->startTrans();
        Uploads('Group_Src');
        // var_dump($_POST);
        // die;
        $G=M('postGp');
        $G->create();
        $res=$G->where("`G_id`=".$_POST['id'])->save();
        // echo $G->_sql();
        // die;
        if($res){
                $m->commit();
                $this->success('修改成功', U('Home/Homeinfo/shezhi'),3);
            }else{
                $pic=$_POST['Group_Src'];
                $pic = './Public'.$pic;
                $r=unlink($pic);
                $this->error('修改失败!');
                $m->rollback();
        }




        }else{

        $gp=M('postGp');

        $u=$_COOKIE['HomeUser_User_Uid'];

        if(empty($u)){
           $u=$_SESSION['Home']['Uid'];
        }

        if(empty($u)){

        $where="`Group_state`=0";
        }else{
            
            $where = "`Group_state`=0 And `Group_Uid`=$u";
        }

        $res=$gp->alias('g')->field('g.*,u.User_Nickname')->join('User_Info as u on u.U_PID=g.Group_Uid')->where($where)->find();

        
        $count=count($ret);
        // var_dump($res);
        // die;
        $this->assign('count',$count);
        $this->assign('tiezi',$res);
        $this->display();
        }
    }




}