<?php
namespace Supplier\Model;
use Think\Model;
class SystemModel extends Model{



	function getupdatepass($pass){
        if($pass){
			$md5_pass=md5($pass);
			$rs=$this->where('username='.$_SESSION['supplier_name'])->setField('password',$md5_pass);
			if($rs){
			$date=1;	//1修改成功，0失败
			}else{
				$date=0;
				}
		}else{
			$date=0;
			}
            return $date;

		}
	
	function getecho(){
		echo "123";
		}
	
}

 ?>