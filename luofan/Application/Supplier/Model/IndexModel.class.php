<?php
namespace Supplier\Model;
use Think\Model;
class IndexModel extends Model{
		function login_outside(){
			if(isset($_GET['username'])){
				$ly_key="5k3hk@8f3_kl";
				$data['username'] = $_GET['username'];
				$getsig = $_GET['sig'];
				$sig = md5($data['username'].$ly_key);
				if($getsig==$sig){
					$rs = $this->where($data)->select();

					if($rs){

						$_SESSION['supplier_name'] = $rs[0]['username'];
						$_SESSION['supplier_id'] = $rs[0]['id'];
						return true;
					}
				}else{
					return false;
				}

			}
		}
	}
 ?>