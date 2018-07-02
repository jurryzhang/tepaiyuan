<?php

namespace app\common\model;

use think\Model;

class Market extends Model {

	/**
	 * [getNum description]
	 *
	 * @param  integer $uid         [当前登录会员id]
	 * @param  integer $status_type [状态类型 0_即时订单 1_3小时订单  2_6小时订单]
	 *
	 * @return [type]               [description]
	 */
	public function getNum($uid = 0, $status_type = '0,1,2')
	{
		if ($uid > 0)
		{
			$dingwei = $this->getdingwei($uid);
		}
		else
		{
			$dingwei = [];
		}
		$marketlist = $this->getlist($dingwei);//获取当前区域中所有市场列表

		$markerid_str = data_getstr($marketlist, 'id');
		// var_dump($markerid_str);exit();
		$map2                   = array();
		$map2['market_id']      = array('in', $markerid_str);
		$map2['status']         = 9;//已发货
		$map2['pay_status']     = 1;//已付款
		$map2['status_type']    = ['in', $status_type];
		$map2['freight_status'] = 0;
		$result                 = model('order_market')
			->field('market_id,count(market_id) as num')
			->where($map2)
			->group('market_id')
			->select();
		// echo model('order')->getLastSql();exit();
		//将当前数组进行重组
		$result = get_newData($result, 'market_id', 'num');
		// var_dump($result);
		// exit;
		foreach ($marketlist as $key => $val)
		{
			if (array_key_exists($val['id'], $result))
			{
				$marketlist[$key]['num'] = $result[$val['id']];
			}
			else
			{
				$marketlist[$key]['num'] = 0;
			}
		}

		// var_dump($marketlist);exit;
		return $marketlist;
	}

	//获取指定字段值
	public function getFileData($field)
	{
		$result = $this->where($map)->value($field);

		return $result;
	}
/*********************** 增加 *************************/
	public function getorderlist($id, $order, $uid, $status_type)
	{
		$map                   = array();
		$map['market_id']      = array('in', $id);
		$map['status']         = 2;//已发货
		$map['pay_status']     = 1;//已付款
		$map['status_type']    = ['in', $status_type];
		$map['freight_status'] = 0;
		$fields                = 'id,order_no,create_time,accept_time,real_freight,telphone,address,juli,status,freight_id,freight_status';
		$result                = model('order')->field($fields)->where($map)->order($order)->select();
		// echo model('order')->getLastSql();exit();
		//获取商品总重量
		foreach ($result as $k => $v)
		{
			// SELECT *,sum((goods_nums*goods_weight)) as weight FROM `iwebshop_order_goods` GROUP BY order_id;
			$data = model('order_goods')
				->field('sum((goods_nums*goods_weight)) as weight')
				->where('order_id', $v['id'])
				->group('order_id')
				->select();
			// echo model('order_goods')->getLastSql().'<br>';
			// echo '<pre>';var_dump($data);
			if ($data)
			{
				$result[$k]['weight'] = $data[0]['weight'];
			}
			else
			{
				$result[$k]['weight'] = 0;
			}

			$status = 0;
			/**
			 * 立即抢单 0
			 * 抢单成功    1
			 * 订单被抢    2
			 */
			if ($uid <= 0)
			{
				//无登录
				if ($v['freight_id'])
				{
					//订单被抢
					$status = 2;
				}
			}
			else
			{
				//已登录
				if ($v['freight_id'] > 0)
				{
					if ($uid == $v['freight_id'])
					{
						//抢单成功
						$status = 1;
					}
					else
					{
						//订单被抢
						$status = 2;
					}
				}
			}
			$result[$k]['qd_status'] = $status;
		}
		// echo '<pre>';
		// var_dump($result);exit;
		return $result;
	}
	/*********************** 增加 end *************************/

	public function getlist($dingwei, $id = 0)
	{
		$map           = array();
		$map['status'] = 1;
		if ( ! empty($dingwei))
		{
			if ($dingwei['province'] != '0')
			{
				$map['province'] = $dingwei['province'];
			}
			if ($dingwei['city'] != '0')
			{
				$map['city'] = $dingwei['city'];
			}
			if ($dingwei['district'] != '0')
			{
				$map['district'] = $dingwei['district'];
			}
			if ($dingwei['street'] != '0')
			{
				$map['street'] = $dingwei['street'];
			}
		}
		if ($id > 0)
		{
			$map['id']  = $id;
			$marketlist = $this->where($map)->find();
		}
		else
		{
			$marketlist = $this->where($map)->select();
		}

		return $marketlist;
	}

	public function curMarketStr($uid, $mid = 0)
	{
		if ($mid == 0)
		{
			if ($uid > 0)
			{
				$dingwei = $this->getdingwei($uid);
			}
			else
			{
				$dingwei = [];
			}
			$map           = array();
			$map['status'] = 1;
			if ( ! empty($dingwei))
			{
				if ($dingwei['province'] != '0')
				{
					$map['province'] = $dingwei['province'];
				}
				if ($dingwei['city'] != '0')
				{
					$map['city'] = $dingwei['city'];
				}
				if ($dingwei['district'] != '0')
				{
					$map['district'] = $dingwei['district'];
				}
				if ($dingwei['street'] != '0')
				{
					$map['street'] = $dingwei['street'];
				}
			}
		}
		else
		{
			$map           = array();
			$map['status'] = 1;
			$map['id']     = $mid;
		}
		$marketlist     = $this->field('id,name')->where($map)->select();
		$markerid_str   = data_getstr($marketlist, 'id');
		$list           = get_newData($marketlist, 'id', 'name');
		$market         = array();
		$market['list'] = $list;
		$market['str']  = $markerid_str;

		return $market;
	}

	public function allList($city = 0)
	{
		$map = array();
		if ($city != 0)
		{
			$map['city'] = $city;
		}
		$map['status'] = 1;
		$list          = $this->where($map)->field('id,name')->select();

		return $list;
	}

	public function getdingwei($uid = 0)
	{
		$dingwei = model('freight')->field('province,city,district,street')->where('id', $uid)->select();
		if($dingwei)
		{
			$dingwei = $dingwei[0];
		}else{
			$dingwei=array();
		}

		return $dingwei;
	}

}
