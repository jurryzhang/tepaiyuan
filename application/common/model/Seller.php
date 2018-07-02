<?php
namespace app\common\model;
use think\Model;

class Seller extends Model
{
	public function getlist($dingwei=[])
	{
		
	}

	public function reg1($data)
	{
		$data['status']      = $data['status'] == 'on' ? 2 : 0;
		$data['create_time'] = time();
		$state               = $this->allowField(TRUE)->save($data);
		if ($state)
		{
			return 1;
		}
		else
		{
			return 2;
		}
	}
	//更新图片
	public function updateimg($data)
	{
		$id=$data['id'];
		$uimg=$this->where(['id'=>$id])->value('certif_img');
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
}
