<?php

namespace app\gys\controller;

class Product extends Indexbase {

	public function index()
	{
		$data = array('htitle' => '产品');
		$this->assign('hdata', $data);

		// 产品分类
		$cates = model('category')->order('parent_id asc,sort asc')->select();
		$list  = [];
		foreach ($cates as $v)
		{
			$list[$v['id']] = $v->getData();
		}
		$list = sortdata($list, 0, '--');
		$this->assign('cates', $list);

		$key  = input('get.key');
		$type = input('get.type');
		$sort = input('get.sort');
		$this->assign([
			'key'  => $key,
			'type' => $type,
			'sort' => $sort,
		]);
		$list = model('goods');

		if ($key)
		{
			$list
				->whereOr('name|search_words', 'LIKE', '%'.$key.'%');
		}
		if ($type)
		{
			$type = model('category')->find($type);
			if ($type)
			{
				$ids = [$type['id']];
				if ($type['parent_id'] == 0)
				{
					$children = model('category')->where('parent_id', $type['id'])->select();
					$children = array_map(function ($one) {
						return $one->getData();
					}, $children);
					$ids      = array_merge($ids, array_column($children, 'id'));
				}
				$cates     = model('category_extend')->where('category_id', 'IN', $ids)->select();
				$cates     = array_map(function ($one) {
					return $one->getData();
				}, $cates);
				$goods_ids = array_column($cates, 'goods_id');

				$list->where('id', 'IN', $goods_ids);
			}
		}

		$list = $list->where('seller_id', $this->_seller['id'])->where('is_del', 0);

		if ( ! in_array($sort, ['1', '2', '3']))
		{
			$sort = 0;
		}
		switch ($sort)
		{
			case 1:
				$list->order('sale desc');
				break;
			case 2:
				$list->order('sell_price asc');
				break;
			case 3:
				$list->order('sell_price desc');
				break;
			default:
				$list->order('create_time desc,id desc');
				break;
		}
		$list = $list->select();

		foreach ($list as &$one)
		{
			$one        = $one->getData();
			$one['img'] = config('img_pathtype')[$one['img_pathtype']].$one['img'];
		}

		$this->assign('goods', $list);

		return view();
	}

	public function add()
	{
		if ($this->request->isPost())
		{
			$data = $this->request->post();

			if ( ! (isset($data['name'], $data['cate_id'], $data['store_nums'], $data['market_price'], $data['sell_price'], $data['cost_price'], $data['weight'], $_FILES['img'])
				&& $data['name']
				&& $data['cate_id']
				&& $data['store_nums']
				&& $data['market_price']
				&& $data['sell_price']
				&& $data['cost_price']
				&& $data['weight']
				&& $_FILES['img']['size'][0]
			))
			{
				$this->error('缺少字段：商品名称，所属分类，库存，市场价格，销售价格，成本价格，重量，图片');
			}
			if (model('goods')->where(['name' => $data['name']])->find())
			{
				$this->error('该商品名已经存在');
			}

			$goods_attr = [];
			// 处理模型里面的扩展属性字段
			foreach ($data as $key => $val)
			{
				//数据过滤分组
				if (strpos($key, 'attr_id_') !== FALSE)
				{
					$goods_attr[ltrim($key, 'attr_id_')] = $val;
					unset($data[$key]);
				}
			}

			$data['seller_id']   = $this->_seller['id'];
			$data['create_time'] = date('Y-m-d H:i:s');
			$data['goods_no']    = config('goods_no_pre').time().rand(10, 99);
			// 分类
			$cate_id = $data['cate_id'];
			$cate    = model('category')->find($cate_id);

			if ( ! $cate)
			{

				$this->error('没有这个分类');
			}
			$cate_arr = $cate->getData();
			unset($data['cate_id']);

			// 上传图片
			$imgs = request()->file('img');
			$urls = [];

			$first = '';
			foreach ($imgs as $img)
			{
				$info = $img->move(config('imguploads')['rootPath']);
				if (empty($first))
				{
					$first = '\\'.config('imguploads')['rootPath'].DS.$info->getSaveName();
					$first = strtr($first, '\\', '/');
				}

				$urls[] = [
					'url' => strtr('\\'.config('imguploads')['rootPath'].DS.$info->getSaveName(), '\\', '/'),
					'md5' => md5_file($info->getRealPath()),
				];
			}

			if ($first)
			{
				$data['img']          = $first;
				$data['img_pathtype'] = '1';
			}
			$data['is_del'] = '0';
			// 保存商品
			model('goods')->save($data);
			$goods_id = model('goods')->getLastInsID();

			// 增加商品属性
			if ($goods_attr)
			{
				if ($data['model_id'] > 0 && isset($goods_attr) && $goods_attr)
				{
					foreach ($goods_attr as $key => $val)
					{
						$attrData = array(
							'goods_id'        => $goods_id,
							'model_id'        => $data['model_id'],
							'attribute_id'    => $key,
							'attribute_value' => is_array($val) ? join(',', $val) : $val,
						);
						model('goods_attribute')->create($attrData);
					}
				}
			}

			//分类的相关
			model('category_extend')->save([
				'goods_id'    => $goods_id,
				'category_id' => $cate_id,
			]);

			$goods_photo_model          = model('goods_photo');
			$goods_photo_relation_model = model('goods_photo_relation');
			foreach ($urls as $one)
			{
				if ( ! $photo = $goods_photo_model->where('id', $one['md5'])->find())
				{
					$goods_photo_model->data([
						'img'  => $one['url'],
						'type' => '1',
						'id'   => $one['md5'],
					])->isUpdate(FALSE)->save();
				}
				$id = $one['md5'];
				$goods_photo_relation_model->data([
					'goods_id' => $goods_id,
					'photo_id' => $id,
				])->isUpdate(FALSE)->save();
			}

			$this->success('商品新增成功', url('index'));
		}
		else
		{
			// 模型
			$models = model('mm')->select();
			foreach ($models as &$model)
			{
				$model = $model->getData();
			}
			$this->assign('models', $models);

			// 产品分类
			$cates = model('category')->order('parent_id asc,sort asc')->select();
			$list  = [];
			foreach ($cates as $v)
			{
				$list[$v['id']] = $v->getData();
			}
			$list = sortdata($list, 0, '--');

			$ids        = array_unique(array_column($list, 'id'));
			$parent_ids = array_unique(array_column($list, 'parent_id'));

			$child_ids = array_diff($ids, $parent_ids);
			$this->assign('cates', $list);
			$this->assign('child_ids', $child_ids);

			// 品牌
			$mlist = model('brand')->select();
			$this->assign('mlist', $mlist);

			return view();
		}
	}

