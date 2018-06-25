<?php
namespace Iphone\Controller;
use Think\Controller;
header("Content-type:text/html;charset=utf-8");
class PersonLoanController extends LoanController {
    
    
    public function _initialize(){
        parent::_initialize();
        $this->assign("controller_name", CONTROLLER_NAME);
        $this->assign("action_name", ACTION_NAME);	

    }

    public function index(){
        session('loan',null);
        $data        =   array('cate_id'=>1,'is_del'=>0,'is_sale'=>1);
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
        $type1  =   $c->where(array('id'=>array('in','4,10,18')))->select();
        foreach($type1 as $k=>$v){
            $type1[$k]['typeid']    =   $c->where(array('pid'=>$v['id']))->select();
        }
        $this->assign('type1',$type1);
        /* dump($type1); */
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
        
        
        //นฅยิ
        $gl =   M('news');
        $cargonlve  =   $gl->where(array('cate_pid'=>93))->order('add_time desc')->limit(0,6)->select();
        $this->assign('cargonlve',$cargonlve);
        //dump($carloan);
        $this->display();
    }
    

}

?>