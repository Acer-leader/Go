<?php
namespace Supplier\Controller;
use Supplier\Common\Controller\CommonController;
class PaysController extends CommonController {
    public function index(){
        $pic = M('virtual_config')->where(array("id"=>1))->field("wx_ewm,alipay_ewm")->find();
        $this->assign('pic',$pic);
        $this->display('pays');
    }
}