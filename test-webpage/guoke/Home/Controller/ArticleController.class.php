<?php
namespace Home\Controller;
use Think\Controller;
class ArticleController extends Controller {
    //文章详情页,评论
    public function index(){
        //获取P标识用于楼层初始化
        $p=I('get.p');
        //没有P传值的时候初始化
        $p=empty($p)?0:$p-1;
        //过滤id
        $id=I('get.id');
        
        
        //cookie 用于判断是否已点击查看评论
        if (cookie('wid')==$id) {
            $style="style='display:none'";
            $styles="style='display:block'";
            $this->assign('style',$style);
            $this->assign('styles',$styles);
        }
        //实例化表
        $user=M('sc_article');
        //查询
        $res = $user->field('Class_Pid,sc_article.id,Time,Title,Class_Title,Content,User_Id,User_Nickname,user_profile.User_pic')->join('left join user_info on sc_article.User_Id = user_info.U_PID')->join('left join sc_class on sc_article.Fid= sc_class.id')->where('sc_article.id='.$id)->join('left join user_profile on sc_article.User_Id= user_profile.U_PID')->where('sc_article.id='.$id.' and user_profile.State=1')->order('sc_article.id desc')->limit($number,$num)->select();
        
        // 转换数据中的文本和时间
        foreach ($res as $k => $v) {
            $res[$k]['Content']=htmlspecialchars_decode($res[$k]['Content']);
            if (date('Ymd',$res[$k]['Time'])==date('Ymd')) {
                $res[$k]['Time']=date('今天H:i',$res[$k]['Time']);
            }elseif (date('Ymd',$res[$k]['Time'])==date('Ymd',time()-86400)) {
                $res[$k]['Time']=date('昨天H:i',$res[$k]['Time']);
            }elseif (date('Ymd',$res[$k]['Time'])==date('Ymd',time()-172800)) {
                $res[$k]['Time']=date('前天H:i',$res[$k]['Time']);
            }else{
                $res[$k]['Time']=date('m-d H:i',$res[$k]['Time']);
            }
        }
        //二维变一维
        $res=$res[0];
        //查询该作者其他文章
        $other=$user->field('sc_article.id,Title')->where('User_Id='.$res['User_Id'])->select();
        //只取其中5篇文章
        $other=array_chunk($other,5)[0];
        
        

        //查询评论
        //实例化评论表
        $comment=M('sc_comment');
        //查询此文章的评论
        
        //设置每页显示的数量
        $num = 4;
        // 查询满足要求的总记录 数
        $count = $comment->where('Deletes=0 and Wid='.$id)->count();
        // 实例化分页类 传入总记录数和每页显示的记录数
        $Page = new \Think\Page($count,$num);
        //获取limit参数
        $limit = $Page->firstRow.','.$Page->listRows;
        //查询
        $commentres = $comment->field('sc_comment.id,user_info.User_Nickname,user_info.U_PID,sc_comment.Pid,sc_comment.Cm_Content,sc_comment.Cm_Time,user_profile.User_pic')->join('right join user_info on sc_comment.Uid = user_info.U_PID')->join('right join user_profile on sc_comment.Uid= user_profile.U_PID')->where("sc_comment.Deletes=0 and sc_comment.Wid=".$id." and user_profile.State=1")->limit($limit)->select();
        //热门评论条件,被赞大于多少
        $hotnum=1;
        //初始化楼层标识
        $i=$num*$p;
        //循环改变参数和添加参数
        foreach ($commentres as $k => $v) {
            //初始化点赞表
            $like=M('sc_like');
            //判断当前用户是否登录
            if (isset($_SESSION['Home']) && !empty($_SESSION['Home'])) {
                //查询当前登录用户是否已对当前评论点过赞
                $likeres=$like->where('Like_Lid='.$v['id'].' and Like_Uid='.$_SESSION['Home']['Uid'])->select();
                if ($likeres) {
                    // 点过赞
                    $commentres[$k]['ynz']=1;
                } else {
                    // 没点过
                    $commentres[$k]['ynz']=0;
                }
            }
            //点赞数量
            
            //给每一条评论查询点赞数量
            $commentres[$k]['likeCount']=$like->where('Like_Lid='.$v['id'])->Count(); 
            //转换评论内容为HTML
            $commentres[$k]['Cm_Content']=htmlspecialchars_decode($v['Cm_Content']);
            //格式化时间
            if (date('Ymd',$v['Cm_Time'])==date('Ymd')) {
                $commentres[$k]['Cm_Time']=date('今天H:i',$v['Cm_Time']);
            }elseif (date('Ymd',$rv['Cm_Time'])==date('Ymd',time()-86400)) {
                $commentres[$k]['Cm_Time']=date('昨天H:i',$v['Cm_Time']);
            }elseif (date('Ymd',$v['Cm_Time'])==date('Ymd',time()-172800)) {
                $commentres[$k]['Cm_Time']=date('前天H:i',$v['Cm_Time']);
            }else{
                $commentres[$k]['Cm_Time']=date('m-d H:i',$rv['Cm_Time']);
            }
            if ($commentres[$k]['likeCount']>=$hotnum) {
                $hot[]=$commentres[$k];
            }
            // 楼层标识自增
            $i++;
            //楼层
            $commentres[$k]['floor']=$i;
        }
        //对其符热评条件的二维数组进行排序
        $sort = array(  
                'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序  
                'field'     => 'likeCount', //排序字段  
        );  
        $arrSort = array();  
        foreach($hot AS $uniqid => $row){  
            foreach($row AS $key=>$value){  
                $arrSort[$key][$uniqid] = $value;  
            }  
        }
        if($sort['direction']){  
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $hot);  
        }
        //设置热评最多条数
        $hotnums=3;
        //截取点赞最多的数组
        $hot=array_slice($hot,0,$hotnums);

        
        // 分页显示输出
        $pages = $Page->show();
        
