<?php
namespace Supplier\Controller;
//use Think\Controller;
use Supplier\Common\Controller\CommonController;
class SupplymessageController extends CommonController{
    public function _initialize(){
        parent::_initialize();
        $this->assign("urlname", ACTION_NAME);
    }
    /**
     * 消息列表
     * @author Chandler_qjw  ^_^
     */
    public function MessageList(){
        if(IS_POST){
            //查询
            $title = I("post.name");
            $map['msg'] = array('like',"%$title%");
        }
        $islooked = I("get.islooked");
        if($islooked != "" && $islooked == 0){
            $map['islooked'] = $islooked;
        }
        if($islooked != "" && $islooked == 1){
            $map['islooked'] = $islooked;
        }
        $map['supplier_id']=$_SESSION['supplier_id'];
        $map['isdel'] = 0;
        $count =  M('supplier_message')->where($map)->count();
        $count1 =  M('supplier_message')->where(array('supplier_id'=>$_SESSION['supplier_id'],'islooked'=>1,'isdel'=>0))->count();//已查看
        $count2 =  M('supplier_message')->where(array('supplier_id'=>$_SESSION['supplier_id'],'islooked'=>0,'isdel'=>0))->count();//未查看
        $Page  = getpage($count,10);
        $show  = $Page->show();
        $data = M('supplier_message')->field('*')->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('res',$data);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->assign('count1',$count1);
        $this->assign('count2',$count2);
        $this->display();
    }

    /**
     * 消息详情
     * @author Chandler_qjw  ^_^
     */
    public function MessageDetail(){
        $id = I("get.id");
        M('supplier_message')->where(array('id'=>$id))->setField('islooked',1);
        $data = M('supplier_message')->find($id);
        $this->assign('data',$data);
        $this->display();
    }
    /**
     * 删除消息
     */
    public function DelMessage(){
        $id = I("id");
        $res = M("supplier_message")->where(array("id" => $id))->save(array("isdel" => 1));
        if ($res !== false) {
            $this->success("删除成功！");
            die;
        }
        $this->error("删除失败！");
        die;
    }


    /*
     * 刷新列表
     */
    public function shuaxinList(){
        if(IS_POST){
            //查询
            $title = I("post.name");
            $map['logo_info'] = array('like',"%$title%");
        }
        $islooked = I("param.islooked");
        if($islooked != "" && $islooked == 0){
            $map['islooked'] = 0;
        }
        if($islooked != "" && $islooked == 1){
            $map['islooked'] = $islooked;
        }
        $map['supplier_id']=$_SESSION['supplier_id'];
        $map['isdel'] = 0;
        $count =  M('shuaxin_log')->where(array('supplier_id'=>$_SESSION['supplier_id'],'isdel'=>0))->count();
        $count1 =  M('shuaxin_log')->where(array('supplier_id'=>$_SESSION['supplier_id'],'islooked'=>1,'isdel'=>0))->count();//已查看
        $count2 =  M('shuaxin_log')->where(array('supplier_id'=>$_SESSION['supplier_id'],'islooked'=>0,'isdel'=>0))->count();//未查看
        $Page  = getpage($count,10);
        $show  = $Page->show();
        $data = M('shuaxin_log')->field('*')->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('res',$data);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->assign('count1',$count1);
        $this->assign('count2',$count2);
        $this->display();
    }

    /**
     * 刷新详情
     */
    public function shuaxinDetail(){
        $id = I("get.id");
        M('shuaxin_log')->where(array('id'=>$id))->setField('islooked',1);
        $data = M('shuaxin_log')->find($id);
        $this->assign('data',$data);
        $this->display();
    }
    /*
     * 删除消息
     */
    public function Delshuaxin(){
        $id = I("id");
        $res = M("shuaxin_log")->where(array("id" => $id))->save(array("isdel" => 1));
        if ($res !== false) {
            $this->success("删除成功！");
            die;
        }
        $this->error("删除失败！");
        die;
    }
}
