<?php
namespace Supplier\Controller;
use Supplier\Common\Controller\CommonController;
class SupplierController extends CommonController {
    public function _initialize(){
        parent::_initialize();
        $this->assign("urlname", ACTION_NAME);
    }

    public function index(){

        $action=D('supplier');
        $rsdate=$action->getmemberlist();

        $rs=$rsdate['list'];
		$count=$rsdate['count'];
        $page=$rsdate['page'];
        $this->assign('memberlist',$rs);
		$this->assign('count',$count);
		if($count>10)
		{
		$this->assign('page',$page);
		}
        $this->assign("title",I("title"));
        $this->assign("isexamine",I("isexamine"));
        $this->assign("comptype",0);
        $this->display();
    }



     // 财务审核/不审核
    public function cw_shenhe(){
        if(IS_AJAX){
            $id = I("param.id");
            $m = M("supplier");
            $where['id']=$id;
            $res = $m->where($where)->field("id,is_sale")->find();
            if($res){
                $res['is_sale'] = $res['is_sale']==1?0:1;
                $res2 = $m->save($res);
                $arr = array("未审核","已审核");
                if($res2){
                    $return = array(
                        "status" => 1,
                        "info" => $arr[$res['is_sale']]
                    );
                }else{
                    $return = array(
                        "status" => 0,
                        "info" => $arr[$res['is_sale']]
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
    public function editMember(){

        $id = $this->supplier_id;

        if(IS_POST){
            // 编辑资料

            /*$data['username']=I("param.username");
            $data['tel']=I("param.tel");
            $data['sort']=I("param.sort");
            $data['realname']=I("param.realname");
            $data['telephone']=I("param.telephone");
            $data['legal_personname']=I("legal_personname");
            $data['legal_telephone']=I("legal_telephone");
            $data['id_card']=I("param.id_card");
            $data['company_des']=I("param.company_des");
            $data['update_time']=time();*/
           /* if(I("param.cart_zpic")){
                $data['id_pic']=I("param.cart_zpic");
            }
            if(I("param.cart_fpic")){
                $data['id_pic1']=I("param.cart_fpic");
            }
            if(I("param.license_zpic")){
                $data['license_pic']=I("param.license_zpic");
            }
            if(I("param.license_fpic")){
                $data['license_pic1']=I("param.license_fpic");
            }*/

            $data=I("post.");
            $data['update_time']=time();
            unset($data['is_sale']);
            $m = M("supplier");
            $res = $m->where(array('id'=>$id))->find();
            if($res){
                $res2 = $m->where(array('id'=>$id))->save($data);
                if($res2 !== false){
                    $this->success("修改成功!");
                }else{
                    $this->error("修改失败!");
                }
            }else{
                $this->error("删除失败！");
            }
        }else{
            $this->error("修改失败！");
        }
    }







    public function jifen(){
        $openid = I('openid');
        if(!empty($openid)){
            $member = M('supplier')->where('weixin_openid="%s"',$openid)->find();
            if(empty($member)){
                $this->assign('empty',"无该用户数据!");
            }else{
                $this->assign('supplier',$member);
            }
            $this->assign('openid',$openid);
        }
        $this->display();
    }
    public function updatejifen(){
        $jifen = intval(I('post.jifen'));
        if(!empty($jifen)){
            $ret = M('supplier')->where('id=%d',I('post.id',0,'int'))->setInc('jifen',$jifen);
            if($ret){
                echo '0';
            }else{
                echo '1';
            }
        }
    }


    public function friend(){
        $action=D('supplier');
        $rsdate=$action->getfriend();
        $rs=$rsdate['list'];
        $count=$rsdate['count'];
        $this->assign('memberlist',$rs);
        $this->assign('count',$count);
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

    public function agent_list(){

		$action=D('supplier');
        $rsdate=$action->getmember();
		$lv=I('post.dengji');

		$ar=$rsdate;

		//重组数组，放入上级分销商
		$superior=$action->getMemberSuperior($ar);
//        var_dump($rsdate);
        $rs=$superior['list'];
		$count=$superior['count'];
        $page=$superior['page'];

		$this->assign('supplier',$rs);
        $this->assign('page',$page);
		$this->assign('lv',$lv);
		$this->assign('count',$count);
        $this->assign('munetype',6);
        $this->display();
    }

    public function jiangli(){
        if(IS_POST){
            $ret = M('Systemglobal')->where("blname='yongjinshezhi'")->setField('blvalue',json_encode($_POST));
            if($ret){
                $this->assign('修改成功!');
                $this->success();
            }else{
                $this->error("修改失败!");
            }
        }else{
            $blvalue = M('Systemglobal')->where("blname='yongjinshezhi'")->getField('blvalue');
            $blvalue = json_decode($blvalue,true);
            $this->assign('blvalue',$blvalue);
            $this->display();
        }
    }


	public function detail(){
        $mid=I('get.id');
        $act=D('supplier');
        $memberdetail=$act->where('id='.$mid)->find();

        $this->assign('memberdetail',$memberdetail);
        $this->assign("img",IMG_URL."Public/Supplier/Upload_pic/header.png");
		//$this->assign('categorylist',$rs);
		$this->assign('munetype',5);
		$this->display();

    }

    public function updatehdid(){
        $data['hdid'] = I('post.hdid',0,'int');
        $memberModel = M('supplier');
        $whe = $memberModel->where('hdid=%d',$data['hdid'])->getField('id');
        if($whe){
            echo '3';//已存在
        }else{
            $ret = $memberModel->where("id=%d",I('post.id',0,'int'))->save($data);
            if($ret){
                echo '0';//成功
            }else{
                echo '1';//失败
            }
        }
    }

	public function agent_edit(){


        $mid=I('get.id');
        $act=D('supplier');
        $memberdetail=$act->where('id='.$mid)->find();
		$superior=$act->where('id='.$memberdetail['fid'])->find();
		$memberdetail['superior']=$superior['telephone'];

        $this->assign('memberdetail',$memberdetail);


		$this->assign('munetype',6);
		$this->display();

    }

    public function memberAddMoney(){
        $id = I('post.id',0,'int');
        $money = I('post.money',0);
        if(is_numeric($money)){
            $upRet = M('supplier')->where("id=%d",$id)->setInc('balance',$money);
            if($upRet){
                $data = array(
                    'yongjintype'=>1,
                    'addtime'=>date('Y-m-d H:i:s'),
                    'userid'=>$id,
                    'num'=>$money,
                    'description'=>I('post.beizhu')
                );
                $ret = M('Balance')->add($data);
            }
            if($ret){
                echo '1';//成功
            }else{
                echo '2';//失败
            }
        }else{
            echo "3";
        }
    }



    public function sign(){
        $action=D('supplier');
        $rsdate=$action->getsign();
        $rs=$rsdate['cache'];
        $count=$rsdate['count'];
        $page=$rsdate['page'];
        $this->assign('signlist',$rs);
        $this->assign('count',$count);
        if($count>10)
        {
        $this->assign('page',$page);
        }
        $this->assign("comptype",1);
        $this->display();
    }


    public function delsign(){
        $id = I('cid');
        $res = M('sign')->where('id='.$id)->setField('isdel','1');
        if($result !== false){
            $this->success();
        }else{
             $this->error();
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





}