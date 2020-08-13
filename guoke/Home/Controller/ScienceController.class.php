<?php
namespace Home\Controller;
use Think\Controller;
class ScienceController extends Controller {
    public function index(){
        
        // var_dump($_GET);
        //用于瀑布流分类的选择
        $yid=I('get.id');
        //用于父分类查询子分类
        $tid=I('get.tid');
        // var_dump($yid);
        // var_dump($tid);
        // die;
        //实例化表
        $user=M('sc_class');
        //where条件,查询显示的父分类条件
        $where="Class_Pid=0 and display=1";
        //查询父分类
        $res = $user->field('id,Class_Title')->where($where)->select();
        //查询是否有子类,有子类给标识,用于显示三角形
        foreach ($res as $k => $v) {
            $sonres=$user->where("Class_Pid=".$v['id']." and display=1")->select();
            if ($sonres) {
                $res[$k]['sonres']=1;
            }else{
                $res[$k]['sonres']=0;
            }
        }
        //利用父分类查询子分类显示
        if (!empty($tid)) {
            $pname=$user->field('Class_Title')->where("id=".$tid)->select();
            $pname=$pname[0]['Class_Title'];
            $sonval=$user->field('id,Class_Title')->where("Class_Pid=".$tid." and display=1")->select();
            $this->assign('pname',$pname);
            $this->assign('sonval',$sonval);
        }
        
        // var_dump($sonval);
        // die;
        // 结果给前台
        $this->assign('yid',$yid);
        $this->assign('res',$res);

        //解析模板
       $this->display();
    }
    //ajax瀑布流
    public function ajaxwaterfall(){
            //初始化$where
            $where="";
            //过滤id
            $id=I('post.id');
            //初始化表
            $user=M('sc_article');
            //获取每页显示的数量
            $num = 6;
            //where条件,只要上线的文章
            //id>0代表分类下有内容,=0代表查看所有内容
            if ($id>0) {
                // 实例化表
                $users=M('sc_class');
                //查出子分类
                $cpid=$users->field('id')->where('Class_Pid='.$id)->select();
                //如果有子分类
                if (!empty($cpid)) {
                    //拼接条件
                    foreach ($cpid as $k => $v) {
                        $where.="sc_article.Fid=".$v['id']." or ";
                    }
                    //去除最后的无用OR
                    $where=rtrim($where,' or ');
                    //拼接上线分类
                    $where.=" and sc_article.Display=1 and sc_class.display=1";
                    // var_dump($where);
                    // die;
                }else{
                    //否则,查看当前类上线分类内容
                    $where="sc_article.Fid=".$id." and sc_article.Display=1 and sc_class.display=1";
                }
            }else{
                //否则,查看所有上线内容
               $where="sc_article.Display=1 and sc_class.display=1";
            }
            // echo $count;
            //过滤P
            $p=I('post.p');
            //limit从开始位
            $number=$num*$p;
            //查询瀑布流显示内容
            $res = $user->field('Class_Pid,Img,sc_article.id,Time,Title,Class_Title,Content,User_Id,User_Nickname')->join('left join user_info on sc_article.User_Id = user_info.U_PID')->join('left join sc_class on sc_article.Fid= sc_class.id')->where($where)->order('sc_article.id desc')->limit($number,$num)->select();
            // 转换数据中的文本和时间
            foreach ($res as $k => $v) {
                //取随机数用于截取文章内容作外部缩略显示
                $num=rand(100,200);
                //转换字符,缩略图片
                $res[$k]['Img']=htmlspecialchars_decode($v['Img']);
                //截取文章内容作为外部缩略显示
                $res[$k]['Content']=mb_substr(strip_tags(htmlspecialchars_decode($v['Content'])),0,$num,'utf-8').'...';
                //格式化时间
                if (date('Ymd',$v['Time'])==date('Ymd')) {
                    $res[$k]['Time']=date('今天H:i',$v['Time']);
                }elseif (date('Ymd',$rv['Time'])==date('Ymd',time()-86400)) {
                    $res[$k]['Time']=date('昨天H:i',$v['Time']);
                }elseif (date('Ymd',$v['Time'])==date('Ymd',time()-172800)) {
                    $res[$k]['Time']=date('前天H:i',$v['Time']);
                }else{
                    $res[$k]['Time']=date('m-d H:i',$rv['Time']);
                }
                //如果不是父分类
                if ($v['Class_Pid']!=0) {
                    //实例化表
                    $userc=M('sc_class');
                    //查出父分类
                    $vals=$userc->field('id','Class_Title')->where('id='.$v['Class_Pid'])->select();
                    //结果集加入$res数组
                    $res[$k]['pcname']=$vals[0]['Class_Title'];
                }else{
                     $res[$k]['pcname']='';   
                }
            }
            // echo $user->_sql();
            // var_dump($p);
            // var_dump($res);
            //结果返回前台 
            echo json_encode($res);
    }
    public function ajaxclass(){
        $id=I('post.id');        
        $user=M('sc_class');
        $where='Class_Pid='.$id;
        $val=$user->field('Class_Title')->where('id='.$id)->select();
        $res = $user->field('id,Class_Title')->where($where)->select();
        if ($val && $res) {
             $cval[]=$val[0]['Class_Title'];
             $cval[]=$res[0];
             // var_dump($cval);
             echo json_encode($cval);
        } else {
            $cval[]='此分类没有东西';
            echo json_encode($cval);
        }
        

    }
    
}