<?php
namespace Home\Controller;
use Think\Controller;
class HouseLoanController extends LoanController{

    public function _initialize(){
        parent::_initialize();
        //查询城市贷款  6
    }

    public function index(){
        //重新搜索
        $money = I('get.money');  //接受金额
        $date = I('get.date');  //接受期限
        $is_organ = I('get.is_organ');
        if($money){
            //查询区间
            $money_limit = M("search_config")->where(array("id"=>$money,"type"=>1))->find();
            $where["money"] = ["gt",$money_limit["start"]];
            $where["money1"] = ["lt",$money_limit["end"]];
        }
        if($date){
            //查询区间
            $date_limit = M("search_config")->where(array("id"=>$money,"type"=>2))->find();
            $where["dkqx"] = ["gt",$date_limit["start"]];
            $where["dkqx1"] = ["lt",$date_limit["end"]];
        }
        //查询搜索配置
        $money = M("search_config")->where(array("type"=>1))->select();
        $date = M("search_config")->where(array("type"=>2))->select();
        $this->assign('money',$money);
        $this->assign('date',$date);
        
        $where['cate_id'] = $this->lontypeArr['house'];
        $where['is_del']  = 0;
        $where['is_sale'] = 1;
        if($this->city){
            $where['city'] = array('in',array($this->city,'0'));
        }
        
        
        $count = $this->Loan_db->where($where)->count();
        $page  = getpage($count,5);
        $show  = $page->show();

        if($is_organ == 1){
            $where['organid'] = ['like',"%45%"];
        }

        $listsloanArr = $this->Loan_db->where($where)->order('sorts asc,id desc')->limit($page->firstRow.','.$page->listRows)->select(); // dump($lists);


        $shouxian = M('Cate')->where(array('pid'=>26))->select();
        $lilv = M('Cate')->where(array('pid'=>27))->select();
        $this->assign('shouxian',$shouxian);
        $this->assign('lilv',$lilv);
        
        $this->assign('count',$count);
        $this->assign('listsloanArr',$listsloanArr);
        $this->assign('page',$show);
        
        
        $hot = M('Problemss')->order('browse desc')->limit('7')->select();//热门话题
        $this->assign('hot',$hot);
        
        $article = M('news');
        $map['cate_name'] = '房贷报告';
        $artList1 = $article->where($map)->order('sort asc')->limit(1)->select();
        $artList = $article->where($map)->order('sort asc')->limit(1,7)->select();
        $this->assign('artlist',$artList);
        $this->assign('artlist1',$artList1);
        $l_o = M('loan_order');
		$l_o_r = $l_o ->where(array('cate_id'=>2))-> order('id desc')->limit(3)->select();
        foreach($l_o_r as $k => $v){
            $p = unserialize($v['orderlon']);
            $l_o_r[$k]['p_title'] =$p['title'];
            $l_o_r[$k]['cate_id'] =$p['cate_id'];
        }
        $this->assign('l_o_r',$l_o_r);

        $houser = M('house')->select();
        $this->assign('houser',$houser);
        $this->assign('a_b_zy',$this->advBanner(8));  //右中
        $this->display();
    }
        

     public function Newsdot(){
        $id = trim(I('id'));
        // $news = M('Article')->find($id);
        // $res = M('SeoView')->where(array('controller'=>'PersonLoan','function'=>'Newsdot'))->find();
        // $title = str_replace('$title',$news['title'],$res['title']);
        // $this->assign('title',$title);
        
        if(!$id){
            $this->error('文章不存在');
        }
        // $this->findArticle($id);
        $articlefind = M("newsgonglue")->where(array('id'=>$id))->find();
        $uponarticlefind = M("newsgonglue")->where(array('cate_id'=>$articlefind['cate_id'],'id'=>array('lt',$id)))->order('id desc')->find();
        $downarticlefind = M("newsgonglue")->where(array('cate_id'=>$articlefind['cate_id'],'id'=>array('gt',$id)))->order('id asc')->find();
        
        $this->assign('articlefind',$articlefind);
        $this->assign('uponarticlefind',$uponarticlefind);
        $this->assign('downarticlefind',$downarticlefind);
        
        
        //相关资讯
        $newslist = M('news')->where(array('is_show'=>0))->limit(0,5)->select();
        $news_pid = $newslist[0]['cate_id'];
        //今日头条
        $today = M('news')->where(array('cate_name'=>'今日头条'))->order('sort asc')->limit(6)->select();
        $pid2 = $today[0]['cate_id'];
        //热门资讯
        $hot = M('news')->where(array('is_hot'=>1))->order('sort asc')->limit(3)->select();
        $pid3 = $hot[0]['cate_id'];
        
        $this->assign('newslist',$newslist);
        $this->assign('pid1',$news_pid);
        $this->assign('today',$today);
        $this->assign('id1',$pid2);
        $this->assign('hot',$hot);
        $this->assign('id2',$pid3);
        $this->display("PersonLoan/Newsdot");

    }


