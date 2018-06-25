<?php
namespace Admin\Controller;
use Common\Controller\CommonController;
use Think\Db\Driver\Pgsql;

class CreditcardController extends CommonController
{
    /**
     *  lin
     */
    public function index(){

        $title = I('get.title');
        if($title){
            $sql['creditname'] = array('like','%'.$title.'%');
            $this->assign('title',$title);
        }
        $is_recommend=I("get.is_recommend");
        if($is_recommend!=null){
            $sql['is_recommend']=$is_recommend;
            $this->assign("is_recommend",$is_recommend);
        }
        $cc = M('creditcard');
        $count=$cc->where($sql)->count();
        $Page  = getpage($count,10);
        $show  = $Page->show();
        $cc_res = $cc->order('sort desc')->where($sql)->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($cc_res as $key => $value){
            if($value["small_imgs"]){
                $cc_res[$key]["small_imgs"] = explode(',',$value["small_imgs"]);
            }
        }
        $this->assign('cc_res',$cc_res);
        $this->assign("page",$show);
        $attr_A = M('card_type')->where(array('pid'=>0))->select();
        $attr_B = M('card_type')->where(array('pid'=>array('neq',0)))->select();
        $this->assign('attr_A',$attr_A);
        $this->assign('attr_B',$attr_B);
        $this->display();
    }

    public function updstatus(){
        if(IS_AJAX){
            $data=array();
            $data["id"]=I("post.id");
            $db = M('creditcard');
            $status=$db->where(array("id"=>$data["id"]))->getField("is_recommend");
            $data['is_recommend']=$status==0?1:0;
            $result=$db->save($data);
            if($result){
                $this->ajaxReturn(array("status"=>1,"info"=>"操作成功"));exit;
            }else{
                $this->ajaxReturn(array("status"=>0,"info"=>"操作失败"));exit;
            }

        }
    }
    /**
     * 添加信用卡 lin
     */
    public function creditcard_add(){

        if(IS_POST){

            $data = I('post.');
            $small_imgs = implode(",",$data["pic1"]);
            if($small_imgs){
                $data["small_imgs"] = $small_imgs;
                unset($data['pic1']);
            }else{
                unset($data['pic1']);
            }

            $edit_id = I('post.id');
            $card_type = $data['card_type'];

            //序列化数据
            $card_type = serialize($card_type);
            $data['card_type'] = $card_type;



            $cc = M('creditcard');
            // $info['city_card'] = array_filter(explode(",",$info['city_card']));

           /* $city = I("post.city");
            foreach($city as $k=>$v){
                $cityName .= $this->cardChangeName($v).",";
                $cityCard .= $v.",";
            }
            $data['city'] = $cityName;
            $data['city_card'] = $cityCard;*/
            $unionpay2 = I("post.unionpay2");
            foreach($unionpay2 as $k2=>$v2){
//                $cityName .= $this->cardChangeName($v2).",";
                $unionpay2_d .= $v2.",";
            }
            $data['unionpay2'] = $unionpay2_d;
            $unionpay = I("post.unionpay");
            foreach($unionpay as $k1=>$v1){
//                $cityName .= $this->cardChangeName($v2).",";
                $unionpay_d .= $v1.",";
            }
            $data['unionpay'] = $unionpay_d;
//            $data['city_card'] = $cityCard;
            if($edit_id){
                $e_res = $cc->where(array('id'=>$edit_id))->find();
                if (!$e_res){
                    $this->error("此信用卡不存在！");exit;
                }
                $res = $cc->where(array('id'=>$edit_id))->save($data);
            }else{
                $data['add_time'] = time();
                $res = $cc->add($data);
            }
            if($res !==false){
                $this->success($edit_id?'修改成功':"添加成功");exit;
            }else{
                $this->error($edit_id?'修改失败':"添加失败！");exit;
            }
//            var_dump($data);
        }
        $id = $_GET['id'];
        if ($id){
            $cc = M('creditcard');
            $res = $cc ->where(array('id'=>$id))->find();
//            $res['city'] = array_filter(explode(",",$res['city']));
            $res['unionpay'] = array_filter(explode(",",$res['unionpay']));
            $res['unionpay2'] = array_filter(explode(",",$res['unionpay2']));
            $res['card_type_id'] = unserialize($res['card_type']);
            $res["small_imgs"] = explode(',',$res["small_imgs"]);
            $this->assign('res',$res);
        }
        $attr_A = M('card_type')->where(array('pid'=>0))->select();
        $attr_B = M('card_type')->where(array('pid'=>array('neq',0)))->select();
        $this->assign('attr_A',$attr_A);
        $this->assign('attr_B',$attr_B);
        $map = "LevelType=1 or LevelType=0";
        $provinceList = M("region")->where($map)->select();
        $this->assign('provinceList',$provinceList);
        $unionpay = M('unionpay')->where(array('pid'=>1))->select();
        $unionpay2 = M('unionpay')->where(array('pid'=>2))->select();
        $this->assign('unionpay',$unionpay);
        $this->assign('unionpay2',$unionpay2);
        $this->display();
    }

