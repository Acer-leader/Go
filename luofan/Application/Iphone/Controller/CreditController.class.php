<?php
namespace Iphone\Controller;
use Think\Controller;
use Think\Model;
header("Content-type: text/html; charset=utf-8");
class CreditController extends PublicController{
    public $pagenum =   6;  //分页的一页数据
    public function index(){
        //按银行卡选
        $c_t    = M('card_type');
        $c_t_r  = $c_t->where(array('isdel'=>0,'pid'=>15))->order('sort desc')->limit(0,9)->select();
        //按用途选
        $c_t_y  = $c_t->where(array('isdel'=>0,'pid'=>20))->order('sort desc')->select();
        //信用卡申请
        $c_c = M('Creditcard');
        $res = $c_c->where(array('is_sale'=>0))->order("add_time desc")->limit('0,4')->select();
        
        //信用卡攻略
        $i = M('strategy');
        $count  =   $i->where(array('is_sale'=>1,'type'=>1))->count();
        $page    =   getpage($count,$this->pagenum);
        $page->setConfig('theme', '%UP_PAGE%%DOWN_PAGE%');
        $show   =   $page->show();
        $strategy   =   $i->where(array('is_sale'=>1,'type'=>1))->limit($page->firstRow,$page->listRows)->field('id,news_title')->order('add_time desc')->select();
        //dump($strategy);
        $this->assign('c_t_r',$c_t_r);
        $this->assign('c_t_y',$c_t_y);
        $this->assign('res',$res);
        $this->assign('strategy',$strategy);
        $this->assign('p',I('get.p'));
        $this->assign('count',$count);
        if($count>10){
            $this->assign('page',$show);
        }
        $this->display();
    }
    public function ajaxpage(){
        if(IS_AJAX){
            $p      =   I('post.p');
            $type   =   I('post.type');
            $count  =   I('post.count');
            $page   =   $p+$type;
            $newpage    =   $page*$this->pagenum;
            if($page<0){
                    $this->ajaxReturn(array('status'=>0, 'info'=>"已经是第一页了"));
                }
            if($page>=$count){
                $this->ajaxReturn(array('status'=>0, 'info'=>"已经是最后一页了"));
            }
            $res    =   M('strategy')->where(array('is_sale'=>1,'type'=>1))->limit($newpage,$this->pagenum)->order('add_time desc')->select();
            $str    =   "";
            foreach($res as $k=>$v){
                $str    .=  '<li><a href="__HOST__/Credit/newsDetail/id/'.$vo['id'].'">'.$vo['news_title'].'</a></li>';
            }
            $this->ajaxReturn(array('status'=>1, 'info'=>$str));
        
        }
        
    }
   /*  public function newsDetail(){
        $i      =   M('strategy');
        $id     =   I('get.id');
        if($id){
            $strategy   =   $i->find($id);
            $this->assign('strategy',$strategy);
            $this->display();
        }

    } */
    public function cardbank(){
        $id     =   I('get.id');
        $c_c    =   M('Creditcard');
        $c_t    =   M('card_type');
        
        $c_t_r    =   $c_t->where(array('id'=>$id))->field('pid,classname')->find();
        $res    =   $c_c->where(array('is_sale'=>0))->order('sort desc')->select();
        if($id){
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
          
        $this->display();
    }
    public function getsearch(){
        if(IS_AJAX){
            $c_c    =   M('Creditcard');
            $id     =   I('post.id');
            /* $pid    =   I('post.pid'); */
            $arr_id     =   explode('-',rtrim($id,'-'));
            /* $arr_pid    =   explode('-',rtrim($pid,'-'));
            $card_type    =   array_combine($arr_pid,$arr_id); */
 /*            foreach($card_type as $k=>$v){
                if(!$v){
                    unset($card_type[$k]);
                }
            } */
            $res = $c_c->where(array('is_sale'=>0))->order("sort desc")->select();
            $new    =   array();
            foreach($res as $k=>$v){
                
                $u_card_type  =   unserialize($v['card_type']);
                $res[$k]['logo_pic'] =   M('card_type')->where(array('id'=>$u_card_type[15]))->getField('logo_pic');
                foreach($arr_id as $val){
                    $flag   =   true;
                    if(!in_array($val,$u_card_type)){
                        $flag   =   false;
                        break;
                    }
                     
                }
                if($flag){
                    $new[]  =   $res[$k];
                }
              
               
            }
            if(empty($arr_id)){
                $new    =   $res;
            }
            $html   =   '';
            $html   .=  '<ul>';
            foreach($new as $k=>$vo){
                $html   .=  '<li>
                            <a href="__HOST__/Credit/card_dot/id/'.$vo["id"].'">
                            <div class="ny_gedai_box_list_l">
                            <div class="ny_gedai_box_list_l_type">
                            <span>人气</span>
                            <div class="ny_gedai_box_list_l_type_bg"></div>
                            </div>
                            <img src="'.__ROOT__.$vo["logo_pic"].'" />
                            </div>
                            <div class="ny_gedai_box_list_r">
                            <div class="ny_gedai_box_list_r_top">
                            '.$vo["creditname"].'
                            </div>
                            <div class="ny_gedai_box_list_r_bottom">
                            <div class="ny_gedai_box_list_r_bottom_list">
                            <dl>
                            <dt>申请数量： </dt>
                            <dd><span class="num_red">'.$vo["apply"].'</span>人申请</dd>
                            </dl>
                            <dl>
                            <dt>年费介绍： </dt>
                            <dd><span class="num_red">'.$vo["annual_fee"].'</span> </dd>
                            </dl>
                            </div>
                            <div class="ny_gedai_box_list_r_bottom_btn">查看详情</div>
                            </div>
                            </div>
                            <div class="clear"></div>
                            </a>
                            </li>';
            }
             $html   .=  '</ul>';
            /* dump($html); */
            if($new){
                $this->ajaxReturn(array('status'=>1,'info'=>$html));
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>"没有您要查找的卡型!"));
            }
          
            
        }
    }
    //选卡中心 cx
    public function creditList(){
        $c_c = M('Creditcard');
        $res = $c_c->where(array('is_sale'=>0))->order("sort desc")->select();
        $id = I('get.id');
        $city = $this->city;
      
            $card_type_15   =   I('get.card_type_15');
            if($card_type_15){
                $card_type[15]  =   $card_type_15;
                $this->assign('card_type_15',$card_type_15);
            }
            $card_type_20   =   I('get.card_type_20');
            if($card_type_20){
                $card_type[20]  =   $card_type_20;
                $this->assign('card_type_20',$card_type_20);
            }
            $card_type_21   =   I('get.card_type_21');
            if($card_type_21){
                $card_type[21]  =   $card_type_21;
                $this->assign('card_type_21',$card_type_21);
            }
            $c_t = M('card_type');
            
            $type   =   $c_t->where(array('isdel'=>0,'pid'=>0))->order('sort desc')->select();
            $type3  =   array();
            $type5  =   array();
            foreach($type as $k=>$v){
                $type[$k]['cate']   =   $c_t->where(array('isdel'=>0,'pid'=>$v['id']))->select();
                if($k<3){
                    $type3[]    =   $type[$k];
                }else{
                    $type5[]    =   $type[$k];
                }
            }
            $data['is_sale'] =   0;
            //dump($type3);
            
            //$res = $c_c->where(array('is_sale'=>0))->order('sort desc')->limit($page->firstRow.','.$page->listRows)->select();
        
            $res    = $c_c ->where($data)->order('sort desc')->select();
            $new    =   array();            
            foreach($res as $k=>$v){
                $u_card_type  =   unserialize($v['card_type']);
                $res[$k]['logo_pic'] =   M('card_type')->where(array('id'=>$u_card_type[15]))->getField('logo_pic');
                foreach($card_type as $val){
                    $flag   =   true;
                    if(!in_array($val,$u_card_type)){
                        $flag   =   false;
                        break;
                    }
                    
                }
                 if($flag){
                    $new[]  =   $res[$k];
                }
               
            }
            if(empty($card_type)){
                $new    =   $res;
            }
            
            //dump($new);
            $count  = count($new);
            $page   = getpage($count,10);
            $page->setConfig('theme', '%UP_PAGE%%DOWN_PAGE%');
            $show   = $page->show();
            $new = array_slice($new,$page->firstRow,$page->listRows);
                
            $this->assign('page',$show);
            
            /* $this->assign('c_t_r',$c_t_r); */
            $this->assign('res',$new);
            $this->assign('type3',$type3);
            $this->assign('type5',$type5);
            /* $this->assign('count',count($res)); */
           // dump($res);
        $this->display();
    }
    public function card_dot(){
        $id = I('get.id');
        $cc = M('creditcard');
        $cc_res = $cc -> where(array('id'=>$id))->find();
        $c_t = unserialize($cc_res['card_type']);
        $u = M('unionpay');
        if ($cc_res['unionpay']){
            $uni1 = $u->where(array('id'=>array('in',$cc_res['unionpay'])))->select();
            $this->assign('uni1',$uni1);
        }
        if ($cc_res['unionpay2']){
            $uni2 = $u->where(array('id'=>array('in',$cc_res['unionpay2'])))->select();
            $this->assign('uni2',$uni2);
        }

//        dump($c_t);
        $back = M('back')
            -> field('a.*,b.classname')
            ->alias('a')
            ->join('app_card_type as b on a.card_type_id = b.id ')
            ->where(array('a.card_type_id'=>$c_t['15']))
            ->find();
        $this->assign('back',$back);
        //卡银行
        $c = M('card_type');
        $c_t_res = $c->where(array('pid'=>15))->select();
        $this->assign('c_t_res',$c_t_res);
//        dump($c_t_res);
//        dump($back);
     /*    if(!$cc_res){
            $this->error('没有此信用卡');
        } */
        $news_m = M("news");
        $cate_m = M("news_cate");
        $cate = $cate_m->field('id,classname')->where(array("sort"=>3,"pid"=>0))->find();
        $hot_list['cate_id'] = $cate['id'];
        $new_list['cate_id'] = $cate['id'];
        //最热
        $hot_list['data'] = $news_m->where(array('is_hot'=>1,"cate_id"=>$cate['id']))->limit(8)->order("add_time desc")->select();
        //最新
        $new_list['data'] = $news_m->where(array("cate_id"=>$cate['id']))->limit(7)->order("add_time desc")->select();
        $this->assign('new_list',$new_list);
        $hot = M('Problem')->order('browse desc')->limit('5')->select();//热门话题
        $this->assign('hot',$hot);
		
		//seo配置
		$seo = M('SeoView')->where(array('controller'=>'Credit','function'=>'card_dot'))->find();
		$title = str_replace('$title',$cc_res['creditname'],$seo['title']);
		$this->assign('title',$title);
        $this->assign('cc_res',$cc_res);
        //你喜欢
        $you_res = $cc ->where(array('is_sale'=>0))->order('sort desc')->limit(4)->select();
//        dump($you_res);
        $this->assign('you_res',$you_res);
        //dump($cc_res);
        $this->display();
    }

   
    //信用卡优惠
    public function discount(){
        //置顶
        $f = M('favor');
        $cat=M('favor_cate');
        $cate_type=$cat->order('sort asc')->select();
        $cat1=M('favor_card_cate');
        $cate1_type=$cat1->order('sort asc')->select();
        $this->assign('cate_type',$cate_type);
        $this->assign('cate1_type',$cate1_type);

        $c_id = I('get.c_id');
        $t_id = I('get.t_id');
        if($c_id=="" and $t_id==""){
            $f_list = $f->order('sort desc')->select();
        }else if($c_id!="" and $t_id==""){
            $f_list = $f->where(array('card_type'=>$c_id))->order('sort desc')->select();
        }else if($c_id=="" and $t_id!=""){
            $f_list = $f->where(array('type'=>$t_id))->order('sort desc')->select();
        }else if($c_id!="" and $t_id!=""){
            $f_list = $f->where(array('card_type'=>$c_id,'type'=>$t_id))->order('sort desc')->select();
        }

        $this->assign('c_id',$c_id);
        $this->assign('t_id',$t_id);
        $this->assign('f_list',$f_list);
        /*$f_res_h = $f->where(array('is_sale'=>1,'is_hot'=>1))->limit(4)->order('sort desc')->select();
        $f_res = $f->where(array('is_sale'=>1))->order('sort desc')->limit(5)->select();
        $f_res1 = $f->where(array('is_sale'=>1,'type'=>1))->order('sort desc')->limit(5)->select();
        $f_res2 = $f->where(array('is_sale'=>1,'type'=>2))->order('sort desc')->limit(5)->select();
        $f_res3 = $f->where(array('is_sale'=>1,'type'=>3))->order('sort desc')->limit(5)->select();
        $f_res4 = $f->where(array('is_sale'=>1,'type'=>4))->order('sort desc')->limit(5)->select();
        $f_res5 = $f->where(array('is_sale'=>1,'type'=>5))->order('sort desc')->limit(5)->select();
        $f_res6 = $f->where(array('is_sale'=>1,'type'=>6))->order('sort desc')->limit(5)->select();
        $this->assign('f_res_h',$f_res_h);
        $this->assign('f_res',$f_res);
        $this->assign('f_res1',$f_res1);
        $this->assign('f_res2',$f_res2);
        $this->assign('f_res3',$f_res3);
        $this->assign('f_res4',$f_res4);
        $this->assign('f_res5',$f_res5);
        $this->assign('f_res6',$f_res6);
        //积分活动
        $i = M('integral');
        $i_res = $i->where(array('is_sale'=>1))->order('sort desc')->limit(10)->select();
        $this->assign('i_res',$i_res);
        $n = M('new_user');
        $n_res = $n->where(array('is_sale'=>1))->order('sort desc')->limit(10)->select();
        $this->assign('n_res',$n_res);

        //卡银行
        $c = M('card_type');
        $c_t_res = $c->where(array('pid'=>15))->select();
        $this->assign('c_t_res',$c_t_res);*/
        $this->display();
    }


    public function discountdot(){
        $id = I('get.id');
        $f = M('favor');
        $f_dot = $f->find($id);
        $this->assign('res',$f_dot);
        $this->display();
    }

    //信用卡攻略
    public function raiders(){
        $s = M('strategy');
        //卡攻略
        $s_r1 = $s->where(array('is_sale'=>1,'type'=>1))->order("sort desc")->limit(8)->select();
        $s_r2 = $s->where(array('is_sale'=>1,'type'=>2))->order("sort desc")->limit(8)->select();
        $s_r3 = $s->where(array('is_sale'=>1,'type'=>3))->order("sort desc")->limit(4)->select();
        $s_r4 = $s->where(array('is_sale'=>1,'type'=>4))->order("sort desc")->limit(8)->select();
        $s_r5 = $s->where(array('is_sale'=>1,'type'=>5))->order("sort desc")->limit(5)->select();
        $s_r6 = $s->where(array('is_sale'=>1,'type'=>6))->order("sort desc")->limit(4)->select();
        $this->assign('s_r1',$s_r1);
        $this->assign('s_r2',$s_r2);
        $this->assign('s_r3',$s_r3);
        $this->assign('s_r4',$s_r4);
        $this->assign('s_r5',$s_r5);
        $this->assign('s_r6',$s_r6);
        //热么关键字
        $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
        $seo = $Model->query("select DISTINCT seo from app_strategy where is_sale = 1");
        $this->assign('seo',$seo);
//        var_dump($res);
        //卡银行
        $c = M('card_type');
        $c_t_res = $c->where(array('pid'=>15))->limit(8)->select();
        $this->assign('c_t_res',$c_t_res);
        $this->display();
    }
    
    //申请进度查询
    public function query(){
        //卡银行
        $c_t = M('card_type');
        $c_t_res = $c_t
            ->alias('a')
            ->field('a.*,b.schedule')
            ->join('app_back as b on a.id = b.card_type_id')
            ->where(array('a.pid'=>15))->select();
        $this->assign('c_t_res',$c_t_res);
        //信用卡
        $c_c = M('creditcard');
        $c_c_res = $c_c->order('sort desc')->order('sort desc')->limit(9)->select();
        $this->assign('c_c_res',$c_c_res);
        $this->display();
    }

  
    public function addAnswer(){
        if(IS_AJAX){
            $data['p_id'] = I('post.id');
            $data['content'] = I('post.content');
            M('Problem')->where(array('id'=>$data['p_id']))->setInc('reply');
            $res = M('Answer')->add($data);
            if($res){
                $this->ajaxReturn(array('status'=>1));
            }else{
                $this->ajaxReturn(array('status'=>0, 'info'=>'发生未知错误，请联系管理员！'));
            }
        }
    }
    //关键字搜索
    public function search(){
        if(IS_AJAX){
            $title = I('post.content');
            $sql['title'] = array('like','%'.$title.'%');
            $res = M('Problem')->where($sql)->select();
            if($res){
                $this->ajaxReturn(array('status'=>1, 'info'=>$res));
            }else{
                $this->ajaxReturn(array('status'=>0, 'info'=>'提问失败！'));
            }
        }
    }
   
    //各种详情页
    public function newsDetail(){
        $type = I('get.type');
        $types = I('get.types');
        $id = I('get.id');
		// dump($type);
		// dump($types);
		// dump($id);
        if ($types == 1) {
            $i = M('integral');
            $res_s = $i->where(array('is_sale'=>1,'id'=>array('lt',$id)))->order('id DESC')->limit(1)->field('id,news_title,type')->find();//上一个
            $res = $i->where(array('is_sale'=>1,'id'=>$id))->find();//当前这个
            $res_x = $i->where(array('is_sale'=>1,'id'=>array('gt',$id)))->order('id ASC')->limit(1)->field('id,news_title,type')->find();//下一个
            if ($res) {
                $res['types'] =1;
            }
            if ($res_s) {
                $res_s['types'] =1;
            }
            if ($res_x) {
                $res_x['types'] =1;
            }

            $res_list = $i->where(array('is_sale'=>1,'id'=>array('NEQ',$id)))->limit(8)->select();//当前这个以外的
            if ($res_list) {
                foreach ($res_list as $key => $value) {
                    $res_list[$key]['types'] =1;
                }
            }
        }elseif ($types == 2) {
            $i = M('favor');
            $res_s = $i->where(array('is_sale'=>1,'type'=>$type,'id'=>array('lt',$id)))->order('id DESC')->limit(1)->field('id,news_title,type')->find();//上一个
            $res = $i->where(array('is_sale'=>1,'type'=>$type,'id'=>$id))->find();//当前这个
            $res_x = $i->where(array('is_sale'=>1,'type'=>$type,'id'=>array('gt',$id)))->order('id ASC')->limit(1)->field('id,news_title,type')->find();//下一个

            if ($res_s) {
                $res_s['types'] =2;
            }
            if ($res_x) {
                $res_x['types'] =2;
            }
            if ($res) {
                $res['types'] =2;
                $map1['see'] = $res['see']+1; 
                $res_xx = $i->where(array('id'=>$id))->save($map1);
            }
            $res_list = $i->where(array('is_sale'=>1,'type'=>$type,'id'=>array('NEQ',$id)))->limit(8)->select();//当前这个以外的
            if ($res_list) {
                foreach ($res_list as $key => $value) {
                    $res_list[$key]['types'] =2;
                }
            }
        }elseif ($types == 3) {
            $i = M('new_user');
            $res_s = $i->where(array('is_sale'=>1,'id'=>array('lt',$id)))->order('id DESC')->limit(1)->field('id,news_title')->find();//上一个
            $res = $i->where(array('is_sale'=>1,'id'=>$id))->find();//当前这个
            $res_x = $i->where(array('is_sale'=>1,'id'=>array('gt',$id)))->order('id ASC')->limit(1)->field('id,news_title')->find();//下一个
            if ($res_s) {
                $res_s['types'] =3;
            }
            if ($res_x) {
                $res_x['types'] =3;
            }
            if ($res) {
                $res['types'] =3;
            }
            $res_list = $i->where(array('is_sale'=>1,'id'=>array('NEQ',$id)))->limit(8)->select();//当前这个以外的
            if ($res_list) {
                foreach ($res_list as $key => $value) {
                    $res_list[$key]['types'] =3;
                }
            }
        }elseif ($types == 4) {
            $i = M('strategy');
            $res_s = $i->where(array('is_sale'=>1,'id'=>array('lt',$id),'type'=>$type))->order('id DESC')->limit(1)->field('id,news_title,type')->find();//上一个
            $res = $i->where(array('is_sale'=>1,'id'=>$id,'type'=>$type))->find();//当前这个
            $res_x = $i->where(array('is_sale'=>1,'id'=>array('gt',$id),'type'=>$type))->order('id ASC')->limit(1)->field('id,news_title,type')->find();//下一个
            if ($res_s) {
                $res_s['types'] =4;
            }
            if ($res_x) {
                $res_x['types'] =4;
            }
            if ($res) {
                $res['types'] =4;
            }
            $res_list = $i->where(array('is_sale'=>1,'id'=>array('NEQ',$id),'type'=>$type))->limit(8)->select();//当前这个以外的
            if ($res_list) {
                foreach ($res_list as $key => $value) {
                    $res_list[$key]['types'] =1;
                }
            }
        }
		//seo设置
		$seo = M('SeoView')->where(array('controller'=>'Credit','function'=>'newsDetail'))->find();
		$title = str_replace('$title',$res['news_title'],$seo['title']);
		$this->assign('title',$title);
		
		//相关资讯
        $newslist = M('news')->where('id >'.$id)->where(array('is_show'=>0))->limit(0,5)->select();
		$news_pid = $newslist[0]['cate_pid'];
        //今日头条
        $today = M('news')->where(array('cate_name'=>'今日头条'))->order('sort asc')->limit(3)->select();
		$pid2 = $today[0]['cate_id'];
        //热门资讯
        $hot = M('news')->where(array('is_hot'=>1))->order('sort asc')->limit(3)->select();
		$pid3 = $hot[0]['cate_id'];
		$this->assign('pid1',$news_pid);
		$this->assign('id1',$pid2);
		$this->assign('id2',$pid3);
		$this->assign('today',$today);
        $this->assign('hot',$hot);
       //dump($res);
        $this->assign('res_s',$res_s);
        $this->assign('res_x',$res_x);
        $this->assign('res',$res);
        $this->assign('res_list',$res_list);
        $this->display();
    }



}

?>