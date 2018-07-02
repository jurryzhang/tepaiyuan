<?php
namespace app\index\controller;
use think\Cookie;
use think\Session;

class Member extends Indexbase
{
    public function __construct()
    {
        parent::__construct();
        if(Session::get('userinfo')) {
            $uinfo=Session::get('userinfo');
            $w['id']=$uinfo['uid'];
            $w['telphone']=$uinfo['telphone'];
            $uinfo=model('freight')->where($w)->find();
            $this->assign('info',$uinfo);
        }
    }
    public function index()
    {
        $data=array('htitle' => '会员中心');
    	$this->assign('headdata',$data);
        if(!Session::get('userinfo')) {
            $this->error('请先登录','Member/login');
        }else{
            $uid=Session::get('userinfo')['uid'];
            if($uid>0)
            {
                $jiedanStatue=Session::get('jiedanStatue');
                if($jiedanStatue==null)
                {
                    $jiedanStatue=1;
                }
                //获取银行卡数量
                $bankcount=model('freight_banklist')->where('uid',$uid)->count();
                $this->assign('bankcount',$bankcount);
                $this->assign('jiedanStatue',$jiedanStatue);
                return view();
            }else{
                $this->error('请先登录','Member/login');
            }
        }
    }

    //退出
    public function signout()
    {
        Session::set('userinfo','');
        Cookie::set('userinfo','');
        $this->success('退出成功','Member/login');
    }


    //忘记密码
    public function forgetpwd()
    {
        $data=array('htitle' => '忘记密码');
        $this->assign('headdata',$data);
        if(request()->ispost()){
            $sep=input('sep');
            // echo '<pre>';var_dump($_POST);exit;
            if($sep==1)
            {
                $mobile=input('telphone');
                $this->assign('mobile',$mobile);

                if(config('other')['smsyz_status']==1 && input('telcode')!=Session::get('sms_mobile_code'))
                {
                    $this->error('验证码输入有误');exit();
                }
                $num=model('freight')->where('telphone',$mobile)->count();
                if($num==0)
                {
                    $this->error('该手机号没有被注册');exit();
                }
                $this->assign('sep',2);
                return view();
            }
            if($sep==2)
            {
                $mobile=input('mobile');
                $pwd=input('newpwd');
                $newpwd=md5(config('other')['pwd_prefix'].$pwd);
                model('freight')->where('telphone',$mobile)->update(['pwd'=>$newpwd]);
                $this->assign('sep',3);
                return view();
            }
            if($sep==3)
            {
                return view();
            }

        }else{
            $this->assign('sep',1);
            return view();
        }
    }
    //修改密码
    public function resetpwd()
    {
        $data=array('htitle' => '修改密码');
        $this->assign('headdata',$data);
        if(request()->ispost()){
            $uid=input('uid');
            $oldpwd=input('oldpwd');
            $oldpwd=md5(config('other')['pwd_prefix'].$oldpwd);
            $newpwd=input('newpwd');
            $newpwd=md5(config('other')['pwd_prefix'].$newpwd);
            $pwd=model('freight')->where('id',$uid)->value('pwd');
            if($oldpwd!=$pwd)
            {
                $this->error('旧密码输入有误一致');exit();
            }
            if($newpwd==$pwd)
            {
                $this->error('新密码与旧密码一致');exit();
            }else{
                $state=model('freight')->update(['id'=>$uid,'pwd'=>$newpwd]);
                Session::set('userinfo','');
                $this->success('修改成功，请重新登录','Member/login');exit();
            }
        }else{
            return view();
        }
    }

    //定位
    public function dingwei()
    {
        if(!Session::get('userinfo'))
        {
            echo json_encode(['code'=>3,'msg'=>'请先登录']);exit();
        }
        $id=Session::get('userinfo')['uid'];
        $userinfo=Session::get('userinfo');
        $userinfo['address']=$_POST['address'];
        Session::set('userinfo',$userinfo);
        $_POST['id']=$id;

        $_POST['province']='河南省';
        $_POST['city']='郑州市';
        $_POST['district']='二七区';

        $address_data=model('freight')->quyuUpdata($_POST);
        $province=$address_data['province'];
        $city=$address_data['city'];
        $district=$address_data['district'];
        //获取市级列表
        $cityTemp=model('areas')->getAreasSelect($province,$city);
        //获取区级列表
        $districtTemp=model('areas')->getAreasSelect($city,$district);
        echo json_encode(['code'=>1,'msg'=>'定位成功','province'=>$province,'cityTemp'=>$cityTemp,'districtTemp'=>$districtTemp]);
    }
    //修改定位区域
    public function updata_quyu()
    {
        $state=model('freight')->update(input());
        echo json_encode(['code'=>1,'msg'=>'重新定位成功']);

    }

