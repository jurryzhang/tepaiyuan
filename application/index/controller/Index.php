<?php
namespace app\index\controller;
use think\Session;

class Index extends Indexbase
{
    public function index()
    {
    	$data=array('htitle' => '首页','hleft'=>'Member/index');
    	$this->assign('headdata',$data);

    	// echo '<pre>';
    	//获取特派员当前地址信息 定位附近市场
    	//获取当前用户的
    	if(Session::get('userinfo'))
    	{
    		$uid=Session::get('userinfo')['uid'];
            //获取会员区域信息
            $uinfo=model('freight')->where('id',$uid)->field('province,city,district')->find();
            $province=$uinfo['province'];
            $city=$uinfo['city'];
            $district=$uinfo['district'];
            //获取市级列表
            $cityTemp=model('areas')->getAreasSelect($province,$city);
            //获取区级列表
            $districtTemp=model('areas')->getAreasSelect($city,$district);

            $this->assign('province',$province);
            $this->assign('cityTemp',$cityTemp);
            $this->assign('districtTemp',$districtTemp);
    	}else{
            $uid=0;
        }
        $jiedanStatue=Session::get('jiedanStatue');
        if($jiedanStatue==null)
        {
            $jiedanStatue=1;
        }
        if($jiedanStatue==0)
        {
            $sellerlist=array();
            $empty="<div class='empty'>已停止接单</div>";
        }else{
            $sellerlist=model('market')->getNum($uid);
            $empty="<div class='empty'>暂无数据</div>";
        }
        $this->assign('uid',$uid);
        $this->assign('serv_time',time());
        $this->assign('sellerlist',$sellerlist);
    	$this->assign('empty',$empty);
        $this->assign('jiedanStatue',$jiedanStatue);
        return view();
    }
    public function marketNum()
    {
        $jiedanStatue=Session::get('jiedanStatue');
        if($jiedanStatue==null)
        {
            $jiedanStatue=1;
        }
        if($jiedanStatue==0)
        {
            echo json_encode(['code'=>'0','msg'=>'请先开启接单','temp'=>"<div class='empty'>已停止接单</div>"]);exit();
        }
        $status_type=input('type');
        if(Session::get('userinfo'))
        {
            $uid=Session::get('userinfo')['uid'];
        }else{
            $uid=0;
        }
        if($status_type==0)
        {
           $status_type='0,1,2';
        }else{
            $status_type='3';
        }
        $sellerlist=model('market')->getNum($uid,$status_type);
        $temp=$this->TempStr($sellerlist,'marketlist',input('type'));
        echo json_encode(['code'=>'1','temp'=>$temp]);exit();
    }
    public function jiedan()
    {
        $curstatus=input('curstatus');
        $status_type=input('curtype');
        Session::set('jiedanStatue',$curstatus);
        if($curstatus==0)
        {
            echo json_encode(['code'=>'1','temp'=>"<div class='empty'>已停止接单</div>"]);exit();
        }else{
            if(Session::get('userinfo'))
            {
                $uid=Session::get('userinfo')['uid'];
            }else{
                $uid=0;
            }
            if($status_type==1)
            {
               $status_type='1,2';
            }
            $sellerlist=model('market')->getNum($uid,$status_type);
            // var_dump($sellerlist);exit();
            $temp=$this->TempStr($sellerlist,'marketlist',input('curtype'));
            // var_dump($temp);exit;
            echo json_encode(['code'=>'1','temp'=>$temp]);exit();
        }
    }

    //指定市场订单列表（可进行抢单）
    public function shichanglist()
    {
    	//获取指定市场订单列表（预约/即时）
        $id=input('id');
        $status_type=input('type');
        if($status_type==0)
        {
            $status_type='0,1,2';
        }else{
            $status_type='3';
        }
        $name=model('market')->where('id',$id)->value('name');
        $data=array('htitle' => $name);
        $this->assign('mid',$id);
        $this->assign('headdata',$data);
        $this->assign('serv_time',time());
        $this->assign('cur_url','Index/shichanglist/id/'.$id.'/type/'.input('type'));

        $orderstr=input('order')==1?'asc':'desc';
        $order='id '.$orderstr;

        if(input('paixu')!='all' && input('paixu'))
        {
            $order=input('paixu').' '.$orderstr;
        }
        if(Session::get('userinfo'))
        {
            $uid=Session::get('userinfo')['uid'];
        }else{
            $uid=0;
        }

        $list=model('order_market')->getorderlist($id,$order,$uid,$status_type);
        
        $this->assign('uid',$uid);
        if(request()->isAjax())
        {
            $temp=$this->TempStr($list,'orderlist');
            // echo $temp;exit();
            echo json_encode(['code'=>'1','temp'=>$temp,'order'=>$order]);
        }else{
            $this->assign('empty',"<div class='empty'>暂无数据</div>");
            $this->assign('list',$list);
            return view();
        }

    }

    public function qiangdan()
    {
        if(!Session::get('userinfo'))
        {
           echo json_encode(['code'=>'3','msg'=>'请先登录！']);exit();
        }else{
            $uid=Session::get('userinfo')['uid'];
            $carload=Session::get('userinfo')['carload'];
        }
    	if(request()->isAjax()){
            $oid=input('oid');
            $map=array();
            $map['id']=$oid;
            // $map['freight_id']=0;
            $res=model('order_market')->where($map)->find();
            // var_dump($res);exit;
            //判断是否超出载重
            //1、获取当前载重量
            if($res)
            {
                if($res['freight_id']==$uid)
                {
                   echo json_encode(['code'=>'5','msg'=>'订单已收入囊中']);exit(); 
                }
                if($res['freight_id']>0)
                {
                    echo json_encode(['code'=>'2','msg'=>'已经被人抢先了啊！']);exit();
                }
                $state=model('order_market')->getCarload($uid,$oid,$carload);
                if($state==0)
                {
                    echo json_encode(['code'=>'4','msg'=>'超出车辆载重']);exit();
                }
                $state=model('order_market')->idUpdate($oid,$uid,$res['orderid_str']);
                if($state==1)
                {
                    echo json_encode(['code'=>'1','msg'=>'抢单成功！']);exit();
                }else{
                    echo json_encode(['code'=>'2','msg'=>'被人抢先了啊！']);exit();
                }
            }else{
                echo json_encode(['code'=>'0','msg'=>'订单查询出错!']);exit();
            }
        }
    }


}
