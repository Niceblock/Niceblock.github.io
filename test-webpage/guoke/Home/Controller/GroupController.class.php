<?php
namespace Home\Controller;
use Think\Controller;

class GroupController extends PublicController{

    public function index(){
 		$G=M('postGp');
        // $res=$user->alias('a')->join('User_info as i ON a.U_id=i.U_PID')->join('User_profile ON a.U_id=User_profile.U_Pid')->limit($Page->firstRow.','.$Page->listRows)->where($where)->select();
 		$where="`Group_state`='0'";
 		//组的状态
 		$res=$G->field('g.G_id,g.Group_Name,g.Group_Src')->alias('g')->where($where)->select();
 		//组员多少
 		$Dao = M();
 		$ren=$Dao->query('select Au_Gid,count(*) from post_audit group by Au_Gid ORDER BY count(*) desc');
 		// var_dump($res);

 		// die;
 		$u=$_COOKIE['HomeUser_User_Uid'];

        if(empty($u)){
            $u=$_SESSION['Home']['Uid'];
        }

		$b=$Dao->query('select Au_Gid,Au_Uid,Au_State from post_audit');

        
        foreach ($res as $key => $val) {
            // var_dump($val);
            foreach ($ren as $k => $v) {
                if($v['Au_Gid']==$val['G_id']){
                    $res[$key]['num']=$v['count(*)'];
                }
            }

        }

      $ARR=array();
	   	$ni=array();
	   	foreach ($b as $key => $val) {
        // var_dump($val);
        // die;
	   		if($val['Au_Uid']==$u){
          if($val['Au_State']==3){
            $ni[]=$val['Au_Gid'];
          }else{
	   			   $ARR[]=$val['Au_Gid'];
          }

	   		}

	   	}
        // var_dump($ni);
        // die;
        // var_dump($ARR);
        // die;
    $this->assign('block',$ni);   
 		$this->assign('arr',$ARR);
 		$this->assign('res',$res);
        // 

        if(empty($_GET['myid'])){
            $where='select G_id from post_gp';
        }else{
            $where="select Au_Gid from post_audit where Au_Uid=".$_GET['myid'];
        }

        $rp=M();
        $ret=$rp->query("select * from post_info where Post_Tid in (select Au_Gid from post_audit $where)");
        // echo $rp->_sql();
        // var_dump($ret);
        // die;
        // echo $rp->_sql();
         $count=count($ret);
        $Page=new \Think\Page($count,5);
        $PageShow=$Page->show();
         $sql='select g.*,i.User_Nickname,p.Group_Name from post_info g INNER JOIN user_info as i on g.Post_Uid=i.U_PID INNER JOIN post_gp as p on p.G_id=g.Post_Tid where Post_Tid in ('.$where.') limit '.$Page->firstRow.','.$Page->listRows.'';
        $tiezi=$rp->query($sql);
        // echo $rp->_sql();
        // $tiezi=$rp->alias('g')->field('g.*,i.User_Nickname,p.Group_Name')->join('user_info as i on g.Post_Uid=i.U_PID')->join('post_gp as p on p.G_id=g.Post_Tid')->where('g.Post_Display=0')->order('Post_Hot desc')->select();
        // var_dump($tiezi);
        // die;
        $Dao = M();
        foreach ($tiezi as $key => $value) {
        // var_dump($value);
            // die;
            $rrr=$Dao->query('select Rp_Tid,Rp_Postid,count(*) from Post_Reply where Rp_Postid='.$value['Post_Id'].' group by Rp_Postid');
            $cha=time()-$value['Post_Updatetime'];
            // echo $Dao->_sql();
            // die;
            $minute=floor($cha/60); 
            $hour=floor($cha/60/60); 
            $day=floor($cha/60/60/24); 
            $zan=$Dao->query("select * from Post_zan where `Post_Tid`='".$value['Post_Tid']."' And `Post_Id`='".$value['Post_Id']."' And `U_id`='".$u."'");
            // echo $Dao->_sql();
            // var_dump($zan);
            // die;
            if($zan){
                $tiezi[$key]['zan']='zan';
            }

            if($minute<60){ 
               $tiezi[$key]['ji']="最后更新 $minute 分钟前"; 
            } 
            elseif($minute<24*60) { 
                $tiezi[$key]['ji']="最后更新 $hour 小时前"; 
            }else{
                $tiezi[$key]['ji']="最后回复".date('Y-m-d H:i:s',$tiezi[$key]['Post_Updatetime']); 
            }
            foreach ($rrr as $k => $v){

                if($v['Rp_Postid']==$value['Post_Id']){
                    $tiezi[$key]['count']=$v['count(*)'];
                }
            }
            $value['Post_Content']=html_entity_decode($value['Post_Content']);
            // $result='/<img.+src=\".+\.(jpg|gif|bmp|bnp|png))\"?.+\\/>/i';

            $result='/<img src="([^"]+)"/';
            preg_match_all($result,$value['Post_Content'],$match); 
            // var_dump($match);
            $tiezi[$key]['img']=$match[1];
            // var_dump($value['Post_Content']);
        }

        //我的果壳
        //发帖
        $fa=$Dao->query("select count(*) from Post_Info where `Post_Uid`=".$u."");
        $hui=$Dao->query("select count(*) from Post_Reply where `Rp_Id`=$u");
        $username=$Dao->query("select User_Nickname from user_Info where `U_PID`='".$u."'");
        // 
        // 加入的小组
        $xiaozu=$Dao->query("select Au_Gid from post_audit where `Au_Uid`='".$u."' and `Au_State`=1");
        // echo $Dao->_sql();
        foreach($xiaozu as $k=>$v){
            $n[]=$Dao->query("select * from Post_Gp where G_id=".$v['Au_Gid']);
        }
        $group=array_slice($n,0,8);
        // var_dump($group);
        // die;
        // var_dump($xiaozu);
        // die;
        if($_GET['myid']){
            $this->assign('name','我的小组');
        }else{
            $this->assign('name','小组热帖');
        }
        $touxiang=$Dao->query("select User_pic from User_profile where `U_PID`='".$u."' and State=1");

        
        // var_dump($tiezi);
        // die;






        // die;
        $this->assign('tou',$touxiang[0]['User_pic']);
        $this->assign('group',$group);
        $this->assign('username',$username[0]['User_Nickname']);
        $this->assign('fa',$fa[0]['count(*)']);
        $this->assign('hui',$hui[0]['count(*)']);
        $this->assign('pages',$PageShow);
        $this->assign('myid',$u);
        $this->assign('tie',$tiezi);
    	$this->display();
    }