    //明细
    public function mingxi()
    {
        $data=array('htitle' => '账户明细');
        $this->assign('headdata',$data);
        $uid=Session::get('userinfo')['uid'];
        $logdata=model('Freight_paylog')->where('uid',$uid)->order('id desc')->select();
        $chongzhilog=[];
        $tixianlog=[];
        if($logdata)
        {
            foreach($logdata as $key => $val)
            {
                switch ($val['type']) {
                    case '1':
                        $val['total_fee']="<span class='span_enter'>".$val['total_fee']."</span>";
                        break;
                    case '2':
                        $val['total_fee']="<span>-".$val['total_fee']."</span>";
                        break;
                    case '3':
                        $val['total_fee']="<span>-".$val['total_fee']."</span>";
                        break;
                    case '4':
                        $val['total_fee']="<span class='span_enter'>".$val['total_fee']."</span>";
                        break;
                    case '5':
                        $val['total_fee']="<span class='span_enter'>".$val['total_fee']."</span>";
                        break;
                    case '6':
                         $val['total_fee']="<span>-".$val['total_fee']."</span>";
                        break;
                }
                switch ($val['type']) {
                    case '3':
                        $tixianlog[]=$val;
                        break;
                    case '4':
                        $chongzhilog[]=$val;
                        break;
                }
            }
        }
        // echo '<pre>';var_dump($logdata);exit();
        $this->assign('logdata',$logdata);
        $this->assign('chongzhilog',$chongzhilog);
        $this->assign('tixianlog',$tixianlog);
        $this->assign('paylogstatus',config('paylogstatus'));
        return view();
    }

    //提现
    public function tixian()
    {
        $data=array('htitle' => '余额提现');
        $this->assign('headdata',$data);
        if(request()->ispost())
        {
            if($_POST['total_fee']<=0)
            {
                $this->error('请填写提现金额');exit();
            }
            //判断当前余额是否够提现金额
            $uid=$_POST['uid'];
            $amount=model('Freight')->where('id',$uid)->value('amount');
            if($_POST['total_fee']>$amount)
            {
                $this->error('提现余额不能大于账户余额');exit();
            }
            $_POST['cur_amount']=$amount;
            $state=model('Freight_paylog')->dataSave($_POST,2,3);
            if($state)
            {
                $this->success('申请操作成功，请耐心等待','Member/index');exit();
            }else{
                $this->error('申请失败');exit();
            }
        }else{
            //获取当前会员添加银行卡列表
            $uid=Session::get('userinfo')['uid'];
            $ubanklist=model('FreightBanklist')->where('uid',$uid)->select();
            $this->assign('ubanklist',$ubanklist);
            return view();
        }
    }
    //充值
    public function chongzhi()
    {
        $data=array('htitle' => '余额充值');
        $this->assign('headdata',$data);
        if(request()->ispost()){

        }else{
            $uid=Session::get('userinfo')['uid'];
            $ubanklist=model('FreightBanklist')->where('uid',$uid)->select();
            $this->assign('ubanklist',$ubanklist);
            return view();
        }
    }

    //退押金
    public function tyj()
    {
        $data=array('htitle' => '退押金');
        $this->assign('headdata',$data);
        if(request()->ispost())
        {
            if($_POST['total_fee']<=0)
            {
                $this->error('暂无押金可退');exit();
            }
            $uid=$_POST['uid'];
            $amount=model('Freight')->where('id',$uid)->value('yajin');
            $_POST['cur_amount']=$amount;
            $state=model('Freight_paylog')->dataSave($_POST,2,2);
            if($state)
            {
                $this->success('申请操作成功，请耐心等待','Member/index');exit();
            }else{
                $this->error('申请失败');exit();
            }
        }else{
            //获取当前会员添加银行卡列表
            $uid=Session::get('userinfo')['uid'];
            $ubanklist=model('FreightBanklist')->where('uid',$uid)->select();
            $this->assign('ubanklist',$ubanklist);
            return view();
        }

    }
    // 银行卡列表 --
    public function list_yhk()
    {
        $data=array('htitle' => '银行卡列表');
        $this->assign('headdata',$data);
        $uid       = Session::get('userinfo')['uid'];
        $list = model('freight_banklist')->where('uid', $uid)->select();
        $this->assign('list', $list);
        return view();
    }

