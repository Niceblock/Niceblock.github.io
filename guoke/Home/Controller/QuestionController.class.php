<?php
namespace Home\Controller;
use Think\Controller;
class QuestionController extends Controller {
    // 问题首页模块
    public function index(){
        $uid=$_SESSION['Home']['Uid'];
        $sub=M('qac_subject');
        $tag=M('qac_tag');
        $ans=M('qac_answer');
        // 等待回答问题
        $wait=$sub->where('is_examine=1 and answer_num>1')->order('sub_time desc')->limit(8)->select();
        // 热门问题
        $hot=$sub->where('is_hot=1')->order('sub_time desc')->limit(10)->select();
        // 精彩问题
        $wonderful=$sub->where('is_wonderful=1')->order('sub_time desc')->limit(10)->select();
        // 人文社科标签
        $renwen=$tag->where('state="2" and is_hot="1"')->order('sub_num desc')->limit(6)->select();
        // 科学技术标签
        $kexue=$tag->where('state="3" and is_hot="1"')->order('sub_num desc')->limit(6)->select();
         // 生活娱乐
        $life=$tag->where('state="4" and is_hot="1"')->order('sub_num desc')->limit(6)->select();
         // 自然生态
        $ziran=$tag->where('state="5" and is_hot="1"')->order('sub_num desc')->limit(6)->select();
        // 最新的回答
        $newans=$ans->alias('a')->join('LEFT JOIN user_info u ON u.U_PID=a.user_id')->join('LEFT JOIN qac_subject s ON s.id=a.sub_id')->order('a.ans_time desc')->limit(6)->select();
        // 大家喜欢
        $likeans=$ans->alias('a')->join('LEFT JOIN user_info u ON u.U_PID=a.user_id')->join('LEFT JOIN qac_subject s ON s.id=a.sub_id')->where('a.is_like="1"')->order('a.zan desc')->limit(4)->select();
        // 等待支持
        $waitans=$ans->alias('a')->join('LEFT JOIN user_info u ON u.U_PID=a.user_id')->join('LEFT JOIN qac_subject s ON s.id=a.sub_id')->where('a.zan >=3 and a.is_like="0"')->order('a.ans_time desc')->limit(4)->select();
        // 最佳回答者
        $optimum=$ans->alias('a')->join('LEFT JOIN user_info u ON u.U_PID=a.user_id')->join('LEFT JOIN user_profile p ON p.U_PID=a.user_id')->order('a.zan desc')->limit(6)->select();
        // var_dump($newans);die;
        $this->assign('renwen',$renwen);
        $this->assign('optimum',$optimum);
        $this->assign('kexue',$kexue);
        $this->assign('ziran',$ziran);
        $this->assign('life',$life);
        $this->assign('waitans',$waitans);
        $this->assign('likeans',$likeans);
        $this->assign('newans',$newans);
        $this->assign('wait',$wait);
        $this->assign('uid',$uid);
        $this->assign('hot',$hot);
        $this->assign('wonderful',$wonderful);

        $this->display();
        
    }
    // 添加问题页面
    public function add(){
        $uid=$_SESSION['Home']['Uid'];
        if(empty($uid)){
            header('location:'.U('Home/Login/index'));
        }else{
            $this->assign('uid',$uid);
            $this->display();
        }
    }
    // 添加问题模块
    public function insert(){
        // 接收用户id
        $arr['user_id']=!empty($_SESSION['Home']['Uid'])?$_SESSION['Home']['Uid']:die;
        // 接收post数据
        $arr['sub_name']=!empty($_POST['question'])?$_POST['question']:die;
        $arr['sub_msg']=$_POST['sub_msg'];
        $arr['sub_time']=date('Y-m-d H:i:s',time());
        // 接收标签id
        $tagid=$_POST['tags'];

        $tanswer=M('qac_tag_answer');
        $qf=M('qac_follow');
        $model=M();
        $tag=M('qac_subject');
        // 添加问题
        $res=$tag->add($arr);
        if($res){
            // 关联问题和标签
            if(!empty($tagid)){
                $tagarr=explode(',',$tagid);
                $count=count($tagarr);
                for ($i=0; $i <= $count-1; $i++) { 
                    // 问题id
                    $data['sub_id']=$res;
                    // 标签id
                    $data['tag_id']=$tagarr[$i];
                    //增加该标签问题数
                    $model->execute("update qac_tag set sub_num=sub_num+1 where tag_id='$tagarr[$i]'");
                    //插入到表中
                    $tanswer->add($data);
                }
            }
            // 关联用户和问题关注
            $quef['sub_id']=$res;
            $quef['fu_id']=$arr['user_id'];
            $qacf=$qf->add($quef);
            if($qacf){
                $model->execute("update qac_subject set follow_num=follow_num+1 where id='$res'");
            }
            header('location:'.U('Home/Question/subject',array('id'=>$res)));
        }

    }
    // 添加问题标签
    public function addtag(){
       $data['tag_name']=!empty($_POST['tag_name'])?$_POST['tag_name']:die;

        $tag=M('qac_tag');
        // 查询是否有该标签名
        $res=$tag->where($data)->select();
        if($res){
            echo $res[0]['tag_id'];
        }else{
            $num=$tag->add($data);
            echo $num;
        }
    }
    // 发现问题模块
    public function question(){
        // 接收用户登入id
        $uid=$_SESSION['Home']['Uid'];
        // 接收get值
        $num=!empty($_GET['num'])?$_GET['num']:1;
        $this->assign('num',$num);
        $this->assign('uid',$uid);
        $ans=M('qac_answer');
        // 判断
        if($num==1){
            $where_count="is_hot='1' and is_examine='1' and is_examine='1'";
            $where="is_hot='1' and is_examine='1' and is_examine='1'";
        }elseif($num==2){
            $where_count="is_wonderful='1' and is_examine='1' and is_examine='1'";
            $where="is_wonderful='1' and is_examine='1' and is_examine='1'";
        }elseif($num==3){
            // 查询满足条件总记录数
            $count=$ans->where('is_like="1"')->count();
            //实例化分页类
            $page=new \Think\Page($count,10);
            // 获取limit参数
            $limit=$page->firstRow.','.$page->listRows;
            // 大家喜欢
            $likeans=$ans->alias('a')->join('LEFT JOIN user_info u ON u.U_PID=a.user_id')->join('LEFT JOIN qac_subject s ON s.id=a.sub_id')->where('a.is_like="1"')->order('a.zan desc')->limit($limit)->select();
            $pages=$page->show();
            $this->assign('likeans',$likeans);
            $this->assign('pages',$pages);
            $this->display();
            die;
        }elseif($num==4){
            // 查询满足条件总记录数
            $count=$ans->where('zan >=3 and is_like="0"')->count();
            //实例化分页类
            $page=new \Think\Page($count,10);
            // 获取limit参数
            $limit=$page->firstRow.','.$page->listRows;
              // 等待支持
            $waitans=$ans->alias('a')->join('LEFT JOIN user_info u ON u.U_PID=a.user_id')->join('LEFT JOIN qac_subject s ON s.id=a.sub_id')->where('a.zan >=3 and a.is_like="0"')->order('a.ans_time desc')->limit($limit)->select();            
            $pages=$page->show();
            $this->assign('waitans',$waitans);
            $this->assign('pages',$pages);
            $this->display();
            die;
        }

        $sub=M('qac_subject');
        $ta=M('qac_tag_answer');
        // 查询满足条件总记录数
        $count=$sub->where($where_count)->count();
        //实例化分页类
        $page=new \Think\Page($count,10);
        // 获取limit参数
        $limit=$page->firstRow.','.$page->listRows;
        // 查询问题
        $que=$sub->field('id,sub_name,sub_msg,follow_num,answer_num')->where($where)->order('sub_time asc')->limit($limit)->select();
        // 初始化
        $t=array();
        $i=0;
        if($que){
            foreach ($que as $v) {
                // 查询标签
                $t[]=$ta->alias('ta')->join('LEFT JOIN qac_tag t ON t.tag_id=ta.tag_id')->where('ta.sub_id='.$v['id'])->select();
            }
            // 将标签信息压入数组
            foreach ($que as $k => $v) {
                $que[$k]['tag_name']=$t[$i];
                $i++;
            }
            $pages=$page->show();
            $this->assign('pages',$pages);
            $this->assign('que',$que);
        }
        $this->assign('uid',$uid);
        $this->display();
    }

