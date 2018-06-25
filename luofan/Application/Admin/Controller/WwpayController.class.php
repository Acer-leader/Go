<?php
namespace Admin\Controller;
//use Think\Controller;
use Common\Controller\CommonController;
class WwpayController extends CommonController {

    public function index(){
    	$title = I('get.title');
    	if($sql){
    		$sqls['personname|telephone'] = array('like','%'.$title.'%');
    		$this->assign('title',$title);
    	}
        $count=M('Pay_view')->where($sqls)->count();
        $Page  = getpage($count,10);
        $show  = $Page->show();
        $res = M('Pay_view')->where($sqls)->limit($Page->firstRow.','.$Page->listRows)->select();
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
			$xiaodaiInfo = M('xiaodai')->where(array('telephone'=>$telephone,'isdel'=>0,'is_check'=>1))->find();
			if(!$xiaodaiInfo){
				$this->ajaxReturn(array('status'=>0,'info'=>"小贷公司不存在！"));
			}
    		$data['add_time'] = time();
			$res_l=array(
				'money'=>$data['money'],
				'add_time'=>time(),
				'xiaodai_id'=>$xiaodaiInfo['id']
			);
			M()->startTrans();
			$res = M('Pay')->add($res_l);
			$res1 = M('xiaodai')->where(array('id'=>$xiaodaiInfo['id']))->setInc('wallet',$data['money']);
			if($res && $res1){
				M()->COMMIT();
                $map   =   array(
                    'title' =>  '充值',
                    'pay'   =>  '+'.$data['money'].'元',
                    'addtime'   =>  time(),
                    'user_id'   =>  $xiaodaiInfo['id'],     
                );
                /* M('xiaodai_bill')->add($map); */
				$this->ajaxReturn(array('status'=>1,'info'=>"充值成功",'url'=>U('Admin/Wwpay/index')));
			}else{
				M()->ROLLBACK();
				$this->ajaxReturn(array('status'=>0,'info'=>"充值失败",'url'=>U('Admin/Wwpay/set')));
			}
    		
    	}
    	$res = M('Pay')->find($id);
    	$this->assign('pay',$res);
    	$this->display();
    }
    public function del(){
        $id = I('get.id');
        $res = M('Pay')->delete($id);
        if($res){
        	$this->redirect('Admin/Wwpay/index');
        }else{
        	$this->error('删除失败',U('Admin/Wwpay/index'));
        }
    }
    public function erweima(){
    	$erweima = M('Erweima')->select();
    	$this->assign('pic',$erweima);
    	$this->display();
    }
    public function sete(){
    	if(IS_AJAX){
    		$data = I('post.');
    		if($data['id']){
    			$res = M('Erweima')->save($data);
    			if($res){
		        	$this->ajaxReturn(array('status'=>1, 'info'=>'修改成功'));
		        }else{
		        	$this->ajaxReturn(array('status'=>0, 'info'=>'修改失败'));
		        }
    		}else{

    			$data['add_time'] = time();
    			$res = M('Erweima')->add($data);
    			if($res){
		        	$this->ajaxReturn(array('status'=>1, 'info'=>'修改成功'));
		        }else{
		        	$this->ajaxReturn(array('status'=>0, 'info'=>'添加失败'));
		        }
    		}
    	}
    }
    public function dele(){
    	$id = I('post.id');
        $res = M('Erweima')->delete($id);
        if($res){
        	$this->ajaxReturn(array('status'=>1, 'info'=>'删除成功'));
        }else{
        	$this->ajaxReturn(array('status'=>0, 'info'=>'删除成功'));
        }
    }
}