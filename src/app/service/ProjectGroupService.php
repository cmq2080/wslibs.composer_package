<?php
/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2020/5/20
 * Time: 17:08
 */

namespace composer\packages\app\service;


use epii\orm\Db;

class ProjectGroupService
{
    /**
     * 获取下拉列表
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getOptions()
    {
        $projectGroups = Db::name('project_group')->field('id,project_group_name as name')->select();
        return $projectGroups;
    }
}