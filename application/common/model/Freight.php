<?php

namespace app\common\model;

use think\Model;
use think\Loader;
use think\Session;

class Freight extends Model
{
	public function edit_ziliao($data)
	{
		$validate=Loader::validate('Freight');
		if(!$validate->scene('edit_ziliao')->check($data)) {
			return $validate->getError();
		}
		$state=$this->update($_POST);
		if($state) {
			return 1;
		}else {
			return 2;
		}
	}

	//会员区域更新
	public function quyuUpdata($getdata)
	{
		// echo '<pre>';
		$province=$getdata['province'];
        $city=$getdata['city'];
        $district=$getdata['district'];
        $street=$getdata['street'];
        $map['area_name']  = ['IN',[$province,$city,$district,$street]];
        $data=model('areas')->where($map)->column('area_id,area_name,area_type,parent_id');
        // var_dump($data);
       	$updata['province']=$provinceid=$this->getAreaId($data,$province);//省
       	// var_dump($provinceid);
       	// echo '<hr>';
       	$updata['city']=$cityid=$this->getAreaId($data,$city,1,$provinceid);//市
       	// echo '<hr>';
       	// exit();
       	// var_dump($cityid);
       	$updata['district']=$districtid=$this->getAreaId($data,$district,2,$cityid);//区县
       	// echo '<hr>';
       	// var_dump($districtid);
       	$updata['street']=$this->getAreaId($data,$street,0,$districtid);//街道
       	// echo '<hr>';
       	$updata['id']=$getdata['id'];
       	// var_dump($updata);exit();
        //将所有的数据存到用户表中
        $this->update($updata);
        return $updata;
	}
	public function  getAreaId($data,$name,$type=0,$parent=0)
	{
		$area_id=0;
		if($data)
		{
			foreach ($data as $k => $v) {
				if($v['area_name']==$name && $v['parent_id']==$parent)
				{
					$areas_city=config('other')['areas_city'];
					$parent_id=$v['parent_id'];
					if(in_array($parent_id, $areas_city) && $v['area_type']==$type)
					{
						$area_id=$v['area_id'];
					}else{
						$area_id=$v['area_id'];
					}
					continue;
				}
			}
		}
		return $area_id;
	}

	//更新图片
	public function updateimg($data)
	{
		$id=$data['id'];
		$uimg=$this->where(['id'=>1])->value('uimg');
		$path=ROOT_PATH.'public'.$uimg;
		if($uimg && file_exists($path))
		{
			 unlink($path);
		}
		$state=$this->update($data);
		if($state) {
			return 1;
		}else {
			return 2;
		}
	}

    public function reg1($data) {
    	$data['status']=$data['status']=='on'?2:0;
        $data['grade']=1;
        $data['reg_time']=time();
        $state=$this->allowField(true)->save($data);
		if($state) {
			return 1;
		}else {
			return 2;
		}
	}
	
	public function login($data) {
		$validate=Loader::validate('User');
		if(!$validate->scene('login')->check($data)) {
			return $validate->getError();
		}
		$state=$this->where(['username'=>$data['username'],'password'=>$data['password']])->find();
		if($state) {
			Session::set('username',$data['username']);
			Session::set('level',$state['level']);
			return 1;
		}else {
			return 2;
		}
	}
}
