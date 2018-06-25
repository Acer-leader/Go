<?php
namespace Admin\Controller;
use Common\Controller\CommonController;
class CreditController extends CommonController {

	public function banlk(){
	// 	//查银行卡
	// 	$arr = array();
	// 	$blank = 
	// 	foreach($banlk as $k=>$v ){
	// 		$arr[$v['id']]=$v['classname'];
	// 	}
	// 	$this->assign('banlk',$blank);
		$this->bankarr = $arr;
	}

    public function index(){
    	// $this->banlk();
    	// $arr = array('17'=>'1','18'=>'2');
    	// $this->bankarr['17'];
    	$res = M('Credit')->select();
    	$this->assign('res',$res);
        $this->display();
    }
    public function set(){
        if(IS_GET){
            if(I('get.id')){
                $id = I('get.id');
                $credit = M('Credit')->find($id);
                foreach($credit as $k=>$v){
                   if($k == 'attr'){
                        $attr = unserialize($v);
                        $attr_value = '';
                        foreach($attr as $a=>$b){
                            $attr_value .= $b;
                            echo $attr_value;
                        }
                        exit;
                        $credit[$k] = $attr_value;
                   }else{
                        $credit[$k] = $v;
                   }
                }
                $this->assign('edit',$credit);
            }
        }
    	if(IS_POST){
    		$classname = I('post.Creditname');
    		$attr = I('post.name');
    		$data['name'] = $classname;
    		$data['attr'] = serialize($attr);
    		$data['add_time'] = date('Y-m-d',time());
    		$res = M('Credit')->add($data);
    		if($res){
    			$this->redirect('Admin/Credit/Index');
    		}else{
    			$this->success('添加失败',U('Admin/Credit/set'));
    		}
    	}
    	$attr_A = M('Attribute')->where(array('level'=>0))->select();
    	$attr_B = M('Attribute')->where(array('level'=>1))->select();
    	$this->assign('attr_A',$attr_A);
    	$this->assign('attr_B',$attr_B);
    	
    	$this->display();
    }
    
}
