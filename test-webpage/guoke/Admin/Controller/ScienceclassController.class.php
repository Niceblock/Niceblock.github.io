<?php
namespace Admin\Controller;
use Think\Controller;
//后台的文章分类管理控制器.
class ScienceclassController extends CommonController {
    //文章顶级分类显示板块
    public function index(){
        $user=M('sc_class');
        //查询顶级分类
        $res=$user->where('Class_Pid=0')->select();
        // 遍历判断每个顶级分类下面是否有子类
        foreach ($res as $k => $v) {
            //查询是否有子类
            $ress=$user->where('Class_Pid='.$v['id'])->select();
            //把是否显示改成文字
            if ($v['display']==1) {
                $res[$k]['display']='是';
            } else {
                $res[$k]['display']='否';
                
            }
            
            // 有子类标志为1
            if (!empty($ress)) {
                $res[$k]['son']=1;
            // 无子类标志为0
            }else{
                $res[$k]['son']=0;
            }
        }
        // var_dump($res);
        // die;
        // 查询结果赋值给前台
        $this->assign('res',$res);
        //解析模板
        $this->display();
    }

    //分类添加显示板块
    public function add(){
        if(IS_POST){
            $user=M('sc_class');
            // var_dump($_POST);
            // die;
            if (empty($_POST['Class_Title'])) {
                $this->error('分类名不允许为空',U('Admin/Science/index'));
            }
            $user->create();
            $res=$user->add();
            if ($res) {
                $this->success('添加成功',U('Admin/Scienceclass/index'),3);
            } else {
                $this->error('添加失败',U('Admin/Scienceclass/index'),3);
            }
            
        }else{
        //分类的添加页面显示
        $user=M('sc_class');
        //查询顶级分类
        $res=$user->where('Class_Pid=0')->select();
        $this->assign('res',$res);
        $this->display();
        }
    }
    //文章子分类显示板块
    public function subclass(){
        $user=M('sc_class');
        //接收GET
        $id=I('get.id');
        //查询ID对应分类名
        $class=$user->field('Class_Title')->find($id);
        $class=$class['Class_Title'];
        //查询子分类
        $res=$user->where('Class_Pid='.$id)->select();
        // 遍历
        foreach ($res as $k => $v) {
            //把是否显示改成文字
            if ($v['display']==1) {
                $res[$k]['display']='是';
            } else {
                $res[$k]['display']='否';
                
            }
        }
        if ($res) {
            //查询子分类给前台
            $this->assign('res',$res);
        } else {
            $this->assign('res','没有子分类');
        }
        
        //赋值父级分类给前台
        $this->assign('class',$class);
        

        //解析模板
        $this->display();
    }
    //ajax修改
    public function ajaxReva(){
        //把是否显示文本改成数字
        if ($_POST['display']=='是') {
            $_POST['display']=1;
        }else{
            $_POST['display']=0;
        }
        //实例化表
        $user=M('sc_class');
        //数据过滤
        $user->create();
        //修改
        $res=$user->save();
        echo $res;
        
    }
    //ajax删除
    public function ajaxDel(){
        $id=I('post.id');
        //查询是否有子类
        $user=M('sc_class');
        //
        $res=$user->where('Class_Pid='.$id)->select();
        if($res){
             echo '还有子类,不允许删除';
             die;
         }
        //删除
        $ress=$user->delete($id);
        if ($ress) {
            echo '删除成功';
        }else{
            echo '删除失败';
        }
    }
}