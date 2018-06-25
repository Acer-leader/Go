<?php
namespace Admin\Controller;
use Common\Controller\CommonController;
class FenxiaoController extends CommonController {
    public function _initialize(){
        parent::_initialize();
        $this->assign("urlname", ACTION_NAME);
    }

 /**wzz 20170415
     * 分类列表
     */
    public function cateList(){
        $m     = M("inquire_cate");
        $res   = $m->where(array("pid"=>0, "isdel"=>0))->order("sort asc")->select();           //获取每页数据
        $cate  = $m->where(array("pid"=>0, "isdel"=>0))->order("sort Asc")->field("id,classname")->select();
        foreach($res as $k=>$v){
            $res[$k]["data"] = $m->where(array("pid"=>$v['id'], "isdel"=>0))->order("sort Asc")->select();
            foreach ($res[$k]["data"] as $key => $val) {
                $res[$k]["data"][$key]['child'] = $m->where(array("pid"=>$val['id'], "isdel"=>0))->order("sort Asc")->select();
            }
        }
        $this->assign("cache", $res);
        $this->assign("cate",  $cate);
        $this->assign("comptype", 0);
        $this->display();
    }


    /**wzz 20170415
     * 增加分类
     */
    public function addCate(){
        if(IS_AJAX){
            $classname 		= I("post.classname");
            $englishname 	= I("post.englishname");
            $pid       		= I("post.fid");
            $pic       		= I("post.pic");
            $pic1           = I("post.pic1");
            $pic2       	= I("post.pic2");
            $color       	= I("post.color");
            $sort           = I("post.sort");
            $is_recommend   = I("post.is_recommend");
            if(!$is_recommend){
                $is_recommend = 0;
            }
            $describe1      = I("post.describe1");
            $describe2      = I("post.describe2");
            $m = M("inquire_cate");
            $res = $m->where(array("classname"=>$classname, "pid"=>$pid, "isdel"=>0))->find();
            if($res){
                $this->ajaxReturn(array("status"=>0, "info"=>"类名已存在！"));
            }

            $data['classname']      = $classname;
            $data['is_recommend']   = $is_recommend;
            $data['englishname'] 	= $englishname;
            $data['pid']       		= $pid;
            $data['sort']      		= $sort;
            $data['pic']            = $pic;
            $data['pic1']           = $pic1;
            $data['pic2']      		= $pic2;
            $data['color'] 			= $color;
            $data['create_at'] 		= time();
            $res = $m->add($data);
            if($res){
                $this->ajaxReturn(array("status"=>1, "info"=>"增加成功！"));
            }else{
                $this->ajaxReturn(array("status"=>0, "info"=>"新增失败！"));
            }
        }
    }



    /**wzz 20170415
     * 删除分类
     */
    public function delCate(){
        $m  = M("inquire_cate");if(IS_AJAX){
            $id = I('post.id');

            $arr = explode('_',$id);
            $arr = array_filter($arr);

            foreach($arr as $key => $val){

                $data = $m->find($val);
                if(!$data){
                    $this->ajaxReturn(array("status"=>0,"info"=>"删除失败"));
                }
                if($data['pid']){
                    $res = $m->where(array("id"=>$id))->delete();
                    if($res){
                        $this->ajaxReturn(array("status"=>1,"info"=>" 删除成功","url"=>U('Admin/Goods/cate')));
                    }else{
                        $this->ajaxReturn(array("status"=>0,"info"=>"删除失败"));
                    }
                }else{
                    $res1 = $m->where(array("id"=>$id))->delete();
                    $res2 = $m->where(array("pid"=>$id))->delete();
                    if($res1!==false && $res2!==false){
                        $this->ajaxReturn(array("status"=>1,"info"=>" 删除成功","url"=>U('Admin/Goods/cate')));
                    }else{
                        $this->ajaxReturn(array("status"=>0,"info"=>"删除失败"));
                    }
                }
            }
        }
    }




