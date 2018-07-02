<?php
namespace app\common\model;
use think\Model;

class Areas extends Model
{
	public function index()
	{}	
	/**
	 * [get_regions 获取指定parent_id下的所有列表]
	 * @param  integer $parent [parent_id]
	 * @param  integer $type   [description]
	 * @return [type]          [description]
	 */
	public function get_regions($parent = 0,$type=0)
	{
		$map=array();
		$map['parent_id']=$parent;
		$list=$this->where($map)->field('area_id,area_name')->select();
		$list=get_newData($list,'area_id','area_name');
		return $list;
	}
	public function getAreasSelect($parent,$val=0)
	{
		$list=$this->get_regions($parent);
		$temp='';
		if($list)
		{
			foreach ($list as $k => $v) {
				if($k==$val)
				{
					$select='selected';
				}else{
					$select='';
				}
				$temp .="<option value='".$k."' ".$select." >".$v."</option>";
			}
		}
		return $temp;
	}
}
