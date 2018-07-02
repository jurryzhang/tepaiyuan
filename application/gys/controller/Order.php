<?php

namespace app\gys\controller;

//特派员订单类
class Order extends Indexbase {

	public function index()
	{
		$list = model('order');

		$status = input('param.status');
		if ( ! in_array($status, ['0', '2', '8,11', '5,10', '3,4', '6,7']))
		{
			$status = '0';
		}
		$this->assign('status', $status);
		if ($status)
		{
			$status = explode(',', $status);
			$list->where('status', 'in', $status);
		}

		$list = $list->where('seller_id', $this->_seller['id'])->order('id desc')->select();
		foreach ($list as &$one)
		{
			$one                  = $one->getData();
			$id                   = $one['id'];
			$where                = [
				'orderid_str' => [
					['LIKE', "%,$id"],
					['LIKE', "$id,%"],
					['LIKE', "%,$id,%"],
					$id,
					'or',
				],
			];
			$order_market         = model('order_market')->where($where)->find();
			$one['qiangdan_time'] = $order_market['qiangdan_time'];

			if ('0000-00-00 00:00:00' == $one['songda_time'])
			{
				$one['songda_time'] = '尽快配送中';
			}
			if ($one['freight_id'])
			{
				switch ($one['freight_status'])
				{
					//0尚未抢单 1已接单 2已取货 3已送达 4订单完成
					case 0:
						$one['freight_status'] = '尚未抢单';
						break;
					case 1:
						$one['freight_status'] = '已接单';
						break;
					case 2:
						$one['freight_status'] = '已取货';
						break;
					case 3:
						$one['freight_status'] = '已送达';
						break;
					case 4:
						$one['freight_status'] = '订单完成';
						break;
					default:
						$one['freight_status'] = '未知';
						break;
				}
				$freight                  = model('freight')->find($one['freight_id'])->getData();
				$one['freight_plate_num'] = $freight['plate_num'];
				$one['freight_name']      = $freight['name'];
				$one['freight_telphone']  = $freight['telphone'];
				$one['freight_landline']  = $freight['landline'];
			}
			$goods = model('order_goods')->where('order_id', $one['id'])->select();

			$return         = model('returng')->where('order_id', $one['id'])->find();
			$order_goods_id = [];
			// 是否需要商家处理申请退货信息
			$one['is_refund']      = FALSE;
			$one['return_content'] = '';
			if ($return)
			{
				$order_goods_id = explode(',', $return['order_goods_id']);
				if ($return['status'] == 0)
				{
					$one['is_refund'] = TRUE;
					$one['return_id'] = $return['id'];
				}
				if ( ! isset($return['return_type']))
				{
					$return['return_type'] = 0;
				}
				switch ($return['return_type'])
				{
					case 1:
						$e = '运输问题';
						break;
					case 2:
						$e = '货品质量问题';
						break;
					default:
						$e = '未知原因';
				}
				$one['return_content'] = $e.'--'.$return['content'];
			}

			foreach ($goods as &$good)
			{
				$good              = $good->getData();
				$good['is_refund'] = FALSE;
				// 全部退货
				if ($one['status'] == 6)
				{
					$good['is_refund'] = TRUE;
				}
				// 部分退货
				elseif ($one['status'] == 7)
				{
					if (in_array($good['goods_id'], $order_goods_id))
					{
						$good['is_refund'] = TRUE;
					}
				}
				$tmp          = json_decode($good['goods_array'], TRUE);
				if (is_array($tmp))
				{
					$name    = isset($tmp['name']) ? $tmp['name'] : '';
					$goodsno = isset($tmp['goodsno']) ? $tmp['goodsno'] : '';
					$tmp     = $name.'--'.$goodsno;
				}
				$good['img']         = config('img_pathtype')[$good['img_pathtype']].$good['img'];
				$good['goods_array'] = $tmp;

				$good['unit'] = '';
				$real_good    = model('goods')->find($good['goods_id']);
				if ( ! $real_good)
				{
					continue;
				}
				$real_good = $real_good->getData();
				if ( ! $real_good)
				{
					continue;
				}
				$good['unit'] = $real_good['unit'];
			}
			$one['goods'] = $goods;
		}

		$this->assign('list', $list);

		return view();
	}

	// 确认订单
	public function check()
	{
		$id    = input('param.id');
		$check = input('param.check');
		if ( ! $this->refuse_order($id, $check))
		{
			$this->error('该订单不能确认');
		}
		// 拒绝订单的时候要设置退货单
		if ( ! $check)
		{
			$order       = model('order')->find($id)->getData();
			$order_goods = model('order_goods')->where(['order_id' => $id])->field('id')->select();
			$order_goods = get_collection($order_goods);
			$order_goods = array_column($order_goods, 'id');

			$order_goods_id = implode(',', $order_goods);
			if ($order)
			{
				model('refund')->save([
					'order_no'       => $order['order_no'],
					'order_id'       => $order['id'],
					'user_id'        => $order['user_id'],
					'amount'         => $order['order_amount'],
					'time'           => date('Y-m-d H:i:s'),
					'pay_status'     => '0',
					'content'        => '商家拒绝接单自动退款',
					'if_del'         => '0',
					'order_goods_id' => $order_goods_id,
					'seller_id'      => $order['seller_id'],
				]);
			}
		}

		$msg = $check ? '确认成功' : '拒绝接单';
		$this->success($msg, url('index'));
	}

	// 确认退货
	public function check_return()
	{
		$id        = input('param.id');
		$status    = input('param.status');
		$return_id = input('param.return_id');
		$return    = model('returng')->find($return_id);
		if ( ! $return)
		{
			$this->error('不存在退货信息');
		}
		if ($return['status'] != 0)
		{
			$this->error('该退货已经处理过了');
		}
		if ( ! in_array($status, ['1', '2']))
		{
			$this->error('处理状态不正确');
		}
		$data = ['status' => $status];
		if ($status == '2')
		{
			$return_amount = input('post.amount');
			if ($return_amount > $return['amount'])
			{
				$this->error('确认退款额度超过订单额度');
			}
			$data['return_amount'] = $return_amount;
		}
		$return->save($data);
		$this->success('处理退货信息完成');
	}
}