    /**
     * 修改信用卡 lin
     */
    public function creditcard_edit(){

    }

    /**
     * 删除信用卡 lin
     */
    public function creditcard_del(){
        $id = $_GET['id'];
        $cc = M('creditcard');
        $res = $cc->where(array('id'=>$id))->find();
        if(!$res){
            $this->error("此信用卡不存在！");exit;
        }
        $res = $cc->where(array('id'=>$id))->delete();
        if($res){
            $this->success('删除成功!');exit;
        }else{
            $this->error("删除失败!");exit;
        }
    }
    /**
     * 卡类型 银联
     */
    public function unionpay(){
        $attr_A = M('unionpay')->where(array('pid'=>0))->select();
        $attr_B = M('unionpay')->where(array('pid'=>array('neq',0)))->select();
        // dump($attr_A);exit;
        $this->assign('attr_A',$attr_A);
        $this->assign('attr_B',$attr_B);
        $this->display();
    }
    public function unionpay_add_edit(){
        if (IS_AJAX){
            $id = I('post.id');
            $data = I('post.');
            unset($data['id']);
            $c = M('unionpay');
            if (!$id){
                //不存在ID添加
                $data['create_at'] = time();
                $res = $c->add($data);
            }else{
                $r_c = $c->where(array('id'=>$id))->find();
                if(!$r_c){
                    $this->ajaxReturn(array("status"=>0,"info"=>'此类型不存在!'));
                }
                $res = $c->where(array('id'=>$id))->save($data);
            }
            if($res !== false){
                $this->ajaxReturn(array('status'=>1,"info"=>$id?'修改成功!':'添加成功!'));
            }else{
                $this->ajaxReturn(array('status'=>0,"info"=>$id?'修改失败!':'添加失败!'));
            }
        }
    }

    public function unionpay_del(){
        if(IS_AJAX){
            $id = I('post.id');
            $c = M('unionpay');
            M()->startTrans();
            $res = $c -> where(array('id'=>$id))->find();
            if (!$res){
                $this->ajaxReturn(array('status'=>0,"info"=>'此类型不存在!'));
            }
            if ($res['pid'] == 0){
                //有子儿子
                $res_c = $c->where(array('pid'=>$res['id']))->getField('id',true);
                if($res_c){
                    //先删除儿子
//                var_dump($res_c);
                    $arr = implode(',',$res_c);
                    $arr = $c->delete($arr);
                    if ($arr===false){
                        M()->rollback();
                        $this->ajaxReturn(array('status'=>0,"info"=>'删除失败!'));
                    }
                }
            }
            $res_del = $c->where(array('id'=>$id))->delete();
            if ($res_del){
                M()->commit();
                $this->ajaxReturn(array('status'=>1,"info"=>'删除成功!'));
            }else{
                M()->rollback();
                $this->ajaxReturn(array('status'=>0,"info"=>'删除失败!'));
            }
        }
    }

