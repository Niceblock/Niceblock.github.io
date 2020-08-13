<?php
namespace Admin\Controller;
use Think\Controller;
//后台的文章审核管理控制器.
class ScienceauditController extends CommonController {
    //文章审核显示板块
    public function index(){
        $user=M('sc_article');
        //获取每页显示的数量
        $num = !empty($_GET['num']) ? $_GET['num'] : 6;

        //获取关键字
        if(!empty($_GET['keyword'])){
            //有关键字
            $where = "sc_article.`Display`='0' and sc_article.Repulse='0' and Title like '%".$_GET['keyword']."%'";
        }else{
            $where = "sc_article.Display='0' and sc_article.Repulse='0'";
        }


        // 查询满足要求的总记录数
        $count = $user->where($where)->count();
        // echo $count;
        // 实例化分页类 传入总记录数和每页显示的记录数
        $Page = new \Think\Page($count,$num);
        //获取limit参数
        $limit = $Page->firstRow.','.$Page->listRows;
        //查询
        $res = $user->field('Img,sc_article.id,User_Email,Time,Title,Class_Title')->join('left join user_verify on sc_article.User_Id = user_verify.U_id')->join('left join sc_class on sc_article.Fid = sc_class.id')->where($where)->limit($limit)->select();

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

    //审核成功板块
    public function ajaxaudit(){
            //增加审核成功标识
            $_POST['Display']=1;
            //实例化文章表
            $user=M('sc_article');
            //过滤数据
            $user->create();
            //更新
            $res=$user->save();
            // 结果集判断
            if ($res) {
                echo 1;
            } else {
                echo 0;
            }
            
    }
    //文章内容显示板块
    public function content(){
        $user=M('sc_article');
        //接收GET
        $id=I('get.id');
        // echo $id;
        // die;
        //查询标题和文章内容
        $res=$user->field('Title,Content')->find($id);
        // var_dump($res);
        //赋值标题
        $title=$res['Title'];
        //赋值内容,转HTML
        $content=htmlspecialchars_decode($res['Content']);
        // die;
        
        //将结果赋值给前台
        $this->assign('title',$title);
        $this->assign('content',$content);
        //解析模板
        $this->display();
    }
    
    //文章打回意见处理
    public function repulse(){
        //增加打回标识
        $_POST['Repulse']=1;
        //实例化文章表
        $article=M('sc_article');
        //过滤数据
        $article->create();
        //更新数据
        $res=$article->save();
        //结果集判断
        if ($res) {
            $this->success('打回成功',U('Admin/Scienceaudit/index'),3);
        } else {
            $this->error('系统繁忙,请稍后重试',U('Admin/Scienceaudit/index'),3);
        }
    }
}