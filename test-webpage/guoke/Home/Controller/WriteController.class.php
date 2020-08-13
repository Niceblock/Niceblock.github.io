<?php
namespace Home\Controller;
use Think\Controller;
class WriteController extends Controller {
    public function index(){
        if (isset($_SESSION) && !empty($_SESSION['Home']['Uid'])) {
            $user=M('sc_class');
            //查询顶级分类
            $res=$user->where('Class_Pid=0')->select();
            // var_dump($res);
            // 查询结果赋值给前台
            $this->assign('res',$res);
           $this->display();
        } else {
            $this->error('请登录',U('Home/Login/index'),3);
        }
    }
    public function ajaxclass(){
        $user=M('sc_class');
        //查询顶级分类
        $pid=I('post.Class_Pid');
        $res=$user->where('Class_Pid='.$pid)->select();
        // var_dump($res);
        if ($res) {
            echo json_encode($res);
        } else {
            echo 0;
        }
        
    }
    //文章上传
    public function update(){
        // var_dump($_POST);
        // die;
        // if (empty($_POST['User_Id'])) {
        //     $this->error('只有登录的用户才可以发表文章',U('Home/Write/index'),3);
        //     die;
        // }
        if (empty($_POST['Fid'])) {
            $this->error('文章分类必须选择,这有将利于您的投稿',U('Home/Write/index'),3);
            die;
        }
        if (empty($_POST['Title'])) {
            $this->error('文章标题必须填写,这有将利于您的投稿',U('Home/Write/index'),3);
            die;
        }
        if (!isset($_POST['Content']) || empty($_POST['Content'])) {
            $this->error('文章内容必须填写,这有将利于您的投稿',U('Home/Write/index'),3);
            die;
        }

        //获取被匹配的文本
        $content=$_POST['Content'];
        //正则表达式
        // var_dump($_POST);
        // die;
        $regular="/<img .*? src=.*? title=.*?\/>/";
        //执行正则找图片
        preg_match($regular,$content,$img);
        if (!isset($img[0]) || empty($img[0])) {
            $this->error('您的文章格式不正确,请核对后重新投稿',U('Home/Write/index'),3);
            die;
        }
        //传入图片
        $_POST['Img']=$img[0];
        //传入时间
        $_POST['Time']=time();
        //实例化表
        $user=M('sc_article');
        //过滤数据
        $user->create();
        //插入数据
        $res=$user->add();
        //结果集判断
        if ($res) {
            $this->success('提交成功成功,请等待审核',U('Home/Write/index'),3);
        } else {
            $this->error('添加失败,服务器繁忙,请稍后重试',U('Home/Write/index'),3);
        }
    }
}