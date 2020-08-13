<?php
namespace Admin\Controller;
use Think\Controller;
//后台的问题管理控制器.
class QuestionController extends CommonController {
	//问题列表显示板块
    public function index(){
        // 接收数据
        $name=$_GET['name'];
        $state=$_GET['num'];
        $sub=M('qac_subject');
        // 获取显示数量
        $num=!empty($_GET['page'])?$_GET['page']:5;
        //获取关键字
        if(!empty($_GET['keyword'])){
            $where=" and sub_name like '%".$_GET['keyword']."%'";
        }else{
            $where="";
        }
        $order="'id asc'";
        if(!empty($name) && $state==null){
            $order=$name.' desc';
            $state=1;
        }elseif($state==1){
            $order=$name.' asc';
            $state=null;
        }
        // 查询满足条件总记录数
        $count=$sub->where("is_examine='1'".$where)->count();
        //实例化分页类
        $page=new \Think\Page($count,$num);
        // 获取limit参数
        $limit=$page->firstRow.','.$page->listRows;

        $res=$sub->join('left join user_verify on qac_subject.user_id = user_verify.U_id')->where("is_examine='1'".$where)->order($order)->limit($limit)->select();
        foreach ($res as $k => $v) {
            $res[$k]['sub_msg']=htmlspecialchars($v['sub_msg']);
        }
        // var_dump($res);die;
        // 分页输出
        $pages=$page->show();

        // var_dump($res);
        $this->assign('state',$state);
        $this->assign('pages',$pages);
        $this->assign('num',$num);
        $this->assign('res',$res);
    	$this->display();
    }



    // 问题修改显示模块
    public function save(){
        $sub=M('qac_subject');

        // 接收id
        $id=$_GET['id'];

        $res=$sub->find($id);
        $sub_msg=htmlspecialchars_decode($res['sub_msg']);
        // var_dump($res);
        $this->assign('res',$res);
        $this->assign('sub_msg',$sub_msg);
        $this->display();
    }
    // 问题修改数据模块
    public function update(){
        // 处理sub_msg字段
        // if(!empty($_POST['sub_msg'])){
            // $_POST['sub_msg']=str_replace('<img','<img width="850px" height=600px ',$_POST['sub_msg']);
        // }
        // var_dump($_POST);

        $sub=M('qac_subject');

        // 创建数据
        $sub->create();
        // 执行修改
        $res=$sub->save();
        if($res){
            $this->success('修改成功',U('Admin/Question/index'),3);
        }else{
            $this->error('修改失败','',3);
        }
    }
    
    //是否热门
    public function ajaxhot(){
        // 接收数据
        $is_hot=$_GET['state'];
        $id=$_GET['id'];
        $sub=M();
        // 执行修改
        $res=$sub->execute("update qac_subject set is_hot='{$is_hot}' where id={$id}"); 

        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }
    //是否精彩
    public function ajaxwonderful(){
        // 接收数据
        $is_wonderful=$_GET['state'];
        $id=$_GET['id'];
        $sub=M();
        // 执行修改
        $res=$sub->execute("update qac_subject set is_wonderful='{$is_wonderful}' where id={$id}"); 

        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }

    // ajax详细描述模块
    public function ajaxdetailed(){
        // 获取id
        $id=$_GET['id'];
        $sub=M('qac_subject');
        $res=$sub->where('id='.$id)->select();
        // var_dump($res);
        //将sub_msg转换为实体;
        $sub_msg=htmlspecialchars_decode($res['0']['sub_msg']);

        echo $sub_msg;
        // $this->display();

    }

    // 问题删除模块
    public function ajaxdel(){
        // 接收id
        $id=$_GET['id'];
        $uid=$_GET['uid'];
        $msg=$_GET['msg'];
        
        $sub=M('qac_subject');
        $ans=M('qac_answer');
        $fol=M('qac_follow');
        $ta=M('qac_tag_answer');
        $email=M('email');
        $res=$sub->where('id='.$id)->delete();
        $dat['uid']='1';
        $dat['time']=date('Y-m-d H:i:s');
        $dat['pid']=$uid;
        if($res){
            $fol->where('sub_id='.$id)->delete();
            $ta->where('sub_id='.$id)->delete();
            $ans->where('sub_id='.$id)->delete();
            $dat['msg']="您的发布的".$msg."问题存在不合法信息,现已被删除";
            $email->add($dat);
            echo 1;
        }else{
            echo 0;
        }
        
    }
}