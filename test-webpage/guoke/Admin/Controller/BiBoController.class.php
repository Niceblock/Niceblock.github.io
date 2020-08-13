<?php
namespace Admin\Controller;
use Think\Controller;
//前台显示的模块管理控制器.
class BiBoController extends CommonController {


    public function index(){
        if(IS_POST){
            // var_dump($_POST);
            $zt=M('Bibo');
            if($_POST['Display']==1){
                $zt->create();
                $zt->save();
                // $zt->_sql();
                $this->ajaxReturn(1);
            }else{
                $zt->create();
                $zt->save();
                // $zt->_sql();
                $this->ajaxReturn(0);
            }
        }else{
            $p=I('get.page');
            if(empty($p)){
                $p='5';
            }

            if(!empty($_GET['keyword'])){
                //有关键字
                $where = "BiBo_title like '%".$_GET['keyword']."%'";
            }else{
                $where = '';
            }


            $user=M('Bibo');
            $count = $user->where($where)->count();
            $Page=new \Think\Page($count,$p);
            $PageShow=$Page->show();


            $res=$user->limit($Page->firstRow.','.$Page->listRows)->where($where)->select();
            $this->assign('res',$res);
            $this->assign('pages',$PageShow);
            $this->display();
        }
    }


    //添加模块
    public function add(){
        if(IS_POST){
            // var_dump($_POST);
            $B=M('Bibo');
            $where="`BiBo_title`='".$_POST['BiBo_title']."'";
            $r=$B->where($where)->find();
            if(!$r){
                $B->create();

                $res=$B->add();
                if($res){
                    $this->ajaxReturn(1);
                }
            }else{
                $this->ajaxReturn(2);
            }
            
        }else{
            $this->display();
        }
    }


    public function update(){
        // var_dump($_POST);
        $bu=M('Bibo');
        $bu->create();
        $res=$bu->save();
        // echo $bu->_sql();
        if(!$res){
         $this->ajaxReturn(1);
        }
    }





}