    /**
     * lin
     */
    public function card_type(){
        $attr_A = M('card_type')->where(array('pid'=>0))->select();
        $attr_B = M('card_type')->where(array('pid'=>array('neq',0)))->select();
        foreach($attr_A as $key => $value){
            //根据id查询apply地址
            $apply = M("back")->where(array("card_type_id"=>$value['id']))->getField("apply");
            $attr_A[$key]['apply'] = $apply;
        }
        foreach($attr_B as $key => $value){
            //根据id查询apply地址
            $apply = M("back")->where(array("card_type_id"=>$value['id']))->getField("apply");
            $attr_B[$key]['apply'] = $apply;
        }
        // dump($attr_A);exit;
        $this->assign('attr_A',$attr_A);
        $this->assign('attr_B',$attr_B);
        $this->display();
    }
    /**
     * 添加 修改 银行卡类型
     */
    public function card_add_edit(){
        if (IS_AJAX){
            $id = I('post.id');
            $data = I('post.');
            unset($data['id']);
            $c = M('card_type');
            if (!$id){
                $data['create_at'] = time();
                //不存在ID添加
                M("back")->add(array("card_type_id"=>$id,"apply"=>$data["applyUrl"]));
                $res = $c->add($data);
            }else{
                $r_c = $c->where(array('id'=>$id))->find();
                if(!$r_c){
                    $this->ajaxReturn(array("status"=>0,"info"=>'此类型不存在!'));
                }
                //根据类型id查询back表是否有该银行卡类型
                $back = M("back")->where(array("card_type_id"=>$id))->find();
                if($back){
                    $backsql = M("back")->where(array("card_type_id"=>$id))->save(array("apply"=>$data["applyUrl"]));
                }else{
                    M("back")->add(array("card_type_id"=>$id,"apply"=>$data["applyUrl"]));
                }
                $res = $c->where(array('id'=>$id))->save($data);
            }
            if($res !== false){
                $this->ajaxReturn(array('status'=>1,"info"=>$id?'修改成功!':'添加成功!'));
            }else{
                $this->ajaxReturn(array('status'=>0,"info"=>$id?'修改失败!':'添加失败!'));
            }
        }
    }
    public function card_del(){
        if(IS_AJAX){
            $id = I('post.id');
            $c = M('card_type');
            M()->startTrans();
            $res = $c -> where(array('id'=>$id))->find();
            if (!$res){
                $this->ajaxReturn(array('status'=>0,"info"=>'此类型不存在!'));
            }
            if ($res['pid'] == 0){
                //有子儿子
                $res_c = $c->where(array('pid'=>$res['id']))->getField('id',true);
                if($res_c){
                    //先删除儿子
//                var_dump($res_c);
                    $arr = implode(',',$res_c);
                    $arr = $c->delete($arr);
                    if ($arr===false){
                        M()->rollback();
                        $this->ajaxReturn(array('status'=>0,"info"=>'删除失败!'));
                    }
                }
            }
            $res_del = $c->where(array('id'=>$id))->delete();
            if ($res_del){
                M()->commit();
                $this->ajaxReturn(array('status'=>1,"info"=>'删除成功!'));
            }else{
                M()->rollback();
                $this->ajaxReturn(array('status'=>0,"info"=>'删除失败!'));
            }
        }
    }


