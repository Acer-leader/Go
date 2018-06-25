<?php
namespace Supplier\Controller;
//use Think\Controller;
use Supplier\Common\Controller\CommonController;
class IndexController extends CommonController {
    public function _initialize(){
        parent::_initialize();
        $this->assign("urlname", ACTION_NAME);
    }
	
	public function mxamine(){
		$Supplier_id = $this->supplier_id;
		$m = M('supplier_examine');
        $m_r = $m->where(array('supplier_id'=>$Supplier_id))->order('id asc')->select(); 
        $this->assign('cache',$m_r);
        $this->display();
	}
	
	
	public function tijiaosh(){
		if(IS_POST){
			$supplier_id = $this->supplier_id;
			$res = M('xiaodai_examine')->where(array('user_id'=>$supplier_id))->order('id desc')->limit(1)->find();
			if($res['is_check']=0){
				$this->ajaxReturn(array('status'=>0,'info'=>"审核已提交，请等待审核……"));
			}else{
			    $s_data = I('post.');
				$s_data['user_id']=$supplier_id;
				$s_data['type']=1;
				$s_data['status']=0;

				M("supplier_examine")->add($s_data);
                M('xiaodai')->where(['id'=>$supplier_id])->save($s_data);
				$res1 =M('xiaodai_examine')->add($s_data);
				M('xiaodai')->where(array('id'=>$supplier_id))->save(array('is_check'=>0));
				if($res1){
					$this->ajaxReturn(array('status'=>1,'info'=>"提交成功"));
				}else{
					$this->ajaxReturn(array('status'=>0,'info'=>"提交失败"));
				}
			}
		}
	}

	/* 供应商提交申请 */
    public function tijiaosh1(){
        $Supplier_id = $this->supplier_id;
        if(IS_POST){
            $data = I("param.");
            
            $data['update_time']=time();
            $data['is_sale']=1;
            $m = M("supplier");
            $res = $m->where(array('id'=>$Supplier_id))->find();
            if($res){
				if($res['caigou_status'] ==2){
					$data['caigou_status']=0;
				}if($res['caiwu_status'] ==2){
					$data['caiwu_status']=0;
				}
                $res2 = $m->where(array('id'=>$Supplier_id))->save($data);
            }else{
                $res3 = $m->add($data);
            }
            if($res3 || $res2 !== false){
                $this->ajaxReturn(array('status'=>1,'info'=>"操作成功"));
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>"操作失败"));
            }
        }else{
            $this->ajaxReturn(array('status'=>0,'info'=>"无效的操作"));
        }
    }
	public function index(){
		$this->redirect('Supplier/index/gyindex');
	}
    public function gyindex(){
        $memberdetail=M('xiaodai')->where('id='.$_SESSION['supplier_id'])->find();
        $this->assign('memberdetail',$memberdetail);
		$shenhe = M('supplier_examine')->where(array('supplier_id'=>$_SESSION['supplier_id']))->order('id desc')->limit(1)->find();
		$this->assign("shenhe",$shenhe);
        $this->display();
    }

    public function editMember(){
        $Supplier_id = $this->supplier_id;
        if(IS_POST){
            $data = I("param.");

            $m = M("xiaodai");
            $res = $m->where(array('id'=>$Supplier_id))->find();
            if($res){
				if($res['is_check']==0){
					$data['is_check']=3;
				}
                $res2 = $m->where(array('id'=>$Supplier_id))->save($data);
				if($res2 !==false){
					$this->ajaxReturn(array('status'=>1,'info'=>"操作成功"));
				}else{
					$this->ajaxReturn(array('status'=>0,'info'=>"操作失败"));
				}  
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>"小贷公司不存在！"));
            }
        }else{
            $this->error("无效的操作！");
        }
    }


    /**
     * 重新提交审核
     */
    public function goexamine()
    {
        $Supplier_id = $this->supplier_id;
        if(IS_AJAX){
            $data = I("param.");
            $m = M("xiaodai");
            $res = $m->where(array('id'=>$Supplier_id))->find();
            if($res){
                //判断该小贷公司是否已提交审
                $is_check = M("xiaodai")->where(['id'=>$Supplier_id])->field('is_check,add_time')->find();
                if($is_check['is_check'] == 4){
                    $this->ajaxReturn(['status'=>0,'info'=>'申请已经提交,请耐心等待']);
                }

                M("xiaodai")->where(['id'=>$Supplier_id])->setField('is_check',4);

                $data['is_check'] = 0;
                $data['update_time'] = time();
                $data['add_time'] = $is_check['add_time'];
                $data['user_id'] = $Supplier_id;
                $res2 = M("xiaodai_examine")->fetchsql(false)->add($data);
                if($res2 !==false){
                    $this->ajaxReturn(array('status'=>1,'info'=>"操作成功"));
                }else{
                    $this->ajaxReturn(array('status'=>0,'info'=>"操作失败"));
                }
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>"小贷公司不存在！"));
            }
        }
    }

    // 标记预约处理状态
    public function changeStatus(){
        if(IS_AJAX){
            $id = I("param.id");
            $m = M("yuyue");
            $res = $m->where("id=$id")->field("id,is_status")->find();
            if($res){
                $res['is_status'] = $res['is_status']==1?0:1;
                $res2 = $m->save($res);
                if($res2){
                    $arr = array("已处理","未处理");
                    $return = array(
                        "status" => 1,
                        "info" => $arr[$res['status']]
                    );
                }else{
                    $return = array(
                        "status" => 0
                    );
                }
            }else{
                $return = array(
                    "status" => 2
                );
            }
            $this->ajaxReturn($return);
        }
    }

    /**
     * 未处理预约列表
     */
    public function newyuyue(){
        $m   = M("yuyue");
        $count = $m->where(array("is_del"=>0))->count();
        $res = $m->where(array("is_del"=>0,"is_status"=>0))->order("id desc")->select();
        $this->assign("count", $count);
        $this->assign("lists", $res);
        $this->display();
    }

    /*
     * 已处理预约列表
     */
    public function oldyuyue(){
        $m   = M("yuyue");
        $count = $m->where(array("is_del"=>0))->count();
        $res = $m->where(array("is_del"=>0,"is_status"=>1))->order("id desc")->select();
        $this->assign("count", $count);
        $this->assign("lists", $res);
        $this->display();
    }

    /**
     * 预约详情
     */
    public function yuyue_detail(){
        $m   = M("yuyue");
        $id=I('param.id');
        $res = $m->where(array("is_del"=>0,"id"=>$id,))->find();
        $this->assign("memberdetail", $res);
        $this->display();
    }


    /**
     * 预约列表
     */
    public function search_yuyue(){
        $tel=I('param.search_text');
        $is_status=I('param.is_status');
       $this->ss_yuyue($tel,$is_status);
    }

    public function search_yuyue2(){
        $tel=I('param.search_text');
        $is_status=I('param.is_status');
        $this->ss_yuyue($tel,$is_status);
    }
    public function ss_yuyue($tel,$is_status){

        $m   = M("yuyue");
        $count = $m->where(array("is_del"=>0,'telephone'=>$tel,'is_status'=>$is_status))->count();
        $res = $m->where(array("is_del"=>0,'telephone'=>$tel,'is_status'=>$is_status))->order("id desc")->select();
        $this->assign("title", $tel);
        $this->assign("count", $count);
        $this->assign("lists", $res);
        if( $is_status==0){
            $this->display('index/newyuyue');
        }else{
            $this->display('index/oldyuyue');
        }
    }

    /*广告列表*/

    public function banner(){
        /*$where = '';

        $type=I('get.type');
        $where['classid']=$type;
        */
        $action=M('banner');
        $result=$action->join('as b LEFT JOIN app_bannertype as bt ON b.type=bt.id')
            ->field('bt.classname,b.pic,b.url,b.title,b.title1,b.type,b.id')
            ->order('b.type asc')->select();


        foreach($result as $k=>$v){
            $result[$k]["pic"] = "/".trim(trim($v["pic"],"."),"/");
        }


        $bannertype=M('bannertype');
        $bannertypelist=$bannertype->field("id,classname")->where(array('isdel'=>0))->order('sort asc')->select();

        $this->assign('bannertypelist',$bannertypelist);

        $count=count($result);
        $this->assign('piclist', $result);
        $this->assign('count', $count);
        $this->display();
    }


