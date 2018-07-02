<?php
namespace app\common\model;
use think\Model;

class OrderMarket extends Model
{
	// 详情
	public function getDetails($id)
	{
		$yunfeiChange=model('setconfig')->getVal('freight_rate_value');
		$map['id']=$id;
		$info=model('order_market')->where($map)->find();
		if(!$info){return array('code'=>0,'msg'=>'没有找到相关订单信息！');}
		if($info['freight_id']==0){return array('code'=>0,'msg'=>'没有找到相关订单信息！');}
		// echo '<pre>';var_dump($info);exit;
		
		//查询订单获取没有被取消的
		$otherinfo=model('order')->getorderOtherInfo($info['orderid_str'],'8,11,5,10,6,7',$info['freight_id']);
		// echo '<pre>';var_dump($otherinfo);exit();
		$info['send_time']=(!$otherinfo)?0:$otherinfo['send_time'];
		$info['accept_name']=(!$otherinfo)?'':$otherinfo['accept_name'];
		$info['mobile']=(!$otherinfo)?'':$otherinfo['mobile'];
		$info['accept_time']=(!$otherinfo)?0:$otherinfo['accept_time'];
		$info['address']=(!$otherinfo)?'':$otherinfo['address'];

		// 如果退货退款
		$returnlist=array();
		if($info['status']==6 || $info['status']==7)
		{
			$rw=array();
			$rw['order_id']=array('in',$info['orderid_str']);
			$returnRow=model('Returng')->field('order_id,order_goods_id')->where($rw)->select();

			foreach ($returnRow as $k => $v) {
				$order_goods_id = explode(',', $v['order_goods_id']);
				$returnlist[$v['order_id']]=$order_goods_id;
			}
		}

		// echo '<pre>';var_dump($info);exit;
		if($otherinfo)
		{
			$goodsdata=model('order_goods')->goodsdata($otherinfo['orderid_str'],$returnlist);
			// echo '<pre>';var_dump($goodsdata);exit();
			$info['goodslist']=(!$goodsdata)?array():$goodsdata['list'];
			$info['weight']=(!$goodsdata)?0:$goodsdata['weight'];
			$info['count']=(!$goodsdata)?0:$goodsdata['count'];
		}else{
			$info['goodslist']=array();
			$info['weight']=0;
			$info['count']=0;
		}
		$juli=$info['juli']/1000;
		$weight=$info['weight']/1000;

		$info['juli']=$juli;
		$info['weight']=$weight;

		$info['yunfei']=round($yunfeiChange*$juli,2)*$weight;


		//获取市场地址
		$address=model('market')->where('id',$info['market_id'])->value('address');
		$info['fh_address']=(!$address)?'':$address;

		//获取特派员信息
		$uinfo=model('freight')->where('id',$info['freight_id'])->column('id,name,telphone');
		// var_dump($uinfo);exit();
		if($info['freight_id']>0)
		{
			$info['fh_name']=$uinfo[$info['freight_id']]['name'];
			$info['fh_telphone']=$uinfo[$info['freight_id']]['telphone'];
		}else{
			$info['fh_name']='';
			$info['fh_telphone']='';
		}

		$info['code']=1;
		return $info;
	}

	// 特派员市场订单列表
	public function getlist($uid,$map=array())
	{
		$yunfeiChange=model('setconfig')->getVal('freight_rate_value');
		$map['freight_id']=$uid;
		$list=$this->where($map)->order('id desc')->select();
		// echo $this->getLastSql();exit();

		foreach ($list as $k => $v) {
			
			// $list[$k]['weight'] = round(($v['weight']/1000),2);
			// 获取当前市场下的区域名称
			$address=model('order')->getUserOrder($v['orderid_str'],8);

			$list[$k]['address'] = $address['address'];
			$list[$k]['accept_time'] = $address['accept_time'];


			$goodsdata=model('order_goods')->goodsdata($v['orderid_str']);
			// echo '<pre>';var_dump($goodsdata);
			$list[$k]['goodslist']=$goodsdata['list'];

			$juli=round(($v['juli']/1000),2);
			$list[$k]['juli'] = $juli;

			$weight=$goodsdata['weight']/1000;
			$list[$k]['weight']=$weight;
			// 对运费进行计算  获取运费值
			$list[$k]['yunfei']=round($yunfeiChange*$juli,2)*$weight;
			
			
			
			// var_dump($goodsdata['img']);
			// $list[$k]['img']=config('img_pathtype')[$goodsdata['img_pathtype']].$goodsdata['img'];

			$status=0;
			/**
			 * 立即抢单 0
			 * 抢单成功	1
			 * 订单被抢	2
			 */
			if($uid<=0)
			{
				//无登录
				if($v['freight_id'])
				{
					//订单被抢
					$status=2;
				}
			}else{
				//已登录
				if($v['freight_id']>0)
				{
					if($uid==$v['freight_id'])
					{
						//抢单成功
						$status=1;
					}else{
						//订单被抢
						$status=2;
					}
				}
			}
			$list[$k]['qd_status']=$status;
		}
		return $list;
	}

