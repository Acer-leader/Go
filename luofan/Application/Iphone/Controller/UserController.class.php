<?php
namespace Iphone\Controller;
use Think\Controller;

class UserController extends PublicController {
    
    public function _initialize(){
        
        parent::_initialize();
        if(session('user_id')){
            $this->redirect('/Iphone/Index/');
        }
        
    }
    public function index(){
        $this->display();
    }
    public function login1(){
        //dump(session('usertel'));
        $usertel    =   session('usertel');
        $this->assign('usertel',$usertel);
        $this->display();
    }
    public function dingwei(){
        $this->display();
    }
    public function getMessage(){
        if(IS_AJAX){
            $m= M('member');
            $tel   = trim(I("post.tel"));
            session('usertel',$tel);
            //$code_type   = trim(I("post.code_type"));
            //$verify_code = trim(I("post.verify_code"));
            if(!$tel){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写手机号！"));
            }
            if(!preg_match("/^1[35789][0-9]{9}$/", $tel)){
                $this->ajaxReturn(array("status"=>0, "info"=>"手机号码格式错误！"));
            }
            //if(!in_array($code_type,array('1','2','3','4','15'))){
               // $this->ajaxReturn(array("status"=>0, "info"=>"参数有误！"));
            //}
            // if($verify_code && !check_verify($verify_code)){
                // $this->ajaxReturn(array("status"=>0,"info"=>'亲，验证码输错了哦！'));
            // }
       
            //$this->ajaxReturn(array('status'=>1,'info'=>'验证码发送成功，请注意查收'));
            //发送验证码
            $res = sendMessage($tel,3);
       
            $this->ajaxReturn($res);                   

           
        }
    }
    /**wzz 20170425
    * 执行验证码登录
    */
    public function doCodeLogin(){
        if(IS_AJAX){
            $mem = M('member');
            $url =  '__HOST__/Index/';
            $telephone    = session('usertel');
            $code         = I("post.code");
            if(!$code){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写短信验证码！"));
            }
            //=======================检测验证码=================
            $res = $this->checkMessage($telephone, $code, 1);
            //$res['status']  =1;
            //=======================检测验证码=================
            if($res['status']!=1){
                $this->ajaxReturn($res);
            }

            $res = $mem->where(array('telephone'=>$telephone, "isdel"=>0))->find();
            if(!$res){
                $log_data=array(
                    'telephone' => $telephone,
                    'isdel'     => 0,
                    'status'    => 0,
                    'add_time'  => date("Y-m-d H:i:s", time()),
                    'last_login_time'=> date("Y-m-d H:i:s", time()),
                    );
                $res11=$mem->add($log_data);
                if($res11){
                    session("user_id",$res['id']);
                    session("usertel",null);
                    $this->ajaxReturn(array("status"=>1, "info"=>"登录成功！", "url"=>$url));
                }
                $this->ajaxReturn(array("status"=>0, "info"=>"登录失败！"));
            }else{
                if($res['status']){
                    $this->ajaxReturn(array("status"=>0, "info"=>"您的账号已被冻结，请联系管理员"));
                }
                $data   =   array(
                    'id'                =>  $res['id'],
                    'last_login_time'   =>  date("Y-m-d H:i:s", time()),
                );
                $mem->save($data);
                session("user_id",$res['id']);
                session("usertel",null);
                $this->ajaxReturn(array("status"=>1, "info"=>"登录成功！", "url"=>$url));
            }
            
        }
    }
    
    public function checkMessage($phone, $identify, $status){
        $over_time=C('over_time');
        $now_time=time();
        $sql=M('user_verify')->where(array('telephone'=>$phone,"status"=>0))->order('add_time desc')->find();
        if($sql['code']!=$identify){
            return array("status"=>'2',"info"=>"验证码错误");
        }
        $addtime=$sql['add_time'];
        if ($now_time<($addtime+$over_time)){
            $code=$sql['code'];
            if ($code==$identify){
                M('user_verify')->where(array('id'=>$sql['id']))->save(array("status"=>$status));
                return array("status"=>'1',"info"=>"ok");
            }else {
                return array("status"=>'2',"info"=>"验证码错误");
            }
        }else{
            M('user_verify')->where(array('id'=>$sql['id']))->save(array("status"=>9));
            return array("status"=>'3',"info"=>"验证码已过期");
        }
    }
}