<?php
namespace Admin\Controller;
use Think\Controller;
//后台的会话管理控制器.
class ChatController extends CommonController {
	//会话显示板块
    public function index(){
        $user=M('chat');
        //获取每页显示的数量
        $num = !empty($_GET['num']) ? $_GET['num'] : 6;
        //获取关键字
        if(!empty($_GET['keyword'])){
            //有关键字
            $where = "chat.chattext like '%".$_GET['keyword']."%'";
        }else{
            $where = "";
        }
        // 查询满足要求的总记录数
        $count = $user->where($where)->count();
        // 实例化分页类 传入总记录数和每页显示的记录数
        $Page = new \Think\Page($count,$num);
        //获取limit参数
        $limit = $Page->firstRow.','.$Page->listRows;
        //查询
        $res = $user->field('chat.id,chat.uid,chat.chattext,user_info.User_Nickname,chat.display')->join('left join user_info on chat.userid = user_info.U_PID')->where($where)->limit($limit)->select();
        //修改"显示"文字
        $info=M('user_info');
        foreach ($res as $k => $v) {
            if ($v['display']==1) {
                $res[$k]['display']='已结束会话';
            } else {
                $res[$k]['display']='会话中';
            }
            $a=explode(',',$v['uid'])[1];
            $acon=$info->field('User_Nickname')->where('U_PID='.$a)->select()[0]['User_Nickname'];
            $b=explode(',',$v['uid'])[2];

            $bcon=$info->field('User_Nickname')->where('U_PID='.$b)->select()[0]['User_Nickname'];

            $res[$k]['uid']=$acon.','.$bcon;
            
        }
        // var_dump($res);
        // die;
        // 分页显示输出
        $pages = $Page->show();
    
        // 查询结果赋值给前台
        $this->assign('res',$res);
        $this->assign('pages',$pages);
        //解析模板
        $this->display();
    }
    
    //删除
    public function ajaxdel(){
        //实例化会话表
        $user=M('chat');
        //过滤数据
        $user->create();
        //删除
        $res=$user->delete();
        //结果判断
        if ($res) {
            echo 1;
        } else {
            echo 0;
        }
        
    }




}