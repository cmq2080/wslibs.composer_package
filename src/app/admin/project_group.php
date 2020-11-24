<?php
/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2020/5/19
 * Time: 16:24
 */

namespace composer\packages\app\admin;


use composer\packages\libs\Constant;
use epii\admin\center\admin_center_addons_controller;
use epii\admin\ui\lib\epiiadmin\jscmd\Alert;
use epii\admin\ui\lib\epiiadmin\jscmd\JsCmd;
use epii\orm\Db;
use epii\server\Args;
use think\db\Expression;

class project_group extends admin_center_addons_controller
{
    public function index()
    {
        try {
            $this->assign('addons', Constant::ADDONS);
            $this->adminUiDisplay();
        } catch (\Exception $e) {
            $cmd = Alert::make()->icon('5')->msg($e->getMessage())->onOk(null);
            return JsCmd::make()->addCmd($cmd)->run();
        }
    }

    public function ajax_data()
    {
        try {
            $where = [
                'project_group_name' => ($projectGroupName = Args::params('project_group_name')) ? new Expression('like "%' . $projectGroupName . '%"') : null
            ];
            $where = array_filter($where);

            return $this->tableJsonData('project_group', $where, function ($data) {
                $data['create_time'] = date('Y-m-d H:i:s', $data['create_time']);
                $data['update_time'] = date('Y-m-d H:i:s', $data['update_time']);
                return $data;
            });
        } catch (\Exception $e) {
            $cmd = Alert::make()->icon('5')->msg($e->getMessage())->onOk(null);
            return JsCmd::make()->addCmd($cmd)->run();
        }
    }

    /**
     * 新增
     * @return array|false|string
     */
    public function add()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $projectGroupName = Args::params('project_group_name/1');
                $timestamp = time();

                $insertData = [
                    'project_group_name' => $projectGroupName,
                    'create_time' => $timestamp,
                    'update_time' => $timestamp,
                ];

                $res = Db::name('project_group')->insert($insertData);
                if (!$res) {
                    $cmd = Alert::make()->icon('5')->msg('添加失败')->onOk(null);
                    return JsCmd::make()->addCmd($cmd)->run();
                }

                return JsCmd::alertCloseRefresh('成功');
            } else {
                $this->assign('addons', Constant::ADDONS);
                $this->adminUiDisplay();
            }
        } catch (\Exception $e) {
            $cmd = Alert::make()->icon('5')->msg($e->getMessage())->onOk(null);
            return JsCmd::make()->addCmd($cmd)->run();
        }
    }

    /**
     * 修改
     */
    public function edit()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = Args::params('id/1');
                $projectGroupName = Args::params('project_group_name/1');

                $updateData = [
                    'project_group_name' => $projectGroupName,
                    'update_time' => time()
                ];

                $res = Db::name('project_group')->where('id', $id)->update($updateData);
                if (!$res) {
                    $cmd = Alert::make()->icon('5')->msg('修改失败')->onOk(null);
                    return JsCmd::make()->addCmd($cmd)->run();
                }

                return JsCmd::alertCloseRefresh('成功');
            } else {
                $id = Args::params('id/1');
                $projectGroup = Db::name('project_group')->where('id', $id)->find();
                if (!$projectGroup) {
                    $cmd = Alert::make()->icon('5')->msg('项目组不存在')->onOk(null);
                    return JsCmd::make()->addCmd($cmd)->run();
                }
                $this->assign('projectGroup', $projectGroup);

                $this->assign('addons', Constant::ADDONS);
                $this->adminUiDisplay('admin/project_group/add');
            }
        } catch (\Exception $e) {
            $cmd = Alert::make()->icon('5')->msg($e->getMessage())->onOk(null);
            return JsCmd::make()->addCmd($cmd)->run();
        }
    }

    /**
     * 删除
     */
    public function delete()
    {
        try {
            $id = Args::params('id/1');

            $res = Db::name('project_group')->where('id', $id)->delete();
            if (!$res) {
                $cmd = Alert::make()->icon('5')->msg('删除失败#1')->onOk(null);
                return JsCmd::make()->addCmd($cmd)->run();
            }

            return JsCmd::alertRefresh("成功");
        } catch (\Exception $e) {
            $cmd = Alert::make()->icon('5')->msg($e->getMessage())->onOk(null);
            return JsCmd::make()->addCmd($cmd)->run();
        }
    }
}