	// 获取可抢市场订单
	public function getorderlist($id, $order, $uid, $status_type)
	{
		$yunfeiChange=model('setconfig')->getVal('freight_rate_value');
		$map                   = array();
		$map['market_id']      = array('in', $id);
		$map['status']         = 9;//已发货
		$map['pay_status']     = 1;//已付款
		$map['status_type']    = ['in', $status_type];
		$map['freight_status'] = 0;
		$fields='*';

		// $fields                = 'id,order_no,create_time,accept_time,real_freight,telphone,address,juli,status,freight_id,freight_status';
		$result = model('order_market')->field($fields)->where($map)->order($order)->select();
		// echo model('order')->getLastSql();exit();
		//获取商品总重量
		foreach ($result as $k => $v)
		{
			

			// 获取当前市场下的区域信息
			$address=model('order')->getUserOrder($v['orderid_str'],8);
			// echo '<pre>';var_dump($address['address']);exit();
			$result[$k]['address'] = $address['address'];
			$result[$k]['accept_time'] = $address['accept_time'];

			// $data = model('order_goods')
			// 	->field('sum((goods_nums*goods_weight)) as weight')
			// 	->where('order_id', $v['orderid_str'])
			// 	->group('order_id')
			// 	->select();

			$join = [
			    ['iwebshop_order o','o.id=og.order_id','left'],
			];
			$fields='sum((og.goods_nums*og.goods_weight)) as weight';
			$map=array();
			$map['og.order_id']=array('in',$v['orderid_str']);
			$map['o.status']=array('neq','12,3,4');
			$data=model('order_goods')->alias('og')->field($fields)->where($map)->join($join)->group('order_id')->select();
			// echo model('order_goods')->getLastSql();exit;
			$juli=$v['juli']/1000;
			$weight=0;
			if($data)
			{
				foreach ($data as $key => $value) {
					$weight+=$value['weight'];
				}
			}
			$weight=$weight/1000;

			$result[$k]['juli'] = $juli;
			$result[$k]['weight'] = $weight;
			$result[$k]['yunfei'] = round(round($yunfeiChange*$juli,2)*$weight,2);

			// echo model('order_goods')->getLastSql();exit();
			// $juli=round(($v['juli']/1000),2);
			// $list[$k]['juli'] = $juli;

			// $weight=$goodsdata['weight']/1000;
			// $list[$k]['weight']=$weight;
			// // 对运费进行计算  获取运费值
			// $list[$k]['yunfei']=round($yunfeiChange*$juli,2)*$weight;

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

	// 改变市场订单以及订单状态
	public function idUpdate($omid,$uid,$oidstr,$statue=1)
	{
		// 改变市场订单
		$data=array('freight_id'=>$uid,'freight_status'=>$statue);
		$data=array('freight_id'=>$uid,'freight_status'=>$statue);
		$newtime=date("Y-m-d H:i:s");

		if($statue==1){$data['qiangdan_time']=$newtime;}
		if($statue==2){$data['send_time']=$newtime;}
		if($statue==3){$data['delivered_time']=$newtime;}

		$state=$this->where('id',$omid)->update($data);
		if($statue==1){unset($data['qiangdan_time']);}
		if($statue==3){unset($data['delivered_time']);}
		if($state)
		{
			$map=array();
			$map['id']=array('in',$oidstr);
			$map['status']=8;
			// if($statue==2)
			// {
			// 	$data['send_time']=date("Y-m-d H:i:s");//发货时间
			// }
			$status2=model('order')->where($map)->update($data);
			if($status2)
			{
				if($statue==1)
				{
					// 修改订单商品状态值
					$w=array();
					$w['order_id']=array('in',$oidstr);
					$w['is_send']=0;
					model('order_goods')->where($w)->update(array('is_send'=>1));
				}
				return 1;
			}else{
				return 2;//订单更新失败
			}
		}else{
			return 2;//市场订单更新失败
		}

	}

	// 判断是否超重
	public function getCarload($uid,$oid,$carload)
	{
		$map=array();
		$map['freight_status']=['not in','0,3'];
		$map['freight_id']=$uid;

		$cur_carload=$this->where($map)->sum('weight');
		//对重量进行处理(g->kg)
		$cur_carload=$cur_carload/1000;
		// echo $cur_carload;
		//echo $this->getLastSql();exit;

        if($cur_carload > $carload)
        {
        	return 0;
        }else{
        	return 1;
        }

	}
}
