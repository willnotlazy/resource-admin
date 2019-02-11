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
use vae\controller\AdminCheckAuth;

class MenuController extends AdminCheckAuth
{
    public function index()
    { 
        return view();
    }

    //菜单列表
    public function getMenuList()
    {
    	$menu = \think\Db::name('admin_menu')->order('order asc')->select();
    	return vae_assign(0,'',$menu);
    }

    //添加菜单页面
    public function add()
    {
    	return view('',['pid'=>vae_get_param('pid')]);
    }

    //提交添加
    public function addSubmit()
    {
    	if($this->request->isPost()){
            $param = vae_get_param();
    		$result = $this->validate($param, 'app\admin\validate\AdminMenu.add');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                \think\Db::startTrans();
                try{
                    $mid = \think\loader::model('AdminMenu')->strict(false)->field(true)->insertGetId($param);

                    //自动为系统所有者管理组分配新增的菜单
                    $group = \think\loader::model('AdminGroup')->find(1);
                    if(!empty($group)) {
                        $group->menus = $group->menus.','.$mid;
                        $group->save();
                    }
                    \think\Db::commit();
                } catch (\Exception $e)
                {
                    \think\Db::rollback();
                    return vae_assign(0,'数据库错误');
                }
                //清除菜单缓存
                \think\Cache::clear('VAE_ADMIN_MENU');
                return vae_assign();
            }
    	}
    }

    //提交修改
    public function editSubmit()
    {
        if($this->request->isPost()) {
        	$param = vae_get_param();
        	$result = $this->validate($param, 'app\admin\validate\AdminMenu.edit');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
            	$data[$param['field']] = $param['value'];
            	$data['id'] = $param['id'];
                \think\Db::startTrans();
                try{
                    \think\loader::model('AdminMenu')->strict(false)->field(true)->update($data);
                    \think\Db::commit();
                } catch (\Exception $e) {
                    \think\Db::rollback();
                }

                //清除菜单缓存
                \think\Cache::clear('VAE_ADMIN_MENU');
                return vae_assign();
            }
        }
    }

    //删除
    public function delete()
    {
        $id    = vae_get_param("id");
        $count = \think\Db::name('AdminMenu')->where(["pid" => $id])->count();
        if ($count > 0) {
            return vae_assign(0,"该菜单下还有子菜单，无法删除！");
        }
        \think\Db::startTrans();
        try{
            if (\think\Db::name('AdminMenu')->delete($id) !== false) {
                \think\Db::commit();
                \think\Cache::clear('VAE_ADMIN_MENU');// 删除后台菜单缓存
                return vae_assign(1, "删除菜单成功！");
            }
        } catch (\Exception $e) {
            \think\Db::rollback();
            return vae_assign(0,"删除失败！");
        }
    }
}