    // 最新问题模块
    public function newque(){
        // 接收用户登入id
        $uid=$_SESSION['Home']['Uid'];
        $sub=M('qac_subject');
        $ta=M('qac_tag_answer');
        // 查询满足条件总记录数
        $count=$sub->where("is_hot='0' and is_wonderful='0'")->count();
        //实例化分页类
        $page=new \Think\Page($count,10);
        // 获取limit参数
        $limit=$page->firstRow.','.$page->listRows;
        // 查询问题
        $que=$sub->field('id,sub_name,sub_msg,follow_num,answer_num,sub_time')->where("is_hot='0' and is_wonderful='0'")->order('sub_time desc')->limit($limit)->select();

        // 初始化
        $t=array();
        $i=0;
        if($que){
            foreach ($que as $v) {
                // 查询标签
                $t[]=$ta->alias('ta')->join('LEFT JOIN qac_tag t ON t.tag_id=ta.tag_id')->where('ta.sub_id='.$v['id'])->select();
            }
            // 将标签信息压入数组
            foreach ($que as $k => $v) {
                $que[$k]['tag_name']=$t[$i];
                $i++;
            }
            $pages=$page->show();
            $this->assign('pages',$pages);
            $this->assign('que',$que);
        }
        $this->assign('uid',$uid);
        $this->display();
    }

