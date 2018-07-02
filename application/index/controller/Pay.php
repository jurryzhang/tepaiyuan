<?php
namespace app\index\controller;
use think\Controller;
use think\Session;

class Pay extends Controller
{
	protected $logfile='./paylogfile.txt';
	public function index($data)
	{
		//微信H5支付
		if($data['trade_type']=='weixin')
		{
			$postdata=[
				'appid'=>config('wxapi')['appid'],
				'mch_id'=>config('wxapi')['mch_id'],
				'nonce_str'=>rand(10000,99999),
				'body'=>$data['body'],
				'attach'=>config('wxapi')['notify_url'],
				'out_trade_no'=>$data['out_trade_no'],
				'total_fee'=>$data['total_fee'],
				'spbill_create_ip'=>$data['spbill_create_ip'],
				'notify_url'=>config('wxapi')['notify_url'],
				'trade_type'=>'MWEB',
				'product_id'=>'wa'.date('Ymd')
			];
			$conf=wxresult($postdata,config('wxapi')['key']);
			ceshilog($this->logfile,$conf,'WXH5');
			if (!$conf || $conf['return_code'] == 'FAIL')
			{
				exit("<script>alert('对不起，微信支付接口调用错误!" . $conf['return_msg'] . "');history.go(-1);</script>");
			}
			//对数据进行拼接组合
			//pid_oid_type  
			// $linkid=$data['out_trade_no'];
			$wxredirect_url='http://pay.yuyoupay.com/index/pay/gotr';
			ceshilog($this->logfile,$wxredirect_url,'WXH5');
			$conf['mweb_url'] = $conf['mweb_url'] . '&redirect_url=' . urlencode($wxredirect_url);
			ceshilog($this->logfile,$conf,'WXH5-2');
			// dump($conf['mweb_url']);die;
			$this->assign('mweb_url',$conf['mweb_url']);
			return view('wxh5'); 
		}
		
	}

}