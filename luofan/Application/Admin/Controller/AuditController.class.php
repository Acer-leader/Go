<?php
namespace Admin\Controller;
use Common\Controller\CommonController;
class AuditController extends CommonController {
    public function _initialize(){
        parent::_initialize();
        $this->assign("urlname", ACTION_NAME);
    }


	/* 审核小贷公司 */
    public function shenhe(){
        M()->startTrans();
		$m = M('xiaodai');
        $data = I("post.");
		$id = $data['id'];
        $supplier_id = $data['supplier_id'];	//小贷公司id

		$supplier_info = $m->find($supplier_id);//小贷公司表查询
		if(!$supplier_info){
			$this->ajaxReturn(array('status'=>0,"info"=>"公司不存在！"));
		}
        $status = $data['status'];//状态
        $msg    = $data['msg'];//审核信息
        $data['admin_name'] =$_SESSION['admin'];
        $data['add_time']   = time();
		if(!in_array($status,array('1','2'))){
			$this->ajaxReturn(array('status'=>0,"info"=>"审核失败"));
		}
        if($status==1){
            $info='审核通过';
        }else{
            $info='审核驳回';
        }


        $res = M('supplier_examine')->add($data);  //小贷公司审核记录
		
        if(!$res){
            M()->rollback();
            $this->ajaxReturn(array('status'=>0,"info"=>$info.'失败!'));
        }
        //更新小贷公司
  
		
	
		$log=array(
			'is_check'=>$status,
		);

	    //修改审核表和小贷表的状态为已审核

        M("xiaodai_examine")->where(array('id'=>$id))->save($log);

        //将审核表的信息存入小贷公司标
        $saveData = M("xiaodai_examine")->where(['id'=>$id])->find();
        $saveData['is_check'] = $status;
        if($status==1){
            $saveData['cate']="2";
        }
        $m->where(array('id'=>$supplier_id))->save($saveData);

        M()->commit();
        $this->ajaxReturn(array('status'=>0,"info"=>$info.'成功'));
    }

    public function index(){
        $admin_id = $_SESSION['admin_id'];
        $admin_cate = M('user')->where(array('id'=>$admin_id))->getField('cate');
        if($admin_cate==2){
            $this->redirect('Admin/Supplier/cgindex');
        }elseif($admin_cate==3){
            $this->redirect('Admin/Supplier/cwindex');
        }else{
            $this->redirect('Admin/Supplier/cgindex');
        }
    }
    /*审核人资料*/
    public function mxamine(){
        $id = I('get.id');
        $m = M('supplier_examine');
        $m_r = $m->where(array('supplier_id'=>$id))->order('id asc')->select(); 
        $this->assign('cache',$m_r);
        $this->display();
    }

    function getmemberlist($title,$ischeck){
        $minfo=M('xiaodai');
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
		if(in_array($is_check,array('0','1','2'))){
			$map['is_check']=$is_check;
		}else{
            $map['is_check']=0;
        }
		
		$minfo=M('xiaodai_examine');
		
		$count0  =$minfo->where(array('isdel'=>0,'is_check'=>0))->count();
        $count1  =M("xiaodai")->where(array('isdel'=>0,'is_check'=>1))->count();
        $count2  =M("xiaodai")->where(array('isdel'=>0,'is_check'=>2))->count();
		
		$map["isdel"] = 0;
		$count= $minfo->where($map)->count();
		$p = getpage($count,10);
		if($is_check == 0){
            $list = $minfo->where($map)->order('id desc')->limit($p->firstRow, $p->listRows)->select();
        }elseif($is_check == 1 || $is_check == 2){
            $list = M("xiaodai")->where($map)->order('id desc')->limit($p->firstRow, $p->listRows)->select();
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


    // 市场审核/不审核
    public function sc_shenhe(){
        if(IS_AJAX){
            $id = I("id");
            $m = M("supplier");
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
        $m = M("xiaodai");
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
            $this->success('删除成功',U('/Supplier/Member/index'));
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
            $action=D('supplier');
            $newpwd=I('param.newpass');
            $oldpwd=I('param.oldpass');
            $re=$action->getupdatepass($oldpwd,$newpwd);
            if($re==1){
                $this->success('修改成功',U('/Supplier/index/gyindex'));exit;
            }elseif($re==2){
                $this->error('修改失败,原密码错误');exit;
            }else{
                $this->error('修改失败');exit;
            }
        }
        $this->display();
    }

    /*
     *设置小贷公司权限*20170713*lq
     *
     */
    public function setAuthority()
    {

        //得到小贷公司的id
        $id = I("get.id");
        $memberdetail = M("xiaodai")->where(array("id"=>$id))->find();
        $this->assign("memberdetail",$memberdetail);
        //查询小贷公司的权限
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
            $supplierId = I("post.Supplier_id");
            $memberdetail = M("xiaodai")->where(array("id"=>$supplierId))->find();
            if(!$memberdetail){
                $this->error('无效贷公司信息');
            }
            $post = I("post.");
            $data = array(
                'user_id' => $supplierId,
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
            //根据小贷公司id查询权限表是否存在该公司
            $supplier = M("xiao_authority")->where(array('user_id'=>$supplierId))->find();
            M()->startTrans();
            if($supplier){
                $result1 = M("xiao_authority")->where(array("user_id"=>$supplierId))->save($data);
                $result2 = M("xiaodai")->where(array("id"=>$supplierId))->setField("goods_limit",$post['goods_limit']);
            }else{
                $result1 = M("xiao_authority")->add($data);
                $result2 = M("xiaodai")->where(array("id"=>$supplierId))->setField("goods_limit",$post['goods_limit']);
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


}