    /**
     *银卡信息 接口信息
     */
    public function bank(){
        $title = I('post.title');
        if($title){
            $sql['b.classname'] = array('like','%'.$title.'%');
            $this->assign('title',$title);
        }
        $attr_A = M('card_type')->where(array('pid'=>15))->select();
        $this->assign('attr_A',$attr_A);
        $res = M('back')
            ->alias('a')
            ->field('a.id,a.service,b.classname')
            ->join('app_card_type as b on a.card_type_id = b.id')
            ->where($sql)
            ->order('a.id desc')->select();
        $this->assign('res',$res);
//        var_dump($res);
        $this->display();
    }
    public function bank_add_edit(){
        if(IS_POST){
            $data = I('post.');
            $edit_id = I('post.id');
            $cc = M('back');
            if($edit_id){
                $e_res = $cc->where(array('id'=>$edit_id))->find();
                if (!$e_res){
                    $this->error("此银行信息不存在！");exit;
                }
                $res = $cc->where(array('id'=>$edit_id))->save($data);
            }else{
                $data['add_time'] = time();
                $card_type_id = $data['card_type_id'];
                $res_is = $cc->where(array('card_type_id'=>$card_type_id))->find();
                if ($res_is){
                    $this->error("此银行信息已添加！");exit;
                }

                $res = $cc->add($data);
            }
            if($res !==false){
                $this->success($edit_id?'修改成功':"添加成功");exit;
            }else{
                $this->error($edit_id?'修改失败':"添加失败！");exit;
            }
//            var_dump($data);
        }
        $id = $_GET['id'];
        if ($id){
            $cc = M('back');
            $res = $cc ->where(array('id'=>$id))->find();
            $this->assign('res',$res);
        }

        $attr_A = M('card_type')->where(array('pid'=>15))->select();
        $this->assign('attr_A',$attr_A);

        $this->display();
    }
    /**
     * 信用卡优惠
     */
    public function favor(){
        $title = I('post.title');
        if($title){
            $sql['news_title'] = array('like','%'.$title.'%');
            $this->assign('title',$title);
        }
        $count=M('favor')->where($sql)->count();
        $Page  = getpage($count,10);
        $show  = $Page->show();
        $List = M('favor')->order('sort asc,add_time desc')->where($sql)->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('cache',$List);
        $this->assign("page",$show);
        $this->display();
    }
    public function favor_add(){
        $id = intval(I('param.id'));
        $m  = M('favor');
        $info = $m->find($id);
//        $info['city'] = array_filter(explode(",",$info['city']));
        // $info['city_card'] = array_filter(explode(",",$info['city_card']));

//        $city = I("post.city");
//        foreach($city as $k=>$v){
//            $cityName .= $this->cardChangeName($v).",";
//            $cityCard .= $v.",";
//        }
        if(IS_POST){
            $data=array(
                'news_title'=> I("post.news_title"),
                'detail'    => I("post.detail"),
                'is_hot'    => I("post.is_hot"),
                'sort'      => I("post.sort"),
                'addtime'   => I("post.addtime"),
                'province'  => I("post.province"),
                'type'      => I('post.type'),
                'is_sale'   => I('post.is_sale'),
                'source'    => I('post.source'),
                'author'    => I('post.author'),
//                'city'      => $cityName,
//                'city_card' => $cityCard,

            );
            $logo_pic= I("post.logo_pic");
            $hot_pic= I("post.hot_pic");
            $detail  = I("post.detail");
            if($logo_pic){
                $data['logo_pic']=$logo_pic;
            }
			if($hot_pic){
                $data['hot_pic']=$hot_pic;
            }
            if($detail){
                $data['detail']=$detail;
            }

            if($id){
                $data['edit_time']=time();
                $res = $m->where(array('id'=>$id))->save($data);
            }else {
                $data['add_time']=time();
                $res = $m->add($data);
            }
            if($res){
                $this->success("操作成功");exit;
            }else{
                $this->success("操作失败");exit;
            }
        }
//        $map = "LevelType=1 or LevelType=0";
//        $provinceList = M("region")->where($map)->select();
//        $this->assign('provinceList',$provinceList);
        $this->assign('cache',$info);
        $this->display();
    }
    public function favor_del(){
        $id = I('post.id');
        $cc = M('favor');
        $res = $cc->where(array('id'=>$id))->find();
        if(!$res){
            $this->ajaxReturn(array('status'=>0,'info'=>'此信用卡优惠信息不存在！'));
        }

        $res = $cc->where(array('id'=>$id))->delete();
        if($res){
            $this->ajaxReturn(array('status'=>1,'info'=>'刪除成功!'));
        }else{
            $this->ajaxReturn(array('status'=>0,'info'=>'刪除失敗！'));
        }
    }
    /*public function favor_del_all(){

    }*/


