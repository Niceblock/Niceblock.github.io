<?php
namespace Admin\Controller;
use Think\Controller;
//后台的问题审核管理控制器.
class ReportController extends CommonController {
	//未处理举报显示板块
    public function wait(){
        $rep=M('report');
        $user=M('user_info');

        $res=$rep->where('r_state="0"')->select();

        // 判断是否为空
        if(!empty($res)){
            foreach ($res as $k => $v) {
                $res[$k]['bu']=$user->where('U_PID='.$v['bru_id'])->select();
                $res[$k]['u']=$user->where('U_PID='.$v['ru_id'])->select();
                $res[$k]['msg']=$v['r_msg'];
                $res[$k]['r_msg']=htmlspecialchars($v['r_msg']);
            }
        }
        // var_dump($res);die;
        $this->assign('res',$res);
        $this->display();
    }
    //已处理举报显示板块
    public function suc(){
        $rep=M('report');
        $user=M('user_info');

        $res=$rep->where('r_state="1"')->order('r_time desc')->select();

        // 判断是否为空
        if(!empty($res)){
            foreach ($res as $k => $v) {
                $res[$k]['bu']=$user->where('U_PID='.$v['bru_id'])->select();
                $res[$k]['u']=$user->where('U_PID='.$v['ru_id'])->select();
                $res[$k]['msg']=$v['r_msg'];
                $res[$k]['r_msg']=htmlspecialchars($v['r_msg']);
            }
        }
        // var_dump($res);die;
        $this->assign('res',$res);
        $this->display();
    }

    // 删除已处理举报信息
    public function isdel(){
        // 接收post信息
        $rid=$_POST['rid'];
        $rep=M('report');

        $res=$rep->where('r_id='.$rid)->delete();
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }
    // 删除举报内容 
    public function del(){
        // 接收post值
        $type=$_POST['type'];//类型
        $buid=$_POST['buid'];//被举报用户id
        $msgid=$_POST['msgid'];//举报信息id
        $bm=$_POST['bm'];//基本理由
        $om=$_POST['om'];//其他理由
        $rid=$_POST['rid'];//举报id
        $rmsg=$_POST['rmsg'];//被举报内容

        $data['r_state']='1';

        $sub=M('qac_subject');
        $ans=M('qac_answer');
        $fol=M('qac_follow');
        $rep=M('report');
        $ta=M('qac_tag_answer');
        $email=M('email');

        $dat['uid']='1';
        $dat['time']=date('Y-m-d H:i:s');
        $dat['pid']=$buid;

        // 判断类型
        if($type==1){
            $res=$sub->where('id='.$msgid)->select();
            // 查询是否有该信息
            if($res){
                $sub->where('id='.$msgid)->delete();
                $ta->where('sub_id='.$msgid)->delete();
                $fol->where('sub_id='.$msgid)->delete();
                $ans->where('sub_id='.$msgid)->delete();
                
                $rep->where('r_id='.$rid)->save($data);
                $dat['msg']="您的发布的".$rmsg."问题存在不合法信息,现已被删除";
                $email->add($dat);
                echo 1;
            }else{
                $rep->where('r_id='.$rid)->save($data);
                echo 1;
            }
        }elseif($type==2){
            $res=$ans->where('ans_id='.$msgid)->select();
            // 查询是否有该信息
            if($res){
                $res=$ans->where('ans_id='.$msgid)->delete();
                // 是否删除成功
                if($res){
                    $rep->where('r_id='.$rid)->save($data);
                    $dat['msg']="您回答的".$rmsg."答案存在不合法信息,现已被删除";
                    $email->add($dat);
                    echo 1;
                }
            }else{
                $rep->where('r_id='.$rid)->save($data);
                echo 1;
            }
        }
    }

    // 删除举报信息
    public function delr(){
        $data['r_id']=$_POST['id'];
        $report=M('report');
        $res=$report->where($data)->delete();
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }
}   