    /**wzz 20170415
     * 编辑分类
     */
    public function editCate(){

        if(IS_AJAX){

            $id        = I("post.categoryid");
            $classname = I("post.classname");
            $englishname = I("post.englishname");
            $is_recommend = I("post.is_recommend");
            $pid       = I("post.fid");
            $pic       = I("post.pic");
            $pic1       = I("post.pic1");
            $pic2       = I("post.pic2");
            $color       = I("post.color");
            $sort        = I("post.sort");
            $describe1      = I("post.describe1");
            $describe2      = I("post.describe2");
            $m = M("inquire_cate");
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

            $data['classname']      = $classname;
            $data['englishname']    = $englishname;
            $data['pid']            = $pid;
            $data['color']          = $color;
            $data['sort']           = $sort;
            $data['describe1']      = $describe1;
            $data['describe2']      = $describe2;
            $data['pic']            = $pic;
            $data['pic1']           = $pic1;
            $data['pic2']           = $pic2;
            $data['is_recommend']   = $is_recommend;
            if(!$is_recommend){
                $data['is_recommend'] = 0 ;
            }
            if($pic==""){
                unset($data['pic']);
            }
            if($pic1==""){
                unset($data['pic1']);
            }
            if($pic2==""){
                unset($data['pic2']);
            }
            $res = $m->where(array('id'=>$id))->save($data);
            if($res !== false){
                $this->ajaxReturn(array("status"=>1, "info"=>"修改成功！"));
            }else{
                $this->ajaxReturn(array("status"=>0, "info"=>"修改失败！"));
            }
        }
    }
    public function inquire(){
        $cate_id=I('post.cate_id');
        $name=I('post.name');
        
 
        $this->assign('cate_id', $cate_id);
        $this->assign('name', $name);
        //查询分类
        if($cate_id){
            $pid=M('inquire_cate')->where(array('id'=>$cate_id))->getfield('pid');
            if(!$pid)
            {
                $arr=M('inquire_cate')->where(array('pid'=>$cate_id))->getfield('id', true);
                $map['cate_id'] = array('in',$arr);
            }else{
                $map['cate_id'] = $cate_id;
            }
        }
        //查询名称
        if($name)
        {
            $map['title'] = array('like',"%$name%");
        }



        $m   = M("inquire");
        $map['isdel'] = 0;

        $count=$m->where($map)->count();
        $Page  = getpage($count,10);
        $show  = $Page->show();//分页显示输出
        $res = $m->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('ID desc')->select();

        foreach($res as $k=>$v){
            $res[$k]['classname'] = M("inquire_cate")->where(array('id'=>$v['cate_id']))->getField('classname');
        }

        //分类列表
        $categorylist = M("inquire_cate")->where(array("pid"=>0, "isdel"=>0))->select();
        foreach($categorylist as $k=>$v){
            $categorylist[$k]['cate'] = M("inquire_cate")->where(array('pid'=>$v['id']))->select();

        }
        $this->assign("categorylist", $categorylist);

        $this->assign("page",$show);
        $this->assign("counts",$count);     

        $this->assign("cache", $res);

        $this->display();
    }
    public function addinquire(){

        if(IS_POST){
            $m = M("inquire");
          
            $data = I("post.");
            
            $data['create_at'] = time();
            $res = $m->add($data);
            if($res){
                $this->success("新增成功！",U('Admin/Fenxiao/inquire'));exit;
            }else{
                $this->error("新增失败！");
            }

        }
        $c    = M("inquire_cate");
        $categorylist = $c->where(array("pid"=>0, "isdel"=>0))->select();
        foreach($categorylist as $k=>$v){
            $categorylist[$k]['cate'] = $c->where(array('pid'=>$v['id'],'isdel'=>0))->select();
           /*  foreach ($categorylist[$k]['cate'] as $key => $value) {
                $categorylist[$k]['cate'][$key]['child'] = M("inquire_cate")->where(array('pid'=>$value['id']))->select();
            } */
        }
        
        $this->assign("categorylist", $categorylist);
        $this->display();
    }



