<?php

/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2020/5/19
 * Time: 17:52
 */

namespace wslibs\composer_package\app\admin;

use wslibs\composer_package\libs\Constant;
use wslibs\composer_package\libs\Project;
use wslibs\composer_package\libs\ProjectGroup;
use wslibs\composer_package\libs\Version as LibVersion;
use epii\orm\Db;
use epii\server\Args;

class version extends base
{
    /**
     * 首页
     */
    public function index()
    {
        try {
            $projectId = Args::params("project_id/1/d");
            $project = Db::name(Constant::TABLE_PROJECT)->where('id', $projectId)->find();
            if (!$project) {
                throw new \Exception('项目未找到');
            }
            $this->assign('project', $project);
            $this->adminUiDisplay();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 首页获取数据
     * @return false|string
     */
    public function ajax_data()
    {
        try {
            $projectId = Args::params("project_id/1/d");
            $where = [
                ['project_id', '=', $projectId]
            ];
            return $this->tableJsonData(Constant::TABLE_VERSION, $where, function ($data) {
                $data['source'] = Project::getSourceDesc($data['source']);
                $data['create_time'] = date('Y-m-d H:i:s', $data['create_time']);
                $data['update_time'] = date('Y-m-d H:i:s', $data['update_time']);
                return $data;
            });
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 添加
     */
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

                if (LibVersion::exists(['project_id' => $projectId, 'version_name' => $versionName])) {
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

                $this->success('添加成功');
            } else {
                $project = Db::name(Constant::TABLE_PROJECT)->where('id', $projectId)->find();
                if (!$project) {
                    throw new \Exception('项目未找到');
                }
                $sources = Project::getSourceOptions();
                $projectGroups = ProjectGroup::getOptions();
                $lastVersion = Project::getLastVersion($projectId);
                $source = $lastVersion['source'] ?? '';
                $repoName = $lastVersion['repo_name'] ?? '';

                $this->assign('project', $project);
                $this->assign('projectGroups', $projectGroups);
                $this->assign('sources', $sources);
                $this->assign('source', $source);
                $this->assign('repoName', $repoName);
                $this->assign('versionName', LibVersion::getNewVersionName($lastVersion['version_name']));
                $this->adminUiDisplay();
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 删除
     */
    public function delete()
    {
        try {
            $versionId = Args::params('version_id/1');

            $res = Db::name(Constant::TABLE_VERSION)->where('id', $versionId)->delete();
            if (!$res) {
                throw new \Exception('删除失败');
            }

            Project::autoMake();

            $this->success('成功', 'refresh');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
