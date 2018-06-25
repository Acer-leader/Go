<?php
namespace Home\Controller;
use Think\Controller;
class CarLoanController extends LoanController{
    public function _initialize(){
        parent::_initialize();
    }
    public function index(){
        //重新搜索
        $money = I('get.money');  //接受金额
        $date  = I('get.date');  //接受期限
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
        
        $where['cate_id'] = $this->lontypeArr['car'];
        $where['is_del']  = 0;
        $where['is_sale'] = 1;
        if($this->city){
            $where['city'] = array('in',array($this->city,'0'));
        }
        
        
        /* 头部 */
        $count = $this->Loan_db->where($where)->count();
        $page  = getpage($count,5);
        $show  = $page->show();
        if($is_organ == 1){
            $where['organid'] = ['like',"%45%"];
        }
        $listsloanArr = $this->Loan_db->where($where)->order('sorts asc,id desc')->limit($page->firstRow.','.$page->listRows)->select(); // dump($lists);
        $this->assign('count',$count);
        $this->assign('listsloanArr',$listsloanArr);
        $this->assign('page',$show);
        
        
        /* 下面 */
        $lontype='car';  //类型
        $identityid='';   //身份
        $houseid='';  //房子
        $carid='';  //车子
        $honourid='';  //信用
        $cityid = $this->city;  //城市
        $data_l =$this->selectCityLon($lontype,$identityid,$houseid,$carid,$honourid,$this->city,10); // dump($data_l);//查询显示产品
        $this->assign('data_l',$data_l[1]);
        $this->assign('page1',$data_l[2]);
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

       
        $count = $i->where(array("cate_name"=>"车贷攻略"))->count();
        $Page = getpage($count, 12);
        $show = $Page->show();//分页显示输出
        $res4 = $i->where(array("cate_name"=>"车贷攻略"))->limit($Page->firstRow . ',' . $Page->listRows)->order('sort asc')->select();

        
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


     //信用卡问答
    public function answer(){
         $d_d = array();
        if ($_GET['smwenti']){
            $d_d['a.title'] = array('like', '%'.$_GET['smwenti'].'%');
        }

        $data['reply'] = array('gt',0);
        $answer = M('Problemsss')->where($data)->count();//回答量
        $help = M('Problems')->where(array('is_help'=>1))->count();//帮助量
        $id = I('get.id');
        $problem = M('Problemsss')
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
                M('Problemsss')->save($data);
                $res = $problem[$key];
                $answerContent = M('Answersss')->where(array('p_id'=>$id))->select();
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
        $User = M('problemsss'); // 实例化User对象
        $count      = $User->where('is_show=1')->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,6);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
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
        $list = $User->where($where)->order('add_time')->limit($Page->firstRow.','.$Page->listRows)->select();
        if(count($list) > 6){
            $this->assign('page',$show);// 赋值分页输出
        }
        if(!$titles){
            $titles = '车贷问答';
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
        $hot = M('Problemsss')->order('browse desc')->limit('5')->select();//热门话题
        $new = M('Problemsss')->order('add_time desc')->limit('5')->select();//最新话题
        $this->assign('hot',$hot);
        $this->assign('new',$new);
        $this->assign('answer',$answer);//回答量
        $this->assign('help',$help);//帮助人数
        $this->assign('problem',$content);
        $n = M('new_user');
        $n_res = $n->where(array('is_sale'=>1))->order('sort desc')->limit(10)->select();
        $this->assign('n_res',$n_res);
        $this->display();
    }
    //关键字搜索
    public function answerSearch(){
        if(IS_AJAX){
            $title = I('post.content');
            $sql['title'] = array('like','%'.$title.'%');
            $res = M('Problemsss')->where($sql)->select();
            if($res){
                $this->ajaxReturn(array('status'=>1, 'info'=>$res));
            }else{
                $this->ajaxReturn(array('status'=>0, 'info'=>'提问失败！'));
            }
        }
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
            $res = M('Problemsss')->add($data);
            if($res){
                $this->ajaxReturn(array('status'=>1, 'info'=>'提问成功！'));
            }else{
                $this->ajaxReturn(array('status'=>0, 'info'=>'提问失败！'));
            }
        }
    }
    public function addAnswer(){
        if(IS_AJAX){
            $data['p_id'] = I('post.id');
            $data['content'] = I('post.content');
            M('Problemsss')->where(array('id'=>$data['p_id']))->setInc('reply');
            $res = M('Answersss')->add($data);
            if($res){
                $this->ajaxReturn(array('status'=>1));
            }else{
                $this->ajaxReturn(array('status'=>0, 'info'=>'发生未知错误，请联系管理员！'));
            }
        }
    }

    
    //免费申请
    public function freeApply(){
        $problem = M('Problemsss')->order('add_time')->limit(0,8)->select();
        $this->assign('problem',$problem);
        $n = M('new_user');
        $n_res = $n->where(array('is_sale'=>1))->order('sort desc')->limit(10)->select();
        $this->assign('n_res',$n_res);

        $id = I('id');
        //获取小贷公司id
        $supplier_id = M('Loan')->field('Supplier_id')->find($id);
        if($supplier_id['supplier_id']){
            $small = M('Xiaodai')->find($supplier_id['supplier_id']);
            $this->assign('small',$small);
        }
        
        if(!$id){
            $this->error('访问出错');
        }
        $url        =   strstr(get_url(),'.html',TRUE).'/fenxiao_id/'. $this->fenxiao_id;
        $this->assign('url',$url);
        $res = M('SeoView')->where(array('controller'=>'CarLoan','function'=>'freeApply'))->find();
        $this->showDetailLoan($id,$res);
        $article = M('news');
        $map['cate_name'] = '今日头条';
        $artList = $article->where($map)->order('sort asc')->limit(0,8)->select();
        $this->assign('artlist',$artList);

        $map['cate_name'] = '贷款攻略';
        $artList2 = $article->where($map)->order('sort asc')->limit(0,8)->select();
        $this->assign('artlist2',$artList2);

        $l_o = M('loan_order');
        $l_o_r = $l_o -> order('id desc')->limit(3)->select();
        foreach($l_o_r as $k => $v){
            $p = unserialize($v['orderlon']);
            $l_o_r[$k]['p_title'] =$p['title'];
            $l_o_r[$k]['cate_id'] =$p['cate_id'];
        }
        $this->assign('l_o_r',$l_o_r);
        $guwen = M('LoanView')->where(array('loan_id'=>$id))->limit(0,4)->select();
        $this->assign('sq',$guwen);
        //热门产品
        $hot = $this->selectCityLon('car',$identityid='',$houseid='',$carid='',$honourid='',$city='',$limit=8,$is_sale=1,$is_del=0);
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
        $lists = $this->selectCityLon('car',$identityid,$houseid,$carid,$honourid,$this->city,$organ,$pledge,$repay,5,$is_organ);  //查询显示产品
        if($is_organ){
            $name = "民间借贷";
        }else{
            $name = "热门车贷";
        }
        $html = '<div class="common_tit"> <span>'.$this->city.$name.'</span> </div>';
        if($lists[0])
        {
            foreach($lists[1] as $key=>$val){
                if($key==0){
                    $html .='<div class="chedai3_list chedai3_list_first">';
                }else{
                    $html .='<div class="chedai3_list">';
                }
                $html.='<div class="chedai3_wz">
                            <div class="chedai3_list_left">
                                <div class="chedai3_list_img">
                                    <a href="'.U('/Home/CarLoan/freeApply',array('id'=>$val['id'])).'">
                                        <img src="'.$val['logo_pic'].'"/>
                                    </a>
                                </div>
                            </div>
                            <div class="chedai3_list_right">
                                <div class="chedai3_list_bottom">
                                    <div class="chedai3_bottom_left">
                                        <h5>'.$val['title'].'</h5>
                                        <h4>信用贷款
                                            <span>';
                                            for($i=0;$i<$val['starid'];$i++){
                                                $html .='<img src="/Public/Home/Images/star_.png" alt=""/>';
                                            }
                                            $html.='</span>
                                        </h4>
                                        <ul>
                                            <li><span class="time"></span>'.$val['fksj'].'天放款</li>
                                        </ul>
                                        <div class="xuxian"></div>
                                    </div>
                                    <div class="chedai3_bottom_center">
                                        <dl>
                                            <dt>额度：</dt>
                                            <dd><span class="num_red">'.$val['money'].'~'.$val['money'].'</span> 万</dd>
                                        </dl>
                                        <dl>
                                            <dt>期限：</dt>
                                            <dd><span class="num_red">'.$val['dkqx'].'~'.$val['dkqx1'].'</span> 月</dd>
                                        </dl>
                                        <div class="xuxian2"></div>
                                    </div>
                                    <div class="chedai3_bottom_right">
                                        <div class="chedai3_bottom_table">
                                            <table>
                                                <tbody>
                                                <tr>
                                                    <td>'.htmlspecialchars_decode($val['yaoqiu']).'</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="chedai3_more">
                                            <a href="'.U('/Home/CarLoan/freeApply',array('id'=>$val['id'])).'">查看详情</a>
                                        </div>
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
    public function ajaxsq(){
        if(IS_AJAX){
            $post = I('post.');
            $p = M('loan')->where(['id'=>$post['pro_id']])->find();
            //序列化订单信息
            $orderlon = array();
            $orderlon['id']           = $p['pro_id'];//产品id
            $orderlon['cate_id']      = $p['cate_id'];//产品分类id
            $orderlon['title']        = $p['title'];//商品名称
            $orderlon['starid']       = $p['starid'];//星级
            $orderlon['money']        = $p['money'];//金额
            $orderlon['money1']       = $p['money1'];
            $orderlon['dkqx']         = $p['dkqx'];//期限
            $orderlon['dkqx1']        = $p['dkqx1'];
            $orderlon['ylx']          = $p['ylx'];//利息
            $orderlon['yg']           = $p['yg'];//月供
            $orderlon['fksj']         = $p['fksj'];//放款时间
            $orderlon['lnsm']         = $p['lnsm'];//利率说明,月管理费
            $orderlon['city']         = $this->city;//城市
            $orderlon['supplier_id']  = $p['supplier_id'];//小贷公司id

            $u = M('member')->where(['id'=>$_SESSION['user_id']])->find();
            //序列化个人信息
            $uinfo = array();
            $uinfo['id']              = $u['id'];           //用户id
            $uinfo['personname']      = $u['personname']?$u['personname']:I("name");//昵称
            $uinfo['realname']        = $u['realname'];//真实姓名
            $uinfo['telephone']       = $u['telephone']?$u['telephone']:I("tel");//手机号
            $uinfo['month_money']     = $u['month_money']?$u['month_money']:I("shouru");//月收入
            $uinfo['identityid']      = $u['identityid'];//职业身份
            $uinfo['identityname']    = $this->Cate_db->where(array('id'=>$u['identityid']))->getField('classname');//职业身份描述
            $uinfo['houseid']         = $u['houseid'];//房子
            $uinfo['housename']       = $this->Cate_db->where(array('id'=>$u['houseid']))->getField('classname')?$this->Cate_db->where(array('id'=>$u['houseid']))->getField('classname'):I("fangzi");//房子描述
            $uinfo['carid']           = $u['carid'];//车
            $uinfo['carname']         = $this->Cate_db->where(array('id'=>$u['carid']))->getField('classname')?$this->Cate_db->where(array('id'=>$u['carid']))->getField('classname'):I("chezi");//车描述

            //dump(I('post.'));die;
            $info['housename'] =I('post.fangzi');//房子
            $info['carname'] =I('post.chezi');//车子
            $data['orderlon']	= serialize($orderlon);
            $data['uinfo']      =  serialize($uinfo);//用户信息
            $data['guwen'] = I('post.guwen');//顾问
            $data['truename'] = I('post.name');//真实姓名
            $data['income'] = I('post.shouru');//收入
            $data['telephone'] =I('post.tel');//电话
            $data['cate_id'] = I('post.cate_id');//分类id
            $orderlon['city']         = $this->city;//城市
            $data['create_at'] = time(); //创建时间
            $data['guwen'] = I('post.guwen'); //顾问id
            $data['loanid'] = I('post.pro_id'); //贷款产品id
            $data['money'] = I('post.money'); //借款金额
            $data['qixian'] = I('post.qixian'); //借款期限
            $data['rate'] = I('post.ylx'); //利息
            $data['month_money'] = I('post.yg'); //月供
            $data['all_rate_money'] = I('post.zlx'); //总利息
            $data['apply_at'] = time();
            //查询该电话id
            $user_id = M('member')->where(['telephone'=>$data['telephone']])->getField('id');
            if(empty($user_id)){
                //给该手机号注册用户
                $addData = [
                    'telephone' => $data['telephone'],
                    'person_name' => $data['truename'],
                    'password' => encrypt_pass($data['telephone']),
                    'sex' => 3,
                    'add_time' => date("Y-m-d",time())
                ];
                $user_id = M('member')->add($addData);
            }
            //查询判断该用户是否已经申请过该贷款
            $loan = M('loan_order')->where(['uid'=>$user_id,'loanid'=>$data['loanid']])->fetchsql(false)->find();
            if(!empty($loan)){
                $this->ajaxReturn(['status'=>0,'info'=>'您已申请该贷款,请勿重复申请']);
            }
            $data['uid'] = $user_id;
            $res = M('loan_order')->add($data);
            if(!$res){
                $this->ajaxReturn(array('status'=>0, 'info'=>'申请失败，请联系客服！'));
            }else{
                $this->ajaxReturn(array('status'=>1));
            }

        }
    }
    /*
     * 验证短信
     * */
    public function ajax_dx(){
        if(IS_AJAX){
            $telephone = I('post.telephone');
            $code = I('post.code');
            $res = checkMessage($telephone, $code, 15);
            if($res['status']!=="1"){
                $this->ajaxReturn(array('status'=>0, 'info'=>$res['info']));
            }else{
                $this->ajaxReturn(array('status'=>1));
            }

        }
    }
    public function companyinfo(){
        //小贷公司详情
        $small_id = I('get.id');
        if($small_id){
            $sql['supplier_id'] = $small_id;
            
        }
        $small = M('Xiaodai')->find($small_id);
        /* $this->assign('name',$small['personname']); */
        $this->assign('small',$small);
        //产品展示
        $count = M('Loan')->where(array('supplier_id'=>$small_id))->count();
        $page  = getpage($count,5);
        $show  = $page->show();
        $listsloanArr = M('Loan')->where($sql)->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();
        $this->assign('loan',$listsloanArr);
        $this->assign('page',$show);
        //新闻资讯
        $news = M('News')->where(array('supplier_id'=>$small_id))->order('id asc')->limit(0,9)->select();
        $this->assign('news',$news);



        $this->assign('title',$small["personname"]."-".$small["yewu"]);
        $this->assign('keywords',$small["yewu"]);
        $this->assign('des',$small["jianjie"]);


        $this->display();
    }
}
?>