<?php

namespace Iphone\Controller;
use Think\Controller;
use Think\Model;
header("content-Type: text/html; charset=Utf-8");
class NewsController extends PublicController{

//不许有三级分类
    //资讯
    public function index(){

        $news_m = M("news");
        $classid=I('get.classid');
        if($classid==""){
            $classid=90;
        }
        $news_list = $news_m->where(array('cate_id'=>$classid))->order('sort asc')->select();
        $this->assign('classid',$classid);
        $this->assign('news_list',$news_list);
        $this->display();
    }

    public function newsdot(){
        $news_m = M("news");
        $id=I('get.id');
        $dot=$news_m->find($id);
        $add_time=date('Y-m-d H:i:s', $dot['add_time']); 
        $this->assign('dot',$dot);
        $this->assign('add_time',$add_time);
        $this->display();
    }


    //问答
    public function wenda(){
        $classid=I('get.classid');  //1个贷 2房贷 3车贷 4信用卡
        if($classid==""){
            $classid=1;
        }
        if($classid==1){
            $news_m = M("problem");  
            $wd_list = $news_m->order('sort asc')->select(); 
        }else if($classid==2){
            $news_m = M("problems");  
            $wd_list = $news_m->order('sort asc')->select();
        }else if($classid==3){
            $news_m = M("problemss");  
            $wd_list = $news_m->order('sort asc')->select();
        }else if($classid==4){
            $news_m = M("problemsss");  
            $wd_list = $news_m->order('sort asc')->select();
        }
        //最新
        if($classid==1){
            $news_m = M("problem");  
            $wd_list1 = $news_m->order('add_time desc')->limit(0,10)->select(); 
        }else if($classid==2){
            $news_m = M("problems");  
            $wd_list1 = $news_m->order('add_time desc')->limit(0,10)->select();
        }else if($classid==3){
            $news_m = M("problemss");  
            $wd_list1 = $news_m->order('add_time desc')->limit(0,10)->select();
        }else if($classid==4){
            $news_m = M("problemsss");  
            $wd_list1 = $news_m->order('add_time desc')->limit(0,10)->select();
        }

        $this->assign('classid',$classid);
        $this->assign('wd_list',$wd_list);
        $this->assign('wd_list1',$wd_list1);
        $this->display();
    }

    public function wendadot(){
        $classid=I('get.classid');//1个贷 2房贷 3车贷 4信用卡
        //问答标题 
        $id=I('get.id');  
        if($classid==1){
            $news_m = M("problem");
            $replys=M("answer");
            $dot=$news_m->find($id);
            $reply=$replys->where(array('p_id'=>$id))->select();
            $member=M('member');
            foreach($reply as $k=>$v){
               $reply[$k]['name']=$member->where(array('id'=>$v['user_id']))->getField('person_name');               
            }
        }else if($classid==2){
            $news_m = M("problems");
            $replys=M("answers");
            $dot=$news_m->find($id);
            $reply=$replys->where(array('p_id'=>$id))->select();
            $member=M('member');
            foreach($reply as $k=>$v){
               $reply[$k]['name']=$member->where(array('id'=>$v['user_id']))->getField('person_name');               
            }
        }else if($classid==3){
            $news_m = M("problemss");
            $replys=M("answerss");
            $dot=$news_m->find($id);
            $reply=$replys->where(array('p_id'=>$id))->select();
            $member=M('member');
            foreach($reply as $k=>$v){
               $reply[$k]['name']=$member->where(array('id'=>$v['user_id']))->getField('person_name');               
            }
        }else if($classid==4){
            $news_m = M("problemsss");
            $dot=$news_m->find($id);
            $replys=M("answersss");
            $reply=$replys->where(array('p_id'=>$id))->select();
            $member=M('member');
            foreach($reply as $k=>$v){
               $reply[$k]['name']=$member->where(array('id'=>$v['user_id']))->getField('person_name');               
            }
        }
        $add_time=date('Y-m-d H:i:s', $dot['add_time']);
        $this->assign('add_time',$add_time);
        $this->assign('dot',$dot);
        $this->assign('reply',$reply);
        $this->display();
    }

    //提问
    public function tiwen(){
        $classid=I('get.classid');
        $this->assign('classid',$classid);
        $this->display();
    }