        //取'您可能喜欢'的4个数据
        //查询出现有的所有文章ID,结果为二维数组
        $comnum=$user->field('id')->select();
        //遍历成一维数组
        foreach ($comnum as $k => $v) {
            $numbers[]=$v['id'];
        }
        //shuffle 将数组顺序随即打乱 
        shuffle ($numbers); 
        //取出个数
        $num=4; 
        //array_slice 取该数组中的某一段 
        $result = array_slice($numbers,0,$num); 
        //遍历出内容
        foreach ($result as $k => $v) {
            //查询结果
            $loveres=$user->field('id,Img,Content')->where('id='.$v)->select()[0];
            //转换结果
            $loveres['Img']=htmlspecialchars_decode($loveres['Img']);
            $loveres['Content']=mb_substr(strip_tags(htmlspecialchars_decode($loveres['Content'])),0,15,'utf-8').'...';
            //组合结果
            $loveresule[]=$loveres;
        }
        // var_dump($res);
        // die;
        //变量赋值到前台
        $this->assign('hot',$hot);
        $this->assign('pages',$pages);
        $this->assign('other',$other);
        $this->assign('commentres',$commentres);
        $this->assign('loveresule',$loveresule);
        $this->assign('res',$res);
        //解析模板
        $this->display();
    }
    //评论上传
    public function upload(){
        //接收POST,增加一个时间
        $_POST['Cm_Time']=Time();
        //实例化表
        $user=M('sc_comment');
        $user->create();
        $res=$user->add();
        if ($res) {
            echo "<script>alert('评论成功!');location.href='".$_SERVER["HTTP_REFERER"]."';</script>";
        } else {
            $this->error('评论失败,服务器繁忙,请稍后重试',U('Home/Article/index'),3);
        }



    }
    //AJAX设置cookie
    public function ajaxCookie(){
        $wid=I('post.wid');
        cookie('wid',$wid);  
    }
    //ajax点赞
    public function ajaxZambia(){
        //实例化点赞表
        $user=M('sc_like');
        //过滤数据
        $user->create();
        //添加数据
        $res=$user->add();
        //是否成功
        if ($res) {
            echo 1;
        } else {
            echo 0;
        }
    }

    //ajax取消点赞
    public function ajaxCancelZambia(){
        //过滤数据
        $uid=I('post.uid');
        $likeid=I('post.likeid');
        //实例化点赞表
        $user=M('sc_like');
        //删除数据
        $res=$user->where('Like_Lid='.$likeid.' and Like_Uid='.$uid)->delete();
        //是否成功
        if ($res) {
            echo 1;
        } else {
            echo 0;
        }
    }
    //jajx取消点赞
    public function ajaxcommentdel(){
        //过滤数据
        $id=I('post.id');
        //实例化评论表
        $comment=M('sc_comment');
        //组合数据
        $data['id']=$id;
        $data['Delete']=1;
        //把删除标志改为1
        $res=$comment->save($data);
        //结果集判断
        if ($res) {
            echo 1;
        } else {
            echo 0;
        }
    }
}