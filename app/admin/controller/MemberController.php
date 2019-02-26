<?php
/**
 * Created by PhpStorm.
 * User: 欢迎
 * Date: 2019/2/11
 * Time: 15:42
 */
namespace app\admin\controller;
use think\Config;
use vae\controller\AdminCheckAuth;

class MemberController extends AdminCheckAuth
{
    public function index()
    {
        return view();
    }


    public function getMemberList()
    {
        $param = vae_get_param();
        $where = array();
        if(!empty($param['keywords'])) {
            $where['id|username|nickname|desc|phone'] = ['like', '%' . $param['keywords'] . '%'];
        }
        $rows = empty($param['limit']) ? \think\Config::get('paginate.list_rows') : $param['limit'];
        $admin = \think\Db::connect(\think\Config::get('resource'))
            ->name('user')
            ->field('id,thumb,username,level,accumulatedLoginDays,consecutiveLoginDays,couldLogin,email')
            ->order('id asc')
            ->where($where)
            ->paginate($rows,false,['query'=>$param]);
        return vae_assign_table(0,'',$admin);
    }

    public function edit()
    {
        return view('',['member'=>vae_get_member(vae_get_param('id'))]);
    }


    public function editSubmit()
    {
        if ($this->request->isPost())
        {
            $param = vae_get_param();
            $thumb = $param['thumb'];
            unset($param['file']);
            if (strpos($thumb,'dev-resource.com') === false)
                $param['thumb'] = "http://admin.dev-resource.com" . str_replace('\\','/',$thumb);
            \think\Db::connect(Config::get('resource'))->name('user')->update($param);
            return vae_assign();
        }
    }
}