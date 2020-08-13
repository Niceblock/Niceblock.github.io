<?php
namespace Admin\Controller;
use Think\Controller;
//后台的站内信管理控制器.
class EmailController extends CommonController {
	//站内信列表显示板块
    public function index(){
        
        $email=M('email');
        // 查询满足条件总记录数
        $count=$email->where('pid="1" and is_display="1"')->count();
        //实例化分页类
        $page=new \Think\Page($count,5);
        // 获取limit参数
        $limit=$page->firstRow.','.$page->listRows;
        // 查询数据
        $res=$email->alias('e')->join('User_info as i ON i.U_PID=e.uid')->join('User_profile as p ON p.U_PID=e.uid')->where('e.pid="1" and e.is_display="1"')->order('e.time desc')->limit($limit)->select();
        // var_dump($res);die;
        $pages=$page->show();

        $this->assign('res',$res);
        $this->assign('num',$state);
        $this->assign('pages',$pages);
        $this->display();

    }

    // 删除接收信息
    public function del(){
        // 接收post
        $data['id']=$_GET['id'];

        // 设置隐藏
        $data['is_display']="0";

        $email=M('email');

        $res=$email->save($data);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }

    // 回复消息
    public function reply(){
        // 接收值
        $data['pid']=$_POST['uid'];
        $data['msg']=$_POST['msg'];

        $data['uid']='1';

        $email=M('email');
        $res=$email->add($data);
        if($res){
             $this->success('回复成功');  
        }else{
            $this->error('回复失败');
        }

    }

    // 发送信息
    public function send(){
        $info=M('user_info');
        // 获取状态
        $state=$_POST['state'];
        // 获取信息
        $data['msg']=$_POST['msg'];
        // 获取用户名
        $name=$_POST['uname'];
        if(!empty($name)){
            $id=$info->where('User_Nickname="'.$name.'"')->select()[0]['U_PID'];
        }

        $user=M('user_verify');
        $email=M('email');
        if($state==0){
            $res=$user->select();
            if($res){
                foreach ($res as $k => $v) {
                    if($v['U_id'] != 1) {
                        $data['uid']='1';
                        $data['pid']=$v['U_id'];
                        $data['time']=date('Y-m-d H:i:s');
                        $email->add($data);
                    }
                }
                $this->success('发送成功');  
            }
        }else{
            $data['uid']='1';
            $data['pid']=$id;
            $data['time']=date('Y-m-d H:i:s');
            $email->add($data);
            $this->success('发送成功');
        }
    }

    // 验证用户名
    public function ajaxname(){
        // 接收值
        $name=$_POST['name'];

        $info=M('user_info');
        // 查询
        $res=$info->where('User_Nickname="'.$name.'"')->select();
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }

}