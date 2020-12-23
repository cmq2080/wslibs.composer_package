<?php

/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2020/5/19
 * Time: 16:24
 */

namespace wslibs\composer_package\app\admin;

use wslibs\composer_package\libs\Constant;
use epii\orm\Db;
use epii\server\Args;

class project_group extends base
{
    public function index()
    {
        try {
            $this->adminUiDisplay();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function ajax_data()
    {
        try {
            $where = [];
            if ($projectGroupName = Args::params('project_group_name')) {
                $where[] = [
                    'project_group_name', 'like', '%' . $projectGroupName . '%'
                ];
            }

            return $this->tableJsonData('project_group', $where, function ($row) {
                $row['create_time'] = ($row['create_time'] ? date('Y-m-d H:i:s', $row['create_time']) : '-');
                $row['update_time'] = ($row['update_time'] ? date('Y-m-d H:i:s', $row['update_time']) : '-');
                return $row;
            });
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 新增
     * @return array|false|string
     */
    public function add()
    {
        try {
            $projectGroupId = Args::params('project_group_id/d');
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $projectGroupName = Args::params('project_group_name/1');
                $timestamp = time();

                $insertData = [
                    'project_group_name' => $projectGroupName,
                ];

                if ($projectGroupId) { // 修改
                    $insertData['update_time'] = $timestamp;
                    $res = Db::name(Constant::TABLE_PROJECT_GROUP)->where('id', $projectGroupId)->update($insertData);
                    if (!$res) {
                        throw new \Exception('新增失败');
                    }
                } else { // 添加
                    $insertData['create_time'] = $timestamp;
                    $res = Db::name(Constant::TABLE_PROJECT_GROUP)->insert($insertData, false, true);
                    if (!$res) {
                        throw new \Exception('添加失败');
                    }
                }

                $this->success();
            } else {
                if ($projectGroupId) {
                    $projectGroup = Db::name('project_group')->where('id', $projectGroupId)->find();
                    $this->assign('projectGroup', $projectGroup);
                }

                $this->adminUiDisplay();
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 删除
     */
    public function delete()
    {
        try {
            $id = Args::params('project_group_id/d/1');

            $res = Db::name(Constant::TABLE_PROJECT_GROUP)->where('id', $id)->delete();
            if (!$res) {
                throw new \Exception('删除失败');
            }

            $this->success('删除成功', 'refresh');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