	public function edit()
	{
		$id    = input('param.id');
		$goods = model('goods')->find($id)->getData();
		if ( ! $goods)
		{
			$this->error('没有该商品');
		}
		if ($goods['seller_id'] != $this->_seller['id'])
		{
			$this->error('该商品不是您的，不能修改');
		}

		if ($this->request->isPost())
		{
			$old  = $goods;
			$data = $this->request->post();

			if ( ! (isset($data['name'], $data['cate_id'], $data['store_nums'], $data['market_price'], $data['sell_price'], $data['cost_price'], $data['weight'])
				&& $data['name']
				&& $data['cate_id']
				&& $data['store_nums']
				&& $data['market_price']
				&& $data['sell_price']
				&& $data['cost_price']
				&& $data['weight']
			))
			{
				$this->error('缺少字段：商品名称，所属分类，库存，市场价格，销售价格，成本价格，重量，图片');
			}

			if (model('goods')->where(['name' => $data['name'], 'id' => ['neq', $goods['id']]])->find())
			{
				$this->error('该商品名已经存在');
			}

			if ($old['model_id'] != $data['model_id'])
			{
				model('goods_attribute')->where(['goods_id' => $old['id'], 'model_id' => $old['model_id']])->delete();
			}

			$goods_attr = [];
			// 处理模型里面的扩展属性字段
			foreach ($data as $key => $val)
			{
				//数据过滤分组
				if (strpos($key, 'attr_id_') !== FALSE)
				{
					$goods_attr[ltrim($key, 'attr_id_')] = $val;
					unset($data[$key]);
				}
			}

			$data['seller_id']   = $this->_seller['id'];
			$data['create_time'] = date('Y-m-d H:i:s');
			$data['goods_no']    = config('goods_no_pre').time().rand(10, 99);
			// 分类
			$cate_id  = $data['cate_id'];
			$cate     = model('category')->find($cate_id);
			$cate_arr = $cate->getData();
			if (empty($cate_arr))
			{
				$this->error('没有这个分类');
			}
			model('category_extend')->where(['goods_id' => $id])->find()->save(['category_id' => $cate_id]);

			unset($data['cate_id']);

			// 上传图片
			$imgs = request()->file('img');
			$urls = [];

			$first = '';
			foreach ($imgs as $img)
			{
				$info = $img->move(config('imguploads')['rootPath']);
				if (empty($first))
				{
					$first = '\\'.config('imguploads')['rootPath'].DS.$info->getSaveName();
					$first = strtr($first, '\\', '/');
				}
				else
				{
					$urls[] = [
						'url' => strtr('\\'.config('imguploads')['rootPath'].DS.$info->getSaveName(), '\\', '/'),
						'md5' => md5_file($info->getRealPath()),
					];
				}
			}

			if ($first)
			{
				$data['img']          = $first;
				$data['img_pathtype'] = '1';
			}
			// 保存商品
			model('goods')->find($id)->save($data);

			// 增加商品属性
			if ($goods_attr)
			{
				if ($data['model_id'] > 0 && isset($goods_attr) && $goods_attr)
				{
					foreach ($goods_attr as $key => $val)
					{
						$attrData = array(
							'goods_id'        => $id,
							'model_id'        => $data['model_id'],
							'attribute_id'    => $key,
							'attribute_value' => is_array($val) ? join(',', $val) : $val,
						);
						model('goods_attribute')->create($attrData);
					}
				}
			}
			$goods_photo_model          = model('goods_photo');
			$goods_photo_relation_model = model('goods_photo_relation');
			if ($urls)
			{
				$goods_photo_relation_model->where('goods_id', $id)->delete();
			}

			foreach ($urls as $one)
			{
				if ( ! $photo = $goods_photo_model->where('id', $one['md5'])->find())
				{
					$goods_photo_model->data([
						'img'  => $one['url'],
						'type' => '1',
						'id'   => $one['md5'],
					])->isUpdate(FALSE)->save();
				}
				$md5 = $one['md5'];
				$goods_photo_relation_model->data([
					'goods_id' => $id,
					'photo_id' => $md5,
				])->isUpdate(FALSE)->save();
			}

			$this->success('商品编辑成功', url('index'));
		}
		else
		{
			// 模型
			$models = model('mm')->select();
			$models = get_collection($models);
			$this->assign('models', $models);
			$goods['model_name'] = '';
			$goods['attrs']      = [];
			if ($goods['model_id'])
			{
				$model               = model('mm')->find($goods['model_id'])->getData();
				$attrs               = model('attribute')->where(['model_id' => $goods['model_id']])->select();
				$attrs               = array_map(function ($one) use ($goods) {
					$data              = $one->getData();
					$where             = [
						'goods_id'     => $goods['id'],
						'model_id'     => $goods['model_id'],
						'attribute_id' => $data['id'],
					];
					$data['now_value'] = model('goods_attribute')->where($where)->find()->getData('attribute_value');

					if (in_array($data['type'], [1, 2, 3]))
					{
						$data['value']     = explode(',', $data['value']);
						$data['now_value'] = explode(',', $data['now_value']);
					}

					return $data;
				}, $attrs);
				$goods['model_name'] = $model['name'];
				$goods['attrs']      = $attrs;
			}

			// 产品图片
			$photos    = model('goods_photo_relation')->where(['goods_id' => $id])->select();
			$photos    = get_collection($photos);
			$photo_ids = array_column($photos, 'photo_id');
			$photos    = model('goods_photo')->where('id', 'IN', $photo_ids)->select();
			$photos    = get_collection($photos);
			foreach ($photos as &$photo)
			{
				$photo['img'] = config('img_pathtype')[$photo['type']].$photo['img'];
			}
			$goods['photos'] = $photos;

			// 产品分类
			$cates = model('category')->order('parent_id asc,sort asc')->select();
			$list  = [];
			foreach ($cates as $v)
			{
				$list[$v['id']] = $v->getData();
			}
			$list = sortdata($list, 0, '--');

			$ids        = array_unique(array_column($list, 'id'));
			$parent_ids = array_unique(array_column($list, 'parent_id'));

			$child_ids = array_diff($ids, $parent_ids);
			$this->assign('cates', $list);
			$this->assign('child_ids', $child_ids);
			$goods_cate       = model('category_extend')->where(['goods_id' => $id])->find()->getData();
			$goods['cate_id'] = $goods_cate['category_id'];

			// 品牌
			$mlist = model('brand')->select();
			$this->assign('detail', $goods);
			$this->assign('mlist', $mlist);

			return view();
		}
	}

	public function delete()
	{
		$id = input('param.id');
		model('goods')->save(['is_del' => 1], ['id' => $id]);
		$this->success('商品删除成功', url('index'));
	}

	public function ajax_get_model()
	{
		$model_id = input('get.id');
		$list     = model('attribute')->where('model_id', $model_id)->select();
		$list     = array_map(function ($one) {
			$data          = $one->getData();
			$data['value'] = explode(',', $data['value']);

			return $data;
		}, $list);

		return json($list);
	}

}