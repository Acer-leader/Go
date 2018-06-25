<?php
namespace Admin\Controller;
use Common\Controller\CommonController;
class MemberController extends CommonController {
    public function _initialize(){
        parent::_initialize();
        $this->assign("urlname", ACTION_NAME);
    }

    public function index(){
        $action=D('member');
        $rsdate=$action->getmemberlist();
        $rs=$rsdate['list'];

        $count=$rsdate['count'];
        $page=$rsdate['page'];
        $this->assign('memberlist',$rs);
        $this->assign('count',$count);
        if($count>30){
        $this->assign('page',$page);
        }
        $this->assign("title",I("title"));
        $this->display();
    }

    // 冻结/解冻
    public function changeStatus(){
        if(IS_AJAX){
            $id = I("id");
            $m = M("member");
            $res = $m->where("id=$id")->field("id,status")->find();
            if($res){
                $res['status'] = $res['status']==1?0:1;
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
    
        // 冻结/解冻
    public function changexiaodai(){
        if(IS_AJAX){
            $id = I("id");
            $m = M("xiaodai");
            $res = $m->where(array('id'=>$id))->field("id,status")->find();
            if($res){
                $res['status'] = $res['status']==1?0:1;
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



//删除会员
    public function delMember(){
        if(IS_AJAX){
            $id = I('post.id');
            $arr = explode('_',$id);
            $arr = implode(',',$arr);
            $arr =  rtrim($arr,',');
            $data['id'] = array('in',$arr);
            $del = M('member')->where($data)->delete();
            if($del){
                $this->ajaxReturn(array("status"=>1,"info"=>" 删除成功","url"=>U('Admin/Member/index')));
            }else{
                $this->ajaxReturn(array("status"=>0,"info"=>"删除失败"));
            }
        }
    }


    public function detail(){
        $mid=I('get.id');
        $act=D('Member');
        $memberdetail=$act->where('id='.$mid)->find();
        $this->assign('memberdetail',$memberdetail);
        $this->display();

    }





}