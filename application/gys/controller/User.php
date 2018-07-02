<?php

namespace app\gys\controller;

use think\Cookie;
use think\Session;

class User extends Indexbase {

	// 登录首页--
	public function index()
	{
		$data = array('htitle' => '会员中心');
		$this->assign('hdata', $data);
		if ( ! Session::get('sellerinfo'))
		{
			$this->redirect('User/login');
		}
		else
		{
			$bank_count = model('seller_banklist')->where('seller_id', $this->_seller['id'])->count();
			$this->assign('bank_count', $bank_count);

			$uid = Session::get('sellerinfo')['id'];
			if ($uid > 0)
			{
				return view();
			}
			else
			{
				$this->error('请先登录', 'User/login');
			}
		}
	}

	// 退出登录 --
	public function logout()
	{
		Cookie::set('sellerinfo', NULL);
		Session::set('sellerinfo', NULL);
		$this->success('退出成功', url('login'));
	}

	//忘记密码
	public function forgetpwd()
	{
		$data = array('htitle' => '忘记密码');
		$this->assign('hdata', $data);
		if (request()->ispost())
		{
			$sep = input('sep');
			// echo '<pre>';var_dump($_POST);exit;
			if ($sep == 1)
			{
				$mobile = input('telphone');
				$this->assign('mobile', $mobile);

				if (config('other')['smsyz_status'] == 1 && input('telcode') != Session::get('sms_mobile_code'))
				{
					$this->error('验证码输入有误');
					exit();
				}
				$num = model('seller')->where('telphone', $mobile)->count();
				if ($num == 0)
				{
					$this->error('该手机号没有被注册');
					exit();
				}
				$this->assign('sep', 2);

				return view();
			}
			if ($sep == 2)
			{
				$mobile = input('mobile');
				$pwd    = input('newpwd');
				$newpwd = md5($pwd);
				model('seller')->where('telphone', $mobile)->update(['pwd' => $newpwd]);
				$this->assign('sep', 3);

				return view();
			}
			if ($sep == 3)
			{
				return view();
			}
		}
		else
		{
			$this->assign('sep', 1);

			return view();
		}
	}

	//修改密码
	public function resetpwd()
	{
		$data = array('htitle' => '修改密码');
		$this->assign('hdata', $data);
		if (request()->ispost())
		{
			$uid    = input('uid');
			$oldpwd = input('oldpwd');
			$oldpwd = md5($oldpwd);
			$newpwd = input('newpwd');
			$newpwd = md5($newpwd);
			$pwd    = model('seller')->where('id', $uid)->value('password');
			if ($oldpwd != $pwd)
			{
				$this->error('旧密码输入有误，不一致');
				exit();
			}
			if ($newpwd == $pwd)
			{
				$this->error('新密码与旧密码一致');
				exit();
			}
			else
			{
				$state = model('seller')->update(['id' => $uid, 'password' => $newpwd]);
				Session::set('sellerinfo', '');
				$this->success('修改成功，请重新登录', 'User/login');
				exit();
			}
		}
		else
		{
			return view();
		}
	}

	//明细--
	public function mingxi()
	{
		$data = array('htitle' => '账户明细');
		$this->assign('hdata', $data);
		$uid         = Session::get('sellerinfo')['id'];
		$logdata     = model('seller_paylog')->where('seller_id', $uid)->select();
		$chongzhilog = [];
		$tixianlog   = [];
		if ($logdata)
		{
			foreach ($logdata as $key => $val)
			{
				switch ($val['type'])
				{
					case '1':
						$val['total_fee'] = "<span class='span_enter'>".$val['total_fee']."</span>";
						break;
					case '2':
						$val['total_fee'] = "<span>-".$val['total_fee']."</span>";
						break;
					case '3':
						$val['total_fee'] = "<span>-".$val['total_fee']."</span>";
						break;
					case '4':
						$val['total_fee'] = "<span class='span_enter'>".$val['total_fee']."</span>";
						break;
				}
				switch ($val['type'])
				{
					case '3':
						$tixianlog[] = $val;
						break;
					case '4':
						$chongzhilog[] = $val;
						break;
				}
			}
		}
		$this->assign('logdata', $logdata);
		$this->assign('chongzhilog', $chongzhilog);
		$this->assign('tixianlog', $tixianlog);
		$this->assign('paylogstatus', config('paylogstatus'));

		return view();
	}

