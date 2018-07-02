<?php
namespace app\common\model;
use think\Model;

class FreightBanklist extends Model
{
	/**
	 * [dataSave description]
	 * @param  [array] $data       [需要保存的数据]
	 * @param  [type] $trade_type [支付类型 1 微信支付 2银行卡]
	 * @param  [type] $type       [类型 1押金 2退押金 3提现]
	 * @return [floot]             [1成功 2失败]
	 */
	public function dataSave($data)
	{
		$data['add_time']=time();
		$bankid=$data['bank_id'];
		//获取银行名称
		$bankname=model('bank')->where('id',$bankid)->value('bank_name');
		$data['bank_name']=$bankname;
        $state=$this->allowField(true)->save($data);
        if($state) {
			return 1;
		}else {
			return 2;
		}
	}	
}
