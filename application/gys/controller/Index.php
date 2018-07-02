<?php

namespace app\gys\controller;

class Index extends Indexbase {

	public function index()
	{
		$key = input('get.key');
		$this->assign('key', $key);
		$list = model('goods');
		if ($key)
		{
			$list
				->whereOr('name|search_words', 'LIKE', '%'.$key.'%');
		}
		$list = $list->where('seller_id', $this->_seller['id'])
			->where('is_del', 0)
			->order('create_time desc,id desc')
			->select();
		foreach ($list as &$one)
		{
			$one        = $one->getData();
			$one['img'] = config('img_pathtype')[$one['img_pathtype']].$one['img'];
		}

		$data = array(
			'htitle' => '首页',
		);

		// 已完成和新订单的数量展示
		$overed = model('order')->where('status', '5')->where('seller_id', $this->_seller['id'])->count();
		$new    = model('order')->where('status', '2')->where('seller_id', $this->_seller['id'])->count();
		$this->assign([
			'overed' => $overed,
			'new'    => $new,
		]);

		$this->assign('hdata', $data);
		$this->assign('list', $list);

		return view();
	}

	public function get_order_count()
	{
		// 已完成和新订单的数量展示
		$overed = model('order')->where('status', '5')->where('seller_id', $this->_seller['id'])->count();
		$new    = model('order')->where('status', '2')->where('seller_id', $this->_seller['id'])->count();
		$data   = [
			'overed' => $overed,
			'new'    => $new,
		];
		exit(json_encode($data));
	}
}
