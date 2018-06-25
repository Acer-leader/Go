<?php
namespace Admin\Controller;
//use Think\Controller;
use Common\Controller\CommonController;
class AboutController extends CommonController {
    public function _initialize(){
        parent::_initialize();
        $this->assign("urlname", ACTION_NAME);
    }
	
	public function index(){
		if($_POST){
			$data = I("post.");
			$id = $data['id'];
			unset($data['id']);
			$data['add_time'] =time();	

			$info=M('about')->where(array('id'=>$id))->save($data);
			if($info){
				$this->success("修改成功");
			}else{
				$this->error("修改失败");
			}

		}else{
			
			$cache=M('about')->find();
			$this->assign('cache',$cache);
			$this->display();
		}
	}
	
   
}