    /**
     * 卡积分
     */
    public function integral(){
        $title = I('post.title');
        if($title){
            $sql['news_title'] = array('like','%'.$title.'%');
            $this->assign('title',$title);
        }
        $count=M('integral')->where($sql)->count();
        $Page  = getpage($count,10);
        $show  = $Page->show();
        $List = M('integral')->order('sort asc,add_time desc')->where($sql)->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign("page",$show);
        $this->assign('cache',$List);
        $this->display();
    }
    public function integral_add(){
        $id = intval(I('param.id'));
        $m  = M('integral');
        $info = $m->find($id);
//        $info['city'] = array_filter(explode(",",$info['city']));
        // $info['city_card'] = array_filter(explode(",",$info['city_card']));

//        $city = I("post.city");
//        foreach($city as $k=>$v){
//            $cityName .= $this->cardChangeName($v).",";
//            $cityCard .= $v.",";
//        }
        if(IS_POST){
            $data=array(
                'news_title'=> I("post.news_title"),
                'detail'    => I("post.detail"),
                'is_hot'    => I("post.is_hot"),
                'sort'      => I("post.sort"),
                'addtime'   => I("post.addtime"),
                'province'  => I("post.province"),
                'is_sale'   => I('post.is_sale'),
                'source'    => I('post.source'),
                'author'    => I('post.author'),

//                'city'      => $cityName,
//                'city_card' => $cityCard,

            );
            $logo_pic= I("post.logo_pic");
            $detail  = I("post.detail");
            if($logo_pic){
                $data['logo_pic']=$logo_pic;
            }
            if($detail){
                $data['detail']=$detail;
            }

            if($id){
                $data['edit_time']=time();
                $res = $m->where(array('id'=>$id))->save($data);
            }else {
                $data['add_time']=time();
                $res = $m->add($data);
            }
            if($res){
                $this->success("操作成功");exit;
            }else{
                $this->success("操作失败");exit;
            }
        }
//        $map = "LevelType=1 or LevelType=0";
//        $provinceList = M("region")->where($map)->select();
//        $this->assign('provinceList',$provinceList);
        $this->assign('cache',$info);
        $this->display();
    }
    public function integral_del(){
        $id = I('post.id');
        $cc = M('integral');
        $res = $cc->where(array('id'=>$id))->find();
        if(!$res){
            $this->ajaxReturn(array('status'=>0,'info'=>'此信用卡优惠信息不存在！'));
        }

        $res = $cc->where(array('id'=>$id))->delete();
        if($res){
            $this->ajaxReturn(array('status'=>1,'info'=>'刪除成功!'));
        }else{
            $this->ajaxReturn(array('status'=>0,'info'=>'刪除失敗！'));
        }
    }
    /**
     * 新用户活动
     */
    public function new_user(){
        $title = I('post.title');
        if($title){
            $sql['news_title'] = array('like','%'.$title.'%');
            $this->assign('title',$title);
        }
        $count= M('new_user')->where($sql)->count();
        $Page  = getpage($count,10);
        $show  = $Page->show();
        $List = M('new_user')->order('sort desc,add_time desc')->where($sql)->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign("page",$show);
        $this->assign('cache',$List);
        $this->display();
    }
    public function new_user_add(){
        $id = intval(I('param.id'));
        $m  = M('new_user');
        $info = $m->find($id);
//        $info['city'] = array_filter(explode(",",$info['city']));
//         $info['city_card'] = array_filter(explode(",",$info['city_card']));

//        $city = I("post.city");
//        foreach($city as $k=>$v){
//            $cityName .= $this->cardChangeName($v).",";
//            $cityCard .= $v.",";
//        }
        if(IS_POST){
            $data=array(
                'news_title'=> I("post.news_title"),
                'detail'    => I("post.detail"),
                'is_hot'    => I("post.is_hot"),
                'sort'      => I("post.sort"),
                'addtime'   => I("post.addtime"),
                'province'  => I("post.province"),
                'is_sale'   => I('post.is_sale'),
                'source'    => I('post.source'),
                'author'    => I('post.author'),
//                'city'      => $cityName,
//                'city_card' => $cityCard,

            );
            $logo_pic= I("post.logo_pic");
            $detail  = I("post.detail");
            if($logo_pic){
                $data['logo_pic']=$logo_pic;
            }
            if($detail){
                $data['detail']=$detail;
            }

            if($id){
                $data['edit_time']=time();
                $res = $m->where(array('id'=>$id))->save($data);
            }else {
                $data['add_time']=time();
                $res = $m->add($data);
            }
            if($res){
                $this->success("操作成功");exit;
            }else{
                $this->success("操作失败");exit;
            }
        }
//        $map = "LevelType=1 or LevelType=0";
//        $provinceList = M("region")->where($map)->select();
//        $this->assign('provinceList',$provinceList);
        $this->assign('cache',$info);
        $this->display();
    }
    public function new_user_del(){
        $id = I('post.id');
        $cc = M('new_user');
        $res = $cc->where(array('id'=>$id))->find();
        if(!$res){
            $this->ajaxReturn(array('status'=>0,'info'=>'此信用卡优惠信息不存在！'));
        }

        $res = $cc->where(array('id'=>$id))->delete();
        if($res){
            $this->ajaxReturn(array('status'=>1,'info'=>'刪除成功!'));
        }else{
            $this->ajaxReturn(array('status'=>0,'info'=>'刪除失敗！'));
        }
    }
    //省市card转名称
    public function cardChangeName($card){
        $name = M("region")->where(array("card"=>$card))->getField('name');
        return $name;
    }