    //攻略卡
    public function gonglue(){
        $i = M('newsgonglue');
        // $res1 = $i->where(array('is_sale'=>1,'type'=>1))->order('sort desc')->limit(1)->find();//大图
        // $res2 = $i->where(array('is_sale'=>1,'type'=>1))->limit(1,4)->order('id asc')->select();//小图
        // $res3 = $i->where(array('is_sale'=>1,'type'=>1))->limit(5,3)->order('id asc')->select();//中图

       
        $count = $i->where(array("cate_name"=>"房贷攻略"))->count();
        $Page = getpage($count, 12);
        $show = $Page->show();//分页显示输出
        $res4 = $i->where(array("cate_name"=>"房贷攻略"))->limit($Page->firstRow . ',' . $Page->listRows)->order('sort asc')->select();

        
        // //seo设置
        // $seo = M('SeoView')->where(array('controller'=>'personload','function'=>'gonglue'))->find();
        // $title = str_replace('$title','房贷攻略',$seo['title']);
        // $this->assign('title',$title);
        $this->assign('res4',$res4);
        $this->assign("page", $show);
        // dump($res1);

        // $this->assign('res1',$res1);
        // $this->assign('res2',$res2);
        // $this->assign('res3',$res3);
        $this->display();
    }

    public function houseDetail(){
        $id = I('id');
        if(!$id){
            $this->error('访问出错');
        }
        $url        =   strstr(get_url(),'.html',TRUE).'/fenxiao_id/'. $this->fenxiao_id;
        //dump($url);
        $this->assign('url',$url);
        //获取小贷公司id
        $supplier_id = M('Loan')->field('Supplier_id')->find($id);
        if($supplier_id['supplier_id']){
            $small = M('Xiaodai')->find($supplier_id['supplier_id']);
            $this->assign('small',$small);
        }
        $guwen = M('LoanSq')->where(array('id'=>$id))->limit(0,4)->select();
        $this->assign('sq',$guwen);
        $res = M('SeoView')->where(array('controller'=>'HouseLoan','function'=>'HouseDetail'))->find();
        $this->showDetailLoan($id,$res);
        $article = M('news');
        $map['cate_name'] = '今日头条';
        $artList = $article->where($map)->order('sort asc')->limit(0,8)->select();
        $this->assign('artlist',$artList);

        $map['cate_name'] = '贷款攻略';
        $artList2 = $article->where($map)->order('sort asc')->limit(0,8)->select();
        $this->assign('artlist2',$artList2);
        $hot = M('Problemss')->order('browse desc')->order('id desc')->limit('5')->select();//热门话题
        $this->assign('hot',$hot);
        $hot1 = M('Problemss')->order('browse desc')->order('id desc')->limit('8')->select();//热门话题
        $this->assign('hot1',$hot1);
        $l_o = M('loan_order');
        $l_o_r = $l_o -> order('id desc')->limit(3)->select();
        foreach($l_o_r as $k => $v){
            $p = unserialize($v['orderlon']);
            $l_o_r[$k]['p_title'] =$p['title'];
            $l_o_r[$k]['cate_id'] =$p['cate_id'];
        }
        $this->assign('l_o_r',$l_o_r);
        $id = I('get.id');
        $data['loan_id'] = $id;
        $lists = M('LoanView')->where($data)->order('id desc')->select();
        $this->assign('lists',$lists);
        //热门产品
        $hot = $this->selectCityLon('house',$identityid='',$houseid='',$carid='',$honourid='',$city='',$limit=8,$is_sale=1,$is_del=0);
        $this->assign('hot',$hot[1]);  //dump($hot);
        $this->display();
    }
    
