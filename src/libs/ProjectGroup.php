<?php

/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2020/5/20
 * Time: 17:08
 */

namespace wslibs\composer_package\libs;


use epii\orm\Db;

class ProjectGroup
{
    /**
     * 获取下拉列表
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getOptions($where = [], $unshfitArray = null)
    {
        $projectGroups = Db::name('project_group')->where($where)->field('id,project_group_name as name')->select();

        if ($unshfitArray !== null) {
            array_unshift($projectGroups, $unshfitArray);
        }
        return $projectGroups;
    }

}
