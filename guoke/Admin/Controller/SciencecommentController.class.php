<?php
namespace Admin\Controller;
use Think\Controller;
//后台的文章评论管理控制器.
class SciencecommentController extends CommonController {
	//文章评论显示板块
    public function index(){
        $user=M('sc_comment');
        //获取每页显示的数量
        $num = !empty($_GET['num']) ? $_GET['num'] : 6;

        //获取关键字
        if(!empty($_GET['keyword'])){
            //有关键字
            $where = "sc_article.Title like '%".$_GET['keyword']."%'";
        }else{
            $where = "";
        }


        // 查询满足要求的总记录数
        $count = $user->where($where)->count();
        // echo $count;
        // 实例化分页类 传入总记录数和每页显示的记录数
        $Page = new \Think\Page($count,$num);
        //获取limit参数
        $limit = $Page->firstRow.','.$Page->listRows;
        //查询
        $res = $user->field('sc_comment.id,user_verify.User_Email,sc_article.id as wid,sc_article.Title,sc_comment.Deletes,sc_comment.Cm_Content,sc_comment.Cm_Time')->join('left join user_verify on sc_comment.Uid = user_verify.U_id')->join('left join sc_article on sc_comment.Wid = sc_article.id')->where($where)->order('sc_comment.id desc')->limit($limit)->select();
        foreach ($res as $k => $v) {
            $like=M('sc_like');
            $res[$k]['likeCount']=$like->where('Like_Lid='.$v['id'])->Count(); 
            if ($v['Deletes']==1) {
                $res[$k]['Deletes']='是';
            } else {
                $res[$k]['Deletes']='否';
            }
            $res[$k]['Cm_Content']=htmlspecialchars_decode($v['Cm_Content']);
            if (date('Ymd',$res[$k]['Cm_Time'])==date('Ymd')) {
                $res[$k]['Cm_Time']=date('今天H:i:s',$res[$k]['Cm_Time']);
            }elseif (date('Ymd',$res[$k]['Cm_Time'])==date('Ymd',time()-86400)) {
                $res[$k]['Cm_Time']=date('昨天H:i:s',$res[$k]['Cm_Time']);
            }elseif (date('Ymd',$res[$k]['Cm_Time'])==date('Ymd',time()-172800)) {
                $res[$k]['Cm_Time']=date('前天H:i:s',$res[$k]['Cm_Time']);
            }else{
                $res[$k]['Cm_Time']=date('Y-m-d H:i:s',$res[$k]['Cm_Time']);
            }
        }
        // var_dump($res);
        // die;
        // 分页显示输出
        $pages = $Page->show();
        foreach ($res as $k => $v) {
            $res[$k]['Img']=htmlspecialchars_decode($v['Img']);
            $res[$k]['Time']=date('Y年m月d日H点i分s秒',$v['Time']);
        }
        // echo $user->_sql();
        // var_dump($res);
        // die;
        // 查询结果赋值给前台
        $this->assign('res',$res);
        $this->assign('pages',$pages);
        //解析模板
        $this->display();
    }
    //修改评论显示版块
    public function reva(){
        //过滤接收ID
        $id=I('get.id');
        //实例化评论表
        $user=M('sc_comment');
        //查询数据
        $res=$user->field('id,Cm_Content')->where('id='.$id)->select();
        //评论内容转HTML
        $res[0]['Cm_Content']=htmlspecialchars_decode($res[0]['Cm_Content']);
        //二维变一维
        $res=$res[0];
        //数据赋值到前台
        $this->assign('res',$res);
        //解析模板
        $this->display();
    }
    //点赞用户显示版块
    public function see(){
        $user=M('sc_like');
        //查询
        $res = $user->field('sc_like.id,user_verify.User_Email')->join('left join user_verify on sc_like.Like_Uid = user_verify.U_id')->where('sc_like.Like_Lid='.$_GET['id'])->select();
        

        // 查询结果赋值给前台
        $this->assign('res',$res);
        //解析模板
        $this->display();
    }
    //评论内容修改处理版块
    public function update(){
        //实例化评论表
        $user=M('sc_comment');
        //过滤数据
        $user->create();
        //执行修改
        $res=$user->save();
        //返回结果集判断
        if ($res) {
            $this->success('修改成功',U('Admin/Sciencecomment/index'),3);
        } else {
            $this->error('修改失败',U('Admin/Sciencecomment/index'),3);
        }
    }
    //评论显示改隐藏修改版块
    public function ajaxupdatehide(){
        //过滤数据
        $id=I('post.id');
        //组修改数据
        $data['id']=$id;
        $data['Deletes']=1;
        //实例化评论表
        $user=M('sc_comment');
        //执行修改
        $res=$user->save($data);
        //结果集判断
        if ($res) {
            echo 1;
        } else {
            echo 0;
        }
    }
    //评论隐藏改显示修改版块
    public function ajaxupdatedisplay(){
        //过滤数据
        $id=I('post.id');
        //组修改数据
        $data['id']=$id;
        $data['Deletes']=0;
        //实例化评论表
        $user=M('sc_comment');
        //执行修改
        $res=$user->save($data);
        //结果集判断
        if ($res) {
            echo 1;
        } else {
            echo 0;
        }
    }
    //删除点赞用户
    public function ajaxdel(){
        //实例化点赞表
        $user=M('sc_like');
        //过滤数据
        $user->create();
        //删除
        $res=$user->delete();
        //结果集判断
        if ($res) {
            echo 1;
        } else {
            echo 0;
        }
    }
}