    public function addTiwen(){
        if(IS_AJAX){
            $data['title'] = I('post.title');
            $data['email'] = I('post.email');
            $data['email'] = I('post.email');
            $data['add_time'] = time();
            $user_id = session('user_id');
            $classid=I('post.classid');
            if(!$user_id){
                $this->ajaxReturn(array('status'=>0, 'info'=>'请先登录！'));
            }else{
                $data['user_id'] = $user_id;
            }
            $data['user_id']=1;
            if($classid==1){
                $res = M('Problem')->add($data);
            }else if($classid==2){
                $res = M('Problems')->add($data);
            }else if($classid==3){
                $res = M('Problemss')->add($data);
            }else if($classid==4){
                $res = M('Problemsss')->add($data);
            }
         
            if($res){
                $this->ajaxReturn(array('status'=>1, 'info'=>'提问成功！'));
            }else{
                $this->ajaxReturn(array('status'=>0, 'info'=>'提问失败！'));
            }
        }
    }

    public function gonglue(){
        $news_m = M("news");
        $classid=I('get.classid');
        $gl_list=$news_m->where(array('cate_pid'=>$classid))->select();
        $this->assign('classid',$classid);
        $this->assign('gl_list',$gl_list);
        $this->display();
    }

    public function newsDetail(){
        $news_m = M("news");
        $id=I('get.id');
        $gl_dot=$news_m->find($id);
        $this->assign('gl_dot',$gl_dot);
        $this->display();
    }

    public function xykgl(){
        $gl=M('strategy');
        $classid=I('get.classid');
        $gl_list=$gl->where(array('type'=>$classid))->select();
        $this->assign('gl_list',$gl_list);
        $this->display();
    }

    public function xykgldot(){
        $gl=M('strategy');
        $id=I('get.id');
        $gl_dot=$gl->find($id);
        $this->assign('gl_dot',$gl_dot);
        $this->display();
    }





    //个贷资讯
    public function personalNews(){
        $cate_m = M("news_cate");
        $news_m = M("news");

        $cate = $cate_m->field('id,classname')->where(array("sort"=>1,"pid"=>0))->find();
        $res  = $news_m->where(array('cate_id'=>$cate['id']))->order("add_time desc,sort desc")->limit(50)->select();
        $this->assign('res',$res);
        $this->display();
    }


    //房贷资讯
    public function houseNews(){
        $id  = M("news_cate")->where(array("pid"=>0,'sort'=>2))->getField('id');
        $list = M("news_cate")->where(array("pid"=>$id))->limit(6)->select();
        $toutiao_list = M("news_cate")->where(array("pid"=>$id,"sort"=>6))->find();
        foreach($list as $k=>$v){
            $res[$k]['one_cate']    = $v['classname'];
            $res[$k]['data']        = M("news")->where(array('cate_pid'=>$v['id']))->select();
        }
        //房贷头条
        $toutiao_list['list'] = M("news")->where(array('cate_pid'=>$toutiao_list['id']))->order("sort asc")->limit(26)->select();
        $this->assign("res",$res);
        $this->assign("toutiao_list",$toutiao_list);
        // dump($toutiao_list);
        // dump($res);
        $this->display();
    }

   //车贷资讯
    public function carNews(){
		$cate_m = M("news_cate");
        $news_m = M("news");

        $cate = $cate_m->field('id,classname')->where(array("sort"=>3,"pid"=>0))->find();
        $res  = $news_m->where(array('cate_id'=>$cate['id']))->order("sort desc,add_time desc")->limit(50)->select();
        $this->assign('res',$res);
        // dump($cate);die;

        //贷款资讯
        $res1_cate = $cate_m->field("id,classname")->where(array('sort'=>103,"pid"=>0))->find();
        $res1['data'] = $news_m
                ->where(array('is_hot'=>1,'cate_id'=>$res1_cate['id']))
                ->limit(10)
                ->order("add_time desc,sort desc")
                ->select();
        $res1['classname']  = $res1_cate['classname'];
        $res1['cate_id']    = $res1_cate['id'];
        $this->assign('res1',$res1);

        //房贷资讯
        $res2_cate = $cate_m->field("id,classname")->where(array('sort'=>2,"pid"=>0))->find();
        $res2['data'] = $news_m
                ->where(array('is_hot'=>1,'cate_id'=>$res2_cate['id']))
                ->limit(4)
                ->order("add_time desc,sort desc")
                ->select();
        $res2['classname'] = $res2_cate['classname'];
        $res2['cate_id']   = $res2_cate['id'];
        $this->assign('res2',$res2);        $this->display();
    }

