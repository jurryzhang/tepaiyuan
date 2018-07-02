<?php
namespace app\common\model;
use think\Model;

class Order extends Model
{
	public function index()
	{}

	public function getorderOtherInfo($oidstr,$status,$freight_id)
	{
		$data=array();
		$w=array();
		$w['id']=array('in',$oidstr);
		$w['status']=array('in',$status);
		$w['freight_id']=$freight_id;
		$list=model('order')->field('id,send_time,accept_name,mobile,accept_time,address')->where($w)->order('id desc')->select();
		// echo model('order')->getLastSql();exit();
		// echo '<pre>';var_dump($list);
		if($list)
		{
			if(count($list)>1)
			{
				$orderid_str='';
				foreach ($list as $k => $v) {
					$orderid_str.=$v['id'].',';
					if($k==0)
					{
						$data['send_time']=$v['send_time'];
						$data['accept_name']=$v['accept_name'];
						$data['mobile']=$v['mobile'];
						$data['accept_time']=$v['accept_time'];
						$data['address']=$v['address'];
					}
				}
				$orderid_str=trim($orderid_str,',');
				
			}else{
				if($list[0])
				{
					$orderid_str=$list[0]['id'];
					$data['send_time']=$list[0]['send_time'];
					$data['accept_name']=$list[0]['accept_name'];
					$data['mobile']=$list[0]['mobile'];
					$data['accept_time']=$list[0]['accept_time'];
					$data['address']=$list[0]['address'];
				}
			}
			$data['orderid_str']=$orderid_str;
		}

		return $data;
	}

	public function getUserOrder($oidStr,$status)
	{
		$addw=array();
		$addw['id']=array('in',$oidStr);
		$addw['status']=$status;
		$address=model('order')->field('address,accept_time')->where($addw)->order('id desc')->find();
		return $address;
	}

}