    // 等待回答
    public function waitque(){
        // 接收用户登入id
        $uid=$_SESSION['Home']['Uid'];
        $sub=M('qac_subject');
        $ta=M('qac_tag_answer');
        // 查询满足条件总记录数
        $count=$sub->where("is_hot='0' and is_wonderful='0' and answer_num>=1 and is_examine='1'")->count();
        //实例化分页类
        $page=new \Think\Page($count,10);
        // 获取limit参数
        $limit=$page->firstRow.','.$page->listRows;
        // 查询问题
        $que=$sub->field('id,sub_name,sub_msg,follow_num,answer_num,sub_time')->where("is_hot='0' and is_wonderful='0' and answer_num>=1 and is_examine='1'")->order('sub_time desc')->limit($limit)->select();
        // 初始化
        $t=array();
        $i=0;
        if($que){
            foreach ($que as $v) {
                // 查询标签
                $t[]=$ta->alias('ta')->join('LEFT JOIN qac_tag t ON t.tag_id=ta.tag_id')->where('ta.sub_id='.$v['id'])->select();
            }
            // 将标签信息压入数组
            foreach ($que as $k => $v) {
                $que[$k]['tag_name']=$t[$i];
                $i++;
            }
            $pages=$page->show();
            $this->assign('pages',$pages);
            $this->assign('que',$que);
        }
        $this->assign('uid',$uid);
        $this->display();
    }
    // 问题页面模块
    public function subject(){
        // 接收问题id
        $sub_id=$_GET['id'];
        if(empty($sub_id)){
            header('location:'.U('Home/Quetag/nopage'));
            die;
        }
        // 接收用户登入id
        $uid=$_SESSION['Home']['Uid'];

        $sub=M('qac_subject');
        $ta=M('qac_tag_answer');
        $quef=M('qac_follow');
        $ans=M('qac_answer');
        $zan=M('qac_zan');
        $user=M('user_info');

        // 查询登入用户信息
        $uinfo=$user->where('U_PID='.$uid)->select()[0];
        // 查询登入用户是否在该问题添加答案
        $isans=$ans->where('sub_id='.$sub_id.' and user_id='.$uid)->select();
        // 查询最佳问答
        $optsub=$sub->where('answer_num > 10')->limit(mt_rand(0,10),5)->select();
        // 查询热门回答
        $hotsub=$sub->where('is_hot="1"')->order('id desc')->limit(5)->select();
        // 判断是否登入
        if(!empty($uid)){
            $where=' and a.user_id <> '.$uid;
        }else{
            $where='';
        }
        // 查询问题
        $que=$sub->alias('s')->field('s.id,s.answer_num,s.sub_name,s.sub_msg,s.follow_num,s.user_id,u.User_Nickname,p.User_pic')->join('LEFT JOIN user_info u ON u.U_PID=s.user_id')->join('LEFT JOIN user_profile p ON p.U_PID=s.user_id')->where("id=".$sub_id)->select();
        // var_dump($que);die;
        // 初始化
        $t=array();
        $i=0;
        if($que){
            $this->assign('que',$que[0]);
            // 查询标签
            $t[]=$ta->alias('ta')->join('LEFT JOIN qac_tag t ON t.tag_id=ta.tag_id')->where('ta.sub_id='.$sub_id)->select();
            // 将标签信息压入数组
            foreach ($que as $k => $v) {
                $que[$k]['tag_name']=$t[$i];
                $i++;
            }
            $this->assign('que',$que[0]);
            // 查询标签下的问题
            $question=$ta->alias('ta')->join('LEFT JOIN qac_subject s ON s.id=ta.sub_id')->where('ta.tag_id='.$que[0]['tag_name'][0]['tag_id'])->limit(5)->select();
            if($question){
                $this->assign('question',$question);
            }else{
                $question=$sub->where('is_examine="1"')->order('id desc')->limit(5)->select();
                $this->assign('question',$question);
            }
        }
        // 检测登入用户是否关注该问题
        $follow=$quef->where('sub_id='.$sub_id.' and fu_id='.$uid)->select();

        if($follow){
            $this->assign('follow','1');
        }else{
            $this->assign('follow','0');
        }
        $usupport=array();
        // 查询该问题下除了登入用户的所有答案
        $res=$ans->alias('a')->join('LEFT JOIN user_info u ON u.U_PID=a.user_id')->join('LEFT JOIN user_profile p ON p.U_PID=a.user_id')->where('a.sub_id = '.$sub_id.$where)->select();
        // var_dump($res);die;
        // 查询当前登入用户的答案信息
        $uans=$ans->alias('an')->join('LEFT JOIN user_info u ON u.U_PID=an.user_id')->join('LEFT JOIN user_profile p ON p.U_PID=an.user_id')->where('an.sub_id = '.$sub_id.' and an.user_id = '.$uid.' and p.State="1"')->select()[0];
        // 查询登入用户支持者
        $z=$zan->where('ans_id='.$uans['ans_id'])->select();
        if($z){
            foreach ($z as $k => $v) {
                $usupport[]=$user->where('U_PID='.$v['user_id'])->select();
            }
            $uans['support']=$usupport;
        }
        $support=array();
        $a=0;
        // 查询除了登入用户所有用户支持者
        if($res){
            foreach($res as $v){
                $res[$a]['sup']=$zan->alias('z')->join('LEFT JOIN user_info u ON u.U_PID=z.user_id')->where('z.ans_id='.$v['ans_id'])->select();
                $a++;
                
            }
        }
        // var_dump($res);die;
        // var_dump($res[0]['sup']);die;
        $this->assign('answers',$res);
        $this->assign('uans',$uans);

        $this->assign('optsub',$optsub);
        $this->assign('hotsub',$hotsub);
        $this->assign('uinfo',$uinfo);
        $this->assign('isans',$isans);
        $this->assign('uid',$uid);
        $this->display();
    }