    //信用卡资讯
    public function creditNews(){
       $s = M('strategy');
        //卡攻略
        $s_r1 = $s->where(array('is_sale'=>1,'type'=>1))->order("sort desc")->limit(8)->select();
        $s_r2 = $s->where(array('is_sale'=>1,'type'=>2))->order("sort desc")->limit(8)->select();
        $s_r3 = $s->where(array('is_sale'=>1,'type'=>3))->order("sort desc")->limit(2)->select();
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
        //$Model = new Model(); // 实例化一个model对象 没有对应任何数据表
        //$seo = $Model->query("select DISTINCT seo from app_strategy where is_sale = 1 limit 12 order by add_time desc");
		$seo = M('strategy')->distinct('seo')->limit(10)->order('add_time desc')->select();
        $this->assign('seo',$seo);
//        var_dump($res);
        //卡银行
        $c = M('card_type');
        $c_t_res = $c->where(array('pid'=>15))->limit(8)->select();
        $this->assign('c_t_res',$c_t_res);

        $news_m = M("news");
        $cate_m = M("news_cate");
        $cate = $cate_m->field('id,classname')->where(array("sort"=>4,"pid"=>0))->find();
        $hot_list['cate_id'] = $cate['id'];
        $new_list['cate_id'] = $cate['id'];

        //今日头条
        $top = $cate_m->where(array('sort'=>10,"pid"=>$cate['id']))->find();
        $top_list['data'] = $news_m->where(array("cate_pid"=>$top['id']))->limit(5)->order("add_time desc")->select();
        //最热
        $hot_list['data'] = $news_m->where(array('is_hot'=>1,"cate_id"=>$cate['id']))->limit(8)->order("add_time desc")->select();
        //最新
        $new_list['data'] = $news_m->where(array("cate_id"=>$cate['id']))->limit(8)->order("add_time desc")->select();

        $this->assign('hot_list',$hot_list);
        $this->assign('new_list',$new_list);
        $this->assign('top_list',$top_list);
        $this->display();
    }
    //百科资讯
    public function baikeNews(){
        $cate_m = M("news_cate");
        $news_m = M("news");

        $cate = $cate_m->field('id,classname')->where(array("sort"=>5,"pid"=>0))->find();
        $res  = $news_m->where(array('cate_id'=>$cate['id']))->order("add_time desc,sort desc")->limit(50)->select();
        // dump($res);exit;
        $this->assign('res',$res);
        // dump($res);die;
        
        //房贷百科
        $res3_cate = $cate_m->field("id,classname")->where(array('sort'=>2,"pid"=>$cate['id']))->find();
        $res3['data'] = $news_m
                ->where(array('is_hot'=>1,'cate_pid'=>$res3_cate['id']))
                ->limit(11)
                ->order("add_time desc,sort desc")
                ->select();
        $res3['classname']  = $res3_cate['classname'];
        $res3['cate_id']    = $res3_cate['id'];
        $this->assign('res3',$res3);
        $this->assign('pid3',$res3_cate['id']);
        //房贷百科
        $res1_cate = $cate_m->field("id,classname")->where(array('sort'=>1,"pid"=>$cate['id']))->find();
        $res1['data'] = $news_m
                ->where(array('is_hot'=>1,'cate_pid'=>$res1_cate['id']))
                ->limit(10)
                ->order("add_time desc,sort desc")
                ->select();
        $res1['classname']  = $res1_cate['classname'];
        $res1['cate_id']    = $res1_cate['id'];
        $this->assign('res1',$res1);
        $this->assign('pid1',$res1_cate['id']);

        //信用卡百科
        $res2_cate = $cate_m->field("id,classname")->where(array('sort'=>3,"pid"=>$cate['id']))->find();
        $res2['data'] = $news_m
                ->where(array('is_hot'=>1,'cate_pid'=>$res2_cate['id']))
                ->limit(4)
                ->order("add_time desc,sort desc")
                ->select();
        $res2['classname'] = $res2_cate['classname'];
        $res2['cate_id']   = $res2_cate['id'];
        $this->assign('res2',$res2);
        $this->assign('pid2',$res2_cate['id']);
        $this->display();

    }
    //信用生活资讯
     public function creditedNews(){
        $cate_m = M("news_cate");
        $news_m = M("news");

        $cate = $cate_m->field('id,classname')->where(array("sort"=>6,"pid"=>0))->find();
        $res  = $news_m->where(array('cate_id'=>$cate['id']))->order("add_time desc,sort desc")->limit(50)->select();
        // dump($res);exit;
        $this->assign('res',$res);
        // dump($res);die;

        //芝麻信用
        $res1_cate = $cate_m->field("id,classname")->where(array('sort'=>1,"pid"=>$cate['id']))->find();
        $res1['data'] = $news_m
                ->where(array('is_hot'=>1,'cate_pid'=>$res1_cate['id']))
                ->limit(10)
                ->order("add_time desc,sort desc")
                ->select();
        $res1['classname']  = $res1_cate['classname'];
        $res1['cate_id']    = $res1_cate['id'];
        $this->assign('res1',$res1);
        $this->assign('pid1',$res1_cate['id']);

        //信用卡
        $res2_cate = $cate_m->field("id,classname")->where(array('sort'=>2,"pid"=>$cate['id']))->find();
        $res2['data'] = $news_m
                ->where(array('is_hot'=>1,'cate_pid'=>$res2_cate['id']))
                ->limit(4)
                ->order("add_time desc,sort desc")
                ->select();
        $res2['classname'] = $res2_cate['classname'];
        $res2['cate_id']   = $res2_cate['id'];
        $this->assign('res2',$res2);
        $this->assign('pid2',$res2_cate['id']);
        $this->display();
    }
    //问答资讯
     public function problemNews(){
        $cate_m = M("news_cate");
        $news_m = M("news");

        $cate = $cate_m->field('id,classname')->where(array("sort"=>7,"pid"=>0))->find();
        $res  = $news_m->where(array('cate_id'=>$cate['id']))->order("add_time desc,sort desc")->limit(50)->select();
        // dump($res);exit;
        $this->assign('res',$res);
        // dump($res);die;

        //个贷
        $res1_cate = $cate_m->field("id,classname")->where(array('sort'=>1,"pid"=>$cate['id']))->find();
        $res1['data'] = $news_m
                ->where(array('is_hot'=>1,'cate_pid'=>$res1_cate['id']))
                ->limit(10)
                ->order("add_time desc,sort desc")
                ->select();
        $res1['classname']  = $res1_cate['classname'];
        $res1['cate_id']    = $res1_cate['id'];
        $this->assign('res1',$res1);
        $this->assign('pid1',$res1_cate['id']);

        //房贷
        $res2_cate = $cate_m->field("id,classname")->where(array('sort'=>2,"pid"=>$cate['id']))->find();
        $res2['data'] = $news_m
                ->where(array('is_hot'=>1,'cate_pid'=>$res2_cate['id']))
                ->limit(3)
                ->order("add_time desc,sort desc")
                ->select();
        $res2['classname'] = $res2_cate['classname'];
        $res2['cate_id']   = $res2_cate['id'];
        $this->assign('pid2',$res2_cate['id']);
        $this->assign('res2',$res2);
         //车贷
        $res3_cate = $cate_m->field("id,classname")->where(array('sort'=>3,"pid"=>$cate['id']))->find();
        $res3['data'] = $news_m
                ->where(array('is_hot'=>1,'cate_pid'=>$res3_cate['id']))
                ->limit(10)
                ->order("add_time desc,sort desc")
                ->select();
        $res3['classname']  = $res3_cate['classname'];
        $res3['cate_id']    = $res3_cate['id'];
        $this->assign('res3',$res3);
        $this->assign('pid3',$res3_cate['id']);
         //信用贷
        $res4_cate = $cate_m->field("id,classname")->where(array('sort'=>4,"pid"=>$cate['id']))->find();
        $res4['data'] = $news_m
                ->where(array('is_hot'=>1,'cate_pid'=>$res4_cate['id']))
                ->limit(10)
                ->order("add_time desc,sort desc")
                ->select();
        $res4['classname']  = $res4_cate['classname'];
        $res4['cate_id']    = $res4_cate['id'];
        $this->assign('res4',$res4);
        $this->assign('pid4',$res4_cate['id']);
        $this->display();
    }
    //理财资讯
    public function licaiNews(){
        $cate_m = M("news_cate");
        $news_m = M("news");

        $cate = $cate_m->field('id,classname')->where(array("sort"=>8,"pid"=>0))->find();
        $res  = $news_m->where(array('cate_id'=>$cate['id']))->order("add_time desc,sort desc")->limit(50)->select();
        // dump($res);exit;
        $this->assign('res',$res);
        // dump($res);die;

        //攻略
        $res1_cate = $cate_m->field("id,classname")->where(array('sort'=>1,"pid"=>$cate['id']))->find();
        $res1['data'] = $news_m
                ->where(array('is_hot'=>1,'cate_pid'=>$res1_cate['id']))
                ->limit(10)
                ->order("add_time desc,sort desc")
                ->select();
				
        $res1['classname']  = $res1_cate['classname'];
        $res1['cate_id']    = $res1_cate['id'];


        $this->assign('res1',$res1);
        $this->assign('pid1',$res1_cate['id']);

        //平台
        $res2_cate = $cate_m->field("id,classname")->where(array('sort'=>2,"pid"=>$cate['id']))->find();
        $res2['data'] = $news_m
                ->where(array('is_hot'=>1,'cate_pid'=>$res2_cate['id']))
                ->limit(4)
                ->order("add_time desc,sort desc")
                ->select();
        $res2['classname'] = $res2_cate['classname'];
        $res2['cate_id']   = $res2_cate['id'];
        $this->assign('pid2',$res2_cate['id']);
        $this->assign('res2',$res2);
		
		//产品
        $res3_cate = $cate_m->field("id,classname")->where(array('sort'=>3,"pid"=>$cate['id']))->find();
        $res3['data'] = $news_m
                ->where(array('is_hot'=>1,'cate_pid'=>$res3_cate['id']))
                ->limit(11)
                ->order("add_time desc,sort desc")
                ->select();
        $res3['classname'] = $res3_cate['classname'];
        $res3['cate_id']   = $res3_cate['id'];
        $this->assign('pid3',$res3_cate['id']);
        $this->assign('res3',$res3);
        $this->display();
    }


