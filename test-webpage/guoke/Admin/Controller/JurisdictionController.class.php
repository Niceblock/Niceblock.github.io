<?php
namespace Admin\Controller;
use Think\Controller;
//后台的权限管理控制器.
class JurisdictionController extends CommonController {
	//权限列表显示板块
    public function index(){
        //实例化think_auth_group表        
        $user=M('think_auth_group');
        //查询数据
        $thres = $user->select();
        //去除第一个admin
        foreach ($thres as $k => $v) {
            if ($k != 0) {
                $res[]=$v;
            }
        }
        //更改结果集数据
        foreach ($res as $k => $v) {
            if ($v['status']==1) {
                $res[$k]['status']='是';
            } else if($v['status']==0){
                $res[$k]['status']='否';
            }
            //实例化管理组管理员表
            $access=M('think_auth_group_access');
            //查询该组下是否有管理员
            $accessres=$access->field('uid')->where('group_id='.$v['id'])->select();
            // 结果集判断,增加一个数组字段
            if ($accessres) {
                $res[$k]['son']=1;
            } else {
                $res[$k]['son']=0;
            }
            //查询组下是否已具有全部管理员
            //实例化管理员表
            $adminuser=M('admin_user');
            //初始化where
            $where="";
            //遍历组not in条件
            foreach ($accessres as $value) {
                //如果有组成员就组,没有就不组
                if (empty($value)) {
                    $where="";
                }else{
                    $where.=$value['uid'].',';
                }
            }
            //再次组合,拼接前缀,没有就不组
            if (empty($where)) {
                $where="";
            } else {
                //拼接时前面手动加上超级管理员ID
                $where='A_ID not in (1,'.rtrim($where,',').')';
            }
            //查询是否已选择全部管理员
            $noaccessres=$adminuser->where($where)->select();
            //没有返回1,有返回0
            if ($noaccessres) {
                $res[$k]['addadmin']=1;
            } else {
                $res[$k]['addadmin']=0;
            }
        }        
        $this->assign('res',$res);
    	$this->display();
    }


    public function ajaxdel(){
        //过滤数据
        $id=I('post.id');
        //实例化权限组表
        $group=M('think_auth_group');
        //删除权限组表的数据
        $groupres=$group->where('id='.$id)->delete();
        //实例化权限组管理员表
        $access=M('think_auth_group_access');
        //删除用户组表中的用户
        $accessres=$access->where('group_id='.$id)->delete();
        //结果判断
        if ($groupres && $accessres) {
            echo 1;
        } else {
            echo 0;
        }
    }

    //ajax修改
    public function ajaxReva(){
        //把是否显示文本改成数字
        if ($_POST['status']=='是') {
            $_POST['status']=0;
        }else{
            $_POST['status']=1;
        }
        //实例化表
        $user=M('think_auth_group');
        //数据过滤
        $user->create();
        //修改
        $res=$user->save();
        //结果集判断
        if ($res) {
            echo 1;
        } else {
            echo 0;
        }
        
    }


    //查看管理员
    public function admins(){
        //实例化权限组表
        $group=M('think_auth_group');
        //实例化权限组管理员表
        $access=M('think_auth_group_access');
        //接收GET
        $id=I('get.id');
        //查询ID对应管理组名
        $admin=$group->field('title')->find($id);
        $admin=$admin['title'];
        //查询管理成员
        $res=$access->field('think_auth_group_access.uid,admin_user.Admin_name')->join('left join admin_user on think_auth_group_access.uid=admin_user.A_ID')->where('think_auth_group_access.group_id='.$id)->select();
        if ($res) {
            //查询子分类给前台
            $this->assign('res',$res);
        } else {
            $this->assign('res','没有管理员');
        }
        
        //赋值父级分类给前台
        $this->assign('admin',$admin);
        

        //解析模板
        $this->display();
    }

