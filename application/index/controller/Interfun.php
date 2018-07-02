<?php
namespace app\index\controller;
use think\Controller;

//接口类
class Interfun extends Controller
{
	//单图片上传
	public function imgupload()
	{
		$model=input('shujubiao');
		$id=input('lookid');
		$ziduan=input('ziduan');
		$file = request()->file('uploadimg');
		// var_dump($file);exit;
		$imgdata=$this->upload($file);
		if($imgdata['code']==1)
		{
			$updata=[
				$ziduan => $imgdata['path'],
				'id'=>$id
			];
			$state=model($model)->updateimg($updata);
			if($state==1)
			{
				echo json_encode(['code'=>1,'msg'=>'上传成功']);
			}else{
				echo json_encode(['code'=>3,'msg'=>'保存失败']);
			}
		}else{
			echo json_encode(['code'=>2,'msg'=>$imgdata['msg']]);
		}

	}

	/**
	 * 单图片上传
	 * @param  [type]  $file [一维数组]
	 * @return [type]        [description]
	 */
	public function upload($file){
		// var_dump($file);
		$data=[];
	    // 移动到框架应用根目录/public/uploads/ 目录下
	    if($file && count($file)==1){
	        $info = $file->move(config('imguploads')['rootPath']);
	        // var_dump($file);exit;
	        if($info){
	        	$data['code']=1;
	        	$data['msg']='上传成功';
	        	$path='\\'.config('imguploads')['rootPath'].DS.$info->getSaveName();
	        	$path       = strtr($path,'\\','/');
	        	$data['path']=$path;
	            // 成功上传后 获取上传信息
	            // 输出 jpg
	            // echo $info->getExtension().'<br>';
	            // // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
	            // echo $info->getSaveName().'<br>';
	            // // 输出 42a79759f284b767dfcb2a0197904287.jpg
	            // echo $info->getFilename().'<br>';
	        }else{
	        	$data['code']=2;
	        	$data['msg']=$file->getError();
	        	$data['path']='';
	            // 上传失败获取错误信息
	            // echo $file->getError();
	        }
	    }
	    return $data;
	}

	//多图片上传
	public function arrUpload($data)
	{
		$result=array();
		// echo '<pre>';
		// var_dump($data);exit;
		// echo '<pre>';
		foreach ($data as $k => $v) {
			$getupload=$this->upload($v);
			// var_dump($getupload);
			$result[$k]=$getupload['path'];
		}
		// exit();
		return $result;
	}



	// 市场订单实时查询
	public function marketOrder()
	{

		$last_time=input('serv_time');
		$last_time=date('Y-m-d H:i:s', $last_time);
		$uid=input('uid');
		$mid=input('mid');

		//获取当前区域中的所有市场
		$market=model('market')->curMarketStr($uid);

		// 判断当前市场下是否有新订单
		$curMarketStr=$mid;
		$map2                   = array();
		$map2['market_id']      = array('in', $curMarketStr);
		$map2['status']         = 9;//已发货
		$map2['pay_status']     = 1;//已付款
		$map2['freight_status'] = 0;
		$map2['seller_finish_time']=['>',$last_time]; //支付时间
		$list=model('order_market')->field('id,market_id')->where($map2)->order('id desc')->find();
		// echo model('order_market')->getLastSql();
		$serv_time=time();
		if($list)
		{
			$market_name=$market['list'][$list['market_id']];
			$tishi="<div class='push_show'>".$market_name."又新增订单了</div>";
		}else{
			$tishi="";
		}
		echo json_encode(['code'=>1,'tishi'=>$tishi,'serv_time'=>$serv_time]);
	}
	//市场订单数量实时查询
	public function marketOrderNum()
	{
		$last_time=input('serv_time');
		$last_time=date('Y-m-d H:i:s', $last_time);
		$uid=input('uid');
		$type=input('type');
		if($type==0)
        {
           $type='0,1,2';
        }else{
            $type='3';
        }

		//获取当前区域中的所有市场
		$market=model('market')->curMarketStr($uid);
		$curMarketStr=$market['str'];
		$map2                   = array();
		$map2['market_id']      = array('in', $curMarketStr);
		$map2['status']         = 9;//已发货
		$map2['pay_status']     = 1;//已付款
		$map2['status_type']    = ['in', $type];
		$map2['freight_status'] = 0;
		$map2['seller_finish_time']=['>',$last_time]; //支付时间
		$orderlist                 = model('order_market')
			->field('market_id,count(market_id) as num')
			->where($map2)
			->group('market_id')
			->select();
		// echo model('order_market')->getLastSql();
		$orderlist=get_newData($orderlist,'market_id','num');
		$serv_time=time();
		echo json_encode(['code'=>1,'list'=>$orderlist,'serv_time'=>$serv_time,'market'=>$curMarketStr]);
	}
}