    // 添加答案模块
    public function addanswer(){
        // 接收用户登入id
        $data['user_id']=$_SESSION['Home']['Uid'];
        // 接收post值
        $data['ans_msg']=$_POST['answer'];
        $sub_id=$data['sub_id']=$_POST['sub_id'];
        // 设置当前时间
        $data['ans_time']=date('Y-m-d H:i:s',time());

        $ans=M('qac_answer');
        $sub=M('qac_subject');
        $question=M();
        $as=$ans->where('user_id='.$data['user_id'].' and sub_id='.$data['sub_id'])->select();
        if(!$as){
            // 添加问题答案
            $res=$ans->add($data);
            $question->execute("update qac_subject set answer_num=answer_num+1 where id='$sub_id'");
        }
        // 查询问题
        $que=$sub->field('id,sub_name,answer_num')->where("is_hot='0' and is_wonderful='0' and answer_num>=1 and is_examine='1'")->order('sub_time desc')->limit(15)->select();
        $this->assign('que',$que);
        $this->assign('sub_id',$data['sub_id']);
        $this->display();

    }
    // 答案支持
    public function zan(){
        // 接收用户登入id
        $uid=$_SESSION['Home']['Uid'];
        // 接收post值
        $ansid=$_POST['ansid'];

        $zan=M('qac_zan');
        $ans=M();

        //查询点赞表
        $res=$zan->where('ans_id='.$ansid.' and user_id='.$uid)->select();
        if($res){
            $res=$zan->where('ans_id='.$ansid.' and user_id='.$uid)->delete();
            if($res){
                $ans->execute("update qac_answer set zan=zan-1 where ans_id='$ansid'");
                echo 0;
            }
        }else{
            $data['ans_id']=$ansid;
            $data['user_id']=$uid;
            $res=$zan->add($data);
            if($res){
                $ans->execute("update qac_answer set zan=zan+1 where ans_id='$ansid'");
                echo 1;
            }
        }
    }

