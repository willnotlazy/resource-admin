<?php
/**
 * Created by PhpStorm.
 * User: 欢迎
 * Date: 2019/2/11
 * Time: 15:42
 */
namespace app\admin\controller;
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
}