	//提现
	public function tixian()
	{
		$data = array('htitle' => '余额提现');
		$this->assign('hdata', $data);
		if (request()->ispost())
		{
			if ($_POST['total_fee'] <= 0)
			{
				$this->error('请填写提现金额');
				exit();
			}
			//判断当前余额是否够提现金额
			$uid    = $_POST['seller_id'];
			$seller = model('seller')->find($uid);
			$amount = $seller->amount;
			if ($_POST['total_fee'] > $amount)
			{
				$this->error('提现余额不能大于账户余额');
				exit();
			}
			$_POST['cur_amount'] = $amount;
			$seller->save(['amount' => $amount - $_POST['total_fee']]);
			$state = model('seller_paylog')->dataSave($_POST, 2, 3);
			if ($state)
			{
				$this->success('申请操作成功，请耐心等待', 'index');
				exit();
			}
			else
			{
				$this->error('申请失败');
				exit();
			}
		}
		else
		{
			//获取当前会员添加银行卡列表
			$uid       = Session::get('sellerinfo')['id'];
			$ubanklist = model('seller_banklist')->where('seller_id', $uid)->select();
			$this->assign('ubanklist', $ubanklist);

			return view();
		}
	}

	//充值--
	public function chongzhi()
	{
		$data = array('htitle' => '余额充值');
		$this->assign('hdata', $data);
		$uid = $this->_seller['id'];
		if (request()->ispost())
		{
			model('seller_paylog')->dataSave($_POST, 2, 4);

			$seller              = model('seller')->find($uid);
			$amount              = $seller->amount;
			$_POST['cur_amount'] = $amount;
			$seller->save(['amount' => $amount + $_POST['total_fee']]);

			$this->success('申请充值成功', url('mingxi'));
		}
		else
		{
			$ubanklist = model('SellerBanklist')->where('seller_id', $uid)->select();
			$this->assign('ubanklist', $ubanklist);

			return view();
		}
	}

	//退押金
	public function tyj()
	{
		$data = array('htitle' => '退押金');
		$this->assign('hdata', $data);
		if (request()->ispost())
		{
			if ($_POST['total_fee'] <= 0)
			{
				$this->error('暂无押金可退');
				exit();
			}
			$uid                 = $_POST['uid'];
			unset($_POST['uid']);
			$amount              = model('seller')->where('id', $uid)->value('yajin');
			$_POST['cur_amount'] = $amount;
			$state               = model('seller_paylog')->dataSave($_POST, 2, 2);
			if ($state)
			{
				$this->success('申请操作成功，请耐心等待', 'User/index');
				exit();
			}
			else
			{
				$this->error('申请失败');
				exit();
			}
		}
		else
		{
			//获取当前会员添加银行卡列表
			$uid       = Session::get('sellerinfo')['id'];
			$ubanklist = model('SellerBanklist')->where('seller_id', $uid)->select();
			$this->assign('ubanklist', $ubanklist);

			return view();
		}
	}

	// 银行卡列表 --
	public function list_yhk()
	{
		$list = model('seller_banklist')->where('seller_id', $this->_seller['id'])->select();
		$this->assign('list', $list);

		return view();
	}

	//添加银行卡 --
	public function add_yhk()
	{
		$data = array('htitle' => '添加银行卡');
		$this->assign('hdata', $data);
		if (request()->ispost())
		{
			$state = model('SellerBanklist')->dataSave($_POST);
			if ($state)
			{
				$this->success('添加成功', 'User/list_yhk');
			}
			else
			{
				$this->error('添加失败');
			}
		}
		else
		{
			//获取银行列表
			$banklist = model('bank')->select();
			$this->assign('banklist', $banklist);

			return view();
		}
	}

