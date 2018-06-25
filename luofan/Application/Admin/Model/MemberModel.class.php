<?php
namespace Admin\Model;
use Think\Model;
class MemberModel extends Model{
	function getmemberlist($isexamine=""){
        $title=I('get.title');
        if(!empty($title)){
            $map['person_name']=array('like',"%$title%");
            $map['telephone']=array('like',"%$title%");
            $map['_logic'] = 'or';
            $date['_complex'] = $map;
        }
        $date["isdel"] = 0;
        $date["status"] = 0;
		$date["telephone"]=array("neq","");
        $count = $this->where($date)->count();
        $p = getpage($count,10);
        $list = $this->where($date)->order('id desc')->limit($p->firstRow, $p->listRows)->select();
        $date['list']=$list; // 赋值数据集
        $date['page']= $p->show();// 赋值分页输出
        $date['count']= $count;
        return $date;

	}
	
	function getmember(){
        if(isset($goodtype)){
            $date['goodstype']=$goodtype;
        }
        $title=I('get.title');
        $classid=I('get.dengji');
        if(!empty($goodtype)){
            $date['goodstype']=$goodtype;
        }
        if(!empty($title)){
            $date['telephone']=array('like',"%$title%");
        }
        if(!empty($classid)){
            $date['dengji']=$classid;
        }
			$date['is_fenxiao']=1;


        $count = $this->where($date)->count();
        $p = getpage($count,10);
        $list = $this->field(true)->where($date)->order('id desc')->limit($p->firstRow, $p->listRows)->select();

		
        $date['list']=$list; // 赋值数据集
        $date['page']= $p->show();// 赋值分页输出
        $date['count']= $count;

        return $date;

    }
	
	public function getHr(){
        if(I("title")){
            $title                   = I("title");
            $where["telephone"]      = array("like","%$title%");
            $where["c.company_name"] = array("like","%$title%");
            $where["_logic"]         = "or";
            $map['_complex']         = $where;
        }
        $DB_PREFIX       = C("DB_PREFIX");
        $map["m.type"]   = 1;
        $map["c.isdel"]  = 0;
        $map["m.isdel"]  = 0;
        $join_str = "inner join {$DB_PREFIX}company as c on m.companyid=c.id";
        $cache    = $this->alias("m")->where($map)->join($join_str)->
            field("m.id,c.company_name,m.person_name,m.status,m.addtime,
                   m.telephone,c.recruitment_nums,c.address,c.type")->
            order("addtime desc")->select();

        $count    = $this->alias("m")->where($map)->join($join_str)->count();
        $p        = getpage($count,10);
        $date['cache']  = $cache; // 赋值数据集
        $date['page']   = $p->show();// 赋值分页输出
        return $date;
    }
	/**
     *根据获取用户信息上级分销商
     * @param  $arr 用户数组
     * @author Chandler_qjw  ^_^
     */
	
	
	public function getMemberSuperior($redate){
		
		foreach($redate['list'] as $k=>$v){
			$sup=$this->where('id='.$v['fid'])->find(); //获取上级分销商
			$redate['list'][$k]['superior']=$sup['telephone']; //重组数组
			}
		$arrs=$redate;
		
		return $arrs;
		}
		

	

    /**
     *根据ID获取用户信息
     * @param  $uid 用户ID
     * @author Chandler_qjw  ^_^
     */
    public function GetInfomation($uid){
        $where = array( //条件数组
            'id' => $uid,
        );
        $rs = $this->where($where)->find(); //查询， 用find()只能查出一条数据，select()多条
        return $rs;
    }

    //获取所有会员
    public function memberGetIsZhen(){
        $count = $this->count();
        $p = getpage($count,10);
        $list = $this->alias('a')->field("a.id,a.telephone,a.truename,a.dengji,a.touimg,a.is_zhen,a.addtime")
            ->limit($p->firstRow, $p->listRows)->order('a.id desc')->select();
        $date['list']=$list; // 赋值数据集
        $date['page']= $p->show();// 赋值分页输出
        $date['count']= $count;
        return $date;
    }
    //获取某个提交实名认证的会员
    public function memberGetIsZhenUser($id){
        return $this->alias('a')->field("a.id,a.telephone,a.truename,a.dengji,a.touimg,a.is_zhen,b.addtime,b.updatetime,b.pic")
            ->join("left join yd_verified b on a.id=b.memberid")->where("a.id=%d",$id)->find();
    }
    //修改会员的认证状态
    public function upMemberIsZhen($id,$status){
        return $this->where("id=%d",$id)->setField('is_zhen',$status);
    }


  

     //获取好友
    function getfriend(){
            $id = I('id');
            $map['isdel'] = 0;
            $map['userid1'] = $id;
            $arr_id = M('member_friend')->where($map)->field('userid2')->select();
            $list = array();
            $date = array();
            if($arr_id){
                foreach ($arr_id as $key => $value) {
                    $date["isdel"] = 0;
                    $date['id']=$value['userid2'];
                    $count = $this->where($date)->count();
                    $list[] = $this->field("id,realname,person_img,telephone,vip,status,sex,wallet,addtime,evaluate")->where($date)->find();
                }
            }
           
            $friend['list']=$list; // 赋值数据集
            $friend['count']= $count;
            return $friend;

    }

    //获取签到日志

    function getsign(){
        if(I("title")){
            $title                   = I("title");
            $where["telephone"]      = array("like","%$title%");
            $where["realname"] = array("like","%$title%");
            $where["_logic"]         = "or";
            $map['_complex']         = $where;
        }
        $DB_PREFIX       = C("DB_PREFIX");
        $map["m.isdel"]  = 0;
        $map["s.isdel"]  = 0;
        $join_str = "inner join {$DB_PREFIX}sign as s on s.userid=m.id";
        $cache    = $this->alias("m")->where($map)->join($join_str)->
            field("s.id,realname,telephone,s.addtime,s.userid,vip")->
            order("addtime desc")->select();
        $count    = $this->alias("m")->where($map)->join($join_str)->count();
        $p        = getpage($count,10);
        $date['cache']  = $cache; // 赋值数据集
        $date['page']   = $p->show();// 赋值分页输出
        $date['count']= $count;
        return $date;

    }


	function getonememberdetail($mid){
        $date['id']=$mid;
        $rs=$this->where($date)->find();
        return $rs;
    }
 }

?>