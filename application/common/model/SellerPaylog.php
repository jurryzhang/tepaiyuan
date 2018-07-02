<?php

namespace app\common\model;

use think\Model;

class SellerPaylog extends Model {

	/**
	 * [dataSave description]
	 *
	 * @param  [array] $data        [需要保存的数据]
	 * @param  [intval] $trade_type [支付类型 1 微信支付 2银行卡]
	 * @param  [intval] $type       [类型 1押金 2退押金 3提现 4充值]
	 * @param  [intval] $status     [状态 0已申请 1成功 2申请未通过]
	 *
	 * @return [floot]              [1成功 2失败]
	 */
	public function dataSave($data, $trade_type, $type, $status = 0)
	{
		unset($data['return_url']);
		$data['add_time']   = time();
		$data['trade_type'] = $trade_type;
		if (empty($data['out_trade_no']))
		{
			$data['out_trade_no'] = date('YmdHis').time();
		}
		$data['spbill_create_ip'] = request()->ip();
		$data['type']             = $type;
		$data['status']           = $status;
		$state                    = $this->save($data);
		if ($state)
		{
			return 1;
		}
		else
		{
			return 2;
		}
	}
}
