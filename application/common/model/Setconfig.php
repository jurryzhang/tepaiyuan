<?php
namespace app\common\model;
use think\Model;

class Setconfig extends Model
{
	protected $table = 'iwebshop_config';
	public function index()
	{}	
	public function getval($name='')
	{
		if(empty($name)){
			$data=$this->select();
			return $data;
		}else{
			$val=$this->where('name',$name)->value('value');
			return $val;
		}
		
	}
}
