<?php
namespace Home\Controller;
use Think\Controller;
class DistributionController extends PublicController{
	public function _initialize(){
        parent::_initialize();
        $this->fenxiao_db       =    M('fenxiao');
        $this->fenxiao_bank_db  =    M('fenxiao_bank');
        $this->fenxiao_share_db =    M('fenxiao_share');
        $this->loan_db          =    M('loan');
        $this->fenxiao_bill_db  =   M('fenxiao_bill');
        $this->creditcard_db    =   M('creditcard');
        $this->inquire_db       =   M('inquire');
        $this->loan_order_db       =   M('loan_order');
        $this->inquire_cate_db       =   M('inquire_cate');
        $this->fenxiao_id  =   session('fenxiao_id');
        if($this->fenxiao_id){
                $fenxiao  =    $this->fenxiao_db->find($this->fenxiao_id);
                $this->assign('fenxiao',$fenxiao);
        }

        $this->fid   =   session('fid');
    }
    
    public function fx_login(){
    
        $this->display();
    }
    public function logout(){
        session('fenxiao_id',null);   
        $this->redirect("Login/fx_login");die;
    }
     public function fx_login1(){
        $this->display();
    }
      public function fx_erweima(){
        $card_id    =   I('get.card_id');
        $this_fenxiao_id =   I('get.fenxiao_id');
        
        $share  =   $this->fenxiao_share_db->where(array('card_id'=>$card_id,'fenxiao_id'=>$this_fenxiao_id))->find();
        $this_fenxiao   =   $this->fenxiao_db->where(array('id'=>$this_fenxiao_id))->field('personname,telephone,pic')->find();
        $share['personname']    =   $this_fenxiao['personname'];
        $share['telephone']     =   $this_fenxiao['telephone'];
        $share['logo_pic']           =   $this_fenxiao['pic'];
        $this->assign('share',$share);
        /* dump($share); */
        $this->display();
    }
    public function fx_my(){
        $this->display();
    }

    public function index(){
        $cate_id    =   I('get.cate_id');
        if($cate_id){
            $data['cate_id']    =   $cate_id;
            $this->assign('cate_id',$cate_id);
        }
        $status     =   I('get.status');
        if($status){
            $data['status']    =   $status;
            if($status==1){
                $data['status'] =   0;
            }
            $this->assign('status',$status);
        }
        
        $client =    $this->loan_order_db->where($data)->order('apply_at desc')->select();
    
        $new    =   array();
        foreach($client as $k=>$v){
            $client[$k]['number']   =   $this->fenxiao_db->where(array('id'=>$v['fid']))->getField('number');
         
            if($v['fid']){
                $res    =   $this->fenxiao_db->where(array('id'=>$v['fid']))->find();
                if($res){
                    if($v['fid']==$this->fenxiao_id){
                        $new[]  =   $client[$k];
                    }
                    
              
                    if($res['fid']==$this->fenxiao_id){
                        $new[]  =   $client[$k];
                    }
      
                    
                  
                }
            }
           
   
        }
      
        /* dump($new); */
        $count  = count($new);
        $page   = getpage($count,10);
        //$page->setConfig('theme', '%UP_PAGE%%DOWN_PAGE%');
        $show   = $page->show();
        $new    = array_slice($new,$page->firstRow,$page->listRows);
        $this->assign('new',$new);     
        $this->assign('page',$show);
        $this->display();
    }
     
    public function fx_commission(){
        $cate_id    =   I('get.cate_id');
    
        if($cate_id){
            $res    =   $this->loan_db->where(array('cate_id'=>$cate_id))->field('id')->select();
            $str    =   '';
            foreach($res as $k=>$v){
                $str    .=   $v['id'].',';
            }
            $str    =   rtrim($str,',');
            $data['card_id'] =   array('in',$str);     
        }
        $this->assign('cate_id',$cate_id);
        $number =   I('get.number');
        if($number){
            $fenxiao_id    =   $this->fenxiao_db->where(array('number'=>$number))->getField('id');
        }
        $data['fenxiao_id'] =   $this->fenxiao_id;
        $share  =   $this->fenxiao_share_db->where($data)->select();

        $new    =   array();
        foreach($share as $k=>$v){
            $share[$k]['card_title']  = $this->loan_db->where(array('id'=>$v['card_id']))->getField('title');
            $share[$k]['logo_pic1']    = $this->loan_db->where(array('id'=>$v['card_id']))->getField('logo_pic1');
            if($share[$k]['card_title']){
                $new[]  =   $share[$k];
            }
            
        }
/*         foreach($share as $k=>$v){
            $share[$k]['card_title']  = $this->creditcard_db->where(array('id'=>$v['card_id']))->getField('creditname');
            if($share[$k]['card_title']){
                $new[]  =   $share[$k];
            }
      
        } */
 
        $count  = count($new);
        $page   = getpage($count,10);
        //$page->setConfig('theme', '%UP_PAGE%%DOWN_PAGE%');
        $show   = $page->show();
        $new    = array_slice($new,$page->firstRow,$page->listRows);
        $this->assign('new',$new);     
        $this->assign('page',$show);
        $this->display();
    }
 

