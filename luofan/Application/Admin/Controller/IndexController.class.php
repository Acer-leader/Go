<?php
namespace Admin\Controller;

//use Think\Controller;
use Common\Controller\CommonController;

class IndexController extends CommonController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->assign("urlname", ACTION_NAME);
    }

    public function index()
    {
        $this->display();
    }

    /*广告列表*/

    public function banner()
    {
        $action = M('banner_view');
		$count=$action->count();
        $Page  = getpage($count,8);
        $show  = $Page->show();
        $cache = $action->order('type asc,sort desc')->where($sql)->limit($Page->firstRow.','.$Page->listRows)->select();
		
        $bannertype = M('bannertype');
        $bannertypelist = $bannertype->field("id,classname")->where(array('isdel' => 0))->order('sort asc')->select();
        $this->assign('bannertypelist', $bannertypelist);
        $count = count($cache);
        $this->assign('cache', $cache);
		$this->assign('page',$show);
        $this->assign('count', $count);
        $this->display();
    }


    /**
     * 添加广告
     */
    public function banner_add()
    {
        if (IS_AJAX) {
            $action = M('banner');
            $type = I('post.type');
            $pic = I('post.pic');
            $pic1 = I('post.pic1');
            $url = I('post.url');
            $title = I('post.title');
            $title1 = I('post.title1');
            if (!$type || !$pic) {
                $this->ajaxReturn(array("status" => 0, "info" => "缺少参数"));
            }
            $data["pic"] = $pic;
            $data["pic1"] = $pic1;
            $data["type"] = $type;
            $data["url"] = $url;
            $data["title"] = $title;
            $data["title1"] = $title1;
            $data["add_time"] = time();
            $result = $action->add($data);
            if ($result) {
                $this->ajaxReturn(array("status" => 1, "info" => "添加成功"));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "添加失败"));
            }
        }
    }

    /**
     * 修改广告
     */
    public function banner_edit()
    {
        if (IS_AJAX) {
            $action = M('banner');
            $title = I('post.title');
            $title1 = I('post.title1');
            $sort = I('post.sort');
            $type = I('post.type');
            $id = I('post.id');
            $pic = I('post.pic');
            $pic1 = I('post.pic1');
            $url = I('post.url');
            if (!$type || !$id || !$pic) {
                $this->ajaxReturn(array("status" => 0, "info" => "缺少参数"));
            }
            $data["pic"] = $pic;
            $data["pic1"] = $pic1;
            $data["type"] = $type;
            $data["url"] = $url;
            $data["title"] = $title;
            $data["title1"] = $title1;
            $data["title1"] = $title1;
            $data["sort"] = $sort;
            $data["edit_time"] = $sort;
            $result = $action->where(array("id" => $id))->save($data);
            if ($result !== false) {
                $this->ajaxReturn(array("status" => 1, "info" => "修改成功"));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "修改失败"));
            }
        }
    }

    /**
     * 删除广告
     */
    public function banner_del()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            $arr = explode('_', $id);
            $arr = implode(',', $arr);
            $arr = rtrim($arr, ',');
            $data['id'] = array('in', $arr);
            $del = M('banner')->where($data)->delete();
            if ($del) {
                $this->ajaxReturn(array("status" => 1, "info" => " 删除成功", "url" => U('Admin/Index/banner')));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "删除失败"));
            }
        }
    }


    /**
     * 广告分类列表
     */
    public function bannertype()
    {
        $m = M("bannertype");
        $count = $m->where(array("isdel" => 0))->count();
        $p = getpage($count, 10);
        $res = $m->where(array("pid" => 0, "isdel" => 0))->limit($p->firstRow, $p->listRows)->order("sort desc")->select();
        //$res = $m->where(array("pid"=>0, "isdel"=>0))->order("sort desc")->select();
        $bannertype = $m->where(array("pid" => 0, "isdel" => 0))->order("sort desc")->field("id,classname")->select();
        foreach ($res as $k => $v) {
            $res[$k]["data"] = $m->where(array("pid" => $v['id'], "isdel" => 0))->select();
        }
        $this->assign("page", $p->show());
        $this->assign("cache", $res);
        $this->assign("bannertype", $bannertype);
        $this->display();
    }

    /**
     * 增加广告分类
     */
    public function bannertype_add()
    {
        if (IS_AJAX) {
            $classname = I("post.classname");
            $pid = I("post.fid");
            $pic = I("post.pic");
            $sort = I("post.sort");
            $m = M("bannertype");
            $res = $m->where(array("classname" => $classname, "pid" => $pid, "isdel" => 0))->find();
            if ($res) {
                $this->ajaxReturn(array("status" => 0, "info" => "类名已存在！"));
            }
            $data['classname'] = $classname;
            $data['pid'] = $pid;
            $data['sort'] = $sort;
            $data['add_time'] = time();
            $pic && $data['pic'] = $pic;
            $res = $m->add($data);
            if ($res) {
                $this->ajaxReturn(array("status" => 1, "info" => "增加成功！"));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "新增失败！"));
            }
        }
    }

    /**
     * 删除广告分类
     */
    public function bannertype_del()
    {
        $m = M("bannertype");
        if (IS_AJAX) {
            $id = I('post.id');
            $arr = explode('_', $id);
            $arr = implode(',', $arr);
            $arr = rtrim($arr, ',');
            $data['id'] = array('in', $arr);
            $del = $m->where($data)->delete();
            if ($del) {
                $this->ajaxReturn(array("status" => 1, "info" => " 删除成功", "url" => U('Admin/Index/bannertype')));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "删除失败"));
            }
        }
    }

    /**
     * 编辑广告分类
     */
    public function bannertype_edit()
    {
        if (IS_AJAX) {
            $id = I("post.bannertypegoryid");
            $classname = I("post.classname");
            $pid = I("post.fid");
            $pic = I("post.pic");
            $sort = I("post.sort");
            $m = M("bannertype");
            $map = array(
                "classname" => $classname,
                "pid" => $pid,
                "id" => array("neq", $id),
                "isdel" => 0,
            );
            $res = $m->where($map)->find();
            if ($res) {
                $this->ajaxReturn(array("status" => 0, "info" => "类名已存在！"));
            }
            $parid = $m->where(array("id" => $id, "isdel" => 0))->getField("pid");
            if ($parid == 0 && $pid != 0) {
                $this->ajaxReturn(array("status" => 0, "info" => "顶级分类无法改变分类！"));
            }
            $data['classname'] = $classname;
            $data['pid'] = $pid;
            $data['sort'] = $sort;
            $data['edit_time'] = time();
            $pic && $data['pic'] = $pic;
            $res = $m->where(array('id' => $id))->save($data);
            if ($res !== false) {
                $this->ajaxReturn(array("status" => 1, "info" => "修改成功！"));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "修改失败！"));
            }
        }
    }

    /**
     * 官网配置
     */
    public function web_config()
    {
        if (IS_POST) {
            $data = I("post.");
            $data['update_time'] = time();
            $res = M("web_config")->where("id=1")->save($data);
            if ($res !== false) {
                $this->ajaxReturn(array("status" => 1, "info" => "保存成功！"));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "保存失败！"));
            }
        }
        $cache = M("web_config")->where(array("id" => 1))->find();
        $this->assign("cache", $cache);
        $this->display();
    }


    //服务列表
    public function servicelist()
    {
        $m = M('service');
        $info = $m->alias('a')
            ->field('a.*,b.classname,b.pid as tid')
            ->join('LEFT JOIN app_servicetype as b ON a.type_id=b.id')
            ->order('a.type_id asc,a.sort asc')
            ->select();
        foreach ($info as $key => $val) {
            $info[$key]['p_classname'] = M('servicetype')->where(array('id' => $val['tid']))->getField('classname');
        }
        $this->assign('info', $info);
        $this->display();
    }


    /**
     * 添加服务
     */
    public function addservice()
    {

        if (IS_POST) {

            $data['type_id'] = I('post.type');
            $data['content'] = I('post.content');
            $data['title_en'] = I('post.title_en');
            $data['addtime'] = time();
            $rs = M('service')->add($data);
            if ($rs) {
                $this->success('添加成功！', U('/Admin/Index/servicelist'));
                exit;
            } else {
                $this->error('添加失败！');
                exit;
            }
        }
        $mm = M('servicetype');
        $typeList = $mm->where(array('pid' => 0))->select();
        foreach ($typeList as $key => $val) {
            $typeList[$key]['sub'] = $mm->where(array('pid' => $val['id']))->select();
        }
        $this->assign('typeList', $typeList);
        $this->display();
    }

    /**
     * 编辑服务
     */

    public function editservice()
    {
        $M = M('service');
        $where['id'] = I('get.id');
        $info = $M->where($where)->find();

        //$info['service']=str_ireplace('\"','"',htmlspecialchars_decode($info['service']));

        if (IS_POST) {
            $where['id'] = I('post.id');
            $data['content'] = I('post.content');
            $data['title_en'] = I('post.title_en');
            $data['type_id'] = I('post.type');
            $rs = $M->where($where)->save($data);
            if ($rs) {
                $this->success('保存成功！');
                exit;
            } else {
                $this->error('保存失败！');
                exit;
            }

        }

        $this->assign("info", $info);
        $this->assign('type', $where['id']);

        $mm = M('servicetype');
        $typeList = $mm->where(array('pid' => 0))->select();
        foreach ($typeList as $key => $val) {
            $typeList[$key]['sub'] = $mm->where(array('pid' => $val['id']))->select();
        }
        $this->assign('typeList', $typeList);

        $this->display();
    }

    /**
     * 删除服务
     */

    public function delservice()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            $arr = explode('_', $id);
            $arr = implode(',', $arr);
            $arr = rtrim($arr, ',');
            $data['id'] = array('in', $arr);
            $del = M('service')->where($data)->delete();
            if ($del) {
                $this->ajaxReturn(array("status" => 1, "info" => " 删除成功", "url" => U('Admin/Index/servicelist')));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "删除失败"));
            }
        }
    }


    /**
     * 服务分类列表
     */
    public function servicetype()
    {
        $m = M("servicetype");
        $count = $m->where(array("isdel" => 0))->count();
        //$p = getpage($count, 10);
        //$res = $m->where(array("pid"=>0, "isdel"=>0))->limit($p->firstRow,$p->listRows)->order("sort asc")->select();
        $res = $m->where(array("pid" => 0, "isdel" => 0))->order("sort asc")->select();
        $servicetype = $m->where(array("pid" => 0, "isdel" => 0))->order("sort asc")->field("id,classname")->select();
        foreach ($res as $k => $v) {
            $res[$k]["data"] = $m->where(array("pid" => $v['id'], "isdel" => 0))->select();
        }
        $this->assign('count', $count);
        //$this->assign("page",  $p->show());
        $this->assign("cache", $res);
        $this->assign("servicetype", $servicetype);
        $this->display();
    }


    /**
     * 增加服务分类
     */
    public function addServicetype()
    {
        if (IS_AJAX) {
            $classname = I("post.classname");
            $pid = I("post.fid");
            $pic = I("post.pic");
            $sort = I("post.sort");
            $m = M("servicetype");
            $res = $m->where(array("classname" => $classname, "pid" => $pid, "isdel" => 0))->find();
            if ($res) {
                $this->ajaxReturn(array("status" => 0, "info" => "类名已存在！"));
            }
            $data['classname'] = $classname;
            $data['pid'] = $pid;
            $data['sort'] = $sort;
            $data['create_at'] = time();
            $pic && $data['pic'] = $pic;
            $res = $m->add($data);
            if ($res) {
                $this->ajaxReturn(array("status" => 1, "info" => "增加成功！"));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "新增失败！"));
            }
        }
    }

    /**
     * 删除服务分类
     */
    public function servicetype_del()
    {
        $m = M("servicetype");
        if (IS_AJAX) {
            $id = I('post.id');
            $arr = explode('_', $id);
            $arr = array_filter($arr);
            foreach ($arr as $key => $val) {
                $data = $m->find($val);
                if (!$data) {
                    $this->ajaxReturn(array("status" => 0, "info" => "删除失败"));
                }
                if ($data['pid']) {
                    $res = $m->where(array("id" => $id))->setField("isdel", 1);
                    if ($res) {
                        $this->ajaxReturn(array("status" => 1, "info" => " 删除成功", "url" => U('Admin/Index/servicetype')));
                    } else {
                        $this->ajaxReturn(array("status" => 0, "info" => "删除失败"));
                    }
                } else {
                    $res1 = $m->where(array("id" => $id))->setField("isdel", 1);
                    $res2 = $m->where(array("pid" => $id))->setField("isdel", 1);
                    if ($res1 !== false && $res2 !== false) {
                        $this->ajaxReturn(array("status" => 1, "info" => " 删除成功", "url" => U('Admin/Index/servicetype')));
                    } else {
                        $this->ajaxReturn(array("status" => 0, "info" => "删除失败"));
                    }
                }
            }
        }
    }

    /**
     * 编辑服务分类
     */
    public function editServicetype()
    {
        if (IS_AJAX) {
            $id = I("post.servicetypegoryid");
            $classname = I("post.classname");
            $pid = I("post.fid");
            $pic = I("post.pic");
            $sort = I("post.sort");
            $m = M("servicetype");
            $map = array(
                "classname" => $classname,
                "pid" => $pid,
                "id" => array("neq", $id),
                "isdel" => 0,
            );
            $res = $m->where($map)->find();
            if ($res) {
                $this->ajaxReturn(array("status" => 0, "info" => "类名已存在！"));
            }
            $parid = $m->where(array("id" => $id, "isdel" => 0))->getField("pid");
            if ($parid == 0 && $pid != 0) {
                $this->ajaxReturn(array("status" => 0, "info" => "顶级分类无法改变分类！"));
            }
            $data['classname'] = $classname;
            $data['pid'] = $pid;
            $data['sort'] = $sort;
            $pic && $data['pic'] = $pic;
            $res = $m->where(array('id' => $id))->save($data);
            if ($res !== false) {
                $this->ajaxReturn(array("status" => 1, "info" => "修改成功！"));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "修改失败！"));
            }
        }
    }

    /**
     * 底部导航配置
     */
    public function FooterConfig()
    {
		$cate_id = I('get.classname');
		if($cate_id){
			$sql['cate_id'] = $cate_id;
		}else{
			$sql = '';
		}
		//分类
		$cate = M('FooterCate')->select();
		$this->assign('sel',$cate);
        $m = M("footer_url");
        $count = $m->where(array('isdel' => 0))->where($sql)->count();
        $Page = getpage($count, 15);
        $show = $Page->show();//分页显示输出
        $data = $m->alias('a')->join('LEFT JOIN app_footer_cate as b ON a.cate_id=b.id')
            ->field('a.*,b.title')
            ->where(array('a.isdel' => 0))->where($sql)->order('a.sorts asc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        $footertypelist = M("footer_cate")->where(array('isdel' => 0))->select();
        $this->assign('footertypelist', $footertypelist);
        $this->assign('page', $show);
        $this->assign('data', $data);
        $this->assign('count', $count);
        $this->display();
    }

    /**
     * 添加编辑底部导航
     */
    public function UpdateFooter()
    {
        if (IS_AJAX) {
            $id = I("post.id");
            $action = M('footer_url');
            if ($id) {
                $type = I('post.type');
                $url = I('post.url');
                $title = I('post.title');
                $sort = I('post.sort');
                if (!$type || !$title) {
                    $this->ajaxReturn(array("status" => 0, "info" => "缺少参数"));
                }
                $data["cate_id"] = $type;
                $data["url"] = $url;
                $data["content"] = $title;
                $data["sorts"] = $sort;
                $result = $action->where(array('id' => $id))->save($data);
                if ($result) {
                    $this->ajaxReturn(array("status" => 1, "info" => "修改成功"));
                } else {
                    $this->ajaxReturn(array("status" => 0, "info" => "修改失败"));
                }
            }
            $type = I('post.type');
            $url = I('post.url');
            $title = I('post.title');
            $sort = I('post.sort');
            if (!$type || !$title) {
                $this->ajaxReturn(array("status" => 0, "info" => "缺少参数"));
            }
            $data["cate_id"] = $type;
            $data["url"] = $url;
            $data["content"] = $title;
            $data["sorts"] = $sort;
            $data["create_at"] = time();
            $result = $action->add($data);
            if ($result) {
                $this->ajaxReturn(array("status" => 1, "info" => "添加成功"));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "添加失败"));
            }
        }
    }

    /**
     * 删除底部导航
     */
    public function del_footer()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            $data['id'] = $id;
            $del = M('footer_url')->where($data)->setField('isdel', 1);
            if ($del) {
                $this->ajaxReturn(array("status" => 1, "info" => " 删除成功", "url" => U('Admin/Index/FooterConfig')));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "删除失败"));
            }
        }
    }

    /**
     * 底部导航分类
     */
    public function FooterCate()
    {
        $m = M("footer_cate");
        $count = $m->where(array('isdel' => 0))->count();
        $Page = getpage($count, 5);
        $show = $Page->show();//分页显示输出
        $data = $m->where(array('isdel' => 0))->order('sort asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('page', $show);
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 添加编辑底部导航分类
     */
    public function AddFooterCate()
    {
        if (IS_AJAX) {
            $id = I("post.id");
            if ($id) {
                $classname = I("post.classname");
                $sort = I("post.sort");
                $m = M("footer_cate");
                $data['title'] = $classname;
                $data['sort'] = $sort;
                $res = $m->where(array('id' => $id))->save($data);
                if ($res) {
                    $this->ajaxReturn(array("status" => 1, "info" => "修改成功！"));
                    exit;
                } else {
                    $this->ajaxReturn(array("status" => 0, "info" => "修改失败！"));
                    exit;
                }
            }
            $classname = I("post.classname");
            $sort = I("post.sort");
            $m = M("footer_cate");
            $res = $m->where(array("title" => $classname, "isdel" => 0))->find();
            if ($res) {
                $this->ajaxReturn(array("status" => 0, "info" => "分类名已存在！"));
            }
            $data['title'] = $classname;
            $data['sort'] = $sort;
            $data['create_at'] = time();
            $res = $m->add($data);
            if ($res) {
                $this->ajaxReturn(array("status" => 1, "info" => "增加成功！"));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "新增失败！"));
            }
        }
    }

    /**
     * 删除底部分类
     */
    public function footercate_del()
    {
        $m = M("footer_cate");
        if (IS_AJAX) {
            $id = I('post.id');
            $arr = explode('_', $id);
            $arr = array_filter($arr);
            foreach ($arr as $key => $val) {
                $data = $m->find($val);
                if (!$data) {
                    $this->ajaxReturn(array("status" => 0, "info" => "删除失败"));
                }
                $res1 = $m->where(array("id" => $id))->setField("isdel", 1);
                if ($res1 !== false) {
                    $this->ajaxReturn(array("status" => 1, "info" => " 删除成功", "url" => U('Admin/Index/FooterCate')));
                } else {
                    $this->ajaxReturn(array("status" => 0, "info" => "删除失败"));
                }
            }
        }
    }

    /**
     * 货币配置
     */
    public function MoneyConfig()
    {
        if (IS_POST) {
            $data = I("post.");
            if(empty($data['wx_ewm'])){
                unset($data['wx_ewm']);
            }
            if(empty($data['alipay_ewm'])){
                unset($data['alipay_ewm']);
            }
            $data['update_time'] = time();
            $res = M("virtual_config")->where("id=1")->save($data);
            if ($res !== false) {
                $this->ajaxReturn(array("status" => 1, "info" => "保存成功！"));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "保存失败！"));
            }
        }
        $cache = M("virtual_config")->where(array("id" => 1))->find();
        $this->assign("cache", $cache);
        $this->display();
    }
    /**
     * 广告列表
     *
     */
    public function cooperate()
    {
        $action = M('cooperate');
        $count = $action->where(array('isdel' => 0))->count();
        $Page = getpage($count, 5);
        $show = $Page->show();//分页显示输出
        $cache = $action->where(array('isdel' => 0))->order('sort desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('page',$show);
        $this->assign('cache', $cache);
        $this->assign('count', $count);
        $this->display();
    }
    /**
     * 添加编辑合作机构
     */
    public function cooperate_add()
    {
        if (IS_AJAX) {
            $id = I("post.id");
            $action = M('cooperate');
            if(!$id){
                $pic = I('post.pic');
                $title = I('post.title');
                $sort = I("post.sort");
                if (!$pic) {
                    $this->ajaxReturn(array("status" => 0, "info" => "缺少参数"));
                }
                $data["pic"] = $pic;
                $data["sort"] = $sort;
                $data["title"] = $title;
                $data["add_time"] = time();
                $result = $action->add($data);
                if ($result) {
                    $this->ajaxReturn(array("status" => 1, "info" => "添加成功"));exit;
                } else {
                    $this->ajaxReturn(array("status" => 0, "info" => "添加失败"));exit;
                }
            }

            $classname = I("post.title");
            $pic = I("post.pic");
            $sort = I("post.sort");
            $data['title'] = $classname;
            $data['sort'] = $sort;
            $data['edit_time'] = time();
            $pic && $data['pic'] = $pic;
            $res = $action->where(array('id' => $id))->save($data);
            if ($res !== false) {
                $this->ajaxReturn(array("status" => 1, "info" => "修改成功！"));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "修改失败！"));
            }
        }
    }

    /**
     * 删除广告
     */
    public function cooperate_del()
    {
        if (IS_AJAX) {
            $id = I('post.id');
            $arr = explode('_', $id);
            $arr = implode(',', $arr);
            $arr = rtrim($arr, ',');
            $data['id'] = array('in', $arr);
            $del = M('cooperate')->where($data)->delete();
            if ($del) {
                $this->ajaxReturn(array("status" => 1, "info" => " 删除成功", "url" => U('Admin/Index/cooperate')));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "删除失败"));
            }
        }
    }

    /**
     * 贷款配置
     */
    public function loan_config()
    {  
        if (IS_POST) {
            $data = I("post.");
            $res = M("loan_config")->where("id=1")->save($data);
            if ($res !== false) {
                $this->ajaxReturn(array("status" => 1, "info" => "保存成功！"));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "保存失败！"));
            }
        }
        $cache = M("loan_config")->where(array("id" => 1))->find();
        $this->assign("cache", $cache); 
        $this->display();
    }
	//短信设置
	public function message()
    {  
        if (IS_AJAX) {
            $data = I("post.");
            $res = M("Jilu")->where("id=1")->save($data);
            if ($res !== false) {
                $this->ajaxReturn(array("status" => 1, "info" => "保存成功！"));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "保存失败！"));
            }
        }
        $cache = M("Jilu")->where(array("id" => 1))->find();
        $this->assign("cache", $cache); 
        $this->display();
    }
	/**
     * seo列表 三个关键字
     */
    public function seo_info()
    {
        $m = M("seo_attr");
        $map = array(
            "fid" => 0,
            "isdel" => 0,
        );
        $res = $m->where($map)->order("sort desc")->select();

        foreach ($res as $k => $v) {
            $res[$k]["data"] = M('seo_val')->where(array("fid" => $v['id']))->select();
        }
        $this->assign('count',count($res));

        $this->assign("cache", $res);
        $this->assign("cate", $res);
        $this->assign("comptype", 1);
        $this->display();
    }

    /**
     * 编辑控制器名称
     */
    public function edit_seo_attr()
    {
        if (IS_AJAX) {
            $id = I("post.categoryid");
            $classname = I("post.classname");
            $controller = I("post.controller");
            $sort = I("post.sort");
            $m = M("seo_attr");
            $map = array(
                "classname" => $classname,
                "id" => array("neq", $id),
            );
            $sku_g = $m->find($id);
            $res = $m->where($map)->find();
            if ($res['id']) {
                $this->ajaxReturn(array("status" => 0, "info" => "控制器名称已存在！"));
            }
            //$parid = $m->where(array("id" => $id, "isdel" => 0))->getField("pid");
            $data['classname'] = $classname;
            $data['controller'] = $controller;
            $data['sort'] = $sort;
            $res = $m->where(array('id' => $id))->save($data);
            if ($res !== false) {
                $this->ajaxReturn(array("status" => 1, "info" => "修改成功！"));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "修改失败！"));
            }
        }
    }

    /**
     * 增加控制器
     */
    public function add_seo_attr()
    {
        if (IS_AJAX) {
            $classname = I("post.classname");
            $controller = I("post.controller");
            $sort = I("post.sort");
            $m = M("seo_attr");
            $res = $m->where(array("classname" => $classname))->find();//属性已存在
            if ($res) {
                $this->ajaxReturn(array("status" => 0, "info" => "属性已存在！"));
            }
            $data['classname'] = $classname;
            $data['controller'] = $controller;
            $data['sort'] = $sort;
            $data['create_at'] = time();
            $res = $m->add($data);
            if ($res) {
                $this->ajaxReturn(array("status" => 1, "info" => "增加成功！"));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "新增失败！"));
            }
        }

    }
    /**
     * 删除控制器名称
     */
    public function del_seo_attr()
    {
        $id = I("id");
        $m = M("seo_attr");
        $data = $m->find($id);
        if (!$data) {
            $this->error("该控制器不存在!");exit;
        }
        $res1 = $m->where(array("id" => $id))->delete();
        if ($res1 !== false) {
            $this->success("删除成功！");exit;
        } else {
            $this->error("删除失败！");exit;
        }
        
    }

    /**
     * 增加方法名称
     */
    public function add_seo_val()
    {
        if (IS_AJAX) {

            $classname = I("post.classname");//方法名称
            $function = I("post.function");//方法
            $sort = I("post.sort");
            $title = I("post.title");
            $keywords = I("post.keywords");
            $description = I("post.description");
            $fid = I('post.fid');

            $m = M("seo_val");
            $res = $m->where(array("name" => $classname, 'fid' => $fid))->find();//方法已存在
            if ($res) {
                $this->ajaxReturn(array("status" => 0, "info" => "方法已存在！"));
            }
            $data['name'] = $classname;
            $data['function'] = $function;
            $data['title'] = $title;
            $data['keywords'] = $keywords;
            $data['description'] = $description;
            $data['sort'] = $sort;
            $data['add_time'] = time();
            $data['fid'] = $fid;
            $res = $m->add($data);
            if ($res) {
                $this->ajaxReturn(array("status" => 1, "info" => "增加成功！"));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "新增失败！"));
            }
        }
       
    }
    /**
     * 编辑方法名
     */
    public function edit_seo_val()
    {
        if (IS_POST) {
		
            $id 		= I("post.valid");
            $classname 	= I("post.classname");//方法名称
            $function 	= I("post.function1");//方法
            $title 		= I("post.title");
            $keywords 	= I("post.keywords");
            $sort 		= I("post.sort");
            $description = I("post.description");

            $m = M("seo_val");
            $map = array(
                "name" => $classname,
                "id" => array("neq", $id),
            );

            $res = $m->where($map)->find();
	
            if ($res) {
                $this->ajaxReturn(array("status" => 0, "info" => "方法已存在！"));
            }
            $data['name'] = $classname;
            $data['function'] = $function;
            $data['title'] = $title;
            $data['keywords'] = $keywords;
            $data['description'] = $description;
            $data['sort'] = $sort;
            $res = $m->where(array('id' => $id))->save($data);
            if ($res !== false) {
                $this->ajaxReturn(array("status" => 1, "info" => "修改成功！"));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => "修改失败！"));
            }
        }
    }

    /**
     * 删除方法
     */
    public function del_seo_val()
    {
        $id = I("id");
        $m = M("seo_val");
        $data = $m->find($id);
        if (!$data) {
            $this->error("方法不存在!");
        }
        
        $res1 = $m->where(array("id" => $id))->delete();
        if ($res1 !== false) {
            $this->success("删除成功！");
            die;
        } else {
            $this->error("删除失败！");
        }
    }
	
	/**
     * SEO 配置
     */
    public function seo_config(){
        if(IS_POST){
            $data=I("post.");
            $data['update_time']=time();
            $res = M("seo_config")->where("id=1")->save($data);
            if($res !== false){
                $this->ajaxReturn(array("status"=>1, "info"=>"保存成功！"));
            }else{
                $this->ajaxReturn(array("status"=>0, "info"=>"保存失败！"));
            }
        }
        $cache = M("seo_config")->where(array("id"=>1))->find();
        $this->assign("cache",$cache);
        $this->display();
    }
}
?>