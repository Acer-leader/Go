<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends PublicController
{
    public function index()
    {
        $m = M('news');
        $list1_cate  = M("news_cate")->where(array('sort'=>101,"pid"=>0))->find();
        $where['cate_id'] = $list1_cate['id'];
        //贷款攻略
        //$list1 = $m->where($where)->order('sort asc')->limit(0,6)->select();
        //$list2 = $m->where($where)->order('sort asc')->limit(6,6)->select();
        //$this->assign('list1',$list1);
        //$this->assign('list2',$list2);
        $dkgllists = $m->where(array('is_hot'=>1,'cate_id'=>37))->order('sort asc')->limit(12)->select();
        $this->assign('dkgllists',$dkgllists);
        
        
        //贷款资讯
        $article = M('news');
        $article_cate  = M("news_cate")->where(array('sort'=>103,"pid"=>0))->find();
        $map['cate_id'] = $article_cate['id'];
        $artList = $article->where($map)->order('sort asc')->limit(0,6)->select();
        $this->assign('artlist',$artList);
        //新手必读
        $new_user = M('news');
        $article_cate1 = M('news_cate')->where(array('sort'=>102,'pid'=>0))->find();
        $map['cate_id'] = $article_cate1['id'];
        $new_read = $new_user->where($map)->order('sort asc')->limit(0,6)->select();
        //var_dump($new_read);
        //问答
        $pro = M('problem');   //信用卡问答
        $proList = $pro->where('is_show=1')->order('browse asc')->limit(0,4)->select();
        $this->assign('prolist',$proList);
        
        //常见问题
        //$comPro = $pro->where('is_show=1')->order('browse asc')->limit(6)->select();
        $comPro = $article->where(array('cate_id'=>90))->order('sort asc')->limit(6)->select();
        $this->assign('compro',$comPro);
        

        //关于洛凡
        $luo = M('about');
        $luo = $luo->where(array('id'=>1))->find();
        $this->assign('luo',$luo);

        //房贷信息资讯
        $house_cate  = M("news_cate")->where(array('sort'=>2,"pid"=>0))->find();
        $houseNews_A = M('News')->where(array('cate_id'=>$house_cate['id']))->limit('0,4')->select();
        $houseNews_B = M('News')->where(array('cate_id'=>$house_cate['id']))->limit('4,4')->select();

        //热门信用卡资讯
        $cardHot_cate  = M("news_cate")->where(array('sort'=>4,"pid"=>0))->find();
        // $cardHot = M('News')->where(array('cate_id'=>$cardHot_cate['id']))->limit(0,9)->select();
        $cardHot = M('strategy')->where(array("type"=>1))->limit(7)->order("add_time desc")->select();

        //热门发卡银行
        $cardBank = M('CardType')
                    ->alias('a')
                    ->join('LEFT JOIN __BACK__ b on a.id=b.card_type_id')
                    ->field('a.classname,a.logo_pic,b.discount,b.interest')
                    ->where(array('a.pid'=>15))
                    ->order('a.create_at desc')
                    ->select();
        //热门房贷银行
        $houseBank = M('CardType')
                    ->alias('a')
                    ->join('LEFT JOIN __BACK__ b on a.id=b.card_type_id')
                    ->field('a.classname,a.logo_pic,b.discount,b.interest')
                    ->where(array('a.pid'=>15))
                    ->order('a.create_at')
                    ->select();
        //热点
        $hotSpot = M('News')->where(array('is_hot'=>1))->order('add_time desc')->limit(0,4)->select();
        $this->assign('hotSpot',$hotSpot);
        $this->assign('cardBank',$cardBank);
        $this->assign('houseBank',$houseBank);
        $this->assign('cardHot',$cardHot);
        $this->assign('houseNews_A',$houseNews_A);
        $this->assign('houseNews_B',$houseNews_B);
        $this->assign('new_read',$new_read);

        //申请弹框
        $ca= M('cate');
        $fang = $ca->where(array('pid'=>10))->select();
        $che  = $ca->where(array('pid'=>18))->select();
        $this->assign('fang',$fang);
        $this->assign('che',$che);

        //首页轮播
        $lunbo = M("banner")->field('pic,url')->where(array('type'=>1,'isdel'=>0))->order('sort asc')->select();
        $foupic = M("banner")->field('pic,title,title1')->where(array('type'=>2,'isdel'=>0))->order('add_time asc')->limit(4)->select();
        $bigpic = M("banner")->field('pic,title,url')->where(array('type'=>4,'isdel'=>0))->find();
        $pics = M("banner")->field('pic,title,title1,url')->where(array('type'=>3,'isdel'=>0))->order('add_time asc')->limit(4)->select();
        $this->assign('lunbo',$lunbo);
        $this->assign('foupic',$foupic);
        $this->assign('bigpic',$bigpic);
        $this->assign('pics',$pics);

        //合作机构
        $cooperate = M("cooperate")->field('pic')->select();
        $count = ceil(count($cooperate)/2);
        $this->assign('count',$count);
        $this->assign('cooperate',$cooperate);
        $this->display();
    }

    //常见问题列表
    public function proList(){
        $pro = M('problem');
        $proList = $pro->where('is_show=1')->order('browse desc')->limit(15)->select();
        $this->assign('prolist',$proList);
        $this->display();

    }
    function wordTime($time) {
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
     //信用卡资讯详情
    public function cardDot(){
        $id = I('get.id');
        $m = M('News');
        //热门信用卡
        $cards = $m->where('id<'.$id)->order('add_time desc')->limit('1')->where(array('cate_name'=>'热门信用卡'))->select();
        $cardx = $m->where('id>'.$id)->order('add_time')->limit('1')->where(array('cate_name'=>'热门信用卡'))->select();
        $cardHotx = $cardx[0];
        $cardHots = $cards[0];
        $card = $m->find($id);
        // $num = $news['dj']+1;
        // $res = M('News')->where(array('id'=>$id))->save(array('dj'=>$num));
        $this->assign('cardHotx',$cardHotx);
        $this->assign('cardHots',$cardHots);
        $this->assign('card',$card);
        $this->display();
    }


    //首页-常见问题
    public function answer(){
        $data['reply'] = array('gt',0);
        $answer = M('Problem')->where($data)->count();//回答量
        $help = M('Problem')->where(array('is_help'=>1))->count();//帮助量
        $id = I('get.id');
        $problem = M('Problem')
                   ->alias('a')
                   ->join('LEFT JOIN __MEMBER__ b on a.user_id=b.id')
                   ->field('a.id,a.title,a.add_time,a.user_id,a.browse,a.reply,a.is_help,b.person_name')
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
                $this->assign('answerContent',$answerContent);
                $this->assign('res',$res);
            }
            //时间
            foreach($val as $k=>$v){
                if($k == 'add_time'){
                    $content[$num]['stamptime'] = $v;
                   // $content[$num][$k] = $this->wordTime($v);
                }else{
                    $content[$num][$k] = $v;
                }
            }
        }
        $hot = M('Problem')->order('browse desc')->limit('5')->select();//热门话题
        $this->assign('hot',$hot);
        $this->assign('answer',$answer);//回答量
        $this->assign('help',$help);//帮助人数
        $this->assign('problem',$content);

        //echo 1;
        //die();
        $this->display();

    }






    //首页申请贷款  这个是和产品列表的申请合并到一起的
    public function freeApply(){
        if(IS_POST){
            $mem = M('member');
            $m   = M('loan_order');
            $data = I("post.");
            //=======================检测验证码=================
            $res = checkMessage($data['telephone'], $data['code'], 4);
            //=======================检测验证码=================
            if($res['status']!=1){
                $this->ajaxReturn($res);
            }
            switch($data['type']){
                case '个贷':
                    $cate_id=1;
                    break;
                case '房贷':
                    $cate_id=2;
                    break;
                case '车贷':
                    $cate_id=3;
                    break;
            }

            $arrayTime=array(
                    '0'=>mktime(0,0,0,date('m'),date('d'),date('Y')),
                    '1'=>mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1,
                );
            $res = $m->where(array('telephone'=>$data['telephone'],'cate_id'=>$cate_id,'apply_at'=>array('between',$arrayTime)))->select();
            if($res){
                $this->ajaxReturn(array('status'=>0,'info'=>"您已经预约，请耐心等待！"));
            }

            //查询用户是否注册，没有注册的话 保存为会员
            $memInfo = $mem->where(array('telephone'=>$data['telephone']))->find();
            $memId = "";
            $type  = "";
            if(!$memInfo){
                $password = encrypt_pass($data['telephone']);
                $memId = $mem->add(array('telephone'=>$data['telephone'],'password'=>$password,'sex'=>$data['sex'],'surname'=>$data['surname'],'person_name'=>$data['name']));
                $type  =1;
            }
            $log_data=array(
                'user_id'   => $memId?$memId:$memInfo['id'],
                'cate_id'   => $cate_id,
                'city'=>$this->city,
                'status'=>"0",
                'apply_at'  => date("Y-m-d H:i:s",time()),
                );
            $res = $m->add($log_data);
            if($res){
                if(!$memInfo){
                    $msg = "尊敬的客户您好！您已成功申请".$data['type'].",且注册为洛凡金融平台会员，密码默认为手机号";
                }else{
                    $msg = "尊敬的客户您好！您已成功申请".$data['type'].",稍后会有客服与您电话联系";
                }                
                sendMessage($data['telephone'],'',$msg);
                $this->ajaxReturn(array('status'=>1,'info'=>"预约成功！！",'type'=>$type,'x_id'=>$memId?$memId:$memInfo['id']));
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>"预约失败！"));
            }
        }
    }


    //贷款搜索-选择贷款类型
    public function choose(){
        //var_dump(I('post.'));
        $type = I('post.type');
        $money = I('post.money');
        $date = I('post.date');
        if($type=='个人贷款'){
            $this->redirect('Home/PersonLoan/index',array('money'=>$money,'date'=>$date));
        }
        if($type=='房产贷款'){
            $this->redirect('Home/HouseLoan/index',array('money'=>$money,'date'=>$date));
        }
        if($type=='汽车贷款'){
            $this->redirect('Home/CarLoan/index',array('money'=>$money,'date'=>$date));
        }else{
            $this->redirect('Home/PersonLoan/index',array('money'=>$money,'date'=>$date));
        }
    }
    public function cityList(){
        $p = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        $c = M('region')->select();
        foreach($c as $key=>$val){
            foreach($val as $k=>$v){
                if($k == 'pinyin'){
                    $c[$key]['zimu'] = substr($v,0,1);
                }
                if($k == 'card'){
                    $c[$key]['dj'] = substr($v,0,2);
                }
            }
        }
        // dump($c);exit;
        $this->assign('citys',$c);
        $this->assign('p',$p);
        $this->display();
    }

    public function dbj(){
        
        
        
        $this->display();
    }
    
    public function dbx(){
        
        
        $this->display();
    }
    
    
    public function gjj(){
        
        

        $this->display();		
    }
    
    public function app_dow(){
        $img = M("banner")->field('pic,url')->where(array('type'=>9,'isdel'=>0))->order('sort asc,id asc')->select();
        $this->assign("img",$img);
        $this->assign("title","手机APP下载");
        $this->display();
    }
    
    
    
}