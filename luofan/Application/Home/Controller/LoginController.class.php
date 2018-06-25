<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends PublicController{
    public function _initialize(){
        parent::_initialize();
        $this->fenxiao_db       =    M('member');
        $this->fenxiao_bank_db  =    M('fenxiao_bank');
        $this->fenxiao_share_db =    M('fenxiao_share');
        $this->loan_db          =    M('loan');
        $this->fenxiao_bill_db  =   M('fenxiao_bill');
        $this->creditcard_db    =   M('creditcard');
        $this->inquire_db       =   M('inquire');
        $this->loan_order_db       =   M('loan_order');
        $this->inquire_cate_db       =   M('inquire_cate');

        $this->fenxiao_id =   session('fenxiao_id');


    }
    public function fx_login(){
        if($this->fenxiao_id){
           $this->redirect("Distribution/index");die; 
        }
        $this->display();
    }
    public function fx_gmym(){
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
    
    public function fx_login1(){
        $this->display();
    }
    public function fx_erweima(){
        $this->display();
    }

    public function fx_register(){
        $fid    =   I('get.fid');
        if($fid){
            $gurl    =   strstr(get_url(), '/fid/', TRUE);
        }else{
            $gurl     =   get_url();
        }
        $url    =   $gurl.'/fid/';       
        $this->assign('fid',$fid);
         $this->assign('url',$url);
        $this->display();
    }

    
    public function doReg(){
        if(IS_AJAX){
            $data = I("post.");
            $realname= $data['person_name'];
            $telephone  = $data['telephone'];
            $password   = $data['password'];
            $repassword = $data['repassword'];
            $code       = $data['code'];
            $url       = $data['url'];
            $this->fid  = $data['fid'];
            if(!$realname){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写姓名！"));
            }
            if(!$telephone){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写手机号！"));
            }
            if(!preg_match("/^1[35789][0-9]{9}$/", $telephone)){
                $this->ajaxReturn(array("status"=>0, "info"=>"手机号码格式错误！"));
            }
            $tel = $this->fenxiao_db->where(array('telephone'=>$telephone))->count();
            if($tel){
                $this->ajaxReturn(array("status"=>0, "info"=>"该手机已被注册！"));
            } 
            if(!$password){
                $this->ajaxReturn(array("status"=>0, "info"=>"请输入6~20位字母数字密码！"));
            }
            if(!preg_match("/.{6,24}/", $password)){
                $this->ajaxReturn(array("status"=>0, "info"=>"请输入6~20位字母数字密码！"));
            }
            if(!$repassword){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写确认密码！"));
            }
            /* if(!preg_match("/.{6,24}/", $repassword)){
                $this->ajaxReturn(array("status"=>0, "info"=>"确认密码格式错误！"));
            } */
            if($password !==$repassword)
                $this->ajaxReturn(array("status"=>0, "info"=>"两次密码不一致！"));
            //=======================检测验证码=================
            $res = checkMessage($telephone, $code, 1);
            $res['status']  =1;
            //=======================检测验证码=================
            if($res['status']==1){
                $map['telephone']   =   $telephone;
                $map['password']    =   encrypt_pass($password);
                $map['realname']  =   $realname;
                $map['agency_time']    =   time();
                $count  =   $this->fenxiao_db->count();
                $map['number']      =   sprintf("%06d",$count+1);
                if($this->fid){
                    /* $str    =   $this->fenxiao_db->getFieldByid($this->fid,'fid');  
                    if($str){
                        $fid    =   $str.',';
                    } */
                    $map['fid']     =  $this->fid;    
                }
               
                $res = $this->fenxiao_db->add($map);
                if($res){
                    if($this->fid){
                        $cstr    =   $this->fenxiao_db->getFieldById($this->fid,'cid');
                        if($cstr){
                            $cid    =   $cstr.',';
                        } 
                  
                        $data_info  =   array(
                            'id'    =>  $this->fid,
                            'cid'   =>  $cid.$res,
                        );
            
                        $cres   =   $this->fenxiao_db->save($data_info);//将该分销商ID存入父级分销商里
       
                        if(!$cres){
                            $this->fenxiao_db->where(array('id'=>$res))->delete();
                            $this->ajaxReturn(array("status"=>0, "info"=>"注册失败1！"));
                        }
                    }
                   
                    session("fenxiao_id", $res);
          
                    $this->getqrcode($res,$url);
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
            if(!in_array($code_type,array('1','2','3','4','5','6','7','8','15'))){
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
            }elseif($code_type==8){
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
 
    public function doCodeLogin(){
        if(IS_AJAX){
            $mem = M('member');
            $code         = I("post.code");
            if(!$code){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写短信验证码！"));
            }
            //=======================检测验证码=================
            $res = $this->checkMessage($telephone, $code, 3);
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
                    session("fenxiao_id",$res['id']);
              
                    $this->ajaxReturn(array("status"=>1, "info"=>"登录成功！"));
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
                session("fenxiao_id",$res['id']);
                session("usertel",null);
                $this->ajaxReturn(array("status"=>1, "info"=>"登录成功！", "url"=>$url));
            }
            
        }
    }
    public function doLogin(){
        if(IS_AJAX){
            $mem = M('member');
            $realname = I("post.personname");
            $password  = I("post.password");
            if(!$realname){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写您的姓名"));
            }
            
            if(!$password){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写密码！"));
            }			
            $res = M("member")->where(array('realname'=>$realname, "isdel"=>0))->find();

            if(!$res){
                $this->ajaxReturn(array("status"=>0, "info"=>"账号不存在！"));
            }
            if($res['status']){
                $this->ajaxReturn(array("status"=>0, "info"=>"您的账号已被冻结，请联系管理员"));
            }
            if($res["password"] == encrypt_pass($password)){
                session("fenxiao_id",$res['id']);
                $this->ajaxReturn(array("status"=>1, "info"=>"登录成功！"));
            }else{
                $this->ajaxReturn(array("status"=>0, "info"=>"密码错误！"));
            }
        }
    }

            //生成二维码
    public function getqrcode($fenxiao_id,$url){
        $fenxiao_id =   $fenxiao_id;
        
        $url        =   $url.$fenxiao_id;
          
        $save_path	= "./Uploads/qrcode/";
        $pic    =   $this->qrcode($url,$save_path);

        $data   =   array('url'=>$url,'fpic'=>$pic,'id'=>$fenxiao_id);
        M('fenxiao')->save($data);
            
    }

    //生成二维码
    public function qrcode($qr_data="",$save_path="",$web_path="",$qr_level="",$qr_size="",$save_prefix=""){
        $save_path = $save_path?$save_path:"./Uploads/qrcode/";  //图片存储的路径
        $web_path = $web_path?$web_path:__ROOT__.'/Uploads/qrcode/';        //图片在网页上显示的路径
        $qr_data = $qr_data?$qr_data:'http://www.zetadata.com.cn/';
        $qr_level = $qr_level?$qr_level:'H';
        $qr_size = $qr_size?$qr_size:'4';
        $save_prefix = $save_prefix?$save_prefix:'unohacha_';
        if($filename = createQRcode($save_path,$qr_data,$qr_level,$qr_size,$save_prefix)){
            $pic = $web_path.$filename;
            $img = $save_path.$filename;
        }
    
        $image = new \Think\Image();   
        $image->open($img); 
        //水印
         $image->water('./Public/Iphone/images/water.jpg',5,100)->save($img,NULL,100,true);

        //$image = new \Think\Image();   
        //$image->open($img); 
        //水印
         //$image->water('./Public/Iphone/images/water.jpg',5)->save($img,NULL,100,true);
         // $image->text('123', 'D:/WWW/luofan/Public/Iphone/fonts/FontAwesome.otf', 12, $color = '#00000000',6, $offset = 0, $angle = 0)->save($img,NULL,100,true);
         //$image->text->save($img);
         //$image->text->save($img);
        //echo "<img src='".$pic."'>";
        return $pic;
    }  
    
    
   
   

    //检验短信验证码
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
    
    //发送短信验证码
    public function codecheck(){
        if(IS_AJAX){

            $card_tel   = trim(I("post.card_tel"));
            session('usertel',$card_tel);
            $code_type   = trim(I("post.code_type"));
            //$verify_code = trim(I("post.verify_code"));
            if(!$card_tel){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写手机号！"));
            }
            if(!preg_match("/^1[35789][0-9]{9}$/", $card_tel)){
                $this->ajaxReturn(array("status"=>0, "info"=>"手机号码格式错误！"));
            }
           
            //$this->ajaxReturn(array('status'=>1,'info'=>'验证码发送成功，请注意查收'));
            //发送验证码
            $res = sendMessage($card_tel,$code_type);
       
            $this->ajaxReturn($res);                   

           
        }
    }

}
?>