    //添加银行卡
    public function add_yhk()
    {
        $data=array('htitle' => '添加银行卡');
        $this->assign('headdata',$data);
        if(request()->ispost())
        {
            $state=model('FreightBanklist')->dataSave($_POST);
            if($state)
            {
                $this->success('添加成功','Member/list_yhk');
            }else{
                $this->error('添加失败');
            }
        }else{
            //获取银行列表
            $banklist=model('bank')->select();
            $this->assign('banklist',$banklist);
            return view();
        }
    }

    //修改资料
    public function edit_ziliao()
    {
        $data=array('htitle' => '会员信息编辑');
        $this->assign('headdata',$data);
        if(request()->ispost())
        {
            $state=model('freight')->edit_ziliao($_POST);
            if($state==1)
            {
                $this->success('编辑成功');
            }else{
                $this->error('编辑失败');
            }
        }else{
            return view();
        }
    }

    //登录
    public function login()
    {
    	$data=array('htitle' => '会员登录');
    	$this->assign('headdata',$data);
        if(request()->ispost())
        {
            $w['telphone']=input('telphone');
            $w['pwd']=md5(config('other')['pwd_prefix'].input('pwd'));
            // var_dump($w);exit();
            $uinfo=model('freight')->where($w)->find();
            // echo model('freight')->getLastSql();
            // var_dump($uinfo);exit();
            // echo $uinfo['id'];exit();
            if($uinfo)
            {
                $s_userinfo=[
                    'uid'=>$uinfo['id'],
                    'telphone'=>$uinfo['telphone'],
                    'yajin'=>$uinfo['yajin'],
                    'status'=>$uinfo['status'],
                    'address'=>$uinfo['address'],
                    'carload'=>$uinfo['carload'],
                ];
                Cookie::set('userinfo',$s_userinfo);
                Session::set('userinfo',$s_userinfo);
                Session::set('jiedanStatue',1);
                $this->redirect('Member/index');
            }else{
                $this->error('登录失败，账号或密码有误');
            }

        }else{
            return view();
        }


    }