    public function editinquire(){
        if(IS_POST){
            $m = M("inquire");
         
            $data = I("post.");
            $id   = $data['id'];
            
            unset($data['id']);
         
            $data['create_at'] = time();
            if(!$id){
                $this->error("缺少参数！");
            }
            $res = $m->where(array("id"=>$id,'isdel'=>0))->save($data);
            if($res !== false){
      
                $this->success("修改成功!",U('Admin/Fenxiao/inquire'));exit;
            }else{
                $this->error("修改失败！");
            }
        }
        $id = I("get.id");
        if(!$id){
            echo "<script>alert('缺少参数！');window.history.back();</script>";die;
        }
        $goods = M("inquire")->where(array('id'=>$id, "isdel"=>0))->find();
        if(!$goods){
            echo "<script>alert('无此商品！');window.history.back();</script>";die;
        }
        $c    = M("inquire_cate");
        //$goods['classname'] = $c->where(array('id'=>$goods['cate_id'],'isdel'=>0))->getField('classname');
        $goods['classname1'] = $c->where(array('id'=>$goods['cate_id1'],'isdel'=>0))->getField('classname');
        $goods['classname2'] = $c->where(array('id'=>$goods['cate_id2'],'isdel'=>0))->getField('classname');
        $goods['classname3'] = $c->where(array('id'=>$goods['cate_id3'],'isdel'=>0))->getField('classname');
        $categorylist = $c->where(array("pid"=>0, "isdel"=>0))->order('sort asc')->select();
        foreach($categorylist as $k=>$v){
            $categorylist[$k]['cate'] = $c->where(array('pid'=>$v['id']))->order('sort asc')->select();
           /*  foreach ($categorylist[$k]['cate'] as $key => $value) {
                $categorylist[$k]['cate'][$key]['child'] = M("cate")->where(array('pid'=>$value['id']))->select();
            } */
        }

        $this->assign("cache", $goods);
        $this->assign("categorylist", $categorylist);
        $this->display();
    }
 
    public function delinquire(){
        if(IS_AJAX){
            $id  = I("post.ids");
            
            $m   = M("inquire");

            $ids = array_filter(explode("-", $id));
            if(empty($ids)){
                $this->ajaxReturn(array("status"=>0, "info"=>"请选择！"));
            }
            foreach($ids as $v){
                $res = $m->where(array("id"=>$v))->save(array("isdel"=>1));
                if($res === false){
                    $this->ajaxReturn(array("status"=>0, "info"=>"删除失败！"));
                }
            }
            $this->ajaxReturn(array("status"=>1, "info"=>"删除成功！"));
        }
        $id  = I("get.id");
        $res = M("inquire")->where(array("id"=>$id))->save(array("isdel"=>1));
        if($res!==false){
            $this->success("删除成功！");die;
        }
        $this->error("删除失败！");die;
    }
    
    

	/* 审核分销商 */
    public function shenhe(){
        M()->startTrans();
		$m = M('member');
        $data = I("post.");
		
        $fenxiao_id = $data['fenxiao_id'];	//分销商id
		$fenxiao_info = $m->find($fenxiao_id);//分销商表查询
		if(!$fenxiao_info){
			$this->ajaxReturn(array('status'=>0,"info"=>"分销商不存在！"));
		}
        $status = $data['status'];
        $msg    = $data['msg'];
        $data['admin_name'] =$_SESSION['admin'];
        $data['add_time']   = time();
        if($status==2){
            $info='审核通过';
        }else{
            $info='审核驳回';
        }
	
        $res = M('fenxiao_examine')->add($data);  //分销商审核记录
		
        if(!$res){
            M()->rollback();
            $this->ajaxReturn(array('status'=>0,"info"=>$info.'失败!'));
        }
        //更新分销商
  
		$log=array(
			'is_check'=>$status,
			);
		
/* 		if($status==2){
			$log=array(
				'is_check'=>1,
				'cate' =>2
			);
		}else{
			$log=array(
				'is_check'=>1,
			);
		} */
		
		$res11 = $m->where(array('id'=>$fenxiao_id))->save($log);
        M()->commit();
        $this->ajaxReturn(array('status'=>0,"info"=>$info.'成功'));
    }