    //删除管理组中的管理成员
    public function ajaxadmindel(){
        //实例化管理组成员表
        $access=M('think_auth_group_access');
        //过滤数据
        $id=I('post.id');
        //删除数据
        $res=$access->where('uid='.$id)->delete();
        //结果集判断
        if ($res) {
            echo 1;
        } else {
            echo 0;
        }
    }


    //管理组添加显示面板
    public function add(){
        //实例化管理组表
        $group=M('think_auth_group');
        //实例化规则表
        $rule=M('think_auth_rule');
        //查询出所有规则
        $ruleres=$rule->field('id,title')->select();
        //查询出所有还未分组的管理员
        //实例化管理组管理员表
        $access=M('think_auth_group_access');
        //查询出已有管理组的管理员
        $accessadminres=$access->field('uid')->select();
        //实例化$where
        $where="";
        //组where
        foreach ($accessadminres as $k => $v) {
            $where.=$v['uid'].',';
        }
        $where="A_ID not in (".rtrim($where,',').")";
        //查询出未分组管理员
        $adminuser=M('admin_user');
        //
        $adminres=$adminuser->field('A_ID,Admin_name')->where($where)->select();
        //变量至前台
        $this->assign('ruleres',$ruleres);
        $this->assign('adminres',$adminres);
        //解析模版
        $this->display();
    }

    //管理组的添加
    public function save(){
        //过滤接收权限
        $rules=I('post.rules');
        //过滤接收组名
        $title=I('post.title');
        //过滤接收组成员
        $uid=I('post.uid');
        //查看组名是否为空
        if (empty($title)) {
            $this->error('组名不允许为空',U('Admin/Jurisdiction/add'),3);
        }
        //初始化rules
        $rule="";
        //组合权限
        foreach ($rules as $v) {
            $rule.=$v.',';
        }
        //实例化权限组表
        $group=M('think_auth_group');
        //组合数据
        $groupdata['title']=$title;
        $groupdata['rules']=$rule;
        //插入权限组表
        $groupres=$group->add($groupdata);
        if (!$groupres) {
            $this->error('系统繁忙,请稍后重试',U('Admin/Jurisdiction/add'),3);    
        }
        //新增组ID
        $accessdata['group_id']=$groupres;
        //实例化权限组成员表
        $access=M('think_auth_group_access');
        //插入组员
        foreach ($uid as $v) {
            //组成员写入
            $accessdata['uid']=$v;
            //插入组成员
            $accessres=$access->add($accessdata);
            //结果集判断
            if (!$accessres) {
                $this->error('系统繁忙,请稍后重试',U('Admin/Jurisdiction/add'),3);
            }
        }
        //添加成功
        $this->success('添加成功',U('Admin/Jurisdiction/index'),3);
    }




    //判断组名是否可用
    public function ajaxGroupName(){
        //实例化权限组表
        $group=M('think_auth_group');
        //过滤数据
        $groupname=I('post.groupname');
        //查询
        $res=$group->where('title="'.$groupname.'"')->select();
        //结果集判断
        if ($res) {
            echo 1;
        } else {
            echo 0;
        }
    }

    //增加组成员
    public function groupAdd(){
        //实例化权限组表
        $group=M('think_auth_group');
        //实例化权限组管理员表
        $access=M('think_auth_group_access');
        //接收GET
        $id=I('get.id');
        //查询ID对应管理组名
        $admin=$group->field('title')->find($id);
        $admin=$admin['title'];

        //查询出已有管理组的管理员
        $accessadminres=$access->field('uid')->select();
        if ($res) {
            //查询子分类给前台
            $this->assign('res',$res);
        } else {
            $this->assign('res','没有管理员');
        }
        //实例化$where
        $where="";
        //组where
        foreach ($accessadminres as $k => $v) {
            $where.=$v['uid'].',';
        }
        $where="A_ID not in (".rtrim($where,',').")";
        //查询出未分组管理员
        $adminuser=M('admin_user');
        //
        $adminres=$adminuser->field('A_ID,Admin_name')->where($where)->select();
        //赋值组名给前台
        $this->assign('admin',$admin);
        $this->assign('adminres',$adminres);
        $this->assign('id',$id);      

        //解析模板
        $this->display();
    }