    public function getMessage(){
        if(IS_AJAX){
            $m= M('fenxiao');
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
 
            //生成二维码
    public function getqrcode($fenxiao_id){
        $fenxiao_id =   $fenxiao_id;
        
        $url        =   "http://cx.luofan.com/Distribution/fx_register/fid/".$fenxiao_id;
          
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
    
    
   
   


    //提现操作 
    public function tixiando(){
        if(IS_AJAX){
            $yzm         =  I('post.yzm');
            $money       =  I('post.money');
            $card_num    =  I('post.card_num');
            $Verify      =  new \Think\Verify();
            $result      =  $Verify->check($yzm);
            if(!$result){
                $this->ajaxReturn(array("status"=>0,"info"=>"验证码错误"));
            } 
            if($money>5000){
                $this->ajaxReturn(array('status'=>0,'info'=>'单笔限额5000元！'));
            }
            
            $cache   =    $this->fenxiao_bank_db->where(array('card_num'=>$card_num))->find();
            $gmoney  =    $money+$cache['money']; 
            $res     =    $this->fenxiao_bank_db->save(array('id'=>$cache['id'],'money'=>$gmoney));
            if(!$res){
                $this->ajaxReturn(array('status'=>0,'info'=>'网络延迟'));
            }
           
            $cache1   =    $this->fenxiao_db->where(array('id'=>$this->fenxiao_id))->find();
            $smoney  =    $cache1['wallet']-$money;
        
            if($smoney<0){
                $this->ajaxReturn(array('status'=>0,'info'=>'您的余额不足！'));
            }
            $res1    =    $this->fenxiao_db->save(array('id'=>$this->fenxiao_id,'wallet'=>$smoney));
         
            if(!$res1){
                $this->fenxiao_bank_db->save(array('id'=>$cache['id'],'money'=>$cache['money']));//回滚
                $this->ajaxReturn(array('status'=>0,'info'=>'网络延迟'));
            }
            $data   =   array(
                'title'     =>  '提现',
                'pay'     =>  '-'.$money.'元',
                'fenxiao_id'   =>  $this->fenxiao_id,
                'card_num'  =>  $card_num,
                'addtime'   =>  time(),
            );
         
            $add   =   $this->fenxiao_bill_db->add($data);
            if($add){
                $this->ajaxReturn(array('status'=>1,'info'=>'提现成功'));
            }else{
                $this->fenxiao_bank_db->save(array('id'=>$cache['id'],'money'=>$cache['money']));//回滚
                $this->fenxiao_db->save(array('id'=>$this->fenxiao_id,'wallet'=>$fenxiao['money']));
                $this->ajaxReturn(array('status'=>0,'info'=>'网络延迟'));
            }
        }   
    }
    
    public function addImage()
    {   //上传图片
        $data = $this->uploadImg();
        $pic    =   substr($data[0]['savepath'],1).$data[0]['savename'];
        $data_info  =   array(
            'id'    =>  $this->fenxiao_id,
            'pic'   =>  $pic,
        );
        $this->fenxiao_db->save($data_info);
        $this->ajaxReturn($data);
    } 
    public function uploadImg() {//上传图片
        $upload = new \Think\UploadFile;
        $upload->maxSize  = 314572880 ;// 设置附件上传大小
        $upload->allowExts  = array('jpg','JPG','gif', 'png', 'jpeg');// 设置附件上传类型
        $savepath='./Uploads/Picture/'.date('Ymd').'/';
        if (!file_exists($savepath)){
            mkdir($savepath,'0777',true);
        }
        $upload->savePath =  $savepath;// 设置附件上传目录
        if(!$upload->upload()) {// 上传错误提示错误信息
            $this->error($upload->getErrorMsg());
        }else{// 上传成功 获取上传文件信息
            $info =  $upload->getUploadFileInfo();
           // $image = new \Think\Image();
           // $smallpath =  $savepath.$info[0]['savename'];
          //  $image->open($smallpath);
            //将图片裁剪为300x300并保存
           // $image->thumb(450, 272,\Think\Image::IMAGE_THUMB_FIXED)->save($smallpath);
            $image = new \Think\Image(); 
             $smallpath =  $savepath.$info[0]['savename'];
             $image->open($smallpath);
            // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
             $image->thumb(1200, 1200,3)->save($smallpath,NULL,100,true);
            //$image->thumb(100, 100,\Think\Image::IMAGE_THUMB_NORTHWEST)->save($smallpath);
           
        }
        return $info;
    }  
    //修改个人信息
    public function change(){
        if(IS_AJAX){
            $name   =   I('post.name');
            $tel    =   I('post.tel');
            if($name){
                $data   =   array(
                    'id'    =>  $this->fenxiao_id,
                    'personname'  =>  $name,
                );
                $res  = M('fenxiao')->save($data);
            }
            if($tel){
                $data   =   array(
                    'id'    =>  $this->fenxiao_id,
                    'telephone'   =>  $tel,
                );
                $res = $this->fenxiao_db->save($data);
            }
            if($res){
                $this->ajaxReturn(array('sttus'=>1));
            }
           
        }
        
    }
    
    //查询用户贷款申请状态
    public function isapply(){
            $tel    =   I('post.telephone');
        	$url="http://cps.ppdai.com/bd/GetUserListing";
			$data = "ChannelId=%s&SourceId=%s&token=%s&phone=%s&sign=%s";
			$ChannelId  =   212;
			$SourceId   =   372;
			$token      =   '293764805a1c4c6a9a2189f4e89fb52a'; 
            $phone      =   $tel;
            $string     =   'token='.$token.'&phone='.$phone;
            $paramMd5Str=   MD5($string);
            $sign       =   MD5($string.'&paramMd5='.$paramMd5Str );
			$rdata = sprintf($data, $ChannelId, $SourceId, $token, $phone,$sign);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_POST,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$rdata);
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			$result = curl_exec($ch);
			curl_close($ch);
            $content    =   json_decode($result);
            $this->ajaxReturn($result);
            //return $result;
     
    }
    //GD验证码
    public function yzm(){
        ob_clean();
        $config = array(
            'fontSize'    =>    15,    // 验证码字体大小    
            'length'      =>    4,     // 验证码位数    
            'useNoise'    =>    false, // 关闭验证码杂点
            'imageW'      =>    120,  //验证码宽度
            'imageH'      =>    40,   //验证码高度
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }
     
    //绑定银行卡操作
    public function bankdo(){
        if(IS_AJAX){
            $card_name  =   I('post.card_name');
            $card_num   =   I('post.card_num');
            $card_tel   =   I('post.card_tel');
            $code       =   I('post.code');
            if(!$code){
                $this->ajaxReturn(array("status"=>0, "info"=>"请填写短信验证码！"));
            }
  
            //=======================检测验证码=================
            $res = $this->checkMessage($card_tel, $code, 6);
           
            //$res['status']  =1;
            //=======================检测验证码=================
            if($res['status']!=1){
                $this->ajaxReturn(array('status'=>0,'info'=>'验证码错误！'));
            }
            $bank   =   $this->fenxiao_bank_db->where(array('card_num'=>$card_num))->find();
            if(!empty($bank)){
                $this->ajaxReturn(array('status'=>0,info=>"该银行卡已经被绑定了！"));
            }
            $data   =   array(
                'card_preson'   =>  $card_name,
                'card_num'      =>  $card_num,
                'card_tel'      =>  $card_tel,
                'fenxiao_id'       =>  $this->fenxiao_id,
            );
            
            $res    =   $this->fenxiao_bank_db->add($data);
            if($res){
                $this->ajaxReturn(array('status'=>1,info=>"绑定成功！"));
            }else{
                $this->ajaxReturn(array('status'=>0,info=>"绑定失败！"));
            }

        }
        
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
    /******个人中心*******/
}
?>