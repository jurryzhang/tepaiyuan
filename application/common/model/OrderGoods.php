<?php
namespace app\common\model;
use think\Model;

class OrderGoods extends Model
{
	public function index()
	{}

	public function goodsdata($oidStr,$return=array())
	{
		$data=array();
		$join = [
		    ['iwebshop_goods g','og.goods_id=g.id','left'],
		    ['iwebshop_order o','o.id=og.order_id','left'],
		];
		$fields='og.*,(og.goods_nums*og.goods_weight) as weight,g.name,g.content,g.unit';
		$map=array();
		$map['og.order_id']=array('in',$oidStr);
		$map['o.status']=array('neq',12);
		$goodslist=$this->alias('og')->field($fields)->where($map)->join($join)->select();
		$data['count']=count($goodslist);
		// echo model('order_goods')->getLastSql();exit();

		$weight=0;
		foreach ($goodslist as &$v) {
			$v = $v->getData();
			$v['is_return']=0;
			if($return && (in_array($v['goods_id'],$return[$v['order_id']])))
			{
					$v['is_return']=1;
			}
			$weight+=$v['weight'];
			$img=config('img_pathtype')[$v['img_pathtype']].$v['img'];
			$v['img']=$img;

		}
		// echo '<pre>';var_dump($goodslist);exit();
		$data['weight']=$weight;
		$data['list']=$goodslist;
		// echo '<pre>';var_dump($data);
		return $data;
	}
	public function getDetails($id)
	{
		$map['id'] = $id;
		$info      = model('order')->where($map)->find();
		// echo '<pre>';var_dump($info);exit;
		$goodsdata = $this->goodsdata($id);
		//获取市场地址
		$address = model('market')->where('id', $info['market_id'])->value('address');
		//获取特派员信息
		$uinfo = model('freight')->where('id', $info['freight_id'])->column('id,name,telphone');
		// var_dump($uinfo);exit();

		$info['fh_address']  = $address;
		$info['fh_name']     = $uinfo[$info['freight_id']]['name'];
		$info['fh_telphone'] = $uinfo[$info['freight_id']]['telphone'];
		$info['goodslist']   = $goodsdata['list'];
		$info['weight']      = $goodsdata['weight'];
		$info['count']       = $goodsdata['count'];

		return $info;
	}

	public function getCarload($uid, $oid, $carload)
	{
		$map                   = array();
		$map['freight_status'] = ['not in', '0,3,4'];
		$map['freight_id']     = $uid;

		$oidArr = $this->where($map)->column('id');
		// echo model('order')->getLastSql();exit;
		// var_dump($oidArr);exit;
		// $oidStr=data_getstr($oidArr);
		$oidStr = '';
		if ($oidArr)
		{
			foreach ($oidArr as $key => $value)
			{
				$oidStr .= $value.',';
			}
		}
		$oidStr .= $oid;

		// SELECT sum(goods_nums*goods_weight) as w FROM `iwebshop_order_goods` where order_id in (2,4,5);
		$cur_carload = model('order_goods')
			->field('sum(goods_nums*goods_weight) as w')
			->where(['order_id' => ['in', $oidStr]])
			->find();
		// echo model('order_goods')->getLastSql();
		// var_dump($cur_carload['w']);exit;
		if ($cur_carload['w'] > $carload)
		{
			return 0;
		}
		else
		{
			return 1;
		}
	}
}