    public function groupAdminSave(){
        //过滤接收权限
        $rules=I('post.rules');
        //过滤接收选中成员
        $uid=I('post.uid');
        //过滤接收组ID
        $group_id=I('post.group_id');
        //查看组名是否为空
        if (empty($uid)) {
            $this->error('成员必须选择',U('Admin/Jurisdiction/groupAdd'),3);
        }
        
       
        //实例化权限组成员表
        $access=M('think_auth_group_access');
        //组ID写入
        $accessdata['group_id']=$group_id;
        //插入组员
        foreach ($uid as $v) {
            //组成员写入
            $accessdata['uid']=$v;
            //插入组成员
            $accessres=$access->add($accessdata);
            //结果集判断
            if (!$accessres) {
                $this->error('系统繁忙,请稍后重试',U('Admin/Jurisdiction/add'),3);
            }
        }
        //添加成功
        $this->success('添加成功',U('Admin/Jurisdiction/index'),3);
    }


    //编辑权限显示页面
    
    public function edit(){
        //过滤接收ID
        $id=I('get.id');
        //实例化权限组表
        $group=M('think_auth_group');
        //查询ID对应管理组名
        $admin=$group->field('title')->find($id);
        $admin=$admin['title'];
        //查询已有的权限
        $jurisdiction= $group->where('id='.$id)->select();
        //去除最后的逗号
        $jurisdiction=rtrim($jurisdiction[0]['rules'],',');
        //拆分已有权限
        $jurisdictionres=explode(',',$jurisdiction);
        //实例化权限表
        $rule=M('think_auth_rule');
        //遍历
        foreach ($jurisdictionres as $v) {
            //取ID和中文标题
            $existingAuthority[]=$rule->field('id,title')->where('id='.$v)->select()[0];
        }
        //查询可选的权限
        $optionalPermissions=$rule->field('id,title')->where("id not in (".$jurisdiction.")")->select();
        //变量赋值给前台
        $this->assign('admin',$admin);
        $this->assign('existingAuthority',$existingAuthority);
        $this->assign('optionalPermissions',$optionalPermissions);
        //解析模板
        $this->display();
    }

    //删除可选权限
    public function ajaxjurisdictiondel(){
        //过滤数据id
        $id=I('post.id');
        //过滤数据组ID
        $gid=I('post.gid');
        //组合要删除的数据
        $ids=$id.',';
        //实例化权限组表
        $group=M('think_auth_group');
        //取出当前ID的rules
        $rulesres=$group->field('rules')->where('id='.$gid)->select();
        //去除要删除的权限
        $rulesres=str_replace($ids,'',$rulesres[0]['rules']);
        // 把权限重新更新
        $data['rules']=$rulesres;
        $res=$group->where('id='.$gid)->save($data);
        //结果判断
        if ($res) {
            echo 1;
        } else {
            echo 0;
        }

    }

    //
    public function update(){
        //过滤组id
        $gid=I('post.gid');
      //过滤数据rulesva
      $hotRules='';
      $rules=I('post.rules');
      if (empty($rules)) {
        $this->error('新权限必须选择',U('Admin/Jurisdiction/index'),3);
      }
      foreach ($rules as $v) {
        $hotRules.=$v.',';
      }
      //用组ID查出原来有的权限
      $group=M('think_auth_group');
      $rulesres=$group->field('rules')->where('id='.$gid)->select();
      //加上新的权限
      $hotRules=$rulesres[0]['rules'].$hotRules;
      //更新新的权限
      $data['rules']=$hotRules;
      //更新权限
      $res=$group->where('id='.$gid)->save($data);
      //返回结果集判断
        if ($res) {
            $this->success('修改成功',U('Admin/Jurisdiction/index'),3);
        } else {
            $this->error('修改失败',U('Admin/Jurisdiction/index'),3);
        }
      
    }

}