    public function joinGroup(){
    	// var_dump($_POST);
    	$m=M('postGp');
    	$id=i('post.G_id');
    	$where="`G_id`='".$id."' and `Group_Audit`=0";
    	$res=$m->where($where)->find();
    	// echo $m->_sql();
    	// var_dump($res);
     //    die;
        $u=$_COOKIE['HomeUser_User_Uid'];

        if(empty($u)){
            $u=$_SESSION['Home']['Uid'];
        }
    	
    	// var_dump($_SESSION);
       
    	if($res){
    	// var_dump($_COOKIE);
    		$join=M('PostAudit');
    		$arr=array('Au_Uid'=>$u,'Au_Gid'=>$id,'Au_State'=>'1');
    		$add=$join->add($arr);
    		// echo $join->_sql();
    		// var_dump($add);
    		// die;
    		$this->ajaxReturn(1);
    	}
    }

    public function outGroup(){
    		$id=i('post.G_id');
    		$join=M('PostAudit');

            $u=$_COOKIE['HomeUser_User_Uid'];

            if(empty($u)){
                $u=$_SESSION['Home']['Uid'];
            }

    		$where="`Au_Uid`='".$u."'and `Au_Gid`='$id'";
    		$del=$join->where($where)->delete();
    		// echo $join->_sql();
      //       die;
    		if($del){
    		$this->ajaxReturn(1);
    		}
    		// var_dump($del);

    }
    
    public function GpInfo(){

        
        $m=M('PostGp');
        $res=$m->alias('g')->field('g.*,User_Nickname,g.Group_Src,t.User_pic')->join('user_info as i on g.Group_Uid=i.U_PID')->join('User_profile as t on g.Group_Uid=t.U_PID')->where("`G_id`=".$_GET['id']." and t.State=1")->find();
        // echo $m->_sql();
        // var_dump($res);
        // die;
        $u=$_COOKIE['HomeUser_User_Uid'];

       if(empty($u)){
           $u=$_SESSION['Home']['Uid'];
       }


        $x=M('postAudit');
        $where="`Au_Gid`='".$_GET['id']."'and `Au_Uid`='$u'";
        $on=$x->where($where)->find();


        $rp=M('PostInfo');
        $where="`Post_Tid`='".$_GET['id']."'";

        $count = $rp->where($where)->count();

        $Page=new \Think\Page($count,10);
        $PageShow=$Page->show();

        if($_GET['hot']=='yes'){
            $order='Post_Hot desc';
        }

        $tiezi=$rp->alias('g')->field('g.*,i.User_Nickname')->join('user_info as i on g.Post_Uid=i.U_PID')->limit($Page->firstRow.','.$Page->listRows)->where($where)->order($order)->select();
        // echo $rp->_sql();
        // var_dump($res);
        // die;
        $this->assign('pages',$PageShow);
        $Dao = M();
        foreach ($tiezi as $key => $value) {
        // var_dump($value);
            // die;
            $cha=time()-$value['Post_CTime'];
            $rrr=$Dao->query('select Rp_Tid,Rp_Postid,count(*) from Post_Reply where Rp_Postid='.$value['Post_Id'].' group by Rp_Postid');
            // echo $Dao->_sql();
            // die;
            $minute=floor($cha/60); 
            $hour=floor($cha/60/60); 
            $day=floor($cha/60/60/24); 
            

            if($minute<60){ 
               $tiezi[$key]['ji']="已发布 $minute 分钟"; 
            } 
            elseif($minute<24*60) { 
                $tiezi[$key]['ji']="已发布 $hour 小时"; 
            }else{
                $tiezi[$key]['ji']=date('Y-m-d H:i:s',$tiezi[$key]['Post_Updatetime']); 
            }
            // var_dump($rrr);
            foreach ($rrr as $k => $v){

                if($v['Rp_Postid']==$value['Post_Id']){
                    $tiezi[$key]['count']=$v['count(*)'];
                }
            }
        }
        

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
        $ren=$Dao->query('select Au_Gid,count(*) from post_audit where Au_Gid ='.$res['G_id'].' group by Au_Gid');
        // echo $Dao->_sql();
        // var_dump($ren);
        // die;
        $this->assign('tie',$tiezi);
        $this->assign('count',$ren[0]['count(*)']);
        $this->assign('msg',$res);
        $this->display();

    }


