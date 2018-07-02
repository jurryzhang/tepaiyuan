<?php
namespace app\gys\controller;
use think\Controller;

//接口类
class Interfun extends Controller
{
	//单图片上传
	public function imgupload()
	{
		$model  = input('shujubiao');
		$id     = input('lookid');
		$ziduan = input('ziduan');
		$file   = request()->file('uploadimg');
		// var_dump($file);exit;
		$imgdata = $this->upload($file);
		if ($imgdata['code'] == 1)
		{
			$updata = [
				$ziduan => $imgdata['path'],
				'id'    => $id
			];
			$state  = model($model)->updateimg($updata);

			if ($state == 1)
			{
				echo json_encode(['code' => 1, 'msg' => '上传成功']);
			}
			else
			{
				echo json_encode(['code' => 3, 'msg' => '保存失败']);
			}
		}
		else
		{
			echo json_encode(['code' => 2, 'msg' => $imgdata['msg']]);
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
	        	$data['path']='\\'.config('imguploads')['rootPath'].DS.$info->getSaveName();
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
}
