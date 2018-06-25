<?php
namespace Iphone\Controller;
use Think\Controller;

class UcenterController extends PublicController {

	public function _initialize(){
		parent::_initialize();
		$this->checkLogin();
	}
	

	public function index(){
		if(IS_POST){
			$data = I("post.data");
			
		
			/* $person_img = $data['person_img'];
			unset($data['person_img']);
			if($person_img){
				$data['person_img'] = $person_img;
			} */
			$res = M("member")->where(array("id"=>$this->user_id))->save($data);
			if($res){
				$this->ajaxReturn(array("status"=>1 ,"info"=>"修改成功！"));
			}else{
				$this->ajaxReturn(array("status"=>0 ,"info"=>"修改失败！"));
			}
		}
		$position = M("cate")->where(array('pid'=>4))->select();
		$this->assign('position',$position);
		$house = M("cate")->where(array('pid'=>10))->select();
		$this->assign('house',$house);
		$car = M("cate")->where(array('pid'=>18))->select();
		$this->assign('car',$car);
		
		$member_info = $this->user_info;
	
		$this->assign('user',$member_info);
		$this->display();
	}
	//设置个人信息
	public function setUser(){
		if(IS_POST){
			$id = session('user_id');
	        $upload = new \Think\Upload();// 实例化上传类
	        if($_FILES){
		         if($_FILES['logo']['error'] == 0){
		         	$logo = M('Member')->find($id);
		          $upload->maxSize   =     3145728 ;// 设置附件上传大小
		          $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		          $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
		          $upload->savePath  =     'User/'; // 设置附件上传（子）目录
		          $info   =   $upload->uploadOne($_FILES['logo']);
		          $data['head_img'] = $upload->rootPath.$info['savepath'].$info['savename'];
		          unlink($logo['head_img']);
		        }
	        }
			$data['realname'] = I('post.realname');
			$data['person_name'] = I('post.person_name');
			$data['sex'] = I('post.sex');
			$data['address'] = I('post.address');
			$data['year'] = I('post.year');
			$data['moth'] = I('post.moth');
			$data['sun'] = I('post.sun');
			$data['birth'] = $data['year'].'-'.$data['moth'].'-'.$data['sun'];
			$data['qq'] = 	I('post.qq');
			$data['wxin'] = I('post.wxin');
			$data['month_money'] = I('post.month_money');
			$res = M('Member')->where(array('id'=>$id))->save($data);
			if($res){
				$this->redirect('Home/Ucenter/Index');
			}else{
				$this->redirect('Home/Ucenter/Index');
			}
		}
	}

	public function uploadImg() {
		$upload = new \Think\UploadFile;

		//$upload = new \Think\Upload();// 实例化上传类
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

	public function addImage(){
		$data = $this->uploadImg();
		$this->ajaxReturn($data);
	}

}