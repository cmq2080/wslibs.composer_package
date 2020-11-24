<?php
/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2020/5/19
 * Time: 15:19
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
use think\db\Expression;

class project extends admin_center_addons_controller
{
    /**
     * 首页
     */
    public function index()
    {

        try {
            $projectGroups = ProjectGroupService::getOptions();
            array_unshift($projectGroups, ['id' => 0, 'name' => '请选择项目组']);

            $this->assign('projectGroups', $projectGroups); // 只认id和name，算你狠
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
            $projectGroupsStr = (new project_group())->ajax_data();
            $projectGroups = $projectGroupsStr ? json_decode($projectGroupsStr, true)['rows'] : [];

            $where = [
                'project_name' => ($projectName = trim(Args::params('project_name'))) ? new Expression('like "%' . $projectName . '%"') : null,
                'project_group_id' => ($projectGroupId = Args::params('project_group_id/d')) ? $projectGroupId : null,
            ];
            $where = array_filter($where);

            return $this->tableJsonData('project', $where, function ($data) use ($projectGroups) {
                foreach ($projectGroups as $projectGroup) {
                    if ($data['project_group_id'] == $projectGroup['id']) {
                        $data['project_group_name'] = $projectGroup['project_group_name'];
                    }
                }
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
     * 添加项目
     * @return array|false|string
     */
    public function add()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $source = trim(Args::params('source/d/1'));
                $projectGroupId = trim(Args::params("project_group_id/d/1"));
                $projectRepoName = trim(Args::params("project_repo_name/1"));
                $versionName = trim(Args::params("version_name/1"));

                if (!$source) {
                    throw new \Exception('请选择项目来源');
                }
                if (!$projectGroupId) {
                    throw new \Exception('请选择所属项目组');
                }

                $versionInfo = ProjectService::getVersionInfoFromApi($projectRepoName, $versionName);
                if (!$versionInfo) {
                    throw new \Exception('获取项目信息失败');
                }
                $timestamp = time();

                /***********事务开始***********/
                Db::startTrans();
                // 添加or更新
                $project = Db::name('project')->where('project_name', $versionInfo['name'])->find();
                $text = '';
                if (!$project) { // 没有找到？那就添加项目吧
                    $insertData = [
                        'project_name' => $versionInfo['name'],
                        'project_url' => $versionInfo['website'] ?? '',
                        'project_group_id' => $projectGroupId,
                        'create_time' => $timestamp,
                        'update_time' => $timestamp,
                    ];
                    $projectId = Db::name('project')->insert($insertData, false, true);
                } else {
                    $projectId = $project['id'];
                    $text .= '合并导入' . $project['project_name'] . '项目';
                }

                if (!$projectId) {
                    db::rollback();
                    throw new \Exception('添加失败#1');
                }

                if ($versionName) { // 如果填了最初版本，则还得添加最初版本
                    if (VersionService::exists(['project_id' => $projectId, 'version_name' => $versionName])) {
                        throw new \Exception('项目' . $project['project_name'] . ':' . $versionName . '版本已存在');
                    }

                    $insertData = [
                        'source' => $source,
                        'version_name' => $versionName,
                        'project_id' => $projectId,
                        'repo_name' => $projectRepoName,
                        'version_url' => $versionInfo['dist']['url'] ?? '',
                        'version_json' => json_encode($versionInfo, JSON_UNESCAPED_UNICODE),
                        'create_time' => time(),
                        'update_time' => time(),
                    ];
                    $res = Db::name('version')->insert($insertData);
                    if (!$res) {
                        db::rollback();
                        $cmd = Alert::make()->icon('5')->msg('添加失败#2')->onOk(null);
                        return JsCmd::make()->addCmd($cmd)->run();
                    }
                }

                Db::commit();
                /***********事务结束***********/

                ProjectService::autoMake();

                return JsCmd::alertCloseRefresh($text . "成功");
            } else {
                $projectGroups = ProjectGroupService::getOptions();
//            array_unshift($projectGroups, ['id' => 0, 'name' => '请选择所属项目组']); // 已经有啦
                $sources = SourceService::getOptions();

                $this->assign('projectGroups', $projectGroups); // 只认id和name，算你狠
                $this->assign('sources', $sources);
                $this->assign('addons', Constant::ADDONS);
                $this->adminUiDisplay();
            }
        } catch (\Exception $e) {
            $cmd = Alert::make()->icon('5')->msg($e->getMessage())->onOk(null);
            return JsCmd::make()->addCmd($cmd)->run();
        }
    }

    /**
     * 修改项目
     * @return array|false|string
     */
    public function edit()
    {
        try {
            $id = trim(Args::params("id/d"));
            $project = Db::name('project')->where('id', $id)->find();
            if (!$project) {
                throw new \Exception('没有找到该项目');
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = trim(Args::params("id/d"));
//                $projectRepoName = trim(Args::params("project_repo_name/1"));
//                $projectUrl = trim(Args::params("project_url/1"));
                $projectGroupId = trim(Args::params("project_group_id/d"));

                if (!$projectGroupId) {
                    throw new \Exception('请选择所属项目组');
                }

                /***********事务开始***********/
                Db::startTrans();
                $updateData = [
                    'project_group_id' => $projectGroupId,
                    'update_time' => time(),
                ];
                $res = Db::name('project')->where('id', $id)->update($updateData, false, true);
                if (!$res) {
                    db::rollback();
                    throw new \Exception('修改失败#1');
                }

                Db::commit();
                /***********事务结束***********/

                ProjectService::autoMake();

                return JsCmd::alertCloseRefresh("成功");
            } else {
                $this->assign('project', $project);

                $projectGroups = ProjectGroupService::getOptions();
//            array_unshift($projectGroups, ['id' => 0, 'name' => '请选择所属项目组']); // 已经有啦

                $this->assign('projectGroups', $projectGroups); // 只认id和name，算你狠

                $this->assign('addons', Constant::ADDONS);
                $this->adminUiDisplay('admin/project/add');
            }
        } catch (\Exception $e) {
            $cmd = Alert::make()->icon('5')->msg($e->getMessage())->onOk(null);
            return JsCmd::make()->addCmd($cmd)->run();
        }
    }

    public function delete()
    {
        try {
            $id = Args::params('id/1');

            $res = Db::name('project')->where('id', $id)->delete();
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

//    public function getVersionInfo($projectRepoName, $versionName)
//    {
//        // 没有再访问接口
//        $params = [
//            'app' => 'composer',
//            'repo' => $projectRepoName,
//            'git_origin' => '1',
//            'version' => $versionName,
//        ];
//        $url = \app\index\project::BASE_URL . '?' . http_build_query($params);
//        $res = json_decode(file_get_contents($url), true);
//        if ($res['code'] != 1) {
//            return null;
//        }
//        if (!isset($res['data']['name'])) {
//            return null;
//        }
//
//        return $res['data'];
//    }
//
//    public function autoMake()
//    {
//        try {
//            ProjectService::autoMake();
//
//            return JsCmd::alert("生成成功");
//        } catch (\Exception $e) {
//            $cmd = Alert::make()->icon('5')->msg($e->getMessage())->onOk(null);
//            return JsCmd::make()->addCmd($cmd)->run();
//        }
//    }

}