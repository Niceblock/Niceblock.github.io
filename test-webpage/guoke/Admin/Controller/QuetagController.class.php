<?php
namespace Admin\Controller;
use Think\Controller;
//后台的标签管理控制器.
class QuetagController extends CommonController {
    //标签列表显示板块
    public function index(){
        $tag=M('qac_tag');
        // 接收数据
        $name=$_GET['name'];

        // 接收排序方式状态
        $ord=$_GET['num'];
        
        // 接收是否为热门
        if(!empty($_GET['is_hot'])){
            $is_hot="and is_hot=1";
        }else{
            $is_hot='';
        }

        // 获取显示方式
        $state=!empty($_GET['state'])?$_GET['state']:1;

        if($state==1){
            $mode="state=1";
        }elseif($state==2){
            $mode="state=2";
        }elseif($state==3){
            $mode="state=3";
        }elseif($state==4){
            $mode="state=4";
        }else{
            $mode="state=5";
        }

        // 获取显示数量
        $num=!empty($_GET['page'])?$_GET['page']:5;
        
        //获取关键字
        if(!empty($_GET['keyword'])){
            $where=" and tag_name like '%".$_GET['keyword']."%'";
        }else{
            $where="";
        }
        $order="'id asc'";
        if(!empty($name) && $ord==null){
            $order=$name.' desc';
            $ord=1;
        }elseif($ord==1){
            $order=$name.' asc';
            $ord=null;
        }
        
        // 查询满足条件总记录数
        $count=$tag->where($mode.$where)->count();
        
        //实例化分页类
        $page=new \Think\Page($count,$num);
        // 获取limit参数
        $limit=$page->firstRow.','.$page->listRows;

        $res=$tag->where($mode.$where)->order($order)->limit($limit)->select();

        // 分页输出
        $pages=$page->show();

        $this->assign('state',$ord);
        $this->assign('pages',$pages);
        $this->assign('res',$res);
        $this->display();
    }



    // 标签修改显示模块
    public function save(){
        $sub=M('qac_tag');

        // 接收id
        $id=$_GET['id'];

        $res=$sub->find($id);
        $tag_msg=htmlspecialchars_decode($res['tag_msg']);

        $this->assign('res',$res);
        $this->assign('tag_msg',$tag_msg);
        $this->display();
    }
    // 标签修改数据模块
    public function update(){
        Uploads('hp');
        
        // 接收post值
        $id=$_POST['id'];
        $tag=M('qac_tag');
        // 查询原有值
        $filename=$tag->where('tag_id='.$id)->select()[0]['tag_img'];
        $msg=$tag->where('tag_id='.$id)->select()[0]['tag_msg'];
        // 判断传过来值是否为空
        $data['tag_img']=!empty($_POST['hp'])?$_POST['hp']:$filename;
        $data['tag_msg']=!empty($_POST['tag_msg'])?$_POST['tag_msg']:$msg;
        //判断数据库中是否有图片
        if(!empty($_POST['hp'])){
            if($filename!=null){
                $path=$_SERVER['DOCUMENT_ROOT'].'/Public'.$filename;
                unlink($path);
            }
        }
        $res=$tag->where('tag_id='.$id)->save($data);
        if($res){
            $this->success('修改成功',U('Admin/Quetag/index'),1);
        }else{
            $this->error('修改失败','',1);
        }
    }
    
    //是否热门
    public function ajaxhot(){
        // 接收数据
        $is_hot=$_GET['state'];
        $id=$_GET['id'];
        $sub=M();
        // 执行修改
        $res=$sub->execute("update qac_tag set is_hot='{$is_hot}' where tag_id={$id}"); 
      
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
        $tag=M('qac_tag');
        $res=$tag->where('tag_id='.$id)->select();

        //将sub_msg转换为实体;
        $tag_msg=htmlspecialchars_decode($res['0']['tag_msg']);

        echo $tag_msg;

    }

    // 标签删除模块
    public function ajaxdel(){
        // 接收id
        $id=$_GET['id'];
        
        $sub=M('qac_subject');

        // 执行删除
        $res=$sub->where('id='.$id)->delete();

        // 判断是否删除
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }

    // 标签名修改
    public function ajaxname(){
        $id=$_GET['id'];
        $tag=M('qac_tag');
        $tag_name['tag_name']=$_GET['tag_name'];

        $name=$tag->where($tag_name)->select();
        // 判断是否有该标签名
        if($name){
            echo 0;
            die;
        }else{
            $res=$tag->where('tag_id='.$id)->save($tag_name);
            if($res){
                echo 1;
            }
        }
    }

    // 分类修改
    public function ajaxclass(){
        $id=$_GET['id'];
        $data['state']=$_GET['state'];
        $tag=M('qac_tag');
        if($data['state']!=1){
            $res=$tag->where('tag_id='.$id)->save($data);
            if($res){
                echo 1;
            }
        }
    }

    // 是否锁定
    public function ajaxlock(){
        $id=$_GET['id'];
        $data['is_locking']=$_GET['is_locking'];
        $tag=M('qac_tag');
        $res=$tag->where('tag_id='.$id)->save($data);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }
}