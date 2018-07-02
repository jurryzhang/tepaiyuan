<?php
namespace app\index\controller;
use think\Session;

//特派员订单类
class Order extends Indexbase
{
    public function index()
    {
        // echo Indexbase::Site_config();exit();
    	$data=array('htitle' => '特派员订单');
    	$this->assign('headdata',$data);
    	//获得
        if(Session::get('userinfo'))
        {
            $uid=Session::get('userinfo')['uid'];
        }else{
            $this->error('请先登录','Member/login');
        }
        if(input('type')=='yes')
        {
            $map['status']=array('in','5,11,10');
            $type='yes';
        }elseif(input('type')=='no')
        {
            $map['status']=array('not in','5,11,10');
            $type='no';
        }else{
            $map=array();
            $type='all';
        }
        $orderlist=model('order_market')->getlist($uid,$map);
        $this->assign('orderlist',$orderlist);
        $this->assign('ym_type',$type);
        $this->assign('empty',"<div class='empty'>暂无数据记录</div>");
        return view();
    }

    public function details()
    {
        $data=array('htitle' => '订单详情');
        $this->assign('headdata',$data);

        $id=input('id');
        $orderdetails=model('order_market')->getDetails($id);
        if($orderdetails['code']==0)
        {
            $this->error($orderdetails['msg']);
        }
        // echo '<pre>';var_dump($orderdetails);exit();
        $this->assign('orderdetails',$orderdetails);
        return view();
    }
    public function updateStatus()
    {
        $oid=input('oid');
        $type=input('type');
        $w=array();
        if($type=='quhuo')
        {
            $w=['id'=>$oid,'freight_status'=>1];
            $status=2;
        }elseif($type=='queren')
        {
            $w=['id'=>$oid,'freight_status'=>2];
            $status=3;
        }
        $list=model('order_market')->where($w)->select();
        // echo model('order_market')->getLastSql();
        // echo '<pre>';var_dump($list);exit;
        if(count($list)==1)
        {
            model('order_market')->idUpdate($oid,$list[0]['freight_id'],$list[0]['orderid_str'],$status);
            $this->success('确认成功');
        }else{
            $this->error('操作有误');
        }


    }
}
