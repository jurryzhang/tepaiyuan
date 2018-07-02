<?php

namespace app\gys\controller;

use think\Controller;
use Think\Db;
use think\Loader;
use think\Cookie;
use think\Session;

class Indexbase extends Controller {

	public $controller;
	public $module;
	public $action;
	protected $_seller;

	public function __construct()
	{
		parent::__construct();
		$request          = \think\Request::instance();
		$this->controller = $request->controller();
		$this->module     = $request->module();
		$this->action     = $request->action();
		// 不需要强制登录的类和方法
		$except_action = [
			'login',
			'reg',
			'reg1',
			'reg2',
			'reg3',
			'reg_act',
			'mobilesend',
			'forgetpwd',
			'reg_yajin',
		];
		if ( ! ($this->controller == 'User'
			&& in_array($this->action, $except_action)))
		{
			if (Session::get('sellerinfo'))
			{
				$uinfo         = Session::get('sellerinfo');
				$w['id']       = $uinfo['id'];
				$uinfo         = model('seller')->where($w)->find();
				$this->_seller = $uinfo;
				$this->assign('info', $uinfo);
			}
			else
			{
				$this->error('需要登录', url('user/login'));
			}
		}
		$this->assign('c', $this->controller);

		$data = array('htitle' => '供应商');
		$this->assign('hdata', $data);

		switch ($this->controller)
		{
			case 'User':
				$foot = 4;
				break;
			case 'Order':
				$foot = 3;
				break;
			case 'Product':
				$foot = 2;
				break;
			default:
				$foot = 1;
				break;
		}
		$this->assign('foot', $foot);

		$this->auto_refuse_order();
	}

	// 检测商家5分钟不接单就自动拒绝接单
	public function auto_refuse_order()
	{
		$time   = date('Y-m-d H:i:s', time() - 60 * 5);
		$where  = [
			'pay_time'   => ['lt', $time],
			'pay_status' => 1,
			'status'     => 2,
		];
		$orders = model('order')->where($where)->select();

		if (empty($orders))
		{
			return FALSE;
		}

		$orders = get_collection($orders);
		foreach ($orders as $order)
		{
			$this->refuse_order($order['id'], 0);

			$order_goods = model('order_goods')->where(['order_id' => $order['id']])->field('id')->select();
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

		return TRUE;
	}

	public function refuse_order($id, $check)
	{
		$order = model('order')->find($id);
		if ($order['status'] != 2 || $order['pay_status'] != 1 || $order['freight_id'] != 0
			|| $order['freight_status'] != 0)
		{
			return FALSE;
			$this->error('该订单不能确认');
		}

		$where        = [
			'orderid_str' => [
				['LIKE', "%,$id"],
				['LIKE', "$id,%"],
				['LIKE', "%,$id,%"],
				$id,
				'or',
			],
		];
		$order_market = model('order_market')->where($where)->find();

		if ( ! in_array($order_market['status'], [2, 8]) || $order_market['pay_status'] != 1
			|| $order_market['freight_id'] != 0
			|| $order_market['freight_status'] != 0)
		{
			return FALSE;
			$this->error('该订单不能确认');
		}

		$order_str = $order_market['orderid_str'];
		$order_arr = explode(',', $order_str);
		$status    = $check ? 8 : 12;  // 8是确认订单， 12 是拒绝接单
		$order->save(['status' => $status, 'check_time' => date('Y-m-d H:i:s')]);
		$order_count = count($order_arr);
		if ($order_count == 1)
		{
			$data           = [];
			$data['status'] = $check ? 9 : 12;
			if ($check)
			{
				$data['seller_confirmation_time'] = date('Y-m-d H:i:s');
				$data['seller_finish_time']       = date('Y-m-d H:i:s');
			}

			$order_market->save($data);
		}
		else
		{
			$check_count = 0;
			$statuses    = [];
			foreach ($order_arr as $item)
			{
				$now_order = model('order')->find($item);
				if (in_array($now_order['status'], [8, 12, 3, 4]))
				{
					$statuses[] = $now_order['status'];
					$check_count ++;
				}
			}
			if ($check_count == 1)
			{
				if ($statuses[0] == 8)
				{
					$order_market->save(['status' => 8, 'seller_confirmation_time' => date('Y-m-d H:i:s')]);
				}
			}
			elseif ($check_count == $order_count)
			{
				if (in_array('8', $statuses))
				{
					$order_market_status = '9';
				}
				elseif (in_array('4', $statuses))
				{
					$order_market_status = '4';
				}
				elseif (in_array('3', $statuses))
				{
					$order_market_status = '3';
				}
				else
				{
					$order_market_status = '12';
				}
				$data           = [];
				$data['status'] = $order_market_status;
				if ($order_market_status == 9)
				{
					$data['seller_finish_time'] = date('Y-m-d H:i:s');
				}
				$order_market->save($data);
			}
		}

		return TRUE;
	}
}
