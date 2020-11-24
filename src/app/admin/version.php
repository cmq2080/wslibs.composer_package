<?php
/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2020/5/19
 * Time: 17:52
 */

namespace composer\packages\app\admin;

use composer\packages\app\service\ProjectGroupService;
use composer\packages\app\service\ProjectService;
use composer\packages\app\service\SourceService;
use composer\packages\app\service\VersionService;
use composer\packages\libs\Constant;
use epii\admin\center\admin_center_addons_controller;
use epii\admin\ui\lib\epiiadmin\jscmd\Alert;
use epii\admin\ui\lib\epiiadmin\jscmd\JsCmd;
use epii\orm\Db;
use epii\server\Args;

class version extends admin_center_addons_controller
{
    /**
     * 首页
     */
    public function index()
    {
        try {
            $projectId = Args::params("project_id/1/d");
            $project = Db::name('project')->where('id', $projectId)->find();
            if (!$project) {
                exit('项目未找到');
            }
            $this->assign('project', $project);
            $this->assign('addons', Constant::ADDONS);
            $this->adminUiDisplay();
        } catch (\Exception $e) {
            $cmd = Alert::make()->icon('5')->msg($e->getMessage())->onOk(null);
            return JsCmd::make()->addCmd($cmd)->run();
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
            return $this->tableJsonData('version', ['project_id' => $projectId], function ($data) {
                $data['source'] = SourceService::getDesc($data['source']);
                $data['create_time'] = date('Y-m-d H:i:s', $data['create_time']);
                $data['update_time'] = date('Y-m-d H:i:s', $data['update_time']);
                return $data;
            });
        } catch (\Exception $e) {
            $cmd = Alert::make()->icon('5')->msg($e->getMessage())->onOk(null);
            return JsCmd::make()->addCmd($cmd)->run();
        }
    }

    /**
     * 添加
     */
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
                    $cmd = Alert::make()->icon('5')->msg('添加失败#1')->onOk(null);
                    return JsCmd::make()->addCmd($cmd)->run();
                }

                ProjectService::autoMake();

                return JsCmd::alertCloseRefresh("成功");
            } else {
                $projectId = Args::params("project_id/1/d");
                $project = Db::name('project')->where('id', $projectId)->find();
                if (!$project) {
                    exit('项目未找到');
                }
                $sources = SourceService::getOptions();
                $projectGroups = ProjectGroupService::getOptions();
                $lastVersion = VersionService::getLastVersion($projectId);
                $source = $lastVersion['source'] ?? '';
//                $source = versionService::getLastSource($projectId);
                $repoName = $lastVersion['repo_name'] ?? '';

                $this->assign('project', $project);
                $this->assign('projectGroups', $projectGroups);
                $this->assign('sources', $sources);
                $this->assign('source', $source);
                $this->assign('repoName', $repoName);
                $this->assign('versionName', VersionService::getNewVersionName($project['id']));
                $this->assign('addons', Constant::ADDONS);
                $this->adminUiDisplay();
            }
        } catch (\Exception $e) {
            $cmd = Alert::make()->icon('5')->msg($e->getMessage())->onOk(null);
            return JsCmd::make()->addCmd($cmd)->run();
        }
    }

    /**
     * 删除
     */
    public function delete()
    {
        try {
            $id = Args::params('id/1');

            $res = Db::name('version')->where('id', $id)->delete();
            if (!$res) {
                $cmd = Alert::make()->icon('5')->msg('删除失败#1')->onOk(null);
                return JsCmd::make()->addCmd($cmd)->run();
            }

            ProjectService::autoMake();

            return JsCmd::alertRefresh("成功");
        } catch (\Exception $e) {
            $cmd = Alert::make()->icon('5')->msg($e->getMessage())->onOk(null);
            return JsCmd::make()->addCmd($cmd)->run();
        }
    }
}