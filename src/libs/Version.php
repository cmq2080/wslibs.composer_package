<?php

/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2020/5/21
 * Time: 13:49
 */

namespace wslibs\composer_package\libs;


use epii\orm\Db;

class Version
{
    /**
     * 获取新（准备）版本号
     * @param $lastVersionName
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getNewVersionName($lastVersionName = '')
    {
        if (!$lastVersionName) {
            return '';
        }

        $versionArr = explode('.', $lastVersionName);
        $versionArr[count($versionArr) - 1]++;

        return implode('.', $versionArr);
    }

    public static function exists($where)
    {
        $version = Db::name(Constant::TABLE_VERSION)->where($where)->field('id')->find(); // 理论上用find法比用count更好，因为find只要有，数据库就不会再往下查了，避免全表扫描
        return $version ? true : false;
    }
}
