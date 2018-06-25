<?php
namespace Iphone\Controller;
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
        $fenxiao  =    $this->fenxiao_db->find($this->fenxiao_id);
        if(session('fenxiao_id')&&$fenxiao){ 
            $is_check =    $fenxiao['is_check'];
            $this->assign('fenxiao',$fenxiao);
            if($is_check!=1){
                // echo '<script>alert("如已支付，请联系客服!");location.href="/Iphone/Login/ewm"</script>';die;
                $this->redirect("/Iphone/Login/ewm");die;
                // $this->success('如已支付，请联系客服!', '/Iphone/Login/ewm');
            } 
        }else{
            echo "<script>alert('你的账号不存在或已被冻结请联系管理员')</script>";
           $this->redirect("/Iphone/Login/");die;
        }
        
       
    }
    public function index(){
        $share  =   $this->fenxiao_share_db->where(array('fenxiao_id'=>$this->fenxiao_id))->select();
        $new    =   array();
        foreach($share as $k=>$v){
            $share[$k]['card_title']  = $this->loan_db->where(array('id'=>$v['card_id']))->getField('title');
            if($share[$k]['card_title']){
                $new[]  =   $share[$k];
            }
            
        }
        foreach($share as $k=>$v){
            $share[$k]['card_title']  = $this->creditcard_db->where(array('id'=>$v['card_id']))->getField('creditname');
            if($share[$k]['card_title']){
                $new[]  =   $share[$k];
            }
      
        }
        $count  = count($new);
        $page   = getpage($count,10);
        $page->setConfig('theme', '%UP_PAGE%%DOWN_PAGE%');
        $show   = $page->show();
        $new    = array_slice($new,$page->firstRow,$page->listRows);
        $this->assign('new',$new);     
        $this->assign('page',$show);
        $this->display();
    }
    public function houtai1(){
        /* $car    =   $this->loan_order_db->where(array(cate_id=>3,'uid'=>$this->fenxiao_id))->order('apply_at desc')->select();
        $house    =   $this->loan_order_db->where(array(cate_id=>2,'uid'=>$this->fenxiao_id))->order('apply_at desc')->select();
        $person    =   $this->loan_order_db->where(array(cate_id=>1,'uid'=>$this->fenxiao_id))->order('apply_at desc')->select(); */
 
        
        $client =    $this->loan_order_db->order('apply_at desc')->select();
    
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
        $car    =   array();
        $person =   array();
        $house  =   array();
        foreach($new as $k=>$v){
            if($v['cate_id']==1){
                $person[]   =   $v;
            }
            if($v['cate_id']==2){
                $house[]    =   $v;
            }
            if($v['cate_id']==3){
                $car[]      =   $v;
            }
        }
      
        /* dump($new); */
       /*  $count  = count($new);
        $page   = getpage($count,10);
        //$page->setConfig('theme', '%UP_PAGE%%DOWN_PAGE%');
        $show   = $page->show();
        $new    = array_slice($new,$page->firstRow,$page->listRows);
        $this->assign('new',$new);     
        $this->assign('page',$show); */
        $this->assign('car',$car);
        $this->assign('house',$house);
        $this->assign('person',$person);
     
        $this->display();
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
    
    public function houtai(){
        $this->display();
    }
    public function houtai2(){
        $cate   =   $this->inquire_cate_db->where(array('isdel'=>0,'pid'=>0))->order('sort asc')->select();
        foreach($cate as $k=>$v){
            $res = $this->inquire_cate_db->where(array('isdel'=>0,'pid'=>$v['id']))->order('sort asc')->select();
            $count      =   count($res);
            if($k == 0){          
                $num        =   ceil($count/4);
                $gcoop      =   array();
             
                for($j=0; $j<$num; $j++){ 
                    for($i=0; $i<4; $i++){                    
                        $gcoop[$j][$i]  =   $res[$j*4+$i];
                    }
                }
            }else{
                $num        =   ceil($count/2);
                $gcoop      =   array();
             
                for($j=0; $j<$num; $j++){ 
                    for($i=0; $i<2; $i++){                    
                        $gcoop[$j][$i]  =   $res[$j*2+$i];
                    }
                }
            }
            $cate[$k]['cate']  =   $gcoop;
        }
        $cate_id1   =   I('get.cate_id1');
        $cate_id2   =   I('get.cate_id2');
        $cate_id3   =   I('get.cate_id3');
        if($cate_id1){
            $data['cate_id1']   =   $cate_id1;
            $type   =   0;
        }
        if($cate_id2){
            $data['cate_id2']   =   $cate_id2;
            $type   =   1;
        }
        if($cate_id3){
            $data['cate_id3']   =   $cate_id3;
            $type   =   2;
        }
        /* dump($cate); */
        $res    =   $this->inquire_db->where(array('isdel'=>0))->where($data)->order('sort desc')->select();
        $count  = count($res);
        $page   = getpage($count,9);
        $page->setConfig('theme', '%UP_PAGE%%DOWN_PAGE%');
        $show   = $page->show();
        $res = array_slice($res,$page->firstRow,$page->listRows);
                
        $this->assign('page',$show);
    /*     dump($cate); */
        $this->assign('cate',$cate);

        $this->assign('res',$res);
        $this->assign('type',$type);

        $this->display();
    }
    public function register(){
        $this->display();
    }
    
    public function fx_share_gd(){
        $card_id    =   I('get.card_id');
        if($card_id){
            $share  =   $this->fenxiao_share_db->where(array('fenxiao_id'=>$this->fenxiao_id,'card_id'=>$card_id))->find();
            $this->assign('share',$share);
       
            $this->display();
        }

    }
    
    /******个人中心 cx******/
    //首页
    public function fx_my(){
     

        $this->display();


    }
    //个人信息
    public function fx_my_grxx(){

        
        $this->display();
    }
    //提现
    public function fx_yongjingtixian(){
        $tixian =   $this->fenxiao_bank_db->where(array('fenxiao_id'=>$this->fenxiao_id))->find();
    
        $this->assign('tixian',$tixian);

        $this->display();
    }

    //绑定银行卡
    public function fx_bdyhk(){
        $this->display();
    }
  
    //账单
    public function fx_zhangdan(){
        $zhangdan   =   $this->fenxiao_bill_db->where(array('fenxiao_id'=>$this->fenxiao_id))->order('addtime desc')->select();
        $this->assign('zhangdan',$zhangdan);
        
        $this->display();
    }
    //账户余额
    public function fx_zhanghuyue(){
        $tixian =   $this->fenxiao_bank_db->where(array('fenxiao_id'=>$this->fenxiao_id))->find();
        $this->assign('tixian',$tixian);
        $this->display();
    }
    //新手指南
    public function fx_xszn(){
        $this->display();
    }
    //客服
    public function fx_kf(){
        $this->display();
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
            $fenxiao    =   $this->fenxiao_bank_db->where(array('fenxiao_id'=>$this->fenxiao_id))->find();
            if(empty($fenxiao)){
                $data   =   array(
                'card_preson'   =>  $card_name,
                'card_num'      =>  $card_num,
                'card_tel'      =>  $card_tel,
                'fenxiao_id'    =>  $this->fenxiao_id,
                ); 
                $res    =   $this->fenxiao_bank_db->add($data);
            }else{
                $data   =   array(
                'id'            =>  $fenxiao['id'],
                'card_preson'   =>  $card_name,
                'card_num'      =>  $card_num,
                'card_tel'      =>  $card_tel,
                ); 
                $res    =   $this->fenxiao_bank_db->save($data);
            }

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