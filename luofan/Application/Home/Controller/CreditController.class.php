<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;
header("Content-type: text/html; charset=utf-8");
class CreditController extends PublicController{
    public function index(){

        $this->display();
    }

    //选卡中心 ljj
    public function creditList(){
        $id = $_GET['id'];
        $city = $this->city;
        //接收银行的缩写,查询card_type的id
		$flag = false;
        $shortname = I("get.shortname");

        if($shortname){
            $card_type = M("card_type")->where(array("shortname"=>$shortname))->find();
			$this->assign("title",$card_type['classname']);
			$flag = true;
        }
            $c_t = M('card_type');
            $c_t_r = $c_t->where(array('isdel'=>0,'pid'=>0))->order('sort desc')->select();

            foreach ($c_t_r as $k => $v){
                $c_t_r_r = $c_t->where(array('isdel'=>0,'pid'=>$v['id']))->order('sort desc')->select();
                $c_t_r[$k]['r_r'] = $c_t_r_r;
                if($flag == true){
                    if($v['id'] == 15){
                        unset($c_t_r[$k]);
                    }
                }
            }
            //dump($c_t_r);
            $c_c = M('Creditcard');
            $res = $c_c->where(array('is_sale'=>0))->order("add_time desc")->select();
            $count = count($res);

        foreach ($res as $k => $v){
            $arr = unserialize($v['card_type']);
            foreach( $arr as $kk=>$vv){
                if(!$vv) {
                    unset($arr[$kk]);
                }
            }
            $res[$k]['data_category']= implode(', ',$arr);
            if($card_type) {
               if($card_type["id"] != $arr[15]){
				   unset($res[$k]);
			   }
                $count = count($res);
            }

        }
            $cardlist=M("creditcard")->where(array("is_recommend"=>0))->limit("0,4")->select();
            $this->assign("cardlist",$cardlist);
            $page  = getpage($count,10);
            $show  = $page->show();
            //$res = $c_c->where(array('is_sale'=>0))->order('sort desc')->limit($page->firstRow.','.$page->listRows)->select();
            // $res = $c_c ->where(array('is_sale'=>0))->order('sort desc')->select();
            $res = array_slice($res,$page->firstRow,$page->listRows);
			
            $this->assign('page',$show);
            $this->assign('c_t_r',$c_t_r);
            $this->assign('res',$res);
            $this->assign('count',count($res));
//        dump($res);
        $this->display();
    }
    public function card_dot(){
        $id = $_GET['id'];
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
        if(!$cc_res){
            $this->error('没有此信用卡');
        }
        $news_m = M("news");
        $cate_m = M("news_cate");
        $cate = $cate_m->field('id,classname')->where(array("sort"=>3,"pid"=>0))->find();
        $hot_list['cate_id'] = $cate['id'];
        $new_list['cate_id'] = $cate['id'];
        //最热
        $hot_list['data'] = $news_m->where(array('is_hot'=>1,"cate_id"=>$cate['id']))->limit(8)->order("add_time desc")->select();
        //最新
        // $new_list['data'] = $news_m->where(array("cate_id"=>$cate['id']))->limit(7)->order("add_time desc")->select();
        $new_list['data'] = M('strategy')->where(array("type"=>1))->limit(7)->order("add_time desc")->select();
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
        $this->display();
    }

