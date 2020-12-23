<?php

/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2020/5/20
 * Time: 19:14
 */

namespace wslibs\composer_package\libs;


use epii\admin\center\config\Settings;
use wslibs\composer_package\libs\Constant;
use wslibs\composer_package\libs\Uri;
use epii\orm\Db;
use epii\server\Tools;

class Project
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

    public static function getSourceOptions($unshfitArray = null)
    {
        $map = self::getSourceMap();
        foreach ($map as $id => $name) {
            $source[] = ['id' => $id, 'name' => $name];
        }

        if ($unshfitArray !== null) {
            array_unshift($source, $unshfitArray);
        }

        return $source;
    }

    public static function getSourceDesc($key)
    {
        $map = self::getSourceMap();

        return $map[$key] ?? '未知';
    }

    private static function getSourceMap()
    {
        $map = [
            Constant::SOURCE_SELF => '自有项目',
            Constant::SOURCE_GITHUB => 'github',
            Constant::SOURCE_GITEE => '码云',
            Constant::SOURCE_SVN => 'svn',
        ];

        return $map;
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
        $projectGroups = self::all(); // 直接跳过project group，搜project
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
        //        // 伪装demo
        //        $text = '{"stat":0,"msg":"ok","data":{"name":"epii\/admin-center","description":"通用后台管理中心，一件安装，全界面可定制！","authors":[{"name":"MrRen Epii","email":"543169072@qq.com"}],"license":"MIT","require":{"php":">=7.0","epii\/tiny-app-plus":">=0.0.1","epii\/admin-ui-login":">=0.0.1","epii\/admin-ui-upload":">=0.0.1","wangshouwei\/session":">=0.0.1","epii\/get-all-classes-and-methods-for-namespaces":">=0.0.1"},"autoload":{"psr-4":{"epii\\\\admin\\\\center\\\\":"src\/"}},"source":{"type":"git","url":"http:\/\/131.101.28.92:3000\/root\/epii.admin-center.git","refrence":"8c3fa1cf2684e3efe46a87c14acb2a0d80f7e1e3"},"dist":{"type":"git","url":"http:\/\/131.101.28.92:3000\/root\/epii.admin-center\/archive\/0.6.4.zip","refrence":"8c3fa1cf2684e3efe46a87c14acb2a0d80f7e1e3"},"website":"http:\/\/131.101.28.92:3000\/root\/epii.admin-center"}}';
        //
        //        $res = json_decode($text, true);
        //        if ($res['stat']) {
        //            throw new \Exception('请求API失败');
        //        }
        //        return $res['data'];

        // 访问接口
        $params = [
            'r' => $repoName,
            'v' => $versionName,
        ];

        $composerApiUrl = Settings::get(Constant::ADDONS . '.composer_api_url');
        $delimiter = strpos($composerApiUrl, '?') === false ? '?' : '&'; // 如果前面有参数，则用&给它续，否则，那就用？补了
        $url = $composerApiUrl . $delimiter . http_build_query($params);
        $res = json_decode(file_get_contents($url), true);
        if (($res === null) || (isset($res['stat']) === false)) {
            throw new \Exception('请求API失败#-1');
        }
        if ($res['stat']) { // 只有当stat为0时，才算成功
            throw new \Exception('请求API失败#-2');
        }
        if (isset($res['data']['name']) === false) {
            throw new \Exception('请求API失败#-3#找不到项目名称');
        }

        return $res['data'];
    }

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
        $version = Db::name(Constant::TABLE_VERSION)->where('project_id', $projectId)->order(['id' => 'desc'])->find();
        return $version;
    }
}