    /*审核人资料*/
    public function mxamine(){
        $id = I('get.id');
        $m = M('fenxiao_examine');
        $m_r = $m->where(array('fenxiao_id'=>$id))->order('id asc')->select(); 
        $this->assign('cache',$m_r);
        $this->display();
    }

    function getmemberlist($title,$ischeck){
        $minfo=M('fenxiao');
		$map=array();
        if(!empty($title)){
            
        }
            
        $map["isdel"] = 0;
        $map['is_check']=$ischeck?$ischeck:0;
        $count = $minfo->where($map)->count();
        $p = getpage($count,10);
        $list = $minfo->where($map)->order('id desc')->limit($p->firstRow, $p->listRows)->select();
        $date['list']   = $list;        // 赋值数据集
        $date['page']   = $p->show();   // 赋值分页输出
        return $date;
    }

    /*采购审核20170421*/
    public function lists(){
		$title = I("title");
		$is_check = I("is_check");
		if($title){
			$map['personname|telephone']  = array('like',"%$title%");
		}
		if(in_array($is_check,array('1','2','3'))){
			$map['is_check']=$is_check;
		}else{
            $map['is_check']=array('neq',0);
        }
		
		$minfo=M('member');

		$count0  =$minfo->where(array('isdel'=>0,'is_check'=>1))->count();
        $count1  =$minfo->where(array('isdel'=>0,'is_check'=>2))->count();
        $count2  =$minfo->where(array('isdel'=>0,'is_check'=>3))->count();
		
		$map["isdel"] = 0;
       
		$count= $minfo->where($map)->count();
		$p = getpage($count,10);		
		$list = $minfo->where($map)->order('id desc')->limit($p->firstRow, $p->listRows)->select();	
        foreach($list as $k=>$v){
            $list[$k]['bank']   =   M('fenxiao_bank')->where(array('is_first'=>1))->getField('card_num');
        }
        $page= $p->show();
        $this->assign('count',$count);
        $this->assign('count0',$count0);
        $this->assign('count1',$count1);
        $this->assign('count2',$count2);
        if($count>10)
        {
            $this->assign('page',$page);
        }
		$this->assign("memberlist",$list);
        $this->assign("title",I("title"));
        $this->display();
    }