/**
     * 添加广告
     *
     * @author Chandler_qjw  ^_^
     */
    public function addpic(){
        if(IS_AJAX){
            $action=M('banner');
            $type=I('post.type');
            $pic=I('post.pic');
            $url=I('post.url');
            $title=I('post.title');
            $title1=I('post.title1');
            //$_SERVER['SERVER_NAME']."/Wxin/Product/products/id/".
            if(!$type || !$pic){
                $this->ajaxReturn(array("status"=>0,"info"=>"缺少参数"));
            }
            $data["pic"] = $pic;
            $data["type"] = $type;
            $data["url"] = $url;
            $data["title"] = $title;
            $data["title1"] = $title1;
            $result=$action->add($data);
            if($result){
                $this->ajaxReturn(array("status"=>1,"info"=>"添加成功"));
            }else{
                $this->ajaxReturn(array("status"=>0,"info"=>"添加失败"));
            }
        }
    }

    /**
     * 修改广告
     *
     * @author Chandler_qjw  ^_^
     */
    public function editpic(){
        if(IS_AJAX){
            $action=M('banner');
            $title=I('post.title');
            $title1=I('post.title1');
            $type=I('post.type');
            $id=I('post.id');
            $pic=I('post.pic');
            $url=I('post.url');
            if(!$type || !$id || !$pic){
                $this->ajaxReturn(array("status"=>0,"info"=>"缺少参数"));
            }
            $data["pic"] = $pic;
            $data["type"] = $type;
            $data["url"] = $url;
            $data["title"] = $title;
            $data["title1"] = $title1;
            $result=$action->where(array("id"=>$id))->save($data);
            if($result){
                $this->ajaxReturn(array("status"=>1,"info"=>"修改成功"));
            }else{
                $this->ajaxReturn(array("status"=>0,"info"=>"修改失败"));
            }
        }
    }



    /**
     * 删除广告
     *
     * @author Chandler_qjw  ^_^
     */
    public function delpic(){
        $picid=I('get.id');
        $action=M('banner');
        if(!empty($picid)){

            $arr=$action->where('id='.$picid)->find();
            $result=$action->where('id='.$picid)->delete();

            if($result){
                if($arr['classid']==1){
                    $this->success('删除成功');exit;
                }else{
                    $this->success('删除成功');exit;
                }
            }else{
                $this->error('删除失败');
            }
        }
    }



    // /**
    //  * 添加首页轮换图
    //  *
    //  * @author Chandler_qjw  ^_^
    //  */
    // public function addpic(){
    //     if(IS_AJAX){
    //         $action=M('slide');
    //         $type=I('post.type');
    //         $pic=I('post.pic');
    //         $url=I('post.url');
    //         if(!type || !pic){
    //             $this->ajaxReturn(array("status"=>0,"info"=>"缺少参数"));
    //         }
    //         $data["pic"] = $pic;
    //         $data["type"] = $type;
    //         $data["url"] = $url;
    //         $result=$action->add($data);
    //         if($result){
    //             $this->ajaxReturn(array("status"=>1,"info"=>"添加成功"));
    //         }else{
    //             $this->ajaxReturn(array("status"=>0,"info"=>"添加失败"));
    //         }
    //     }
    // }

    // *
    //  * 修改首页轮换图
    //  *
    //  * @author Chandler_qjw  ^_^

    // public function editpic(){
    //     if(IS_AJAX){
    //         $action=M('slide');
    //         $type=I('post.type');
    //         $id=I('post.id');
    //         $pic=I('post.pic');
    //         $url=I('post.url');
    //         if(!type || !$id || !pic){
    //             $this->ajaxReturn(array("status"=>0,"info"=>"缺少参数"));
    //         }
    //         $data["pic"] = $pic;
    //         $data["type"] = $type;
    //         $data["url"] = $url;
    //         $result=$action->where(array("id"=>$id))->save($data);
    //         if($result){
    //             $this->ajaxReturn(array("status"=>1,"info"=>"修改成功"));
    //         }else{
    //             $this->ajaxReturn(array("status"=>0,"info"=>"修改失败"));
    //         }
    //     }
    // }









     /*-----广告分类列表----*/


    /**
     * 广告分类列表
     */
    public function bannertype(){
        $m   = M("bannertype");
        $count = $m->where(array("isdel"=>0))->count();
        //$p = getpage($count, 3);
        //$res = $m->where(array("pid"=>0, "isdel"=>0))->limit($p->firstRow,$p->listRows)->order("sort desc")->select();
		$res = $m->where(array("pid"=>0, "isdel"=>0))->order("sort desc")->select();
        $bannertype = $m->where(array("pid"=>0, "isdel"=>0))->order("sort desc")->field("id,classname")->select();
        foreach($res as $k=>$v){
            $res[$k]["data"] = $m->where(array("pid"=>$v['id'], "isdel"=>0))->select();
        }
        //$this->assign("page",  $p->show());
        //var_dump($res);
        $this->assign("cache", $res);
        $this->assign("bannertype",  $bannertype);
        $this->display();
    }

    /**
     * 增加广告分类
     */
    public function addBannertype(){
        if(IS_AJAX){
            $classname = I("post.classname");
            $pid       = I("post.fid");
            $pic       = I("post.pic");
            $sort      = I("post.sort");
            $m = M("bannertype");
            $res = $m->where(array("classname"=>$classname, "pid"=>$pid, "isdel"=>0))->find();
            if($res){
                $this->ajaxReturn(array("status"=>0, "info"=>"类名已存在！"));
            }
            $data['classname'] = $classname;
            $data['pid']       = $pid;
            $data['sort']      = $sort;
            $data['create_at'] = time();
            $pic && $data['pic'] = $pic;
            $res = $m->add($data);
            if($res){
                $this->ajaxReturn(array("status"=>1, "info"=>"增加成功！"));
            }else{
                $this->ajaxReturn(array("status"=>0, "info"=>"新增失败！"));
            }
        }
    }

    /**
     * 删除广告分类
     */
    public function delBannertype(){
        $id = I("id");
        $m  = M("bannertype");
        $data = $m->find($id);
        if(!$data){
            $this->error("分类不存在!");
        }
        if($data['pid']){
            $res = $m->where(array("id"=>$id))->setField("isdel", 1);
            if($res){
                $this->success("删除成功！");die;
            }else{
                $this->error("删除失败！");
            }
        }else{
            $res1 = $m->where(array("id"=>$id))->setField("isdel", 1);
            $res2 = $m->where(array("pid"=>$id))->setField("isdel", 1);
            if($res1!==false && $res2!==false){
                $this->success("删除成功！");die;
            }else{
                $this->error("删除失败！");
            }
        }
    }

    /**
     * 编辑广告分类
     */
    public function editBannertype(){
        if(IS_AJAX){
            $id        = I("post.bannertypegoryid");
            $classname = I("post.classname");
            $pid       = I("post.fid");
            $pic       = I("post.pic");
            $sort      = I("post.sort");
            $m = M("bannertype");
            $map = array(
                "classname" => $classname,
                "pid"       => $pid,
                "id"        => array("neq", $id),
                "isdel"     => 0,
            );
            $res = $m->where($map)->find();
            if($res){
                $this->ajaxReturn(array("status"=>0, "info"=>"类名已存在！"));
            }
            $parid = $m->where(array("id"=>$id, "isdel"=>0))->getField("pid");
            if($parid == 0 && $pid != 0){
                $this->ajaxReturn(array("status"=>0, "info"=>"顶级分类无法改变分类！"));
            }
            $data['classname'] = $classname;
            $data['pid']       = $pid;
            $data['sort']      = $sort;
            $pic && $data['pic'] = $pic;
            $res = $m->where(array('id'=>$id))->save($data);
            if($res !== false){
                $this->ajaxReturn(array("status"=>1, "info"=>"修改成功！"));
            }else{
                $this->ajaxReturn(array("status"=>0, "info"=>"修改失败！"));
            }
        }
    }

    /**
     * 官网配置详情Jaw
     */
    public function indexConfig(){
        if(IS_POST){
            $data['tl1']            = trim(I("post.tl1"));
            $data['tl2']            = trim(I("post.tl2"));
            $data['tl3']            = trim(I("post.tl3"));
            $data['tl4']            = trim(I("post.tl4"));
            $data['key1']           = trim(I("post.key1"));
            $data['key2']           = trim(I("post.key2"));
            $data['key3']           = trim(I("post.key3"));
            // $data['key4']           = trim(I("post.key4"));
            // $data['key5']           = trim(I("post.key5"));
            // $data['theme1']         = trim(I("post.theme1"));
            // $data['theme2']         = trim(I("post.theme2"));
            // $data['theme3']         = trim(I("post.theme3"));
            $data['qq_num']         = trim(I("post.qq_num"));
            // $data["detail"]         = trim(I("post.detail"));
            // $pic = I("post.weixin_code");
            // if(!empty( $pic)){
            //     $data['weixin_code']    = $pic;
            // }
            // $data['return_price']   = I("post.return_price");
            $res = M("shop_config")->where("id=1")->save($data);
            if($res !== false){
                $this->success("保存成功！");exit;
            }else{
                $this->error("保存失败！");
            }
        }
        $res = M("shop_config")->where(array("id"=>1))->find();
        $this->assign("res",$res);
        $this->display();
    }

    /*删除多条记录限时抢购列表*/
    public function delallbanner(){

        if(IS_POST){
            $id = I('post.id');
            $arr = explode('_',$id);
            $newsid = array_filter($arr);
            foreach ($newsid as $key => $vo) {
                $del = M('banner')->where(array('id'=>$vo))->delete();
            }
            if($del){
                $result = array('status'=>1, 'info'=>'删除成功');
                echo json_encode($result);exit;
            }
        }
    }

    public function seoConfig(){
        $m = M('seo_config');
        $res = $m->find(1);
        $this->assign("cache", $res)->display();
    }

    public function seoChange(){
        if(IS_AJAX){
            $data['url']         = I("post.url");
            $data['logo']        = I("post.logo");
            $data['title']       = I("post.title");
            $data['keywords']    = I("post.keywords");
            $data['description'] = I("post.description");
            $data['copyright']   = I("post.copyright");

            if(empty($data['logo'])){
                unset($data['logo']);
            }
            if(!preg_match("/(http|https):\/\/[\w\-_]+(\.[\w\-_]+)+/i", $data['url'])){
                $this->ajaxReturn(array("status"=>0,"info"=>"网站网址格式错误！"));
            }
            if(empty($data['title'])){
                $this->ajaxReturn(array("status"=>0,"info"=>"SEO标题不能为空！"));
            }
            if(empty($data['keywords'])){
                $this->ajaxReturn(array("status"=>0,"info"=>"SEO关键字不能为空！"));
            }
            if(empty($data['description'])){
                $this->ajaxReturn(array("status"=>0,"info"=>"SEO描述不能为空！"));
            }
            if(empty($data['copyright'])){
                $this->ajaxReturn(array("status"=>0,"info"=>"版权不能为空！"));
            }




            $res = M("seo_config")->where(array('id'=>1))->save($data);
            if($res!==false){
                $this->ajaxReturn(array("status"=>1,"info"=>"修改成功"));
            }else{
                $this->ajaxReturn(array("status"=>0,"info"=>"修改失败！"));
            }
        }
    }







    /*wzz
     * 运费规则配置
     */
    public function freightfee(){
        $cache=M('freight_config')->where(array('is_del'=>0))->order('sort desc')->select();

        $cityAll=M('region')->where(array('region_type'=>1))->select();

        foreach($cache as $key=>$val)
        {
            $cache[$key]['region_name'] = M('frei_link_region')->alias('a')->join(' LEFT JOIN app_region as b ON a.region_id=b.id ')->where(array('a.freight_id'=>$val['id'],'a.status'=>1))->getField('region_name',true);
            $cache[$key]['region_name']=implode('-',$cache[$key]['region_name']);
        }

        $this->assign('cityAll',$cityAll);
        $this->assign('count',count($cache));

        $this->assign('cache',$cache);
        $this->display();
    }

    /**wzz
     * 添加/编辑运费规则
     */
    public function addfreightfee(){
        if(IS_AJAX){

            $action=M('freight_config');

            $id=I('post.id');
            $first_price=I('post.first_price');
            $next_price=I('post.next_price');
            $ratio=I('post.ratio');
            $sort=I('post.sort');


            if(!$first_price || !$next_price){
                $this->ajaxReturn(array("status"=>0,"info"=>"缺少参数"));
            }
            $data["first_price"] = $first_price;
            $data["next_price"] = $next_price;
            $data["ratio"] = $ratio;
            $data["sort"] = $sort;
            $data['create_at']=time();

            if($id){
                $res =$action->where(array('id'=>$id))->save($data);
            }else{
                $res = $action->add($data);
            }
            if($res){
                $this->ajaxReturn(array("status"=>1,"info"=>$id?"修改成功！":"添加成功！",'url'=>U('Supplier/Index/freightfee')));
            }else{
                $this->ajaxReturn(array("status"=>0,"info"=>$id?"修改失败！":"添加失败！"));
            }
        }
    }

    /**wzz
     * 删除运费规则
     */
    public function delfreightfee()
    {
        if (IS_POST) {
            $id = I('post.id');
            $arr = explode('_', $id);
            $newsid = array_filter($arr);
            foreach ($newsid as $key => $vo) {
                $del = M('freight_config')->where(array('id' => $vo))->delete();
            }
            if ($del) {
                $this->ajaxReturn(array("status" => 1, "info" => "删除成功"));
            } else {

                $this->ajaxReturn(array("status" => 0, "info" => "删除失败"));
            }
        }
    }


    /*wzz
     * 获取当前规则的省份
     * */
   public function getcity(){
       $id=I("post.id");
       $city=M('frei_link_region')->field('freight_id,region_id')->select();                                                    //已经选择过的省份;;


       foreach($city as $key =>$val)
       {
           $cityoall[$key]=$val['region_id'];               //全部被选择的
           if($val['freight_id']==$id){
               $city_now[]=$val['region_id'];               //此规则选择的
           }
       }
       $cityold=array_diff($cityoall,$city_now);            //除此规则被选择的
       $this->assign('cityold',$cityold);               //除此规则被选择的                禁用
       $this->assign('city_now',$city_now);             //此规则选择的            chencked

    }

    /*添加城市*/

    public function addcity()
    {
        if (IS_AJAX) {
            $m=M("frei_link_region");
            $id = I("post.id");
            $cityid = I("post.city");

            $res=M('frei_link_region')->where(array('freight_id'=>$id))->select();          //取出关联表的数据
            //dump($res);
            foreach($res as $key =>$val)
            {
                $ress[]=$val['region_id'];
                if($val['status']==0)
                {
                    $res0[]=$val['region_id'];
                }elseif($val['status']==1){
                    $res1[]=$val['region_id'];
                }
            }

            $resdif=array_diff($ress,$cityid);
            foreach($cityid as $k =>$v)
            {
                if(in_array($v,$res0)){
                    $m->where(array('freight_id'=>$id,'region_id'=>$v))->setField('status',1);
                    continue;
                }elseif(in_array($v,$res1)){
                    continue;
                }else{
                    $data=array(
                        'freight_id'=>$id,
                        'region_id'=>$v
                    );
                    $m->add($data);
                    continue;
                }
            }

            foreach($resdif as $ke=>$va)
            {
                $m->where(array('freight_id'=>$id,'region_id'=>$va))->setField('status',0);
            }

            $this->ajaxReturn(array("status"=>1,"info"=>"操作成功"));

        }
    }



    public function updatepwd(){
        $action=D('User');
        $pass=I('post.password');
        if($pass){
            $md5_pass=md5($pass);
            $re=$action->where("username='".$_SESSION['supplier_name']."'")->setField('password',$md5_pass);
            if($re){
                $this->success('修改成功',U('/Supplier/Index/index'));die;
            }else{
                $this->error('修改失败');die;
            }
        }
        $this->assign('munetype',9);
        $this->display('updatepwd');
    }
	
	
	
	
	//服务=========================================================2017.03.03 22:02
	
    /**
     * 服务分类列表
     */
    public function servicetype(){
        $m   = M("servicetype");
        $count = $m->where(array("isdel"=>0))->count();
        //$p = getpage($count, 10);
        //$res = $m->where(array("pid"=>0, "isdel"=>0))->limit($p->firstRow,$p->listRows)->order("sort asc")->select();
		$res = $m->where(array("pid"=>0, "isdel"=>0))->order("sort asc")->select();
        $servicetype = $m->where(array("pid"=>0, "isdel"=>0))->order("sort asc")->field("id,classname")->select();
        foreach($res as $k=>$v){
            $res[$k]["data"] = $m->where(array("pid"=>$v['id'], "isdel"=>0))->select();
        }
		$this->assign('count',$count);
        //$this->assign("page",  $p->show());
        $this->assign("cache", $res);
        $this->assign("servicetype",  $servicetype);
        $this->display();
    }




    //服务列表
    public function servicelist(){
        $m = M('service');
        $info = $m->alias('a')
				->field('a.*,b.classname,b.pid as tid')
        		->join('LEFT JOIN app_servicetype as b ON a.type_id=b.id')
        		->order('a.type_id asc,a.sort asc')
				->select();
				
		foreach($info as $key =>$val){
			$info[$key]['p_classname']=M('servicetype')->where(array('id'=>$val['tid']))->getField('classname');
		}			
        $this->assign('info',$info);
        $this->display();
    }



    /**
     * 添加服务
     */
    public function addservice(){

        if(IS_POST){
					
			$data['type_id'] = I('post.type');
            $data['content'] = I('post.content');
            $data['title_en'] = I('post.title_en');
            $data['addtime'] = time();
/*             if(I('post.pic')){
                $data['img'] = I('post.pic');
            } */
 //           $data['digest'] = $_POST['digest'];

            $rs=M('service')->add($data);
            if($rs){
                $this->success('添加成功！',U('/Supplier/Index/servicelist'));exit;
            }else{
                $this->error('添加失败！');exit;
            }

        }
		$mm=M('servicetype');
		$typeList = $mm->where(array('pid'=>0))->select();
		foreach($typeList as $key=>$val){
			$typeList[$key]['sub']=$mm->where(array('pid'=>$val['id']))->select();
		}
		$this->assign('typeList',$typeList);
        $this->display();
    }

    /**
     * 编辑服务
     */

    public function editservice(){
        $M = M('service');
        $where['id'] = I('get.id');
        $info = $M->where($where)->find();

        //$info['service']=str_ireplace('\"','"',htmlspecialchars_decode($info['service']));

        if(IS_POST){
            $where['id'] = I('post.id');
            $data['content'] = I('post.content');
            $data['title_en'] = I('post.title_en');
            $data['type_id'] = I('post.type');
            $rs=$M->where($where)->save($data);
            if($rs){
                $this->success('保存成功！');exit;
            }else{
                $this->error('保存失败！');exit;
            }

        }

        $this->assign("info",$info);
		$this->assign('type',$where['id']);
		
		$mm=M('servicetype');
		$typeList = $mm->where(array('pid'=>0))->select();
		foreach($typeList as $key=>$val){
			$typeList[$key]['sub']=$mm->where(array('pid'=>$val['id']))->select();
		}
		$this->assign('typeList',$typeList);

        $this->display();
    }
    /**
     * 删除服务
     */

    public function delservice(){
        $where['id'] = I('get.id');
        $rs=M('service')->where($where)->delete();
        if($rs){
            $this->success('删除成功！',U('/Supplier/Index/servicelist'));exit;
        }else{
            $this->error('删除失败！');exit;
        }
    }

    // 服务列表
    public function server(){
        $res = M("service")->where(array("type"=>"1"))->order('id asc')->select();
        $this->assign("cache", $res);
        $this->assign("service", 3);
        $this->display();
    }