    public function soso(){

        $m=M();
        if(empty($_GET['wd'])){
            $where='';
        }else{
            $where="where `Group_Name` like '%".$_GET['wd']."%'";
        }
        $res=$m->query("select * from post_Gp $where");
        // echo $m->_sql();
        // var_dump($res);
        // die;

        $u=$_COOKIE['HomeUser_User_Uid'];

       if(empty($u)){
           $u=$_SESSION['Home']['Uid'];
       }

        foreach ($res as $key => $value) {
            $rr=$m->query("select Au_Gid,count(*) from post_audit where Au_Gid=".$value['G_id']." group by Au_Gid ");
            // echo $m->_sql();
            // var_dump($rr);
            // die;
            foreach ($rr as $k => $v) {
                $res[$key]['count']=$v['count(*)'];
                 $mm=$m->query("select Au_Gid from post_audit where Au_Uid=".$u);
                 // echo $m->_sql();
                 foreach ($mm as $kk => $vv) {
                     # code...
                    if($vv['Au_Gid']==$v['Au_Gid']){
                    $res[$key]['play']=1;
                    }
                 }
            }
        }    

       // 
        $sort = array(  
                  'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序  
                  'field'     => 'count', //排序字段  
          );  
          $arrSort = array();
          foreach($res AS $uniqid => $row){  
              foreach($row AS $key=>$value){  
                  $arrSort[$key][$uniqid] = $value;  
              }  
          }
          if($sort['direction']){  
              array_multisort($arrSort[$sort['field']], constant($sort['direction']), $res);  
          }

        // $mm=$m->query('select * from post_audit $where');
        // var_dump($mm);
        // var_dump($res);
        $this->assign('name',$_GET['wd']);
        $this->assign('res',$res);
        // die;
        $this->display();
    }


    public function sotie(){
        var_dump($_GET);
        // die;
        $where="`Post_Title` like '%".$_GET['wd']."%'";
        $m=M('postInfo');
        $res=$m->field('post_info.*,Group_Name')->where($where)->join('post_Gp as g on post_info.Post_Tid=G_id')->select();
        // echo $m->_sql();
        foreach($res as $key=>$value){
            $res[$key]['Post_Content']=strip_tags(html_entity_decode($value['Post_Content']));


        $cha=time()-$value['Post_Ctime'];
        // echo $Dao->_sql();
        // die;
        $minute=floor($cha/60); 
        $hour=floor($cha/60/60); 
        $day=floor($cha/60/60/24); 
        

           if($minute<60){ 
              $res[$key]['ji']="已发布 $minute 分钟"; 
           } 
           elseif($minute<24*60) { 
               $res[$key]['ji']="已发布 $hour 小时"; 
           }else{
               $res[$key]['ji']=date('Y-m-d',$res[$key]['Post_Updatetime']); 
          }
        }
        // var_dump($res);
        // die;
        $this->assign('res',$res);
        $this->assign('name',$_GET['wd']);
        $this->display();
    }

