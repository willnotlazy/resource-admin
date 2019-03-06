<?php
// +----------------------------------------------------------------------
// | vaeThink [ Programming makes me happy ]
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.vaeThink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 听雨 < 389625819@qq.com >
// +---------------------------------------------------------------------
namespace app\admin\controller;
use think\Db;
use vae\controller\AdminCheckAuth;
use app\common\model\Article as ArticleModel;
use think\Config;

class ArticleController extends AdminCheckAuth
{
    public function index()
    {
        return view();
    }

    //列表
    public function getContentList()
    {
    	$param = vae_get_param();
        $where = array();
        if(!empty($param['keywords'])) {
            $where['a.id|a.title|a.keywords|a.desc|a.content|w.title'] = ['like', '%' . $param['keywords'] . '%'];
        }
        if(!empty($param['article_cate_id'])) {
            $where['a.article_cate_id'] = $param['article_cate_id'];
        }
        $rows = empty($param['limit']) ? \think\Config::get('paginate.list_rows') : $param['limit'];
        $content = \think\Db::connect(Config::get('resource'))
                                ->name('user_post')
                                ->field('a.*,c.name as classifyName,d.name as pidName')
                                ->alias('a')
                                ->join('res_user_resource_classify c','a.classify = c.classifyID')
                                ->join('res_user_resource_classify d', 'a.second_classify = d.classifyID')
                                ->order('a.postTime desc')
                                ->paginate($rows,false,['query'=>$param])
                                ->each(function ($item, $key){
                                   if ($item['authorID'] == 0)
                                   {
                                       $item['author'] = 'admin';
                                   }
                                   else
                                   {
                                       $authorID = $item['authorID'];
                                       $author = \think\Db::connect(Config::get('resource'))->name('user')->where('id',$authorID)->find()['username'];
                                       $item['author'] = $author;
                                   }
                                   return $item;
                                });
    	return vae_assign_table(0,'',$content);
    }

    //添加
    public function add()
    {
    	return view();
    }

    //提交添加
    public function addSubmit()
    {
    	if($this->request->isPost()){
    		$param = vae_get_param();
    		if (in_array($param['second_classify'],[1,2,3,4,5,6,7,8,9,10]))
    		    return vae_assign('','请选择子分类');
    		$classify = \think\Db::connect(Config::get('resource'))->name('user_resource_classify')->where('classifyID',$param['second_classify'])->find();
    		$param['classify']       = $classify['pid'];
    		$param['cover']     = $this->request->domain() . str_replace('\\','/',$param['cover']);
    		$param['checked']   = vae_get_login_admin()['nickname'];
            $param['authorID']  = 0;
            $param['postTime']  = time();
    		unset($param['file']);
            \think\Db::connect(Config::get('resource'))->name('user_post')->strict(false)->field(true)->insert($param);
            return vae_assign();
    	}
    }

    //修改
    public function edit()
    {
        return view('',['article'=>vae_get_article_info(vae_get_param('id')),'author'=>vae_get_param('author')]);
    }

    //提交修改
    public function editSubmit()
    {
        $reply_msg = array();
        if($this->request->isPost()){
            $param = vae_get_param();
            $data = [
                'couldPost'    => $param['couldPost']
                ,'checked'     => vae_get_login_admin()['nickname']
                ,'checkStatus' => 1
                ,'boutique'    => $param['boutique']
            ];
            $reply_msg['reply_time'] = time();
            $reply_msg['uid'] = $param['uid'];
            $reply_msg['post_title'] = $param['title'];
            if ($param['couldPost'] == -1) {
                $data['checkStatus'] = -1;
                $data['failReason'] = $param['reason'];
                $reply_msg['msg_content'] = '您的文章已审核完毕,因为部分内容不符合站内要求,该文章将不予以发布。';
            }
            else{
                $reply_msg['msg_content'] = '您的文章已审核完毕,通过审核。';
            }
            $id = $param['postID'];
            unset($param);
            \think\Db::connect(Config::get('resource'))->name('user_post')->where(['postID'=>$id])->strict(false)->field(true)->update($data);
            \think\Db::connect(Config::get('resource'))->name('msg_reply')->insert($reply_msg);
            unset($reply_msg);
            \think\Cache::clear('VAE_ARTICLE_INFO');
            return vae_assign(1,'提交成功');
        }
    }

    //软删除
    public function delete()
    {
        $id    = vae_get_param("id");
        if (ArticleModel::destroy($id) !== false) {
            return vae_assign(1,"成功放入回收站！");
            \think\Cache::clear('VAE_ARTICLE_INFO');
        } else {
            return vae_assign(0,"删除失败！");
        }
    }
}
