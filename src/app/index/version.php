<?php

/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2020/5/20
 * Time: 17:38
 */

namespace wslibs\composer_package\app\index;


use wslibs\composer_package\libs\Constant;
use epii\orm\Db;
use epii\server\Args;
use wslibs\composer_package\libs\Project;
use wslibs\composer_package\libs\ProjectGroup;
use wslibs\composer_package\libs\Version as LibsVersion;

class version extends base
{
    public function add()
    {
        try {
            $projectId = Args::params("project_id/1/d");
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $source = Args::params("source/1/d");
                $versionName = Args::params("version_name/1");
                $repoName = Args::params('repo_name/1');
                $timestamp = time();

                $project = Db::name(Constant::TABLE_PROJECT)->where('id', $projectId)->find();
                if (!$project) {
                    throw new \Exception('项目未找到');
                }

                $versionInfo = Project::getVersionInfoFromApi($repoName, $versionName);
                if (!$versionInfo) {
                    throw new \Exception('获取项目信息失败');
                }

                if ($project['project_name'] !== $versionInfo['name']) {
                    throw new \Exception('项目名称与之前不符');
                }

                if (LibsVersion::exists(['project_id' => $projectId, 'version_name' => $versionName])) {
                    throw new \Exception('该版本已存在');
                }

                $insertData = [
                    'source' => $source,
                    'version_name' => $versionName,
                    'version_url' => $versionInfo['dist']['url'] ?? '',
                    'version_json' => json_encode($versionInfo, JSON_UNESCAPED_UNICODE),
                    'project_id' => $projectId,
                    'repo_name' => $repoName,
                    'create_time' => $timestamp,
                    'update_time' => $timestamp
                ];
                $res = Db::name(Constant::TABLE_VERSION)->insert($insertData);
                if (!$res) {
                    throw new \Exception('添加失败');
                }

                Project::autoMake();

                $this->success();
            } else {
                $project = Db::name(Constant::TABLE_PROJECT)->where('id', $projectId)->find();
                if (!$project) {
                    throw new \Exception('项目未找到');
                }
                $sources = Project::getSourceOptions();
                $projectGroups = ProjectGroup::getOptions();
                $lastVersion = Project::getLastVersion($project['id']);
                $source = $lastVersion['source'] ?? '';
                $repoName = $lastVersion['repo_name'] ?? '';
                $newVersionName = LibsVersion::getNewVersionName($lastVersion['version_name']);

                $this->assign('project', $project);
                $this->assign('projectGroups', $projectGroups);
                $this->assign('sources', $sources);
                $this->assign('source', $source);
                $this->assign('repoName', $repoName);
                $this->assign('versionName', $newVersionName);
                $this->display();
            }
        } catch (\Exception $e) {
            echo json_encode(['code' => 0, 'msg' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    // public function delete()
    // {
    //     try {
    //         if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //             throw new \Exception('请求方式非法');
    //         }
    //         $id = Args::params('id/1');

    //         $res = Db::name('version')->where('id', $id)->delete();

    //         if (!$res) {
    //             throw new \Exception('删除失败');
    //         }

    //         ProjectService::autoMake();

    //         return json_encode(['code' => 1, 'msg' => '删除成功'], JSON_UNESCAPED_UNICODE);
    //     } catch (\Exception $e) {
    //         return json_encode(['code' => 0, 'msg' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    //     }
    // }
}