	//修改资料
	public function edit()
	{
		$data = array('htitle' => '会员信息编辑');
		$this->assign('hdata', $data);
		if (request()->ispost())
		{
			$seller = model('seller')->find($_POST['id']);
			$file   = $this->request->file('certif_img');
			if ($file)
			{
				$info               = $file->move(config('imguploads')['rootPath']);
				$_POST['certif_img'] = '\\upload\\'.$info->getSaveName();
			}
			$state = $seller->save($_POST);
			if ($state == 1)
			{
				$this->success('编辑成功');
			}
			else
			{
				$this->success('无更新操作');
			}
		}
		else
		{
			return view();
		}
	}

	//登录--
	public function login()
	{
		$data = array('htitle' => '会员登录');
		$this->assign('hdata', $data);
		if (request()->ispost())
		{
			$w['seller_name'] = input('telphone');
			$w['password']    = md5(input('pwd'));
			$uinfo            = model('seller')->where($w)->find();
			if ($uinfo)
			{
				$uinfo = $uinfo->getData();
				unset($uinfo['password']);

				Cookie::set('sellerinfo', $uinfo);
				Session::set('sellerinfo', $uinfo);
				$this->redirect('User/index');
			}
			else
			{
				$this->error('登录失败，账号或密码有误');
			}
		}
		else
		{
			return view();
		}
	}

	//注册--
	public function reg()
	{
		$data = array('htitle' => '供应商注册');
		$this->assign('hdata', $data);

		//获取市场列表
		$mlist = model('market')->allList();
		$this->assign('mlist', $mlist);

		return view();
	}

	//提交注册 --
	public function reg1()
	{
		if (request()->ispost())
		{
			$mobile = $_POST['mobile'];
			$num    = model('Seller')->where('seller_name', $mobile)->count();
			if (empty($_POST['market_id']) | empty($_POST['person_charge']) | empty($_POST['true_name'])
				| empty($_POST['phone']) | empty($_POST['mobile']) | empty($_POST['tax_number'])
				| empty($_POST['stall_number']) | empty($_POST['scope_business']) | empty($_POST['status']))
			{
				$this->error('缺失字段');
			}

			if ($num > 0)
			{
				return $this->error('当前手机号已被注册');
			}
			else
			{
				$_POST['seller_name'] = $mobile;
			}
			if ($_FILES['paper_img']['tmp_name'] == '' || $_FILES['certif_img']['tmp_name'] == ''
				|| $_FILES['carinfo_img']['tmp_name'] == '')
			{
				return $this->error('请传入至少三张照片');
			}
			$filesArr['paper_img']   = request()->file('paper_img');
			$filesArr['certif_img']  = request()->file('certif_img');
			$filesArr['carinfo_img'] = request()->file('carinfo_img');

			$files = action('Interfun/arrUpload', [$filesArr]);
			// var_dump($files);exit;
			$_POST['paper_img']   = $files['paper_img'];
			$_POST['certif_img']  = $files['certif_img'];
			$_POST['carinfo_img'] = $files['carinfo_img'];

			$market_id             = $_POST['market_id'];
			$marketsplaceD         = model('Market')->where('id', $market_id)->column('name');
			$_POST['marketsplace'] = $marketsplaceD[0];
			$state                 = model('Seller')->reg1($_POST);
			if ($state == 1)
			{
				$name   = $_POST['true_name'];
				$mobile = $_POST['mobile'];
				$id     = model('seller')->getLastInsID();
				//先对手机进行实名验证 然后进行手机号验证
				$this->assign(['name' => $name, 'mobile' => $mobile, 'id' => $id]);

				return view();
			}
			elseif ($state == 2)
			{
				return $this->error('注册失败，请检查网络重新注册');
			}
			else
			{
				return $this->error($state);
			}
		}
		else
		{
			// $mobile=Session::get('sms_mobile');
			$mobile = Session::get('sms_mobile') ? Session::get('sms_mobile') : '15188316549';
			$this->assign('mobile', $mobile);
			//获取当前会员数据
			// $data=model('Seller')->where('mobile',$mobile)->column('seller_name','id');
			return view();
		}

		// $mobile=Session::get('sms_mobile')?Session::get('sms_mobile'):'15188316549';

		// $this->assign(['name'=>'田姝童','mobile'=>$mobile,'id'=>1]);
		// return view();
	}

