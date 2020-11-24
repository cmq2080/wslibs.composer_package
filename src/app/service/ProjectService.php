<?php
/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2020/5/20
 * Time: 19:14
 */

namespace composer\packages\app\service;


use composer\packages\libs\Constant;
use composer\packages\libs\Uri;
use epii\orm\Db;
use epii\server\Tools;

class ProjectService
{
//    public static function getLastSource($projectId)
//    {
//        $lastVersion = Db::name('version')->where('project_id', $projectId)->order('id', 'desc')->find();
//        return $lastVersion['source'] ?? 0;
//    }

    /**
     * 检测项目是否存在
     * @param $where
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function exists($where)
    {
        $project = Db::name('project')->where($where)->field('id')->find();
        return $project ? true : false;
    }

    /**
     * 生成packages.json文件
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\db\exception\PDOException
     */
    public static function autoMake()
    {
        $data = ['packages' => []];
        $projectGroups = self::all();
        foreach ($projectGroups as $projectGroup) {
            foreach ($projectGroup['projects'] as $project) {
                $data['packages'][$project['project_name']] = self::getProjectInfo($project);
            }
        }

        @file_put_contents(Tools::getRootFileDirectory() . '/packages.json', json_encode($data, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));

        return true;
    }

    /**
     * 获取所有项目组信息，树状返回
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function all()
    {
        $projectGroups = Db::name('project_group')->select();
        $projects = Db::name('project')->select();
        $versions = Db::name('version')->select();

        $data = [];
        foreach ($projectGroups as $projectGroup) {
            $projectGroup['projects'] = self::getProjects($projectGroup['id'], $projects, $versions);
            $data[] = $projectGroup;
        }

        return $data;
    }

    /**
     * 获取项目集合
     * @param $projectGroupId
     * @param $projects
     * @param $versions
     * @return array
     */
    private static function getProjects($projectGroupId, $projects, $versions)
    {
        $data = [];
        foreach ($projects as $project) {
            if ($project['project_group_id'] != $projectGroupId) { // 不属于该项目组下的项目，直接跳过
                continue;
            }
            $project['versions'] = self::getVersions($project['id'], $versions);
            $project['add_url'] = Uri::make('index\\version', 'add', ['project_id' => $project['id']]);
            $data[] = $project;
        }

        return $data;
    }

    /**
     * 获取版本集合
     * @param $projectId
     * @param $versions
     * @return array
     */
    private static function getVersions($projectId, $versions)
    {
        $data = [];
        foreach ($versions as $version) {
            if ($version['project_id'] != $projectId) {
                continue;
            }
            $version['sub_project_name'] = json_decode($version['version_json'], true)['name'];
            $data[] = $version;
        }

        return $data;
    }

    /**
     * （生成packages.json专用）获取项目composer信息
     * @param $project
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\PDOException
     */
    public static function getProjectInfo($project)
    {
        $projectInfo = [];
        foreach ($project['versions'] as $version) {
            $projectInfo[$version['version_name']] = json_decode(self::getVersionInfo($version), true);
        }

        return $projectInfo;
    }

    /**
     * （生成packages.json专用）获取版本composer信息
     * @param $version
     * @param $projectName
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\PDOException
     */
    public static function getVersionInfo($version)
    {
        if ($version['version_json']) { // 有则直接取
            return $version['version_json'];
        }
        // 没有再访问接口
        $result = self::getVersionInfoFromApi($version['repo_name'], $version['version_name']);
        if ($result) {
            // 获取的接口信息存入数据库中
            Db::name('version')->where('id', $version['id'])->update(['version_json' => json_encode($result, JSON_UNESCAPED_UNICODE)]);
        }

        return $result;
//        $params = [
//            'app' => 'composer',
//            'repo' => $projectName,
//            'git_origin' => '1'
//        ];
//        $params['version'] = $version['version_name'];
//        $url = Constant::COMPOSER_API_URL . '?' . http_build_query($params);
//        $res = json_decode(file_get_contents($url), true);
//        if ($res['code'] != 1) { // 接口请求失败
//            return '';
//        }
//        // 获取的接口信息存入数据库中
//        Db::name('version')->where('id', $version['id'])->update(['version_json' => json_encode($res['data'], JSON_UNESCAPED_UNICODE)]);
//
//        return $res['data'];
    }

    /**
     * 从API中获取版本composer信息
     * @param $repoName
     * @param $versionName
     * @return |null
     */
    public static function getVersionInfoFromApi($repoName, $versionName)
    {
        // 访问接口
        $params = [
            'app' => 'composer',
            'repo' => $repoName,
            'git_origin' => '1',
            'version' => $versionName,
        ];
        $url = Constant::COMPOSER_API_URL . '?' . http_build_query($params);
        $res = json_decode(file_get_contents($url), true);
        if ($res['code'] != 1) {
            return null;
        }
        if (isset($res['data']['name']) === false) {
            return null;
        }

        return $res['data'];
    }
}