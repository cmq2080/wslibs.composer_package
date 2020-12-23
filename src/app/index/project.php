<?php

/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2020/5/19
 * Time: 15:24
 */

namespace wslibs\composer_package\app\index;


use wslibs\composer_package\libs\Constant;
use epii\server\Response;
use wslibs\composer_package\libs\Project as LibsProject;

class project extends base
{
    public function index()
    {
        try {
            $this->display("index/project/index");
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function ajax_data()
    {
        try {
            //            $projectGroups = Db::name('project_group')->select();
            //            $projects = Db::name('project')->select();
            //            $versions = Db::name('version')->select();
            //
            //            $data = [];
            //            foreach ($projectGroups as $projectGroup) {
            //                $projectGroup['projects'] = $this->getProjects($projectGroup['id'], $projects, $versions);
            //                $data[] = $projectGroup;
            //            }
            $data = LibsProject::all();

            return json_encode($data, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }

    //    private function getProjects($projectGroupId, $projects, $versions)
    //    {
    //        $data = [];
    //        foreach ($projects as $project) {
    //            if ($project['project_group_id'] != $projectGroupId) { // 不属于该项目组下的项目，直接跳过
    //                continue;
    //            }
    //            $project['versions'] = $this->getVersions($project['id'], $versions);
    //            $project['add_url'] = "?app=index\\version@add&project_id=" . $project['id'] . '&__addons=addons_composer';
    //            $data[] = $project;
    //        }
    //
    //        return $data;
    //    }
    //
    //    private function getVersions($projectId, $versions)
    //    {
    //        $data = [];
    //        foreach ($versions as $version) {
    //            if ($version['project_id'] != $projectId) {
    //                continue;
    //            }
    //            $version['sub_project_name'] = json_decode($version['version_json'], true)['name'];
    //            $data[] = $version;
    //        }
    //
    //        return $data;
    //    }

    //    public function autoMake()
    //    {
    //        try {
    //            ProjectService::autoMake();
    //
    //            echo '生成成功';
    //        } catch (\Exception $e) {
    //        }
    //    }
    //
    //    public function getProjectInfo($project)
    //    {
    //        $projectInfo = [];
    //        foreach ($project['versions'] as $version) {
    //            $projectInfo[$version['version_name']] = json_decode($this->getVersionInfo($version, $project['project_name']), true);
    //        }
    //
    //        return $projectInfo;
    //    }
    //
    //    public function getVersionInfo($version, $projectName)
    //    {
    //        if ($version['version_json']) { // 有则直接取
    //            return $version['version_json'];
    //        }
    //        // 没有再访问接口
    //        $params = [
    //            'app' => 'composer',
    //            'repo' => $projectName,
    //            'git_origin' => '1'
    //        ];
    //        $params['version'] = $version['version_name'];
    //        $url = self::BASE_URL . '?' . http_build_query($params);
    //        $res = json_decode(file_get_contents($url), true);
    //        if ($res['code'] != 1) {
    //            return '';
    //        }
    //        // 获取的接口信息存入数据库中
    //        Db::name('version')->where('id', $version['id'])->update(['version_json' => json_encode($res['data'], JSON_UNESCAPED_UNICODE)]);
    //        return json_encode($res['data'], JSON_UNESCAPED_UNICODE);
    //    }
    //
    //    public function getPackageJson()
    //    {
    //        try {
    //            $projectName = Args::params('project/1');
    //            $versionName = Args::params('version/1');
    //
    //            $project = Db::name('project')->where('project_name', $projectName)->find();
    //            if (!$project) {
    //                throw new \Exception('项目不存在');
    //            }
    //
    //            $version = Db::name('version')->where(['project_id' => $project['id'], 'version_name' => $versionName])->find();
    //            if (!$version) {
    //                throw new \Exception('项目下版本不存在');
    //            }
    //
    //            $res = $this->getVersionInfo($version, $projectName);
    //
    //            return $res;
    //        } catch (\Exception $e) {
    //            return '';
    //        }
    //    }
}