    public function Gprank(){
         $m=M();
         if(empty($_GET['wd'])){
             $where='';
         }else{
             $where="where `Group_Name` like '%".$_GET['wd']."%'";
         }
         $res=$m->query("select * from post_Gp $where");
         // echo $m->_sql();
         // var_dump($res);
         // die;

         $u=$_COOKIE['HomeUser_User_Uid'];

        if(empty($u)){
            $u=$_SESSION['Home']['Uid'];
        }

         foreach ($res as $key => $value) {
             $rr=$m->query("select Au_Gid,count(*) from post_audit where Au_Gid=".$value['G_id']." group by Au_Gid ");
             // echo $m->_sql();
             // var_dump($rr);
             // die;
             foreach ($rr as $k => $v) {
                 $res[$key]['count']=$v['count(*)'];
                  $mm=$m->query("select Au_Gid,Au_State from post_audit where Au_Uid=".$u);
                  // echo $m->_sql();
                  // 
                  foreach ($mm as $kk => $vv) {
                    // var_dump($vv);

                      if($vv['Au_Gid']==$v['Au_Gid']){
                        if($vv['Au_State']==3){
                          $arr[]=$vv['Au_Gid'];
                        }
                        $res[$key]['play']=1;
                     }
                  }
             }
         }    
        // 
        // var_dump($arr);
        // die;
        switch ($_GET['rk']) {
            case 'huo':
            $sort = array(  
                    'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序  
                    'field'     => 'Group_Hot', //排序字段  
            );  
            $arrSort = array();
            foreach($res AS $uniqid => $row){  
                foreach($row AS $key=>$value){  
                    $arrSort[$key][$uniqid] = $value;  
                }  
            }
            if($sort['direction']){  
                array_multisort($arrSort[$sort['field']], constant($sort['direction']), $res);  
            }
            $this->assign('bibi','活跃榜');
            // $this->assign('huodu',$res['Group_Hot']);
            // var_dump($res);
            // die;
                break;
            
            default:
                
           $sort = array(  
                   'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序  
                   'field'     => 'count', //排序字段  
           );  
           $arrSort = array();
           foreach($res AS $uniqid => $row){  
               foreach($row AS $key=>$value){  
                   $arrSort[$key][$uniqid] = $value;  
               }  
           }
           if($sort['direction']){  
               array_multisort($arrSort[$sort['field']], constant($sort['direction']), $res);  
           }
           $this->assign('bibi','实力榜');
                break;
        }


        $res=array_slice($res,0,49);
        // var_dump($res);

        // die;
        $this->assign('arr',$arr);
        $this->assign('res',$res);
        $this->display();
    }

    public function zan(){
     $ping=M();
    $u=$_COOKIE['HomeUser_User_Uid'];

    if(empty($u)){
         $u=$_SESSION['Home']['Uid'];
     }

        $m=M('postZan');
    if($_POST['zan']=='jia'){
        // $zan=$m->where($where)->find();
        // echo $m->_sql();
        
        $_POST['U_id']=$u;
        // var_dump($_POST);
        // die;
        $m->create();
        $res=$m->add();
        $haocount=$ping->query("select count(*) from post_zan where `Post_Tid`='".$_POST['Post_Tid']."' and `Post_Id`=".$_POST['Post_Id']." group by Post_Id");
        // echo $ping->_sql();
        // var_dump($haocount);
        // die;
        $jk=$ping->query('update post_info set Post_Good='.$haocount[0]['count(*)'].' where Post_Id='.$_POST['Post_Id']);
        // echo $ping->_sql();
        // var_dump($jk);
        // die;
        $this->ajaxReturn(1);
    }else{  
        $where="`Post_Tid`='".$_POST['Post_Tid']."' and `Post_Id`='".$_POST['Post_Id']."' and `U_id`=$u";
        // var_dump($_POST);
        $res=$m->where($where)->delete();
        if($res){
            $haocount=$ping->query("select count(*) from post_zan where `Post_Tid`='".$_POST['Post_Tid']."' and `Post_Id`=".$_POST['Post_Id']." group by Post_Id");
            $jk=$ping->query('update post_info set Post_Good='.$haocount[0]['count(*)'].' where Post_Id='.$_POST['Post_Id']);
            $this->ajaxReturn(2);
        }
    }   


    }


    public function join(){
      if(IS_POST){
        // var_dump($_POST);
        // die;
        $gp=M('postGp');
        $where="`Group_Name`='".$_POST['J_Name']."'";
        $k=$gp->where($where)->find();
        if($k){
          $this->error('该小组已经存在!无法创建');
          die;
        }
        // var_dump($_POST);
        // die;
        if(empty($_POST['J_content'])){
           $this->error('申请理由必须100字以上');
        }
        $u=$_COOKIE['HomeUser_User_Uid'];

         if(empty($u)){
             $u=$_SESSION['Home']['Uid'];
         }
        $_POST['U_id']=$u;
        $_POST['J_ctime']=time();
        // var_dump($_POST);
        // die;
        $m=M('PostJoin');
        $m->create();
        $res=$m->add();
        if($res){
          $this->success('小组已提交申请,请等待管理员的同意', U('Home/Group/index'),3);
        }
        // $m->
      }else{
        $this->display();
      }

    }


}