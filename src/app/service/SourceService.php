<?php
/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2020/5/20
 * Time: 17:03
 */

namespace composer\packages\app\service;


class SourceService
{
    const SOURCE_SELF = 1;
    const SOURCE_GITHUB = 2;
    const SOURCE_GITEE = 3;
    const SOURCE_SVN = 4;

    public static function getOptions()
    {
        $sources = [
            ['id' => self::SOURCE_SELF, 'name' => '自有项目'],
            ['id' => self::SOURCE_GITHUB, 'name' => 'github'],
            ['id' => self::SOURCE_GITEE, 'name' => '码云'],
            ['id' => self::SOURCE_SVN, 'name' => 'svn'],
        ];

        return $sources;
    }

    public static function getDesc($id)
    {
        $map = [
            self::SOURCE_SELF => '自有项目',
            self::SOURCE_GITHUB => 'github',
            self::SOURCE_GITEE => '码云',
            self::SOURCE_SVN => 'svn'
        ];

        return $map[$id] ?? 'unknown';
    }
}