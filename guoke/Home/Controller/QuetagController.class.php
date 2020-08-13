<?php
namespace Home\Controller;
use Think\Controller;
class QuetagController extends Controller {
    // 问题标签主页
    public function index(){
        // 接收用户登入id
        $uid=$_SESSION['Home']['Uid'];

        // 初始化一个数组
        $arr=array();

        $subtag=M('qac_tag');
        $tf=M('qac_tag_follow');

        // 根据uid查询关注的标签id
        $tf_id=$tf->where('uf_id='.$uid)->select();
        // 判断是否为空
        if(!empty($tf_id)){
            foreach ($tf_id as $v) {
                // 将标签id放入一个数组
                $arr[]=$v['tf_id'];
            }
        }
        // 接收get值
        $is_hot=!empty($_GET['is_hot'])?$_GET['is_hot']:1;
        $num=!empty($_GET['num'])?$_GET['num']:1;
        $state=$_GET['state'];
        
        // 判断state是否为空
        if(!empty($state)){
            $where="state=".$state;
        }else{
            // 查询
            $where='state !="1" and is_hot="1"';
        }

        // 查询满足条件总记录数
        $count=$subtag->where($where)->count();
        //实例化分页类
        $page=new \Think\Page($count,10);
        // 获取limit参数
        $limit=$page->firstRow.','.$page->listRows;

        $res=$subtag->where($where)->order('sub_num desc')->limit($limit)->select();
        if($res){
            foreach ($res as $k => $v) {
                $res[$k]['tag_msg']=htmlspecialchars($v['tag_msg']);
                // 标签id是否存在该数组内
                if(in_array($v['tag_id'],$arr)){
                    $res[$k]['isfollow']='1';
                }
            }
        }
        // // 分页输出
        $pages=$page->show();
        // var_dump($res);die;
        $this->assign('uid',$uid);
        $this->assign('res',$res);
        $this->assign('num',$num);
        $this->assign('pages',$pages);
        $this->display();
        
    }
    // 标签中心模块
    public function tag(){
        // 接收session值
        $uid=$_SESSION['Home']['Uid'];

        // 接收问题状态
        $state=!empty($_GET['num'])?$_GET['num']:1;

        // 接收get值
        $data['tag_id']=$_GET['tag_id'];
        if(empty($data['tag_id'])){
            $this->assign('uid',$uid);
            $this->display('nopage');
        }

        $tag=M('qac_tag');
        $tf=M('qac_tag_follow');

        $res=$tag->where($data)->select()[0];
        $msg=$res['tag_msg'];
        $res['tag_msg']=htmlspecialchars($res['tag_msg']);

        // 初始化一个数组
        $arr=array();
        $tf_id=$tf->where('uf_id='.$uid)->select();
        if(!empty($tf_id)){
            foreach ($tf_id as $v) {
               $arr[]=$v['tf_id'];
            }
            if(in_array($res['tag_id'],$arr)){
                $res['isfollow']='1';
            }
        }
        // 调取标签方法
        $this->question($data['tag_id'],$state);
        // 调取查询相关标签方法
        $relevant=$tag->field('tag_name,tag_id,sub_num,tag_img')->where('state <> 1')->order('sub_num desc')->limit(5)->select();
   
        $this->assign('relevant',$relevant);
        $this->assign('res',$res);
        $this->assign('msg',$msg);
        $this->assign('tag_id',$tag_id);
        $this->assign('uid',$uid);
        $this->display();
    }
    // 标签问题模块
    public function question($tag_id,$state){
        $ta=M('qac_tag_answer');
        $this->assign('state',$state);
        $where='';
        if($state==1){
            $state="s.answer_num desc";
        }elseif($state==2){
            $state="s.answer_num asc";
        }else{
            $where=' and s.answer=0';
        }
        // 查询满足条件总记录数
        $count=$ta->where('tag_id='.$tag_id)->count();
        //实例化分页类
        $page=new \Think\Page($count,10);
        // 获取limit参数
        $limit=$page->firstRow.','.$page->listRows;
        // 查询问题
        $que=$ta->alias('ta')->field('s.id,s.sub_name,s.sub_msg,s.follow_num,s.answer_num')->join('LEFT JOIN qac_subject s ON s.id=ta.sub_id')->where('ta.tag_id='.$tag_id.$where)->order($state)->limit($limit)->select();
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
            }else{
            $this->assign('no','该标签下暂无问题');
        }

    }

    // 添加标签关注
    public function follow(){
        // 接收session
        $data['uf_id']=!empty($_SESSION['Home']['Uid'])?$_SESSION['Home']['Uid']:die;

        //接收标签id
        $tag_id=$_GET['tag_id'];
        $data['tf_id']=$tag_id;

        $tf=M('qac_tag_follow');
        $tag=M();
        // 添加数据
        $res=$tf->add($data);
        // 判断是否添加成功
        if($res){
            // 修改该标签关注数
            $res=$tag->execute("update qac_tag set follow_num=follow_num+1 where tag_id='$tag_id'");
            if($res){
                echo 1;
            }else{
                echo 0;
            }
        }
    }

    // 取消标签关注
    public function isfollow(){
        $data['uf_id']=!empty($_SESSION['Home']['Uid'])?$_SESSION['Home']['Uid']:die;
        //接收标签id
        $tag_id=$_GET['tag_id'];
        $data['tf_id']=$tag_id;

        $tf=M('qac_tag_follow');
        $tag=M();
        $res=$tf->where($data)->delete();
        // 判断是否删除成功
        if($res){
            // 修改该标签关注数
            $res=$tag->execute("update qac_tag set follow_num=follow_num-1 where tag_id='$tag_id'");
            if($res){
                echo 1;
            }else{
                echo 0;
            }
        }
    }

    // 编辑头像
    public function editPic(){
        Uploads('tp');
        // var_dump($_FILES);
        //接收标签id 
        $data['tag_id']=$_POST['tag_id'];
         $tag=M('qac_tag');

        $filename=$tag->where($data)->select()[0]['tag_img'];
        // 判断传过来值是否为空
        $t['tag_img']=!empty($_POST['tp'])?$_POST['tp']:$filename;
        //判断数据库中是否有图片
        if(!empty($_POST['tp'])){
            if($filename!=null){
                echo $path=$_SERVER['DOCUMENT_ROOT'].'/Public'.$filename;
                unlink($path);
            }
        }
        $res=$tag->where($data)->save($t);
        if($res){
            header('location:'.$_SERVER['HTTP_REFERER']);
        }else{
            $this->error('修改失败','',2);
        }
    }
    // 编辑标签描述
    public function editmsg(){
        // var_dump($_POST);
        // 接收post值
        $data['tag_id']=!empty($_POST['tag_id'])?$_POST['tag_id']:die;
        $data['tag_msg']=!empty($_POST['tag_msg'])?$_POST['tag_msg']:die;

        $tag=M('qac_tag');
        $res=$tag->save($data);
        if($res){
            header('location:'.$_SERVER['HTTP_REFERER']);
        }else{
            $this->error('修改失败','',2);
        }
    }

    // 搜索标签
    public function search(){
        // 接收用户登入id
        $uid=$_SESSION['Home']['Uid'];
        // 接收表单值
        $tag_name=$_GET['search'];
        // 初始化一个数组
        $arr=array();

        $subtag=M('qac_tag');
        $tf=M('qac_tag_follow');

        // 根据uid查询关注的标签id
        $tf_id=$tf->where('uf_id='.$uid)->select();
        // 判断是否为空
        if(!empty($tf_id)){
            foreach ($tf_id as $v) {
                // 将标签id放入一个数组
                $arr[]=$v['tf_id'];
            }
        }
        if(!empty($tag_name)){
            $where="tag_name like '%$tag_name%'";
            // 查询满足条件总记录数
            $count=$subtag->where($where)->count();
            //实例化分页类
            $page=new \Think\Page($count,3);
            // 获取limit参数
            $limit=$page->firstRow.','.$page->listRows;

            $res=$subtag->where($where)->order('sub_num desc')->limit($limit)->select();
            if($res){
                foreach ($res as $k => $v) {
                    $res[$k]['tag_msg']=htmlspecialchars($v['tag_msg']);
                    // 标签id是否存在该数组内
                    if(in_array($v['tag_id'],$arr)){
                        $res[$k]['isfollow']='1';
                    }
                }
            }else{
                $res='暂无相关结果';
            }
            // var_dump($res);die;
            //分页输出
            $pages=$page->show();
            $this->assign('uid',$uid);
            $this->assign('tag_name',$tag_name);
            $this->assign('res',$res);
            $this->assign('pages',$pages);
        }
        $this->display();
    }


}