    //注册
    public function reg()
    {
    	$data=array('htitle' => '会员注册');
    	$this->assign('headdata',$data);
        return view();
    }
    public function reg1()
    {
        if(request()->ispost())
        {
            // paper_img   营业执照
            // certif_img  车辆证照图片
            // carinfo_img 其他图片信息
            //将数据保存到数据库中
            // echo '<pre>';
            // var_dump($_FILES);
            // exit();
            if($_FILES['paper_img']['tmp_name']=='' || $_FILES['certif_img']['tmp_name']=='' || $_FILES['carinfo_img']['tmp_name']=='')
            {
                return $this->error('请传入至少三张照片');
            }
            $filesArr['paper_img']=request()->file('paper_img');
            $filesArr['certif_img']=request()->file('certif_img');
            $filesArr['carinfo_img']=request()->file('carinfo_img');

            $files=action('Interfun/arrUpload',[$filesArr]);
            // var_dump($files);exit;
            $_POST['paper_img']=$files['paper_img'];
            $_POST['certif_img']=$files['certif_img'];
            $_POST['carinfo_img']=$files['carinfo_img'];
            $state=model('Freight')->reg1($_POST);
            if($state==1) {
                $name=$_POST['name'];
                $mobile=$_POST['telphone'];
                $id=model('freight')->getLastInsID();
                //先对手机进行实名验证 然后进行手机号验证
                $this->assign(['name'=>$name,'mobile'=>$mobile,'id'=>$id]);
                return view();
            }elseif($state==2) {
                return $this->error('注册失败，请检查网络重新注册');
            }else {
                return $this->error($state);
            }
        }else{
            // $mobile=Session::get('sms_mobile');
            $mobile=Session::get('sms_mobile')?Session::get('sms_mobile'):'15188316549';
            $this->assign('mobile',$mobile);
            //获取当前会员数据
            $data=model('Freight')->where('telphone',$mobile)->column('name','id');
            return view();
        }

        // $mobile=Session::get('sms_mobile')?Session::get('sms_mobile'):'15188316549';

        // $this->assign(['name'=>'田姝童','mobile'=>$mobile,'id'=>1]);
        // return view();
    }
    public function reg2()
    {
        //获取数据
        if(request()->ispost())
        {
            $data=$_POST;
            $code=Session::get('sms_mobile_code');
            if(config('other')['smsyz_status']==1 && $data['telcode']!=$code)
            {
                $this->error('验证码有误','Member/reg1');exit();
            }
            //初始化密码123456
            model('freight')->where('telphone', $data['telphone'])->update(['status'=>'3','pwd' => md5(config('other')['pwd_prefix'].'123456')]);
            $uid=model('freight')->where('telphone', $data['telphone'])->value('id');
            $this->assign(['returnUrl'=>'Member/reg_act','uid'=>$uid,'paybody'=>'注册押金','yajin'=>config('other')['zhuce_yajin']]);
            return view();

        }else{
            if(Session::get('sms_mobile'))
            {
                $mobile=Session::get('sms_mobile');
                $uid=model('freight')->where('telphone', $mobile)->value('id');
                $this->assign(['returnUrl'=>'Member/reg_act','uid'=>$uid,'paybody'=>'注册押金','yajin'=>config('other')['zhuce_yajin']]);
                return view();
            }
        }
    }
    //注册押金
    public function reg_yajin()
    {
        if(request()->ispost())
        {
            if(input('trade_type')==Null)
            {
                $this->error('请选择支付方式','Member/reg2');exit();
            }
            if(input('amount')<config('other')['zhuce_yajin'])
            {
                $this->error('押金不能少于'.config('other')['zhuce_yajin'].'元');exit();
            }
            $data=[
                'trade_type'=>input('trade_type'),
                'out_trade_no'=>date('YmdHis').time(),
                'body'=>input('body'),
                'total_fee'=>input('amount'),
                'spbill_create_ip'=>request()->ip(),
                'uid'=>input('uid'),
                'cur_amount'=>0,
                'return_url'=>'Member/reg_act'
            ];
            $state=model('freight_paylog')->dataSave($data,1,1);
            if($state==1)
            {
                // action('Pay/index',[$data]);
                return $this->redirect('Member/reg_act');
            }
        }else{
            return $this->redirect('Member/login');
        }
    }
    //注册成功地址
    public function reg_act()
    {
        // Session::set('userinfo','');
        return view();
    }

    //获取手机验证码
    public function mobileSend()
    {
        $mobile=input('mobile');
        if (empty($mobile)) {
            exit(json_encode(array('code'=>3,'msg' => '手机号码不能为空')));
        }
        if(config('other')['smsyz_status']==0)
        {
            Session::set('sms_mobile',$mobile);
            exit(json_encode(array('code'=>5)));  //短信验证已关闭
        }

        $preg = '/^1[0-9]{10}$/'; //简单的方法
        if (!preg_match($preg, $mobile)) {
            exit(json_encode(array('code'=>3,'msg' => '手机号码格式不正确')));
        }
        if (Session::get('sms_mobile') && (time()-Session::get('sms_mobile_time'))<60) {
            exit(json_encode(array('msg' => '获取验证码太过频繁，一分钟之内只能获取一次。')));
        }

        $mobile_code = random(6, 1);
        $message = "您的验证码是：" . $mobile_code . "，请不要把验证码泄露给其他人，如非本人操作，可不用理会！".config('other')['mobile_sms'];
        $num = sendSMS($mobile, $message);
        if($num == true)
        {
            Session::set('sms_mobile',$mobile);
            Session::set('sms_mobile_code',$mobile_code);
            Session::set('sms_mobile_time',time());
            // exit(json_encode(array(
            //     'code' => 2,'mobile_code' => $mobile_code
            // )));
            exit(json_encode(array(
                'code' => 2
            )));
        }
        else
        {
            exit(json_encode(array(
                'code'=>3,
                'msg' => '手机验证码发送失败。'
            )));
        }
    }
}