     //信用卡攻略
    public function raiders(){

        $this->display();
    }

	//信用生活
	public function xinyong(){
		
		
		$this->display();
		
	}
	
	

	
	
	//理财
	public function licai(){
		$cate_m = M("news_cate");
        $news_m = M("news");

		//找到顶级分类   理财
        $cate = $cate_m->field('id,classname')->where(array("sort"=>8,"pid"=>0))->find();
		//取出所有数据
        $res  = $news_m->where(array('cate_pid'=>$cate['id']))->order("add_time desc,sort desc")->limit(50)->select();
        $this->assign('res',$res);
        // dump($res);die;

        //取出二级分类 	攻略
        $res1_cate = $cate_m->field("id,classname")->where(array('sort'=>1,"pid"=>$cate['id']))->find();
        $res1['data'] = $news_m
                ->where(array('is_hot'=>1,'cate_id'=>$res1_cate['id']))
                ->limit(10)
                ->order("add_time desc,sort desc")
                ->select();
        $res1['classname']  = $res1_cate['classname'];
        $res1['cate_id']    = $res1_cate['id'];
        $this->assign('res1',$res1);

        //平台
        $res2_cate = $cate_m->field("id,classname")->where(array('sort'=>2,"pid"=>$cate['id']))->find();
        $res2['data'] = $news_m
                ->where(array('is_hot'=>1,'cate_id'=>$res2_cate['id']))
                ->limit(4)
                ->order("add_time desc,sort desc")
                ->select();
        $res2['classname'] = $res2_cate['classname'];
        $res2['cate_id']   = $res2_cate['id'];
        $this->assign('res2',$res2);
		
		//产品
		$res3_cate = $cate_m->field("id,classname")->where(array('sort'=>3,"pid"=>$cate['id']))->find();
        $res3['data'] = $news_m
                ->where(array('is_hot'=>1,'cate_id'=>$res3_cate['id']))
                ->limit(10)
                ->order("add_time desc,sort desc")
                ->select();
        $res3['classname']  = $res3_cate['classname'];
        $res3['cate_id']    = $res3_cate['id'];
        $this->assign('res3',$res3);
		
		
        $this->display();
	}
	
	
	
