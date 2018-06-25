<?php
namespace Iphone\Controller;
use Think\Controller;
class HouseLoanController extends LoanController{

    public function _initialize(){
        parent::_initialize();
        //查询城市贷款  6
    }

    public function index(){
        session('loan',null);
        $h  =   M('house');
        $house  =   $h->select();
        $this->assign('house',$house);
        $l  =   M('loan');
        $carloan    =   $l->where(array('cate_id'=>2,'is_hot'=>1,'is_del'=>0,'is_sale'=>1))->order('create_time desc')->limit(0,4)->select();
        $this->assign('carloan',$carloan);
        $gl =   M('news');
        $cargonlve  =   $gl->where(array('cate_pid'=>91))->order('add_time desc')->limit(0,6)->select();
        $this->assign('cargonlve',$cargonlve);
        
        $this->display();
    }


}

?>