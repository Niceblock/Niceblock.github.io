<?php
namespace Home\Controller;
use Think\Controller;
class ReportController extends Controller {
    // 提交举报模块
    public function submit(){
        // 接收post值
        $data['r_type']=$_POST['type'];//举报类型
        $data['bru_id']=$_POST['buid'];//被举报用户id
        $data['r_msg']=$_POST['title'];//举报内容
        $data['rmsg_id']=$_POST['msgid'];//举报内容id
        $data['b_r']=$_POST['rea'];//举报基本理由
        $data['o_r']=$_POST['other'];//举报其他理由
        $data['ru_id']=$_SESSION['Home']['Uid'];//举报用户的id
        // 获取当前时间
        $data['r_time']=date('Y-m-d H:i:s',time());

        $report=M('report');
        // 插入数据
        $res=$report->add($data);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }


}