    // 修改回答
    public function editans(){
        // 接收值
        $data['ans_id']=$_POST['ansid'];
        $data['ans_msg']=$_POST['ansname'];
        // 接收问题id
        $subid=$_POST['subid'];

        $ans=M('qac_answer');
        // 执行修改
        $res=$ans->save($data);
        header('location:'.U('Home/Question/subject',array('id'=>$subid)));
    }
    // 删除回答
    public function delans(){
        // 获取回答id
        $ansid=$_POST['ansid'];
        $subid=$_POST['subid'];

        $answer=M('qac_answer');
        $zan=M('qac_zan');
        $sub=M();
        // 删除
        $res=$answer->where('ans_id='.$ansid)->delete();
        if($res){
            $zan->where('ans_id='.$ansid)->delete();
            $sub->execute("update qac_subject set answer_num=answer_num-1 where id='$subid'");
            echo 1;
        }else{
            echo 0;
        }
    }
    // 添加问题关注
    public function follow(){
        // 接收session
        $data['fu_id']=!empty($_SESSION['Home']['Uid'])?$_SESSION['Home']['Uid']:die;

        //接收问题id
        $sub_id=$_GET['sub_id'];
        $data['sub_id']=$sub_id;

        $tf=M('qac_follow');
        $tag=M();
        // 添加数据
        $res=$tf->add($data);
        // 判断是否添加成功
        if($res){
            // 修改该问题关注数
            $res=$tag->execute("update qac_subject set follow_num=follow_num+1 where id='$sub_id'");
            if($res){
                echo 1;
            }else{
                echo 0;
            }
        }
    }

    // 取消问题关注
    public function isfollow(){
        $data['fu_id']=!empty($_SESSION['Home']['Uid'])?$_SESSION['Home']['Uid']:die;
        //接收问题id
        $sub_id=$_GET['sub_id'];
        $data['sub_id']=$sub_id;

        $tf=M('qac_follow');
        $tag=M();
        $res=$tf->where($data)->delete();
        // 判断是否删除成功
        if($res){
            // 修改该问题关注数
            $res=$tag->execute("update qac_subject set follow_num=follow_num-1 where id='$sub_id'");
            if($res){
                echo 1;
            }else{
                echo 0;
            }
        }
    }

    // 修改问题名字
    public function editname(){
        // 接收post值
        $data['id']=$_POST['subid'];
        $data['sub_name']=$_POST['newname'];
        $sub=M('qac_subject');
        $res=$sub->save($data);
        header('location:'.U('Home/Question/subject',array('id'=>$data['id'])));
    }

    // 修改问题标签
    public function edittag(){
       $data['tag_name']=!empty($_POST['tag_name'])?$_POST['tag_name']:die;
       $subid=$_POST['subid'];
       $subtag['sub_id']=$subid;

        $tag=M('qac_tag');
        $ta=M('qac_tag_answer');
        // 查询是否有该标签名
        $res=$tag->where($data)->select();
        if($res){
            $id=$res[0]['tag_id'];
            // 查询该问题有无该标签
            $out=$ta->where('sub_id='.$subid.' and tag_id='.$id)->select();
            $subtag['tag_id']=$id;
            if(!$out){
                $ta->add($subtag);
                echo $id;
            }
        }else{
            $num=$tag->add($data);
            if($num){
                $subtag['tag_id']=$num;
                // 查询该问题有无该标签
                $out=$ta->where('sub_id='.$subid.' and tag_id='.$id)->select();
                $subtag['tag_id']=$num;
                if(!$out){
                    $ta->add($subtag);
                    echo $num;
                }
            }
        }
    }

    // 删除问题标签
    public function deltag(){
        // 接收post值
        $data['sub_id']=$_POST['subid'];
        $data['tag_id']=$_POST['tagid'];
        $ta=M('qac_tag_answer');
        $res=$ta->where($data)->delete();
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }

    // 修改问题描述
    public function editmsg(){
        // 接收post值
        $data['id']=$_POST['subid'];
        $data['sub_msg']=$_POST['sub_msg'];

        $sub=M('qac_subject');
        $res=$sub->save($data);
        if($res){
            header('location:'.U('Home/Question/subject',array('id'=>$data['id'])));
        }
    }

    // 个人中心
    public function center(){
        // 接收用户登入id
        $mid=$_SESSION['Home']['Uid'];
        // 接收用户id
        $uid=$_GET['uid'];
        $myid=$_GET['mid'];

        $ans=M('qac_answer');
        $uinfo=M('user_info');

        if(empty($myid) && empty($uid)){
            header('location:'.U('Home/Quetag/nopage'));
            die;
        }

        if(!empty($myid)){
            // 查询登入用户信息
            $user=$uinfo->alias('i')->join('LEFT JOIN user_profile p ON p.U_PID=i.U_PID')->where('i.U_PID='.$myid)->select();
            $this->assign('user',$user);
            // 查询用户回答
            $answer=$ans->alias('a')->join('LEFT JOIN qac_subject s ON s.id=a.sub_id')->where('a.user_id='.$myid)->select();
        }
        $this->assign('uid',$mid);
        $this->display();
    }
}
