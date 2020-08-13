<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends PublicController {
    public function index(){

        
        // var_dump($_SESSION);

        //科学人遍历部分
        $article=M('sc_article');
        //查询出数据
        $articleres=$article->field('id,Title,Img')->order('id desc')->select();
        //转换为html
        foreach ($articleres as $k => $v) {
            $articleres[$k]['Img']=htmlspecialchars_decode($v['Img']);
        }
        //取5条,轮播图
        $articleimgres=array_slice($articleres,0,5);
        //取8条,标题
        $articletitleres=array_slice($articleres,5,8);
        

        //小组
        //
        //回答



        $this->assign('articleimgres',$articleimgres);
        $this->assign('articletitleres',$articletitleres);
        $this->display();

    }



    

}