<?php
namespace app\index\controller;

class Article extends Indexbase
{
	//文章详情
    public function index()
    {
    	if(input('id'))
    	{
    		$id=input('id');
    	}else{
    		$id=config('other')['article_id'];
    	}
    	//获取对应文章详情
    	$article_info=model('article')->where('id',$id)->find();
        if(!$article_info)
        {
            $article_info['title']='文章详情';
            $article_info['content']='暂无内容';
        }
    	$data=array('htitle' => $article_info['title']);
    	$this->assign('headdata',$data);
    	$this->assign('article_info',$article_info);
        return view();
    }
    public function datalist()
    {
    	if(input('cid'))
    	{
    		$cid=input('cid');
    	}else{
    		$cid=config('other')['article_cid'];
    	}
    	$htite=model('article_category')->where('id',$cid)->value('name');
    	$article_list=model('article')->where('category_id',$cid)->select();
        if($htite=='')$htite='文章列表';
    	$data=array('htitle' => $htite);
        $this->assign('empty',"<div class='empty'>暂无文章</div>");
    	$this->assign('headdata',$data);
    	$this->assign('article_list',$article_list);
        return view();
    }
}