    public function serch(){
        $where = array();
        $identityid = I('identityid');
        $houseid    = I('houseid');
        $carid      = I('carid');
        $honourid   = I('honourid');
        $organ      = I('organ');
        $pledge     = I('pledge');
        $repay      = I('repay');
        $is_organ = I("post.is_organ");
        $lists = $this->selectCityLon('house',$identityid,$houseid,$carid,$honourid,$this->city,$organ,$pledge,$repay,5,$is_organ);  //查询显示产品
        $html = '';
        if($lists[0])
        {
            foreach($lists[1] as $key=>$val){
                if($key==0){
                    $html .='<div class="gedai2_list gedai2_list_first">';
                }else{
                    $html .='<div class="gedai2_list">';
                }
                
                $html.='<div class="gedai2_wz">
                <div class="gedai2_list_left">';
                    if($val['is_renqi'] == 1){
                        $html .='<div class="gedai2_list_type"> <span>人气</span><div class="gedai2_type_bg"></div></div>';
                    }
                    $html .='<div class="gedai2_list_img">
                        <a href="'.U('/Home/HouseLoan/houseDetail',array('id'=>$val['id'])).'">
                            <img src="'.$val['logo_pic1'].'" />
                        </a>
                    </div>
                </div>
                <div class="gedai2_list_right">
                    <div class="gedai2_list_top"> <a href="'.U('/Home/HouseLoan/houseDetail',array('id'=>$val['id'])).'">'.$val['title'].'</a>
                        <ul class="gedai2_top_list">';
                            if($val['is_diya'] > 0){
                                $html .='<li><span>无需抵押</span></li>';
                            }
                            if($val['is_sbzksq'] > 0){
                                $html .='<li><span>上班族 可申请</span></li>';
                            }
                            if($val['fksj'] > 0){
                                $html .='<li><span>'.$val['fksj'].'天放款</span></li>';
                            }
                            $html .='<div class="clear"></div>
                        </ul>
                        <div class="clear"></div>
                    </div>
                    <div class="gedai2_list_bottom">
                        <div class="gedai2_bottom_left">
                            <h4>速度快 额度高</h4>
                            <ul>';
                            if($val['is_sbzksq']){
                                $html .='<li><span class="person"></span> 上班族可申请</li>';
                            }
                            if($val['fksj']){
                                $html .='<li><span class="time"></span> '.$val['fksj'].'天放款</li>';
                            }
                            $html .='</ul>
                        </div>
                        <div class="gedai2_bottom_center">
                            <dl>
                                <dt>贷款金额： </dt>
                                <dd><span class="num_red">'.$val['money'].'~'.$val['money1'].'</span> 万</dd>
                            </dl>
                            <dl>
                                <dt>月管理费： </dt>
                                <dd><span class="num_red">'.$val['lnsm'].'</span> %</dd>
                            </dl>
                        </div>
                        <div class="gedai2_bottom_right">
                            <div class="gedai2_bottom_table">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td>'.htmlspecialchars_decode($val['yaoqiu']).'</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="gedai2_more"> <a href="'.U('/Home/HouseLoan/houseDetail',array('id'=>$val['id'])).'">查看详情</a> </div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    </div>
                    <div class="clear"></div>
                </div>
                </div>';
            }
        }else{
            $html = '<div class="zanwu_box">
                        <div class="ind_table">
                            <div class="ind_cell">
                                <div class="zanwu">
                                    <img src="/Public/Home/images/zanwu.png" />
                                    <h4>暂无内容</h4>
                                </div>
                            </div>
                        </div>
                    </div>';
        }
        if($lists[2]){
            $lists[2] = str_replace("/serch/","/index/",$lists[2]);
        }
        $html .='<div class=""><div class="wenda3_page">'.$lists[2].'</div></div>';
        if($lists[0])
        {
            $this->ajaxReturn(array('status'=>1,'info'=>$html));
        }
        $this->ajaxReturn(array('status'=>0,'info'=>$html));
    }


    public function answers(){
        $d_d = array();
        if ($_GET['smwenti']){
            $d_d['a.title'] = array('like', '%'.$_GET['smwenti'].'%');
        }

        $data['reply'] = array('gt',0);
        $answer = M('Problemss')->where($data)->count();//回答量
        $help = M('Problemss')->where(array('is_help'=>1))->count();//帮助量
        $id = I('get.id');
        $problem = M('Problemss')
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
                M('Problemss')->save($data);
                $res = $problem[$key];
                $answerContent = M('Answerss')->where(array('p_id'=>$id))->select();
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
        $User = M('Problemss'); // 实例化User对象
        $count      = $User->where($where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,7);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $User->where($where)->order('add_time')->limit($Page->firstRow.','.$Page->listRows)->select();
            $this->assign('page',$show);// 赋值分页输出
        if(!$titles){
            $titles = '房贷问答';
        }
        $this->assign('title',$titles);
        $this->assign('list',$list);// 赋值数据集
        $this->assign('answerContent',$answerContent);
        $this->assign('res',$res);
        $keyword = M('Keyword')->select();
        $this->assign('keyword',$keyword);

        $hot = M('Problemss')->order('browse desc')->limit('5')->select();//热门话题
        //推荐阅读
        $tuijian = M('News')->where(array('cate_id'=>76))->limit(0,7)->select();
        $this->assign('tuijian',$tuijian);
        //最新话题
        $new = M('Problem')->order('id asc')->limit(0,5)->select();
        $this->assign('new',$new);
        $this->assign('hot',$hot);
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
    public function addAnswer(){
        if(IS_AJAX){
            $data['p_id'] = I('post.id');
            $data['content'] = I('post.content');
            $data['user_id'] = $this->user_id;
            M('Problemss')->where(array('id'=>$data['p_id']))->setInc('reply');
            $res = M('Answerss')->add($data);
            if($res){
                $this->ajaxReturn(array('status'=>1));
            }else{
                $this->ajaxReturn(array('status'=>0, 'info'=>'发生未知错误，请联系管理员！'));
            }
        }
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
            $res = M('Problemss')->add($data);
            if($res){
                $this->ajaxReturn(array('status'=>1, 'info'=>'提问成功！'));
            }else{
                $this->ajaxReturn(array('status'=>0, 'info'=>'提问失败！'));
            }
        }
    }
    //关键字搜索
    public function search(){
        if(IS_AJAX){
            $title = I('post.content');
            $sql['title'] = array('like','%'.$title.'%');
            $res = M('Problemss')->where($sql)->select();
            if($res){
                $this->ajaxReturn(array('status'=>1, 'info'=>$res));
            }else{
                $this->ajaxReturn(array('status'=>0, 'info'=>'提问失败！'));
            }
        }
    }
}

?>