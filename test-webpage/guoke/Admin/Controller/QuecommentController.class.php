<?php
namespace Admin\Controller;
use Think\Controller;
//后台的回答管理控制器.
class QuecommentController extends CommonController {
    public function index(){
	// 接收数据
        $name=$_GET['name'];
        $state=$_GET['num'];
        $sub=M('qac_answer');
        // 获取显示数量
        $num=!empty($_GET['page'])?$_GET['page']:5;
        //获取关键字
        if(!empty($_GET['keyword'])){
            $where="ans_msg like '%".$_GET['keyword']."%'";
        }else{
            $where="";
        }
        $order="'ans_id asc'";
        if(!empty($name) && $state==null){
            $order=$name.' desc';
            $state=1;
        }elseif($state==1){
            $order=$name.' asc';
            $state=null;
        }
        // 查询满足条件总记录数
        $count=$sub->where($where)->count();
        //实例化分页类
        $page=new \Think\Page($count,$num);
        // 获取limit参数
        $limit=$page->firstRow.','.$page->listRows;

        $res=$sub->alias('a')->join('left join user_info u on a.user_id = u.U_PID')->where($where)->order($order)->limit($limit)->select();
        if($res){
            foreach ($res as $k => $v) {
                $res[$k]['msg']=$v['ans_msg'];
                $res[$k]['ans_msg']=htmlspecialchars($v['ans_msg']);
            }
        }
        // var_dump($res);die;
        // 分页输出
        $pages=$page->show();

        // var_dump($res);die;
        $this->assign('state',$state);
        $this->assign('pages',$pages);
        $this->assign('num',$num);
        $this->assign('res',$res);
        $this->display();
    }

      //是否喜欢
    public function ajaxhot(){
        // 接收数据
        $is_hot=$_GET['state'];
        echo $_hot;
        $id=$_GET['id'];
        $sub=M();
        // 执行修改
        $res=$sub->execute("update qac_answer set is_like='{$is_hot}' where ans_id={$id}"); 
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }
   // 删除回答
    public function ajaxdel(){
        // 接收id
        $id=$_GET['id'];
        $msg=$_GET['msg'];
        $uid=$_GET['uid'];
        $ans=M('qac_answer');

        $email=M('email');

        $dat['uid']='1';
        $dat['time']=date('Y-m-d H:i:s');
        $dat['pid']=$uid;
        $res=$ans->where('ans_id='.$id)->delete();
        if($res){
            $dat['msg']="您回答的".$msg."答案存在不合法信息,现已被删除";
            $res=$email->add($dat);
            if($res){
                echo 1;
            }else{
                echo 0;
            }
        }
    }
}