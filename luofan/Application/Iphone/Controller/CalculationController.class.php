<?php
namespace Iphone\Controller;
use Think\Controller;

class CalculationController extends PublicController {
    public function index(){
         
        $res = $this->debx('120','10000','0.0515');
        
        //intval
        //header("Content-type: text/html; charset=utf-8");

        $this->display();
    }
  
    public function jisuan1(){
        $this->display();
    }
    
    public function jisuan2(){
        $this->display();
    }
    
    public function jisuandot(){
        if(IS_POST){
            $edu       =   I('post.edu');
            //dump($edu);
            if($edu==1){
                $dkTotal    =   I('post.dkTotal')*10000;

            }else{
                $prize      =   I('post.prize');//单价
                $square     =   I('post.square');//面积
                $house      =   I('post.house'); //1-一房 2-二房
                $downpay    =   I('post.downpay');//首付
                $dkTotal    =   $prize*$square*(1-$downpay/100);//贷款金额
                //dump($dkTotal);
            }
            $dkm        =   I('post.dkm');
            $dknl       =   I('post.dknl');
            $bj_res    =   $this->debj($dkm*12,$dkTotal,$dknl/100);
            //header("Content-type: text/html; charset=utf-8");
            //dump($bj_res);
            $bj_total  =   Number_format($dkTotal+$bj_res['lxTotal'], 2,'.','');
            $this->assign('bj_total',$bj_total);//等额本金 -本息
            $this->assign('dkTotal',$dkTotal);//贷款总额
            $this->assign('dkm',$dkm*12);
            $this->assign('bj_res',$bj_res);
            $this->display();
            
        }

    }
    
    public function jisuandot1(){
        if(IS_POST){
            $edu       =   I('post.edu');
            //dump($edu);
            if($edu==1){
                $dkTotal    =   I('post.dkTotal')*10000;

            }else{
                $prize      =   I('post.prize');//单价
                $square     =   I('post.square');//面积
                $house      =   I('post.house'); //1-一房 2-二房
                $downpay    =   I('post.downpay');//首付
                $dkTotal    =   $prize*$square*(1-$downpay/100);//贷款金额
                //dump($dkTotal);
            }
            $dkm       =   I('post.dkm');
            $dknl      =   I('post.dknl');
            $bx_res    =   $this->debx($dkm*12,$dkTotal,$dknl/100);
            //header("Content-type: text/html; charset=utf-8");
            //dump($bx_res);
            $bx_total  =   Number_format($dkTotal+$bx_res['lxTotal'], 2,'.','');
            $this->assign('bx_total',$bx_total);//等额本息 -本息
            $this->assign('dkTotal',$dkTotal);//贷款总额
            $this->assign('dkm',$dkm*12);
            $this->assign('bx_res',$bx_res);
            $this->display();
            
        }

    }
    
    public function jisuandot2(){
        if(IS_POST){
            $edu       =   I('post.edu');
            //dump($edu);
            if($edu==1){
                $dkTotal    =   I('post.dkTotal')*10000;

            }else{
                $prize      =   I('post.prize');//单价
                $square     =   I('post.square');//面积
                $house      =   I('post.house'); //1-一房 2-二房
                $downpay    =   I('post.downpay');//首付
                $dkTotal    =   $prize*$square*(1-$downpay/100);//贷款金额
                //dump($dkTotal);
            }
            $dkm       =   I('post.dkm');
            $dknl      =   I('post.dknl');
            //等额本金
            $bj_res    =   $this->debj($dkm*12,$dkTotal,$dknl/100);
            //header("Content-type: text/html; charset=utf-8");
            //dump($bj_res);
            $bj_total  =   Number_format($dkTotal+$bj_res['lxTotal'], 2,'.','');
            
            //等额本息
            $bx_res    =   $this->debx($dkm*12,$dkTotal,$dknl/100);
            //header("Content-type: text/html; charset=utf-8");
            //dump($bx_res);
            $bx_total  =   Number_format($dkTotal+$bx_res['lxTotal'], 2,'.','');
            
            $this->assign('bx_total',$bx_total);//等额本息 -本息
            $this->assign('bx_res',$bx_res);
            
            $this->assign('bj_total',$bj_total);//等额本金 -本息
            $this->assign('bj_res',$bj_res);
            
            $this->assign('dkTotal',$dkTotal);//贷款总额
            $this->assign('dkm',$dkm*12);
     
            $this->display();
        }

    }
    
    //等额本息
    public function debx($dkm,$dkTotal,$dknl) 
    { 
        //$dkm   = 240; //贷款月数，20年就是240个月 
        //$dkTotal = 10000; //贷款总额 
        //$dknl  = 0.0515; //贷款年利率 
        $emTotal = round($dkTotal * $dknl / 12 * pow(1 + $dknl / 12, $dkm) / (pow(1 + $dknl / 12, $dkm) - 1),2); //每月还款金额 
        //$lxTotal = 0; //总利息 
        $data['lx']   =   array();
        $data['lxTotal']    =  Number_format( $emTotal*$dkm-$dkTotal, 2,'.','');
       
        for ($i = 0; $i < $dkm; $i++) { 
          $lx   = $dkTotal * $dknl / 12;  //每月还款利息
          $em   = $emTotal - $lx; //每月还款本金
          //$data['lx'][]   =   $emTotal . "第" . ($i + 1) . "期";
          $data['lx'][]   =   Number_format($emTotal, 2,'.','');
          //echo "第" . ($i + 1) . "期", " 本金:", $em, " 利息:" . $lx, " 总额:" . $emTotal, "<br />"; 
          $dkTotal = $dkTotal - $em; 
          //$lxTotal = $lxTotal + $lx; 
        } 
       
        return  $data; 
    } 
    
    //等额本金
    public function debj($dkm,$dkTotal,$dknl) 
    { 
        //$dkm   = 240; //贷款月数，20年就是240个月 
        //$dkTotal = 10000; //贷款总额 
        //$dknl  = 0.0515; //贷款年利率 
           
        $em   = $dkTotal / $dkm; //每个月还款本金 
        $lxTotal = 0; //总利息 
        $data['lx'] =   array();
        for ($i = 0; $i < $dkm; $i++) { 
          $lx   = round($dkTotal * $dknl / 12,2); //每月还款利息 
          $data['lx'][] =   Number_format($lx+$em, 2,'.','');
          $dkTotal -= $em; 
          $lxTotal = $lxTotal + $lx; 
        } 
        $data['lxTotal']    =  Number_format($lxTotal, 2,'.','');
        return $data; 
    } 
}