    // 审核/不审核
    public function sc_shenhe(){
        if(IS_AJAX){
            $id = I("id");
            $m = M("member");
            $res = $m->where("id=$id")->field("id,is_check")->find();
            if($res){
                $res['is_check'] = $res['is_check']==1?0:1;
                $res2 = $m->save($res);
                $arr = array("未审核","已审核");
                if($res2){

                    $return = array(
                        "status" => 1,
                        "info" => $arr[$res['is_check']]
                    );
                }else{
                    $return = array(
                        "status" => 0,
                        'info'=> $arr[$res['is_check']]
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
    // 编辑个人资料
    public function destail(){
        $id = I("param.id");
        $m = M("member");
        if(IS_POST){
            // 编辑资料
            $data['personname']=I("param.personname");
            $data['telephone']=I("param.telephone");
            $data['goods_limit']=I("param.goods_limit");
            $res = $m->where(array('id'=>$id))->find();
            if($res){
                $res2 = $m->where(array('id'=>$id))->save($data);
                if($res2){
                    $info["status"] = 1;
                    $info["info"] = "更新成功";
                    $this->success("修改成功!", U('Admin/audit/lists'));exit;
                }else{
                    $info["status"] = 0;
                    $info["info"] = "更新失败";
                    $this->success("修改失败!", U('Admin/audit/lists'));exit;
                }
            }else{
                $info["status"] = 0;
                $info["info"] = "更新失败";
                $this->error("修改失败！");exit;
            }
        }

        $res = $m->where(array('id'=>$id))->find();
        $this->assign('memberdetail',$res);
        $this->display();
    }

	public function delmember(){
        $memberid=I('get.id');
        $m = M("member"); // 实例化User对象
        $rs=$m->where("id=$memberid")->delete(); // 删除id为5的用户数据
        if($rs){
            $this->success('删除成功',U('/fenxiao/Member/index'));
        }else{
            $this->error('删除失败');
        }
    }


    // 审核身份证
    public function examine(){
        if(IS_AJAX){
            $map["id"] = I("id");
            $data["isexamine"] = I("isexamine");
            $data["examine_msg"] = I("examinemsg");
            $res = M("member")->where($map)->save($data);
            if($res){
                $interface = A("Interface");
                if($data["isexamine"]==1){
                    $res2 = $interface->jpush_msg($map["id"],"您的身份证已通过审核");
                }else{
                    $res2 = $interface->jpush_msg($map["id"],"身份证审核失败：".$data["examine_msg"],"14");
                }
                $result["info"] = "操作成功";
                $result["status"] = 1;
                $result["jpush"] = $res2;
            }else{
                $result["info"] = "操作失败";
                $result["status"] = 0;
            }
            $this->ajaxReturn($result);
        }
    }

    public function updatepwd(){
        if(IS_POST){
            $action=D('member');
            $newpwd=I('param.newpass');
            $oldpwd=I('param.oldpass');
            $re=$action->getupdatepass($oldpwd,$newpwd);
            if($re==1){
                $this->success('修改成功',U('/fenxiao/index/gyindex'));exit;
            }elseif($re==2){
                $this->error('修改失败,原密码错误');exit;
            }else{
                $this->error('修改失败');exit;
            }
        }
        $this->display();
    }

    /*
     *设置分销商权限*20170713*lq
     *
     */
    public function setAuthority()
    {

        //得到分销商的id
        $id = I("get.id");
        $memberdetail = M("fenxiao")->where(array("id"=>$id))->find();
        $this->assign("memberdetail",$memberdetail);
        //查询分销商的权限
        $authority = M("xiao_authority")->where(array("user_id"=>$id))->find();
        if($authority["address_city"]){
            //查询区域信息
            $city_arr = M("region")->where(array('card'=>array("in",$authority["address_city"])))->getField('name',true);
            $citys = implode(',',$city_arr);
            $this->assign("city",$citys);
        }

        $this->assign("authority",$authority);
        $this->assign("dizhi",$authority['province']);
        $dizhi['pro'] = explode(',',$authority["province"]);
        $dizhi['city'] = explode(',',$authority["address_city"]);
        $this->assign("dizhi",$dizhi);
        //查询城市信息
        $map['leveltype'] = array(array('eq',1),array('eq',0),'or');
        $provinceList = M("region")->where($map)->select();
        foreach($provinceList as $kk=>$vv){
            if($vv['card'] != 100000) {
                $provinceList[$kk]['city'] = M("region")->where(array("parentid" => $vv['card']))->select();
            }
        }
        $this->assign('provinceList',$provinceList);


        if(IS_POST){
            $fenxiaoId = I("post.fenxiao_id");
            $memberdetail = M("fenxiao")->where(array("id"=>$fenxiaoId))->find();
            if(!$memberdetail){
                $this->error('无效贷公司信息');
            }
            $post = I("post.");
            $data = array(
                'user_id' => $fenxiaoId,
                'is_url' => $post['is_url']
            );

            if(empty($post['card']) && empty($post['china'])){
                unset($post['card']);
            }elseif(empty($post['card']) && !empty($post['china'])){
                $data['address_city'] =$post['china'];
            }else{
                $data['address_city'] = trim($post['card'],',');
            }

            if(empty($post['province'])){
                unset($post['province']);
            }else{
                //数组去重
                $province_arr = array_unique(explode(',',trim($post["province"],',')));
                $data["province"] = implode(',',$province_arr);
            }
            //根据分销商id查询权限表是否存在该公司
            $fenxiao = M("xiao_authority")->where(array('user_id'=>$fenxiaoId))->find();
            M()->startTrans();
            if($fenxiao){
                $result1 = M("xiao_authority")->where(array("user_id"=>$fenxiaoId))->save($data);
                $result2 = M("fenxiao")->where(array("id"=>$fenxiaoId))->setField("goods_limit",$post['goods_limit']);
            }else{
                $result1 = M("xiao_authority")->add($data);
                $result2 = M("fenxiao")->where(array("id"=>$fenxiaoId))->setField("goods_limit",$post['goods_limit']);
            }
            if($result1 && $result2 !== false){
                M()->commit();
                $this->success("权限设置成功");exit;
            }else{
                M()->rollback();
                $this->error("权限设置失败");exit;
            }

        }
        $this->display();
    }
    
    	    // 冻结/解冻
    public function changexiaodai(){
        if(IS_AJAX){
          
            $id = I("post.id");
            $m = M("member");
            $res = $m->where(array('id'=>$id))->field("id,fstatus")->find();
    
            if($res){
                $res['fstatus'] = $res['fstatus']==1?0:1;
                $res2 = $m->save($res);
                if($res2){
                    $arr = array("正常","冻结");
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
    public function chongzhi(){
    	$title = I('get.title');
    	if($sql){
    		$sqls['personname|telephone'] = array('like','%'.$title.'%');
    		$this->assign('title',$title);
    	}
        $count=M('Pay_fenxiao_view')->where($sqls)->count();
        $Page  = getpage($count,10);
        $show  = $Page->show();
        $res = M('Pay_fenxiao_view')->where($sqls)->limit($Page->firstRow.','.$Page->listRows)->select();
    	$this->assign('page',$show);
    	$this->assign('count',$count);
    	$this->assign('res',$res);
        $this->display();
    }

    public function set(){
    	$id = I('get.id');
    	if(IS_POST){
    		$data = I('post.');
			$telephone = $data['telephone'];
			$fenxiaoInfo = M('member')->where(array('telephone'=>$telephone,'isdel'=>0,'is_check'=>2))->find();
			if(!$fenxiaoInfo){
				$this->ajaxReturn(array('status'=>0,'info'=>"分销商不存在！"));
			}
            
     /*        if($data['money']>$fenxiaoInfo['money']){ 
                $this->ajaxReturn(array('status'=>0,'info'=>"该分销商工资不足",'url'=>U('Admin/fenxiao/chongzhi')));
            } */

    		$data['add_time'] = time();
			$res_l=array(
				'money'=>$data['money'],
				'add_time'=>time(),
				'fenxiao_id'=>$fenxiaoInfo['id']
			);
    
			M()->startTrans();
			$res = M('Pay_fenxiao')->add($res_l);
			$res1 = M('member')->where(array('id'=>$fenxiaoInfo['id']))->setInc('wallet',$data['money']);
			if($res && $res1){
				M()->COMMIT();
                $this->bill($data['money'],$fenxiaoInfo['id']);
				$this->ajaxReturn(array('status'=>1,'info'=>"充值成功",'url'=>U('Admin/fenxiao/chongzhi')));
			}else{
				M()->ROLLBACK();
				$this->ajaxReturn(array('status'=>0,'info'=>"充值失败",'url'=>U('Admin/fenxiao/chongzhi')));
			}
    		
    	}
    	$res = M('Pay_fenxiao')->find($id);
    	$this->assign('pay',$res);
    	$this->display();
    }
    //账单
    public  function bill($money,$fenxiaoid){

        $map   =   array(
            'title' =>  '充值',
            'pay'   =>  '+'.$money.'元',
            'addtime'   =>  time(),
            'fenxiao_id'   =>  $fenxiaoid,     
        );
        M('fenxiao_bill')->add($map);
        /* M('fenxiao')->where(array('id'=>$fenxiaoid))->setDec('money',$money);  */
        
    }
    public function del(){
        $id = I('get.id');
        $res = M('Pay_fenxiao')->delete($id);
        if($res){
        	$this->redirect('Admin/fenxiao/list');
        }else{
        	$this->error('删除失败',U('Admin/fenxiao/index'));
        }
    }
    
    
}
?>