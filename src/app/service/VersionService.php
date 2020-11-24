<?php
/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2020/5/21
 * Time: 13:49
 */

namespace composer\packages\app\service;


use epii\orm\Db;

class VersionService
{
    /**
     * 获取最近版本的数据
     * @param $projectId
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getLastVersion($projectId)
    {
        $lastVersion = Db::name('version')->where('project_id', $projectId)->order('id', 'desc')->find();
        return $lastVersion;
    }

    /**
     * 获取最近版本的来源
     * @param $projectId
     * @return int|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getLastSource($projectId)
    {
        $lastVersion = self::getLastVersion($projectId);
        return $lastVersion['source'] ?? 0;
    }

    /**
     * 获取新（准备）版本号
     * @param $projectId
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getNewVersionName($projectId)
    {
        $lastVersion = self::getLastVersion($projectId);
        if (!$lastVersion) {
            return '';
        }

        $versionArr = explode('.', $lastVersion['version_name']);
        $versionArr[count($versionArr) - 1]++;

        return implode('.', $versionArr);
    }

    /**
     * 获取最近版本所在的仓库名
     * @param $projectId
     * @return mixed|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getLastRepoName($projectId)
    {
        $lastVersion = self::getLastVersion($projectId);
        return $lastVersion['repo_name'] ?? '';
    }

    public static function exists($where)
    {
        $version = Db::name('version')->where($where)->field('id')->find();
        return $version ? true : false;
    }
}