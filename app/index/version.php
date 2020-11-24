<?php
/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2020/5/20
 * Time: 17:38
 */

namespace composer\packages\app\index;


use composer\packages\app\service\ProjectGroupService;
use composer\packages\app\service\ProjectService;
use composer\packages\app\service\SourceService;
use composer\packages\app\service\VersionService;
use composer\packages\libs\Constant;
use epii\app\controller;
use epii\orm\Db;
use epii\server\Args;

class version extends controller
{
    public function add()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $source = Args::params("source/1/d");
                $versionName = Args::params("version_name/1");
                $repoName = Args::params('repo_name/1');
                $projectId = Args::params("project_id/1/d");
                $timestamp = time();

                $project = Db::name('project')->where('id', $projectId)->find();
                if (!$project) {
                    throw new \Exception('项目未找到');
                }

                $versionInfo = ProjectService::getVersionInfoFromApi($repoName, $versionName);
                if (!$versionInfo) {
                    throw new \Exception('获取项目信息失败');
                }

                if ($project['project_name'] != $versionInfo['name']) {
                    throw new \Exception('仓库名称与之前不符');
                }

                if (VersionService::exists(['project_id' => $projectId, 'version_name' => $versionName])) {
                    throw new \Exception('版本已存在');
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
                $res = Db::name('version')->insert($insertData);
                if (!$res) {
                    throw new \Exception('添加失败');
                }

                ProjectService::autoMake();

                echo json_encode(['code' => 0, 'msg' => '成功'], JSON_UNESCAPED_UNICODE);
            } else {
                $projectId = Args::params("project_id/1/d");
                $project = Db::name('project')->where('id', $projectId)->find();
                if (!$project) {
                    exit('项目未找到');
                }
                $sources = SourceService::getOptions();
                $projectGroups = ProjectGroupService::getOptions();
                $lastVersion = VersionService::getLastVersion($project['id']);
                $source = $lastVersion['source'] ?? '';
                $repoName = $lastVersion['repo_name'] ?? '';
                $newVersionName = VersionService::getNewVersionName($project['id']);

                $this->assign('project', $project);
                $this->assign('projectGroups', $projectGroups);
                $this->assign('sources', $sources);
                $this->assign('source', $source);
                $this->assign('repoName', $repoName);
                $this->assign('versionName', $newVersionName);
                $this->assign('addons', Constant::ADDONS);
                $this->display();
            }
        } catch (\Exception $e) {
            echo json_encode(['code' => 0, 'msg' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    public function delete()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('请求方式非法');
            }
            $id = Args::params('id/1');

            $res = Db::name('version')->where('id', $id)->delete();

            if (!$res) {
                throw new \Exception('删除失败');
            }

            ProjectService::autoMake();

            return json_encode(['code' => 1, 'msg' => '删除成功'], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return json_encode(['code' => 0, 'msg' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }
}