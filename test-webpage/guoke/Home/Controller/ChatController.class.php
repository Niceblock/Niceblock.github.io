<?php
namespace Home\Controller;
use Think\Controller;
class ChatController extends Controller {
    //聊天主页面
    public function chat(){
        $chat=M('chat');
        
        if (!isset($_GET['uid']) or empty($_GET['uid'])) {
            $meiron='<h1>使用说明</h1>点击左侧"发起聊天"可向其他用户发起会话,"发起会话"下侧为所有会话,点击可进入发送信息和聊天';
            $this->assign('meiron',$meiron);
        }
        
        
        $uid=$_SESSION['Home']['Uid'];
        $res=$chat->query('select distinct uid from chat where display=0 and uid like "%,'.$uid.',%"');
        $info=M('user_info');
        foreach ($res as $k => $v) {
            $a=explode(',',$v['uid'])[1];
            $acon=$info->field('User_Nickname')->where('U_PID='.$a)->select()[0]['User_Nickname'];
            $b=explode(',',$v['uid'])[2];

            $bcon=$info->field('User_Nickname')->where('U_PID='.$b)->select()[0]['User_Nickname'];

            $res[$k]['cname']=$acon.','.$bcon;
        }

            $this->assign('res',$res);      
            $this->display();
    }

    //开始聊天
    public function zhaochat(){
        // var_dump($_POST);
        // die;
        $uname=I('post.uname');

        $uid=I('post.uid');
        $info=M('user_info');
        $res=$info->field('U_PID')->where('User_Nickname="'.$uname.'"')->select();
        if ($res) {
            $tuid=$res[0]['U_PID'];
            $data['uid']=','.$uid.','.$tuid.',';
            $data['chattext']='恭喜您,您已可以开始聊天';
            $data['userid']=$uid;
            $chat=M('chat');
            $chatres=$chat->add($data);
             if ($chatres) {
                $this->success('发起会话成功',U('Home/Chat/chat'),3);
            } else {
                $this->error('系统繁忙,请稍后重试',U('Home/Chat/chat'),3);
            }

        } else {
            $this->error('服务器繁忙,请稍后重试',U('Home/Chat/chat'),3);
        }
    }
    
    //打回文章再次上传
    public function ajaxChat(){
        var_dump($_POST);

        $chat=M('chat');
        $chat->create();
        $chat->add();
        
        //结果集判断
        // 
    }

    //获取聊天
    public function ajaxhuoquchat(){
        //过滤接收用户ID
        // var_dump($_POST);
        // die;
        $uid=I('post.uid');
        $userid=I('post.userid');
        //接收要忽略的ID标志
        $j=I('post.j');
        //如果此标志为空
        if (empty($j)) {
            $where='';
        } else {
            //不为空,去除最后的逗号
            $j=rtrim($j,',');
            //组合进WHERE条件
            $where=' and id not in ('.$j.')';
        }
        
        //初始化用户表
        //以用户表为基准查询各种对该用户有用的信息
        $user=M('chat');
        //查询
        $ationres=$user->field('id,chattext,userid')->where('uid="'.$uid.'" and display=0'.$where)->select();
        // echo $user->_sql();
        // var_dump($ationres);
        // die;
        //数据修改成HTML
        
        //json
        echo json_encode($ationres);
    }

    public function clearchat(){
        $uid=I('get.uid');        
        $chat=M('chat');
        $res=$chat->execute('update chat set display=1 where uid="'.$uid.'"');
         if ($res) {
            $this->success('关闭会话成功',U('Home/Chat/chat'),3);
        } else {
            $this->error('系统繁忙,请稍后重试',U('Home/Chat/chat'),3);
        }
    }
}