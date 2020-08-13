<?php
namespace Admin\Controller;
use Think\Controller;
//后台的问题审核管理控制器.
class QueexamineController extends CommonController {
    //问题审核列表显示板块
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
        $count=$sub->where("is_examine='0'".$where)->count();
        //实例化分页类
        $page=new \Think\Page($count,$num);
        // 获取limit参数
        $limit=$page->firstRow.','.$page->listRows;

        $res=$sub->join('left join user_verify on qac_subject.user_id = user_verify.U_id')->where("is_examine='0'".$where)->order($order)->limit($limit)->select();
        foreach ($res as $k => $v) {
            $res[$k]['sub_name']=str_replace(keyword(),highlight(),$v['sub_name']);
            $res[$k]['merge']=$sub->where('is_examine="1" and sub_name="'.$v['sub_name'].'"')->select();
        }

        // 分页输出
        $pages=$page->show();

        $this->assign('state',$state);
        $this->assign('pages',$pages);
        $this->assign('num',$num);
        $this->assign('res',$res);
        $this->display();
    }

    // ajax通过模块
    public function adopt(){
        // 设置通过
        $is_examine['is_examine']=1;

        $id=$_GET['id'];
        $sub=M('qac_subject');
        //执行修改
        $res=$sub->where('id='.$id)->save($is_examine);
        header('location:'.$_SERVER['HTTP_REFERER']);

    }

    // ajax详细描述模块
    public function ajaxdetailed(){
        // 获取id
        $id=$_GET['id'];
        $sub=M('qac_subject');
        $res=$sub->where('id='.$id)->select();

        //将sub_msg转换为实体;
        $sub_msg=htmlspecialchars_decode($res['0']['sub_msg']);

        echo $sub_msg;
    }

    // 问题删除模块
    public function ajaxdel(){
        // 接收id
        $id=$_GET['id'];
        // 获取当前用户id
        $uid=$_GET['uid'];
        $msg=$_GET['msg'];

        $sub=M('qac_subject');
        $email=M('email');

        // 执行删除
        $res=$sub->where('id='.$id)->delete();

        // 判断是否删除
        if($res){
            $dat['uid']='1';
            $dat['pid']=$uid;
            $dat['time']=date('Y-m-d H:i:s');
            $dat['msg']='您发布的'.$msg.'问题存在不合法信息,现已被删除';
            $res=$email->add($dat);
            echo 1;
        }else{
            echo 0;
        }
    }

    // 合并相同信息
    public function merge(){
        // 获取pid
        $pid=$_POST['pid'];
        // 获取当前id
        $id=$_POST['id'];
        // 获取当前用户id
        $uid=$_POST['uid'];
        // 获取问题
        $msg=$_POST['msg'];

        $data['sub_id']=$pid;

        $sub=M('qac_subject');
        $ans=M('qac_answer');
        $ta=M('qac_tag_answer');
        $fol=M('qac_follow');
        $email=M('email');
        $sav=M();
        // 查询当前问题的回答数
        $num=$sub->where('id='.$id)->select()[0]['answer_num'];

        // 修改问题数
        $sav->execute("update qac_subject set answer_num=answer_num+$num where id=$pid");
        $url="http://guoke/Home/Question/subject/id/".$pid.".html";

        $sub->where('id='.$id)->delete();
        $ans->where('sub_id='.$id)->save($data);
        $ta->where('sub_id='.$id)->delete($data);
        $fol->where('sub_id='.$id)->save($data);

        $dat['uid']='1';
        $dat['pid']=$uid;
        $dat['time']=date('Y-m-d H:i:s');
        $dat['msg']='您发布的'.$msg.'问题乡存在相关问题,现已合并到'.$url;
        $res=$email->add($dat);

    }

}