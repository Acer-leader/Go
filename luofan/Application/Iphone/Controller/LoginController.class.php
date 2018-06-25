<?php
namespace Iphone\Controller;
use Think\Controller;
class LoginController extends PublicController{
	
	public function index(){
		$this->display();
	}
	public function ewm(){
		
		$this->display();
	}
	public function ewmdo(){
		$this->fenxiao_db   =    M('member');
		if(IS_AJAX){	
	            $user_id  =   session('fenxiao_id');
	            $is_check  =    $this->fenxiao_db->where(array('id'=>$user_id))->getField('is_check');
	            if($is_check!=1){
	                //echo '<script>alert("如已支付，请联系客服!");location.href="/Iphone/Login/ewm"</script>';die;
	                $this->ajaxReturn(array('status'=>0,'info'=>'如已支付，请联系客服!'));
	                // $this->success('如已支付，请联系客服!', '/Iphone/Login/ewm');die;
	            }else{
	            	$this->ajaxReturn(array('status'=>1,'info'=>'已成功支付'));
	            	// $this->redirect("/Iphone/Distribution/");die;
	            } 
	        
	    }

	}
	public function register(){
		$this->display();
	}

	/** wm 2017.08.12
	 * 执行注册
	*/
	public function doReg(){
		if(IS_AJAX){
			$data = I("post.");
			$realname   = $data['person_name'];
			$telephone  = $data['telephone'];
			$password   = $data['password'];
			$repassword = $data['repassword'];
			$code       = $data['code'];
			if(!$realname){
				$this->ajaxReturn(array("status"=>0, "info"=>"请填写姓名！"));
			}
			if(!$telephone){
				$this->ajaxReturn(array("status"=>0, "info"=>"请填写手机号！"));
			}
			if(!preg_match("/^1[35789][0-9]{9}$/", $telephone)){
				$this->ajaxReturn(array("status"=>0, "info"=>"手机号码格式错误！"));
			}
			$tel = M('member')->where(array('telephone'=>$telephone))->count();
			if($tel){
				$this->ajaxReturn(array("status"=>0, "info"=>"该手机已被注册！"));
			}
			if(!$password){
				$this->ajaxReturn(array("status"=>0, "info"=>"请填写密码！"));
			}
			if(!preg_match("/.{6,24}/", $password)){
				$this->ajaxReturn(array("status"=>0, "info"=>"密码格式错误！"));
			}
			if(!$repassword){
				$this->ajaxReturn(array("status"=>0, "info"=>"请填写确认密码！"));
			}
			if(!preg_match("/.{6,24}/", $repassword)){
				$this->ajaxReturn(array("status"=>0, "info"=>"确认密码格式错误！"));
			}

			//=======================检测验证码=================
			$res = checkMessage($telephone, $code, 1);
			//$res['status']  =1;
			//=======================检测验证码=================
			if($res['status']==1){
				$data['telephone']  = $telephone;
				$data['password']   = encrypt_pass($password);
				$data['realname']   = $realname;
				$data['agency_time']   = time();
				$res = M("member")->add($data);
				if($res){
					session("fenxiao_id", $res);
					$this->ajaxReturn(array("status"=>1, "info"=>"注册成功！"));
				}else{
					$this->ajaxReturn(array("status"=>0, "info"=>"注册失败！"));
				}
			}else{
				$this->ajaxReturn($res);
			}
			
		}		
	}
    public function getMessage(){
	if(IS_AJAX){
		$m= M('member');
		$telephone   = trim(I("post.telephone"));
		$code_type   = trim(I("post.code_type"));
		$verify_code = trim(I("post.verify_code"));
		if(!$telephone){
			$this->ajaxReturn(array("status"=>0, "info"=>"请填写手机号！"));
		}
		if(!preg_match("/^1[35789][0-9]{9}$/", $telephone)){
			$this->ajaxReturn(array("status"=>0, "info"=>"手机号码格式错误！"));
		}
		if(!in_array($code_type,array('1','2','3','4','5','15'))){
			$this->ajaxReturn(array("status"=>0, "info"=>"参数有误！"));
		}
		if($verify_code && !check_verify($verify_code)){
			$this->ajaxReturn(array("status"=>0,"info"=>'亲，验证码输错了哦！'));
		}
		if($code_type==5){
			$res=$m->where(array('telephone'=>$telephone))->find();
         
			if($res){
				$this->ajaxReturn(array("status"=>0, "info"=>"手机号已被注册！"));
			}
		}elseif($code_type==2){
			$res=$m->where(array('telephone'=>$telephone))->find();
			if(!$res){
				$this->ajaxReturn(array("status"=>0, "info"=>"手机号未注册！"));
			}
		}
		//$this->ajaxReturn(array('status'=>1,'info'=>'验证码发送成功，请注意查收'));
		//发送验证码
		$res = sendMessage($telephone,$code_type);
		$this->ajaxReturn($res);                   
	}
}
	/**wm 2017.08.12
	 * 执行登录
	 */
	public function doLogin(){
		if(IS_AJAX){
			$mem = M('member');
			$url = U("Iphone/Distribution/");
			$telephone = I("post.telephone");
			$password  = I("post.password");
			if(!$telephone){
				$this->ajaxReturn(array("status"=>0, "info"=>"请填写手机号！"));
			}
			if(!preg_match("/^1[35789][0-9]{9}$/", $telephone)){
				$this->ajaxReturn(array("status"=>0, "info"=>"手机号码格式错误！"));
			}
			if(!$password){
				$this->ajaxReturn(array("status"=>0, "info"=>"请填写密码！"));
			}			
			$res = M("member")->where(array('telephone'=>$telephone, "isdel"=>0))->find();

			if(!$res){
				$this->ajaxReturn(array("status"=>0, "info"=>"账号不存在！"));
			}
			if($res['status']){
				$this->ajaxReturn(array("status"=>0, "info"=>"您的账号已被冻结，请联系管理员"));
			}
            if($res['is_check']==0){
                $url  = U("Iphone/Login/fx_buy");
            }
			if($res["password"] == encrypt_pass($password)){
				session("fenxiao_id",$res['id']);
				$this->ajaxReturn(array("status"=>1, "info"=>"登录成功！", "url"=>$url));
			}else{
				$this->ajaxReturn(array("status"=>0, "info"=>"密码错误！"));
			}
		}
	}

	public function fx_buy(){
		$this->display();
	}


}
?>