    //更多列表
    public function newsList(){
        $User = M('News'); // 实例化User对象
		if(I('get.pid')){
			$map['cate_id'] = I('get.pid');
			$cate=M('NewsCate')->find($map['cate_id']);
			$classname = $cate['classname'];
		}else{
			$map['cate_pid'] = I('get.id');
			$cate=M('NewsCate')->find($map['cate_pid']);
			$classname = $cate['classname'];
		}
        
        $count      = $User->where($map)->count();// 查询满足要求的总记录数
        $Page = getpage($count,14);
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $newsList = $User->where($map)->order('sort asc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('classname',$classname);
        $this->assign('newslist',$newsList);
        $this->assign('page',$show);// 赋值分页输出
		$res = M('SeoView')->where(array('controller'=>'News','function'=>'newsList'))->find();
		$title = str_replace('$title',$classname,$res['title']);
		$this->assign('title',$title);
        $this->display();
    }
	
	
	//首页-贷款攻略详情
    public function creditdetail(){
        $id = I('get.id');

        $news_forward = M('strategy')->where(array('id'=>array('lt',$id)))->order('id DESC')->limit(1)->find();//上一篇

        $news = M('strategy')->where(array('id'=>$id))->find();//当前这
	
        $news_next = M('strategy')->where(array('id'=>array('gt',$id)))->order('id ASC')->limit(1)->find();//下一篇
        //相关资讯
        $newslist = M('strategy')->where('id >'.$id)->where(array('is_show'=>0))->limit(0,5)->select();
		$news_pid = $newslist[0]['cate_pid'];
        //今日头条
        $today = M('strategy')->where(array('cate_name'=>'今日头条'))->order('sort asc')->limit(6)->select();
		$pid2 = $today[0]['cate_id'];
        //热门资讯
        $hot = M('strategy')->where(array('is_hot'=>1))->order('sort asc')->limit(3)->select();
		$pid3 = $hot[0]['cate_id'];
		$this->assign('pid1',$news_pid);
		$this->assign('id1',$pid2);
		$this->assign('id2',$pid3);
        $this->assign('hot',$hot);
        $this->assign('today',$today);
        $this->assign('newslist',$newslist);
        $this->assign('news',$news);
        $this->assign('news_forward',$news_forward);
        $this->assign('news_next',$news_next);
        //dump($news);
        $this->display();
    }

	
    //贷款攻略详情
    public function newsDot1(){
        $id = I('get.id');

        $news_forward = M('news')->where(array('id'=>array('lt',$id)))->order('id DESC')->limit(1)->find();//上一篇

        $news = M('news')->where(array('id'=>$id))->find();//当前这
		
        $news_next = M('news')->where(array('id'=>array('gt',$id)))->order('id ASC')->limit(1)->find();//下一篇
        //相关资讯
        $newslist = M('news')->where(array('is_show'=>0))->limit(0,5)->select();
		$news_pid = $newslist[0]['cate_id'];
        //今日头条
        $today = M('news')->where(array('cate_name'=>'今日头条'))->order('sort asc')->limit(6)->select();
		$pid2 = $today[0]['cate_id'];
        //热门资讯
        $hot = M('news')->where(array('is_hot'=>1))->order('sort asc')->limit(3)->select();
		$pid3 = $hot[0]['cate_id'];

		/***************************************************/
		$res = M('SeoView')->where(array('controller'=>'News','function'=>'newsDot1'))->find();		
		$title = str_replace('$title',$news['news_title'],$res['title']);
		$title = str_replace('$cate',$news['cate_name'],$title);
		$keywords = str_replace('$title',$news['news_title'],$res['keywords']);
		$keywords = str_replace('$cate',$news['cate_name'],$keywords);

		$des = mb_substr(strip_tags(htmlspecialchars_decode($news['detail'])),0,40,'utf-8');
		$this->assign('title',$title);
		$this->assign('keywords',$keywords);
		$this->assign('des',$des);
		/***************************************************/
		
		$this->assign('pid1',$news_pid);
		$this->assign('id1',$pid2);
		$this->assign('id2',$pid3);
        $this->assign('hot',$hot);
        $this->assign('today',$today);
        $this->assign('newslist',$newslist);
        $this->assign('news',$news);
        $this->assign('news_forward',$news_forward);
        $this->assign('news_next',$news_next);
        //dump($news);
        $this->display();
    }
}

?>