    /**
     * 卡攻略 ljj
     */
    public function strategy(){
//        $news_title = $_GET['news_title'];
//        $this->assign('news_title',$news_title);
//        $data=array();
//        $data['news_title']=array('like', '%'.$news_title.'%');
        $title = I('post.title');
        if($title){
            $sql['news_title'] = array('like','%'.$title.'%');
            $this->assign('title',$title);
        }
        $count=M('strategy')->where($sql)->count();
        $Page  = getpage($count,10);
        $show  = $Page->show();
        $List = M('strategy')->order('sort desc,add_time desc')->where($sql)->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign("page",$show);
        $this->assign('cache',$List);
        $this->display();
    }
    public function strategy_add(){
        $id = intval(I('param.id'));
        $m  = M('strategy');
        $info = $m->find($id);
//        $info['city'] = array_filter(explode(",",$info['city']));
        // $info['city_card'] = array_filter(explode(",",$info['city_card']));

//        $city = I("post.city");
//        foreach($city as $k=>$v){
//            $cityName .= $this->cardChangeName($v).",";
//            $cityCard .= $v.",";
//        }
        if(IS_POST){
            $data=array(
                'news_title'=> I("post.news_title"),
                'detail'    => I("post.detail"),
                'is_hot'    => I("post.is_hot"),
                'sort'      => I("post.sort"),
                'addtime'   => I("post.addtime"),
                'province'  => I("post.province"),
                'is_sale'   => I('post.is_sale'),
                'type'      => I('post.type'),
                'seo'       => I('post.seo'),
                'source'    => I('post.source'),
                'author'    => I('post.author'),
//                'city'      => $cityName,
//                'city_card' => $cityCard,

            );
            $logo_pic= I("post.logo_pic");
            $detail  = I("post.detail");
            if($logo_pic){
                $data['logo_pic']=$logo_pic;
            }
            if($detail){
                $data['detail']=$detail;
            }

            if($id){
                $data['edit_time']=time();
                $res = $m->where(array('id'=>$id))->save($data);
            }else {
                $data['add_time']=time();
                $res = $m->add($data);
            }
            if($res){
                $this->success("操作成功");exit;
            }else{
                $this->success("操作失败");exit;
            }
        }
//        $map = "LevelType=1 or LevelType=0";
//        $provinceList = M("region")->where($map)->select();
//        $this->assign('provinceList',$provinceList);
        $this->assign('cache',$info);
        $this->display();
    }

    public function strategy_del(){
        $id = I('post.id');
        $cc = M('strategy');
        $res = $cc->where(array('id'=>$id))->find();
        if(!$res){
            $this->ajaxReturn(array('status'=>0,'info'=>'此信用卡优惠信息不存在！'));
        }

        $res = $cc->where(array('id'=>$id))->delete();
        if($res){
            $this->ajaxReturn(array('status'=>1,'info'=>'刪除成功!'));
        }else{
            $this->ajaxReturn(array('status'=>0,'info'=>'刪除失敗！'));
        }
    }


    /**
     *
     */
    //多选省市
    public function selCity(){
        if(IS_AJAX){
            $card = I("post.card");
            if($card != 100000){
                $res = M("region")->where(array("parentid"=>$card))->select();
            }
            $str = "";
            if($res){
                foreach($res as $k=>$v){
                    $str .= '<input type="checkbox" name="city[]" class="city" value="'.$v['card'].'"/>'.$v['name']."  ";
                }
                $this->ajaxReturn(array('str'=>$str));
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>'当前没有市区可以选择！'));
            }
        }
    }
	public function keyword(){
		$title = I('get.title');
		if($title){
			$data['title'] = array('like','%'.$title.'%');
			$this->assign('title',$title);
		}
		$keywrod = M('Keyword')->where($data)->select();
		$this->assign('keyword',$keywrod);
		$this->display();
	}
	public function setkey(){
		if(IS_AJAX){
			$data = I('post.');
			if($data['id']){
				$res = M('Keyword')->save($data);
			}else{
				$data['add_time'] = time();
				$res = M('Keyword')->add($data);
			}
			if($res){
				$this->ajaxReturn(array('status'=>1, 'info'=>'操作成功'));
			}else{
				$this->ajaxReturn(array('status'=>0, 'info'=>'操作失败'));
			}
		}
	}
	public function delkey(){
		$id = I('post.id');
		$res = M('Keyword')->delete($id);
		if($res){
			$data['add_time'] = time();
			$this->ajaxReturn(array('status'=>1, 'info'=>'删除成功'));
		}else{
			$this->ajaxReturn(array('status'=>0, 'info'=>'删除失败'));
		}
	}
	
	
	/**
     * 删除银行卡副图*20170714*lq
     */
	public function del_small()
    {
        if(IS_AJAX){
            $id = I("post.id");
            if(empty($id)){
                $this->ajaxReturn(array("status"=>0,"info"=>"请重新删除"));
            }
            $result = M("creditcard")->where(array("id"=>$id))->setField("small_imgs","");
            if($result !== false){
                $this->ajaxReturn(array("status"=>1,"info"=>"删除成功"));
            }else{
                $this->ajaxReturn(array("status"=>0,"info"=>"删除失败"));
            }
        }
    }
	
	
	
	
	
	
	

}