	// 验证手机号，并提醒用户获取验证码--
	public function reg2()
	{
		//获取数据
		if (request()->ispost())
		{
			$data = $_POST;
			$code = Session::get('sms_mobile_code');
			if (config('other')['smsyz_status'] == 1 && $data['telcode'] != $code)
			{
				$this->error('验证码有误', 'User/reg1');
				exit();
			}
			//初始化密码123456
			model('Seller')
				->where('seller_name', $data['telphone'])
				->update(['status' => '3', 'password' => md5('123456')]);
			$uid = model('seller')->where('seller_name', $data['telphone'])->value('id');
			$this->assign(['returnUrl' => 'User/reg_act', 'uid' => $uid, 'paybody' => '注册押金', 'yajin' => config('other')['zhuce_yajin']]);
			return view();
			$this->redirect('User/reg_act');
		}
		else
		{
			if (Session::get('sms_mobile'))
			{
				$mobile = Session::get('sms_mobile');
				$uid    = model('seller')->where('seller_name', $mobile)->value('id');
				$this->assign(['returnUrl' => 'User/reg_act', 'uid' => $uid, 'paybody' => '注册押金', 'yajin' => config('other')['zhuce_yajin']]);
				return view();
			}
		}
	}

	//注册押金
	public function reg_yajin()
	{
		if (request()->ispost())
		{
			if (input('trade_type') == NULL)
			{
				$this->error('请选择支付方式');
				exit();
			}
			if (input('amount') < config('other')['zhuce_yajin'])
			{
				$this->error('押金不能少于'.config('other')['zhuce_yajin'].'元');
				exit();
			}
			$data  = [
				'trade_type'       => input('trade_type'),
				'out_trade_no'     => date('YmdHis').time(),
				'body'             => input('body'),
				'total_fee'        => input('amount'),
				'spbill_create_ip' => request()->ip(),
				'seller_id'              => input('uid'),
				'cur_amount'       => 0,
				'return_url'       => 'User/reg_act',
			];
			$state = model('seller_paylog')->dataSave($data, 1, 1);
			model('seller')->find(Session::get('sellerinfo')['id'])->save(['yajin'=>1500]);
			if ($state == 1)
			{
				// action('Pay/index',[$data]);
				return $this->redirect('User/reg_act');
			}
		}
		else
		{
			return $this->redirect('User/login');
		}
	}

	//注册成功地址 --
	public function reg_act()
	{
		// Session::set('sellerinfo','');
		return view();
	}

	//获取手机验证码--
	public function mobileSend()
	{
		if (config('other')['smsyz_status'] == 0)
		{
			exit(json_encode(array('code' => 5)));  //短信验证已关闭
		}
		$mobile = input('mobile');
		if (empty($mobile))
		{
			exit(json_encode(array('code' => 3, 'msg' => '手机号码不能为空')));
		}

		$preg = '/^1[0-9]{10}$/'; //简单的方法
		if ( ! preg_match($preg, $mobile))
		{
			exit(json_encode(array('code' => 3, 'msg' => '手机号码格式不正确')));
		}
		if (Session::get('sms_mobile') && (time() - Session::get('sms_mobile_time')) < 60)
		{
			exit(json_encode(array('msg' => '获取验证码太过频繁，一分钟之内只能获取一次。')));
		}

		$mobile_code = random(6, 1);
		$message     = "您的验证码是：".$mobile_code."，请不要把验证码泄露给其他人，如非本人操作，可不用理会！".config('other')['mobile_sms'];
		$num         = sendSMS($mobile, $message);
		if ($num == TRUE)
		{
			Session::set('sms_mobile', $mobile);
			Session::set('sms_mobile_code', $mobile_code);
			Session::set('sms_mobile_time', time());
			// exit(json_encode(array(
			//     'code' => 2,'mobile_code' => $mobile_code
			// )));
			exit(json_encode(array(
				'code' => 2,
			)));
		}
		else
		{
			exit(json_encode(array(
				'code' => 3,
				'msg'  => '手机验证码发送失败。',
			)));
		}
	}

	// 使用帮助--
	public function help()
	{
		return view();
	}

	// 客服中心--
	public function service()
	{
		return view();
	}

	// 关于我们--
	public function about()
	{
		return view();
	}
}
