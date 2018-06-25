<?php
namespace Iphone\Controller;
use Think\Controller;
class CarLoanController extends LoanController{

    public function _initialize(){
        header("Content-type: text/html; charset=utf-8");
        parent::_initialize();
      
    }

    public function index(){
        session('loan',null);
        $data        =   array('cate_id'=>3,'is_del'=>0,'is_sale'=>1);
        $card_type1  =   I('get.type1');
        $card_type2  =   I('get.type2');
        $card_type3  =   I('get.type3');
        if($card_type1){
            $data['identityid']   =   $card_type1;
        }
       if($card_type2){
            $data['houseid']   =   $card_type2;
        }
        if($card_type3){
            $data['carid']   =   $card_type3;
        }
        $l  =   M('loan');
        $c  =   M('cate');
        //三种搜索分类
        $type1  =   $c->where(array('id'=>array('in','4,10,18'),'pid'=>0))->select();
        foreach($type1 as $k=>$v){
            $type1[$k]['typeid']    =   $c->where(array('pid'=>$v['id']))->select();
        }
        $this->assign('type1',$type1);
        //剩余的分类
        $type2 =   $c->where(array('id'=>array('in','21,30,34,41'),'pid'=>0))->select();
        foreach($type2 as $k=>$v){
            $type2[$k]['typeid']    =   $c->where(array('pid'=>$v['id']))->select();
        }
        $this->assign('type2',$type2);
        $new    =   $l->where($data)->order('create_time desc')->select();
   

        //dump($new);
        $count  = count($new);
        $page   = getpage($count,10);
        $page->setConfig('theme', '%UP_PAGE%%DOWN_PAGE%');
        $show   = $page->show();
        $new = array_slice($new,$page->firstRow,$page->listRows);          
        $this->assign('page',$show);
        $this->assign('carloan',$new);
        $gl =   M('news');
        $cargonlve  =   $gl->where(array('cate_pid'=>92))->order('add_time desc')->limit(0,6)->select();
        $this->assign('cargonlve',$cargonlve);
        //dump($carloan);
        $this->display();
    }
    
    public function card_search(){
        $id     =   I('get.id');
        $identityid     =   I('get.identityid');
        $houseid        =   I('get.houseid');
        $honourid       =   I('get.honourid');
        $search         =   I('get.search');
        $cateid         =   I('get.cateid');
        if($search=="万元"){
            $search ="";
        }
        if($cateid){
           session('loan.cateid',$cateid);
        }else{
           $cateid =   session('loan.cateid');
        }
        
        
        $data   =   array('cate_id'=>$cateid,'is_del'=>0,'is_sale'=>1);
        if($identityid){
            $data['identityid'] =   $identityid;
        }

        if($houseid){
            $data['houseid']    =   $houseid; 
        }
        if($honourid){
            $data['honourid']    =   $honourid;
        }
        
        if($search){
            $where['money']   = array('elt', $search);    
            $where['money1']  = array('egt',$search);  
            $where['_logic'] = 'and';   
            $data['_complex'] = $where;   
 
        }

        $c  =   M('cate');
        $l  =   M('loan');
        $carloan    =   $l->where($data)->order('create_time desc')->order('create_time desc')->select();
        $res       =   $c->where(array('id'=>array('in','4,10,21'),'isdel'=>0))->field('id,classname')->order('sort desc')->select();
        foreach($res as $k=>$v){
            $res[$k]['cate']    =   $c->where(array('pid'=>$v['id'],'isdel'=>0))->field('id,classname')->order('sort desc')->select();
        }

        $this->assign('cateid',$cateid);
        
        $this->assign('identityid',$data['identityid']);
        $this->assign('houseid',$data['houseid']);
        $this->assign('honourid',$data['honourid']);
        $this->assign('carloan',$carloan);
        $this->assign('res',$res);
        //dump($carloan);
        /* if($id){
           $new    =   array();
            foreach($res as $k=>$v){
                $card_type  = unserialize($v['card_type']);
                if($card_type[$c_t_r['pid']] ==  $id){
                    $new[]  =   $v;
                }
            } 
            $this->assign('new',$new);
            $this->assign('brank',$c_t_r['classname']);
        }else{
            $this->assign('new',$res);
        }
           */
        
        $this->display();
    }
    
    public function car_dot(){
       
        session('loan',null);
        $fenxiao_id =   session('fenxiao_id');
        
        $id     =   I('get.id');
        if($id){
            $res    =   M('loan')->find($id);
            $this->assign('res',$res);
            //dump($res);
            $gl =   M('news');
            $url =  get_url().'/fenxiao_id/'.$fenxiao_id;
      
		
            $shareuser_id   =   I('get.fenxiao_id');
           
       
            $cargonlve  =   $gl->where(array('cate_pid'=>92))->order('add_time desc')->limit(0,6)->select();
            
            $this->assign('shareuser_id',$shareuser_id);
            $this->assign('pic',$pic);
            $this->assign('url',$url);
            $this->assign('cargonlve',$cargonlve);
            $this->assign('fenxiao_id',$fenxiao_id);
            $this->display();
        }
       
    }
    //申请贷款
    public function applydo(){
        $money  =   I('post.money');
        $qixian =   I('post.qixian');
        $ylx =   I('post.ylx');
        $card_id =   I('post.card_id');
        $shareuser_id =   I('post.shareuser_id');
        $data   =   array(
            'money'     =>  $money,
            'qixian'    =>  $qixian,
            'ylx'       =>  $ylx,
            'card_id'   =>  $card_id,
            'shareuser_id'  =>  $shareuser_id,
        );
        session('apply',$data);
        $this->ajaxReturn(array('status'=>1));
       /*  if($shareuser_id){
            $data['shareuser_id']   =   $shareuser_id;
        } */
        
    }
    
    //分享生成二维码
    public function sharedo(){
        $fenxiao_id =   session('fenxiao_id');
        if(IS_AJAX){
            $url    =   I('post.url');
          
            $card_id    =   I('post.card_id');
         	$save_path	= "./Uploads/qrcode/";
            $pic    =   $this->qrcode($url,$save_path);

            $res    =   M('fenxiao_share')->where(array('card_id'=>$card_id,'fenxiao_id'=>$fenxiao_id))->find();
            if($res){
                $data   =   array('id'=>$res['id'],'addtime'=>time());
                $share  =   M('fenxiao_share')->save($data);
            }else{
                $data   =   array('url'=>$url,'pic'=>$pic,'fenxiao_id'=>$fenxiao_id,'card_id'=>$card_id,'addtime'=>time());
                $share  =   M('fenxiao_share')->add($data);
            }
               
            if($share){
                $this->ajaxReturn(array('status'=>1));
            }else{
                $this->ajaxReturn(array('status'=>0));
            }
           
        }
    }
    //攻略详情
    public function newsDetail(){
        $id     =   I('get.id');
        if($id){
            $res    =   M('news')->find($id);
            $this->assign('res',$res);
            //dump($res);
            $this->display();
        }
        
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
         $image->water('./Public/Iphone/images/water.jpg',5)->save($img,NULL,100,true);

      //  $image = new \Think\Image();   
        //$image->open($img); 
        //水印
        // $image->water('./Public/Iphone/images/water.jpg',5)->save($img,NULL,100,true);
         // $image->text('123', 'D:/WWW/luofan/Public/Iphone/fonts/FontAwesome.otf', 12, $color = '#00000000',6, $offset = 0, $angle = 0)->save($img,NULL,100,true);
         //$image->text->save($img);
        //echo "<img src='".$pic."'>";
        return $pic;
    }  
}
?>