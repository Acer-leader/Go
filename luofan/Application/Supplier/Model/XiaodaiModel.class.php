<?php
namespace Supplier\Model;
use Think\Model;
class XiaodaiModel extends Model{
	function getmemberlist($isexamine=""){
	    $supplier_user=M('supplier');
        $title=I('get.title');
        if(!empty($title)){
            $map['username']=array('like',"%$title%");
            $map['telephone']=array('like',"%$title%");
        }
        if(isset($goodtype)){
            $date['goodstype']=$goodtype;
        }
        $date["isdel"] = 0;
        //$date['id']=array('neq',1);
        //$date['username']=array('neq','admin');
        $count = $supplier_user->where($date)->count();
        //echo $supplier_user->getLastSql();
        $p = getpage($count,8);
        // 增加审核信息：身份证，身份证照片
        $list = $supplier_user->where($date)->order('id desc')->limit($p->firstRow, $p->listRows)->select();
        // dump($list);
        $date['list']=$list; // 赋值数据集
        $date['page']= $p->show();// 赋值分页输出
        $date['count']= $count;
        return $date;

	}

    function login(){
        if(isset($_POST['personname'])){
            $data['telephone'] = $_POST['personname'];
            $data['password'] = md5($_POST['password']);
			$data['isdel']= 0;
            $rs = M('xiaodai')->where($data)->find();
            if($rs){

                if($rs['status']==0){
                    $_SESSION['supplier_name'] = $rs['personname'];
					$_SESSION['supplier_id'] = $rs['id'];
					$data_log=array(
						'last_login' => time(),
					);
					M('xiaodai')->where(array('id'=>$rs['id']))->save($data_log);
					return 1; // 登陆成功
                }
                else{
                    return 2 ; // 禁用
                }
                
            }
            else
            {
                return 0; // 用户名密码错误
            }
        }
    }
    function register(){
        $supplier_user=M('xiaodai');
        $username     = I("post.personname");
        $telephone     = I("post.telephone");
        $password      = I("post.password");
        // 判断手机号是否存在
        $count =$supplier_user->where(array("personname"=>$username))->count();
        if($count){
            return 2;
        }
        if(isset($username)){
            $data['personname'] = $username;
            $data['telephone']=$telephone;
            $data['password'] = md5($password);
            $data['add_time']=time();
            $data['last_login']=time();
            $where['per_name']="未审核小贷公司";
            $cate=M('admin_cate')->where($where)->field('id')->find();
            $data['cate']=$cate['id'];
            $rs=$supplier_user->add($data);
            if($rs){
                $_SESSION['supplier_name'] = $username;
                $_SESSION['supplier_id'] = $supplier_user->getLastInsID();
                $_SESSION['last_login']=time();
                return 1; // 登陆成功
            } else {
                return 0 ; // 失败
            }
        }
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

    function getupdatepass($oldpwd,$newpwd){
        if($newpwd) {
            $oldpwd = md5($oldpwd);
            $md5_pass = md5($newpwd);
            $supplier = M('supplier');
            $count = $supplier->where(array('username' => $_SESSION['supplier_name'], 'password' => $oldpwd))->find();
            if (!$count) {
                $date = 2;
            } else {
                $rs = $supplier->where(array('username' => $_SESSION['supplier_name']))->save(array('password'=>$md5_pass));
                if ($rs) {
                    $date = 1;    //1修改成功，0失败
                } else {
                    $date = 0;
                }
            }
        }
        return $date;

    }

 }

?>