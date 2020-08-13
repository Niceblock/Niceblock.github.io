<?php
namespace Home\Controller;
use Think\Controller;
class RepulseController extends Controller {
    //被打回的文章前台显示页面
    public function repulse(){

        if (isset($_SESSION) && !empty($_SESSION['Home']['Uid'])) {
            //获取文章ID
            $wid=I('get.wid');
            //获取当前用户登录ID
            $uid=$_SESSION['Home']['Uid'];
            //实例化文章内容表
            $article=M('sc_article');
            //查询出所有被回的文章标题
            $titleres = $article->field('id,Title')->where("User_id=".$uid." and Repulse=1")->select();
            //查询数据
            if (!empty($wid)) {
                $where=" and id=".$wid;
            } else {
                $resone="";
            }
            //查询
            $res = $article->field('id,Content,Time,Title,Text')->where("User_id=".$uid." and Repulse=1".$where)->select()[0];
            //转换查询的数据
            
                //文章内容转html
                $res['Content']=htmlspecialchars_decode($res['Content']);
                //时间 
                if (date('Ymd',$res['Time'])==date('Ymd')) {
                    $res['Time']=date('今天H:i',$res['Time']);
                }elseif (date('Ymd',$res['Time'])==date('Ymd',time()-86400)) {
                    $res['Time']=date('昨天H:i',$res['Time']);
                }elseif (date('Ymd',$res['Time'])==date('Ymd',time()-172800)) {
                    $res['Time']=date('前天H:i',$res['Time']);
                }else{
                    $res['Time']=date('m-d H:i',$res['Time']);
                }       
            
            

            $this->assign('res',$res);
            $this->assign('titleres',$titleres);
            $this->display();
        } else {
            $this->error('请先登录再投稿',U('Home/Login/index'),3);
        }

    }


    
    //打回文章再次上传
    public function upload(){
        //标题不能为空
        if (empty($_POST['Title'])) {
            $this->error('文章标题必须填写,这有将利于您的投稿',U('Home/Write/index'),3);
            die;
        }
        //内容不能为空
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
        //清除管理员评论内容
        $_POST['Text']='';
        //重置打回标志
        $_POST['Repulse']=0;
        //实例化表
        $user=M('sc_article');
        //过滤数据
        $user->create();
        //更新数据
        $res=$user->save();
        //结果集判断
        if ($res) {
            $this->success('提交成功,请等待重新审核',U('Home/Science/index'),3);
        } else {
            $this->error('服务器繁忙,请稍后重试',U('Home/Repulse/repulse'),3);
        }
    }
}