// ÐÞ¸ÄÖ÷Óª·þÎñ²úÆ·
    public function editServer(){
        $id = I('post.id');
        $data = array(
            "title"   => I("post.title"),
            "digest" => $_POST["service"],
            "url"     => I("post.url"),
        );
        $res = M("service")->where(array("id"=>$id))->order('id asc')->save($data);
        if($res){
            $this->ajaxReturn(array("status"=>1 ,"info"=>"保存成功！"));
        }else{
            $this->ajaxReturn(array("status"=>0 ,"info"=>"保存失败！"));
        }
    }


    // Ö÷Óª·þÎñ²úÆ·×Ö·ÖÀàÁÐ±í
    public function servercont(){
        $id = I('get.id');
        $res = M("serviceServer")->where(array("classid"=>$id))->order('id asc')->select();
        $this->assign("cache", $res);
        $this->assign("service", 3);
        $this->display();
    }

    // Ìí¼Ó·þÎñ²úÆ·×Ö·ÖÀàÁÐ±í
    public function addserver(){
        $res = M("service")->where(array("type"=>"1"))->order('id asc')->select();
        if(IS_POST){

            $data['title'] = I('post.title');
            $data['service'] = I('post.service');
            if(I('post.pic')){
                $data['img'] = I('post.pic');
            }
            $data['classid'] = I('post.type');
            $rs=M('serviceServer')->add($data);
            if($rs){
                $this->success('新增成功！',U('/Supplier/Index/servercont/id/'.$data['classid'].''));exit;
            }else{
                $this->error('新增失败！');exit;
            }

        }

        $this->assign("caches", $res);
        $this->assign("service", 3);
        $this->display();
    }


    /**
     * ÐÞ¸Ä
     */
    public function editservers(){
        $M = M('serviceServer');
        $where['id'] = I('get.id');
        $info = $M->where($where)->find();
        if(IS_POST){
            $where['id'] = I('post.id');
            $data['title'] = I('post.title');
            $data['service'] = I('post.service');
            if(I('post.pic')){
                $data['img'] = I('post.pic');
            }
            $data['classid'] = I('post.type');
            $rs=$M->where($where)->save($data);
            if($rs){
                $this->success('保存成功！',U('/Supplier/Index/servercont/id/'.$data['classid'].''));exit;
            }else{
                $this->error('保存成功！');exit;
            }
        }
        $res = M("service")->where(array("type"=>"1"))->order('id asc')->select();
        $this->assign("caches", $res);

        $this->assign("info",$info);
        $this->assign("service",2);
        $this->display();
    }


    public function delserver(){
        $M = M('serviceServer');
        $where['id'] = I('get.id');
        $info = $M->where($where)->find();
        $classid=$info['classid'];
        $rs=M('serviceServer')->where($where)->delete();
        if($rs){
            $this->success('删除成功！',U('/Supplier/Index/servercont/id/'.$classid.''));exit;
        }else{
            $this->error('删除失败！');exit;
        }
    }

    public function ceshi(){
        mysql_check();
    }



    /**
     * 增加服务分类
     */
    public function addServicetype(){
        if(IS_AJAX){
            $classname = I("post.classname");
            $pid       = I("post.fid");
            $pic       = I("post.pic");
            $sort      = I("post.sort");
            $m = M("servicetype");
            $res = $m->where(array("classname"=>$classname, "pid"=>$pid, "isdel"=>0))->find();
            if($res){
                $this->ajaxReturn(array("status"=>0, "info"=>"类名已存在！"));
            }
            $data['classname'] = $classname;
            $data['pid']       = $pid;
            $data['sort']      = $sort;
            $data['create_at'] = time();
            $pic && $data['pic'] = $pic;
            $res = $m->add($data);
            if($res){
                $this->ajaxReturn(array("status"=>1, "info"=>"增加成功！"));
            }else{
                $this->ajaxReturn(array("status"=>0, "info"=>"新增失败！"));
            }
        }
    }

    /**
     * 删除服务分类
     */
    public function delServicetype(){
        $id = I("id");
        $m  = M("servicetype");
        $data = $m->find($id);
        if(!$data){
            $this->error("分类不存在!");
        }
        if($data['pid']){
            $res = $m->where(array("id"=>$id))->setField("isdel", 1);
            if($res){
                $this->success("删除成功！");die;
            }else{
                $this->error("删除失败！");
            }
        }else{
            $res1 = $m->where(array("id"=>$id))->setField("isdel", 1);
            $res2 = $m->where(array("pid"=>$id))->setField("isdel", 1);
            if($res1!==false && $res2!==false){
                $this->success("删除成功！");die;
            }else{
                $this->error("删除失败！");
            }
        }
    }

    /**
     * 编辑服务分类
     */
    public function editServicetype(){
        if(IS_AJAX){
            $id        = I("post.servicetypegoryid");
            $classname = I("post.classname");
            $pid       = I("post.fid");
            $pic       = I("post.pic");
            $sort      = I("post.sort");
            $m = M("servicetype");
            $map = array(
                "classname" => $classname,
                "pid"       => $pid,
                "id"        => array("neq", $id),
                "isdel"     => 0,
            );
            $res = $m->where($map)->find();
            if($res){
                $this->ajaxReturn(array("status"=>0, "info"=>"类名已存在！"));
            }
            $parid = $m->where(array("id"=>$id, "isdel"=>0))->getField("pid");
            if($parid == 0 && $pid != 0){
                $this->ajaxReturn(array("status"=>0, "info"=>"顶级分类无法改变分类！"));
            }
            $data['classname'] = $classname;
            $data['pid']       = $pid;
            $data['sort']      = $sort;
            $pic && $data['pic'] = $pic;
            $res = $m->where(array('id'=>$id))->save($data);
            if($res !== false){
                $this->ajaxReturn(array("status"=>1, "info"=>"修改成功！"));
            }else{
                $this->ajaxReturn(array("status"=>0, "info"=>"修改失败！"));
            }
        }
    }
	 public function addImage(){
        $data = $this->uploadImg();
        $this->ajaxReturn($data);
    }
    public function uploadImg() {

        $upload = new \Think\UploadFile;
//        $upload = new upload();// 实例化上传类
        $upload->maxSize  = 3145728 ;// 设置附件上传大小
        $upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg','svg');// 设置附件上传类型
        $savepath='./Uploads/Picture/uploads/'.date('Ymd').'/';
        if (!file_exists($savepath)){
            mkdir($savepath);
        }
        $upload->savePath =  $savepath;// 设置附件上传目录
        if(!$upload->upload()) {// 上传错误提示错误信息
            $this->error($upload->getErrorMsg());
        }else{// 上传成功 获取上传文件信息
            $info =  $upload->getUploadFileInfo();
        }
        return $info;
    }

	//案例
    public function master(){
        $main = M("master_main");
        $mains = $main->where(array('isdel'=>0))->select();
        $this->assign('main',$mains);
        $this->display();
    }

    //案例添加
    public function addmaster(){
    if(IS_POST){
        $data = I("post.");
        $main = M("master_main");
        $pic = M("master_pic");
        $data = I("post.");
        $master_pic = $data['pic1'];
        $arr['master_name'] = $data['goods_name'];
        $arr['master_info'] = $data['goods_info'];
        $arr['member_name'] = $data['member_name'];
        $arr['member_info'] = $data['detail'];
        $arr['is_display']= $data['is_display'];
        $arr['pic'] = $data['logo_pic'];
        $arr['add_time'] = time();
        $res = $main->add($arr);
        if(!$res==false){
            foreach($master_pic as $k=>$v){
                $slide_data = array(
                        "zid"   => $res,
                        "time"  => time(),
                        "pic"   => $v,
                    );
                $pic->add($slide_data);
            }
            $this->success("新增成功！",U('Supplier/index/master'));exit;
        }else{
            $this->error("新增失败！");
        }
    }
        $this->display();
    }

    /**
     * 修改案例状态
     */
    public function changes(){
        if(IS_AJAX){
            $id   = I("post.id");
            $item = I("post.item");
            $m    = M("master_main");
            $res  = $m->where(array("id"=>$id))->find();
            if(!$res){
                $this->ajaxReturn(array("status"=>0 ,"info"=>"修改失败！"));
            }
            $res2 = $m->where(array("id"=>$id))->setField($item, 1-intval($res[$item]));
            if($res2){
                $sale=$m->where(array("id"=>$id))->getfield("is_display");
                $arr = array(1,2);
                $this->ajaxReturn(array("status"=>$arr[$res[$item]]));
            }
            $this->ajaxReturn(array("status"=>0 ,"info"=>"修改失败！"));
        }
    }

 /**
     * 编辑产品
     */
    public function editmaster(){
        if(IS_POST){
            $main = M("master_main");
            $pic = M("master_pic");
            $data = I("post.");
            $id   = $data['id'];
            $slide_pic = $data['pic1'];
            $data['member_info'] = $data['detail'];
            if($data['logo_pic']){
                $data['pic'] = $data['logo_pic'];                
            }
            unset($data['id']);
            unset($data['detail']);
            unset($data['pic1']);
            $data['add_time'] = time();
            if(!$id){
                $this->error("缺少参数！");
            }
            $res = $main->where(array("id"=>$id))->save($data);
            if($res !== false){

                foreach($slide_pic as $k=>$v){
                    $slide_data = array(
                        "zid"   => $id,
                        "time"  => time(),
                        "pic"   => $v,
                        ); 
                    $pic->add($slide_data);
                }
                $this->success("修改成功!",U('Supplier/index/master'));exit;
            }else{
                $this->error("修改失败！");
            }
        }
               
        $id = I("id");
        if(!$id){
            echo "<script>alert('缺少参数！');window.history.back();</script>";die;
        }
        $goods = M("master_main")->where(array('id'=>$id))->find();
        $goods_pic = M("master_pic")->where(array('zid'=>$id))->select();
        $goods['master_pic']=$goods_pic;
        if(!$goods){
            echo "<script>alert('无此商品！');window.history.back();</script>";die;
        }
        $this->assign('goods',$goods);        
        $this->display();
    }


    /**
     * 删除产品
     */
    public function delmaster(){
        if(IS_AJAX){
            $id  = I("ids");
            $type  = I("type");
            //var_dump($_POST);exit;
            if($type){
                $m   = M("bathgoods");
            }else{
                $m   = M("goods");
            }

            $ids = array_filter(explode("-", $id));
            if(empty($ids)){
                $this->ajaxReturn(array("status"=>0, "info"=>"请选择商品！"));
            }
            foreach($ids as $v){
                $res = $m->where(array("id"=>$v))->save(array("isdel"=>1));
                if($res === false){
                    $this->ajaxReturn(array("status"=>0, "info"=>"删除商品失败！"));
                }
            }
            $this->ajaxReturn(array("status"=>1, "info"=>"删除商品成功！"));
        }
        $id  = I("id");
        $res = M("master_main")->where(array("id"=>$id))->save(array("isdel"=>1));
        if($res!==false){
            $this->success("删除成功！");die;
        }
        $this->error("删除失败！");die;
    }

    public function delmasters(){
        if(IS_AJAX){
            $id = I('id');
            $m = M('master_pic');
            if($id){
                $del = $m->where(array('id'=>$id))->delete();
                if($del){
                    $arrys = array('status'=>1);
                }else{
                    $arrys = array('status'=>0,'info'=>"删除失败");
                }

                echo json_encode($arrys);exit;

            }


        }

    }
	public function small_seo(){
		$id = session('supplier_id');
		if(IS_AJAX){
			$data['title'] = I('post.title');
			$data['key']   = I('post.key');
			$data['des']   = I('post.des');
			$res = M('SmallSeo')->where(array('supplier_id'=>$id))->save($data);
			if($res){
				$this->ajaxReturn(array('status'=>1, 'info'=>'修改成功！'));
			}else{
				$this->ajaxReturn(array('status'=>0, 'info'=>'修改失败！'));
			}
		}
		
		$seo = M('SmallSeo')->where(array('supplier_id'=>$id))->find();
		$this->assign('seo',$seo);
		$this->display();
	}

    /**
     * 小贷公司客服配置*20170714*lq
     */
    public function small_config()
    {
        if (IS_AJAX) {
            $data = I("post.");
            //验证
            if(!$data["service_name"]){
                $this->ajaxReturn(array("status"=>0,"info"=>'请填写客服姓名'));
            }
            if(!$data["service_phone"]){
                $this->ajaxReturn(array("status"=>0,"info"=>'请填写客服电话号'));
            }
            if(!$data["service_qq"]){
                $this->ajaxReturn(array("status"=>0,"info"=>'请填写客服QQ'));
            }
            if(!$data["service_wechat"]){
                $this->ajaxReturn(array("status"=>0,"info"=>'请填写客服微信号'));
            }
            if(!$data["service_ewm"]){
                $this->ajaxReturn(array("status"=>0,"info"=>'请填写客服二维码'));
            }
            $data['update_time'] = time();
            $data['supplier_id'] = $this->supplier_id;
            if($data["id"]){
                $res = M("small_config")->where(array("id"=>$data["id"]))->save($data);
            }else{
                $res = M("small_config")->add($data);

            }


            if ($res !== false) {
                $this->ajaxReturn(array("status" => 1, "info" => $data['id']?"修改成功":"添加成功！"));
            } else {
                $this->ajaxReturn(array("status" => 0, "info" => $data['id']?"修改失败":"添加失败！"));
            }
        }
        $cache = M("small_config")->where(array("supllier_id" => session('supplier_id')))->find();
        $this->assign("cache", $cache);
        $this->display();
    }

    /**
     * 小贷公司客服列表*20170714*lq
     */
    public function service()
    {
        //根据小贷公司id查询其下的客服
        $supplierId = $this->supplier_id;
        $service_list = M("small_config")->where(array("supplier_id"=>$supplierId,"isdel"=>0))->select();
        $count = count($service_list);
        $p = getpage($count,10);
        $page = $p->show();
        $this->assign("page",$page);
        $service_list = array_slice($service_list,$p->firstRow,$p->listRows);
        $this->assign("service_list",$service_list);
        $this->display();
    }

    public function delServices(){
        $m = M("small_config");
        if(IS_AJAX){
            $id  = I("ids");
            $ids = array_filter(explode("-", $id));
            if(empty($ids)){
                $this->ajaxReturn(array("status"=>0, "info"=>"请选客服！"));
            }
            foreach($ids as $v){
                $res = $m->where(array("id"=>$v))->save(array("isdel"=>1));
                if($res === false){
                    $this->ajaxReturn(array("status"=>0, "info"=>"删除商品失败！"));
                }
            }
            $this->ajaxReturn(array("status"=>1, "info"=>"删除商品成功！"));
        }
        $id  = I("id");
        if(!$id){
            $this->ajaxReturn(array("status"=>0, "info"=>"请选择配置！"));
        }
        $res = $m->where(array("id"=>$id))->save(array("isdel"=>1));
        if($res!==false){
            $this->success("删除成功！");die;
        }
        $this->error("删除失败！");die;
    }

    /**
     * 修改公司资料*20170715*lq
     */
    public function company()
    {
        $memberdetail=M('xiaodai')->where('id='.$_SESSION['supplier_id'])->find();
        $this->assign('memberdetail',$memberdetail);
        $shenhe = M('supplier_examine')->where(array('supplier_id'=>$_SESSION['supplier_id']))->order('id desc')->limit(1)->find();
        $this->assign("shenhe",$shenhe);
        $this->display();
    }

    /**
     *
     * 绑定邮箱发送验证码*20170715*lq
     */
    public function sendemail(){
        if(IS_AJAX){
            $email = I('post.email');
            if(!$email){
                $this->ajaxReturn(array('status'=>0,'info'=>'邮箱格式错误'));
            }
            $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            $code = '';
            for ( $i = 0; $i < 6; $i++ )
            {
                // 这里提供两种字符获取方式
                // 第一种是使用 substr 截取$chars中的任意一位字符；
                // 第二种是取字符数组 $chars 的任意元素
                // $code .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);
                $code .= $chars[ mt_rand(0, strlen($chars) - 1) ];
            }
            $result = sendEmailCode($email,'洛凡金融验证码',$code);
            if($result){
                $data['email'] = $email;
                $data['addtime'] = time();
                $data['code'] = $code;
                $re = M('email_code')->add($data);
                if(!$re){
                    $this->ajaxReturn(array('status'=>0,'info'=>"异常，请稍候再试"));
                }

                $this->ajaxReturn(array('status'=>1,'info'=>"发送成功"));
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>"发送失败"));
            }
        }
    }

    /**
     * 邮箱配置*20170717*lq
     */
    public function emailConfig()
    {
        //查询小贷公司信息
        $memberdetail = M("xiaodai")->where(array("id"=>$this->supplier_id))->find();
        $this->assign("memberdetail",$memberdetail);
        if(IS_AJAX){
            $data = I("post.");
            //接收email
            $email = $data["email"];
            if(empty($email)){
                $this->ajaxReturn(array("status"=>0,"info"=>"请输入邮箱"));
            }
            $code = $data["code"];
            if(empty($code)){
                $this->ajaxReturn(array("status"=>0,"info"=>"请输入验证码"));
            }
            //检测验证码
            $res = checkEmail($email,$code);
            if($res["status"] != 1){
                $this->ajaxReturn($res);
            }
            //修改小贷公司邮箱
            $res= M("xiaodai")->where(array("id"=>$this->supplier_id))->setField("email",$email);
            if($res !== false){
                $this->ajaxReturn(array("status"=>1,"info"=>"绑定成功"));
            }else{
                $this->ajaxReturn(array("status"=>0,"info"=>"绑定失败"));
            }
        }
        $this->display();
    }
}