    /**
     * lin ajax 动态创建页面 信用卡类型 目前不确定用那种
     */
    public function creation(){
        if(IS_AJAX){
            $card_type = I('post.card_type_ids');
            $bank = I('post.bank');
            //查询银行id
            $bank_id = M("card_type")->where(array("shortname"=>$bank))->getField("id");
            $card_type[15] = $bank_id;
            $a = $card_type;
//            var_dump($card_type);
//            echo  '<br>';
            $c_c = M('Creditcard');
            $city = $this->city;
            $res = $c_c ->where(array('is_sale'=>0))->order('sort desc')->select();
//            var_dump($res);
            $new = array();
            foreach ($res as $k => $v){
                $b = unserialize($v['card_type']);
//                $res[$k]['data_category']= implode(', ',$b);
                $v['data_category'] = implode(', ',$b);
                if ($a == array_intersect($a, $b)) {
                    $new[]=$v;
                }
//                
//                $res[$k]['card_type_arr'] = unserialize($v['card_type']);
            }
//            echo '<pre>';
//            print_r($new);
//            echo '</pre>';
            $html = '';
            foreach($new as $kk => $vo){
                $html .= '<div class="filtr-item" data-category="'.$vo['data_category'].'">';
                $html .= '<div class="chedai3_list chedai3_list1">';
                $html .= '<div class="chedai3_wz">';
                $html .= '<div class="chedai3_list_left chedai3_list_left1">';
                $html .= '<div class="chedai3_list_img1">';
                $html .= '<a href="'.U('Home/Credit/card_dot',array('id'=>$vo['id'])).'">';
                $html .= '<img src="'.$vo['logo_img'].'" >';
                $html .= '</a>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '<div class="chedai3_list_right chedai3_list_right1">';
                $html .= '<div class="chedai3_list_bottom">';
                $html .= '<div class="chedai3_bottom_left">';
                $html .= '<h5>'.$vo['creditname'].'</h5><h4>'.$vo['intro'].'</h4></div><div class="chedai3_bottom_center"><dl><dt>系列等级：'.$vo['series'].'</dt><dd></dd></dl><dl><dt>系列币种：'.$vo['currency'].'</dt>';
                $html .= '<dd></dd></dl><dl><dt>取现费用：'.$vo['fee'].'</dt><dd></dd></dl><dl><dt>年费政策：'.$vo['annual_fee'].'</dt><dd></dd></dl></div>';
                $html .= '<div class="chedai3_more chedai3_more1"><a href="'.U('Home/Credit/card_dot',array('id'=>$vo['id'])).'">免费申请</a><h4><span>'.$vo['apply'].'</span>人申请</h4></div>';
                $html .= '<div class="clear"></div></div></div><div class="clear"></div></div><div class="credit_xz">';
                $html .= '<div class="chedai3_list_left chedai3_list_left1"><div class="chedai3_list_img1"><a href="'.U('Home/Credit/card_dot',array('id'=>$vo['id'])).'">';
                $html .= '<img src="'.$vo['logo_img'].'" ></a></div>';
                $html .= '</div><div class="chedai3_list_right chedai3_list_right1"><div class="chedai3_list_bottom">';
                $html .= '<div class="chedai3_bottom_left"><h5>'.$vo['creditname'].'</h5><h4>'.$vo['intro'].'</h4></div>';
                $html .= '<div class="chedai3_bottom_center"><dl><dt>系列等级：'.$vo['series'].'</dt><dd></dd></dl>';
                $html .= '<dl><dt>系列币种：'.$vo['currency'].'</dt><dd></dd></dl>';
                $html .= '<dl><dt>取现费用：'.$vo['fee'].'</dt><dd></dd></dl>';
                $html .= '<dl><dt>年费政策：'.$vo['annual_fee'].'</dt><dd></dd></dl>';
                $html .= '</div><div class="clear"></div></div></div>';
                $html .= '<div class="clear"></div><div class="credit_xia"><div class="credit_small">';
                $html .= '<a href="'.U('Home/Credit/card_dot',array('id'=>$vo['id'])).'"><img src="'.$vo['logo_img'].'" width="80"></a>';
                $html .= '<a href="'.U('Home/Credit/card_dot',array('id'=>$vo['id'])).'"><img src="'.$vo['logo_img'].'" width="80"></a>';
                $html .= '</div><div class="chedai3_more chedai3_more1 chedai3_more2">';
                $html .= '<a href="'.U('Home/Credit/card_dot',array('id'=>$vo['id'])).'">免费申请</a><h4><span>'.$vo['apply'].'</span>人申请</h4>';
                $html .= '</div><div class="clear"></div></div></div></div></div>';
            }
            $html.='<div class="clear"></div>';
            $this->ajaxReturn(array('status'=>1,'info'=>$html,'count'=>count($new)));
        }
    }
    //信用卡优惠
    public function discount(){
        //置顶
        $f = M('favor');
        $f_res_h = $f->where(array('is_sale'=>1,'is_hot'=>1))->limit(4)->order('sort desc')->select();
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
        $this->assign('c_t_res',$c_t_res);
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

    //信用卡问答
    public function answer(){
        $d_d = array();
	    if ($_GET['smwenti']){
	        $d_d['a.title'] = array('like', '%'.$_GET['smwenti'].'%');
        }

        $data['reply'] = array('gt',0);
        $answer = M('Problem')->where($data)->count();//回答量
        $help = M('Problem')->where(array('is_help'=>1))->count();//帮助量
        $id = I('get.id');
        $problem = M('Problem')
            ->alias('a')
            ->join('LEFT JOIN __MEMBER__ b on a.user_id=b.id')
            ->field('a.id,a.title,a.add_time,a.user_id,a.browse,a.reply,a.is_help,b.person_name')
            ->where($d_d)
            ->order('add_time desc')
            ->select();
        $num = '';
        //问题详情
        foreach($problem as $key=>$val){
            $num++;
            if($val['id'] == $id){
                $data['browse'] = $val['browse']+1;
                $data['id'] = $id;
                M('Problem')->save($data);
                $res = $problem[$key];
                $answerContent = M('Answer')->where(array('p_id'=>$id))->select();
				$title = $res['title'];
				$seo = M('SeoView')->where(array('controller'=>'Credit','function'=>'gonglue1'))->find();
				$titles = str_replace('$title',$title,$seo['title']);
				
//                $this->assign('answerContent',$answerContent);
//                $this->assign('res',$res);
            }
            //时间
            foreach($val as $k=>$v){
                if($k == 'add_time'){
                    $content[$num]['stamptime'] = $v;
                    $content[$num][$k] = $this->wordTime($v);
                }else{
                    $content[$num][$k] = $v;
                }
            }
        }
		$sql = I('get.sql');
		if($sql == 1){
			$where = array('is_show'=>1);
		}else if($sql == 2){
			$where = array('is_show'=>1,'reply'=>'');
		}else if($sql == 3){
			$where['is_show'] = array('eq',1);
			$where['reply'] = array('gt',0);
		}else{
			$where = array('is_show'=>1);
		}
		$User = M('Problem'); // 实例化User对象
        $count      = $User->where($where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,7);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $User->where($where)->order('add_time')->limit($Page->firstRow.','.$Page->listRows)->select();

			$this->assign('page',$show);// 赋值分页输出
		if(!$titles){
			$titles = '信用卡问答';
		}
		$this->assign('title',$titles);
        $this->assign('list',$list);// 赋值数据集
        $this->assign('answerContent',$answerContent);
        $this->assign('res',$res);
        $keyword = M('Keyword')->select();
        $this->assign('keyword',$keyword);
		//推荐阅读
		$tuijian = M('News')->where(array('cate_id'=>76))->limit(0,7)->select();
		$this->assign('tuijian',$tuijian);
		//热门话题
        $hot = M('Problem')->order('browse desc')->limit('5')->select();
        $this->assign('hot',$hot);
		//最新话题
		$new = M('Problem')->order('id asc')->limit(0,5)->select();
		$this->assign('new',$new);
        $this->assign('answer',$answer);//回答量
        $this->assign('help',$help);//帮助人数
        $this->assign('problem',$content);
        $n = M('new_user');
        $n_res = $n->where(array('is_sale'=>1))->order('sort desc')->limit(10)->select();
        $this->assign('n_res',$n_res);
        $this->display();
    }
    public function wordTime($time) {
        $time = (int) substr($time, 0, 10);
        $int = time() - $time;
        $str = '';
        if ($int <= 2){
            $str = sprintf('刚刚', $int);
        }elseif ($int < 60){
            $str = sprintf('%d秒前', $int);
        }elseif ($int < 3600){
            $str = sprintf('%d分钟前', floor($int / 60));
        }elseif ($int < 86400){
            $str = sprintf('%d小时前', floor($int / 3600));
        }elseif ($int < 2592000){
            $str = sprintf('%d天前', floor($int / 86400));
        }else{
            $str = date('Y-m-d H:i:s', $time);
        }
        return $str;
    }
    //创建问答
    public function addProblem(){
        if(IS_AJAX){
            $data['title'] = I('post.title');
            $data['add_time'] = time();
            $user_id = session('user_id');
            if(!$user_id){
                $this->ajaxReturn(array('status'=>0, 'info'=>'请先登录！'));
            }else{
                $data['user_id'] = $user_id;
            }
            $res = M('Problem')->add($data);
            if($res){
                $this->ajaxReturn(array('status'=>1, 'info'=>'提问成功！'));
            }else{
                $this->ajaxReturn(array('status'=>0, 'info'=>'提问失败！'));
            }
        }
    }
    public function addAnswer(){
        if(IS_AJAX){
            //验证是否登录
            $user_id = $_SESSION['user_id'];
            if(empty($user_id)){
                $this->ajaxReturn(array('status'=>0,'info'=>"请登录"));
            }
            $data['p_id'] = I('post.id');
            $data['content'] = I('post.content');
			$data['user_id'] = $this->user_id;
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
    //问题内容
    public function answerContent(){
       
    }
    //添加申请数
    public function apply_add(){
        if(IS_AJAX){
            $id = I('post.id');
            $card = I('post.card');
            $c_c = M('creditcard');
            $back = M('back');
            $res = $c_c ->where(array('id'=>$id))->find();
            $apply['apply'] = $res['apply']+1;
            $c_c->where(array('id'=>$id))->save($apply);
            $c_res = $back->where(array('id'=>$card))->find();
            $this->ajaxReturn(array('status'=>1, 'info'=>'成功','url'=>$c_res['apply']));
        }else{
            $this->ajaxReturn(array('status'=>0, 'info'=>'错误请求'));
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
            $where=array();
            if($type!=null){
                $where['type']=$type;
            }
            $res_s = $i->where(array('is_sale'=>1,'id'=>array('lt',$id)))->where($where)->order('id DESC')->limit(1)->field('id,news_title,type')->find();//上一个
            $res = $i->where(array('is_sale'=>1,'id'=>$id))->where($where)->find();//当前这个
            $res_x = $i->where(array('is_sale'=>1,'id'=>array('gt',$id)))->where($where)->order('id ASC')->limit(1)->field('id,news_title,type')->find();//下一个
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
    //攻略卡
    public function gonglue(){
        $i = M('strategy');
        $res1 = $i->where(array('is_sale'=>1,'type'=>1))->order('sort desc')->limit(1)->find();//大图
        $res2 = $i->where(array('is_sale'=>1,'type'=>1))->limit(1,4)->order('id asc')->select();//小图
        $res3 = $i->where(array('is_sale'=>1,'type'=>1))->limit(5,3)->order('id asc')->select();//中图

       
        $count = $i->where(array('is_sale'=>1,'type'=>1))->count();
        $Page = getpage($count, 12);
        $show = $Page->show();//分页显示输出
        $res4 = $i->where(array('is_sale'=>1,'type'=>1))->limit($Page->firstRow . ',' . $Page->listRows)->order('id asc')->select();

		
		//seo设置
		$seo = M('SeoView')->where(array('controller'=>'Credit','function'=>'gonglue'))->find();
		$title = str_replace('$title','信用卡攻略',$seo['title']);
		$this->assign('title',$title);
        $this->assign('res4',$res4);
        $this->assign("page", $show);
        // dump($res1);

        $this->assign('res1',$res1);
        $this->assign('res2',$res2);
        $this->assign('res3',$res3);
        $this->display();
    }

    //攻略卡 type=2
    public function gonglue1(){
        $type = I('get.type');
        $types = I('get.types');
        // $id = I('get.id');

        if ($types == 1) {
            $i = M('integral');
            $count = $i->where(array('is_sale'=>1))->count();
            $Page = getpage($count, 12);
            $show = $Page->show();//分页显示输出
            $res4 = $i->where(array('is_sale'=>1))->limit($Page->firstRow . ',' . $Page->listRows)->order('id asc')->select();
        }elseif ($types == 2) {
            $i = M('favor');
            $count = $i->where(array('is_sale'=>1,'type'=>$type))->count();
            $Page = getpage($count, 12);
            $show = $Page->show();//分页显示输出
            $res4 = $i->where(array('is_sale'=>1,'type'=>$type))->limit($Page->firstRow . ',' . $Page->listRows)->order('id asc')->select();
        }elseif ($types == 3) {
            $i = M('new_user');
            $count = $i->where(array('is_sale'=>1))->count();
            $Page = getpage($count, 12);
            $show = $Page->show();//分页显示输出
            $res4 = $i->where(array('is_sale'=>1))->limit($Page->firstRow . ',' . $Page->listRows)->order('id asc')->select();
        }elseif ($types == 4) {
            $i = M('strategy');
            $count = $i->where(array('is_sale'=>1,'type'=>$type))->count();
            $Page = getpage($count, 12);
            $show = $Page->show();//分页显示输出
            $res4 = $i->where(array('is_sale'=>1,'type'=>$type))->limit($Page->firstRow . ',' . $Page->listRows)->order('id asc')->select();
        }
		$title = '';
		switch($types){
			case 1:
				if($type == 1){
					$title = '积分活动';
				}
			break;
			case 2:
				if($type == 1){
					$title = '美食';
				}elseif($type == 2){
					$title = '休闲娱乐';
				}elseif($type == 3){
					$title = '航空酒店旅行';
				}elseif($type == 4){
					$title = '购物';
				}elseif($type == 5){
					$title = '时尚丽人';
				}elseif($type == 6){
					$title = '生活服务';
				}
			break;
			case 3:
					$title = '新户办卡有礼活动';
			case 4:
				if($type == 1){
					$title = '信用卡攻略';
				}elseif($type ==2){
					$title = '信用卡须知';
				}elseif($type ==3){
					$title = '图解信用卡';
				}elseif($type ==4){
					$title = '达人专栏';
				}elseif($type ==5){
					$title = '信用卡薅羊毛';
				}elseif($type ==6){
					$title = '信用卡专题';
				}
			break;
		}
		$seo = M('SeoView')->where(array('controller'=>'Credit','function'=>'gonglue1'))->find();
		$titles = str_replace('$title',$title,$seo['title']);
		$this->assign('title',$titles);
        $this->assign('type',$type);
        $this->assign('types',$types);
        $this->assign('res4',$res4);
        $this->assign("page", $show);
        $this->display();
    }


}

?>