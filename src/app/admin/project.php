<?php

/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2020/5/19
 * Time: 15:19
 */

namespace wslibs\composer_package\app\admin;

use wslibs\composer_package\libs\ProjectGroup;
use wslibs\composer_package\libs\Project as libProject;
use wslibs\composer_package\libs\Constant;
use epii\orm\Db;
use epii\server\Args;
use think\db\Expression;

class project extends base
{
    /**
     * 首页
     */
    public function index()
    {
        try {
            $projectGroups = ProjectGroup::getOptions([], ['id' => 0, 'name' => '请选择项目组']);

            $this->assign('projectGroups', $projectGroups); // 只认id和name，算你狠
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
            $this->error($e->getMessage());
        }
    }

    /**
     * 添加项目
     * @return array|false|string
     */
    public function add()
    {
        try {
            $projectId = Args::params('project_id/d', 0);
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $source = trim(Args::params('source/d', '请选择项目来源'));
                $projectGroupId = trim(Args::params("project_group_id/d/1", '请选择所属项目组'));
                $projectRepoName = trim(Args::params("project_repo_name")); // 项目仓库名是跟在版本表里的
                $versionName = trim(Args::params("version_name"));

                $versionInfo = libProject::getVersionInfoFromApi($projectRepoName, $versionName); // 内有项目名称
                if (!$versionInfo) {
                    throw new \Exception('获取项目信息失败');
                }
                $timestamp = time();

                /***********事务开始***********/
                Db::startTrans();

                // 构建插入数组
                $insertData = [
                    'project_name' => $versionInfo['name'],
                    'project_url' => $versionInfo['website'] ?? '',
                    'project_group_id' => $projectGroupId,
                ];

                // 添加or更新
                if ($projectId) { // 更新
                    $insertData = array_filter($insertData);
                    $insertData['update_time'] = $timestamp;
                    $res = Db::name(Constant::TABLE_PROJECT)->where('id', $projectId)->update($insertData);

                    if (!$res) {
                        throw new \Exception('更新失败');
                    }
                } else { // 添加
                    // 第二次机会，通过repo_name来找项目
                    $projectId = Db::name(Constant::TABLE_PROJECT)->where('project_name', $versionInfo['name'])->value('id');
                    if (!$projectId) { // 实在找不到
                        $insertData['create_time'] = $timestamp;
                        $insertData['update_time'] = $timestamp;
                        $projectId = Db::name(Constant::TABLE_PROJECT)->insert($insertData, false, true);
                    }

                    if (!$projectId) {
                        throw new \Exception('添加失败');
                    }

                    if ($versionName) { // 如果填了起始版本，则还得添加起始版本（光建了项目本身还不算）
                        $insertData2 = [
                            'source' => $source,
                            'version_name' => $versionName,
                            'project_id' => $projectId,
                            'repo_name' => $projectRepoName,
                            'version_url' => $versionInfo['dist']['url'] ?? '',
                            'version_json' => json_encode($versionInfo, JSON_UNESCAPED_UNICODE),
                            'create_time' => $timestamp,
                            'update_time' => $timestamp,
                        ];

                        // 还得验证存不存在，已经存在了就很难受
                        $versionId = Db::name(Constant::TABLE_VERSION)->where('project_id', $projectId)->where('version_name', $versionName)->value('id');
                        if ($versionId) {
                            throw new \Exception('项目' . $versionInfo['name'] . ':' . $versionName . '版本已存在');
                        } else {
                            $versionId = Db::name(Constant::TABLE_VERSION)->insert($insertData2);
                        }

                        if (!$versionId) {
                            throw new \Exception('添加起始版本失败');
                        }
                    }
                }

                Db::commit();
                /***********事务结束***********/

                $this->success('操作成功');
            } else {
                if ($projectId) {
                    $project = Db::name(Constant::TABLE_PROJECT)->where('id', $projectId)->find();
                    $this->assign('project', $project);
                }

                $projectGroups = ProjectGroup::getOptions();
                $this->assign('projectGroups', $projectGroups); // 只认id和name，算你狠
                //            array_unshift($projectGroups, ['id' => 0, 'name' => '请选择所属项目组']); // 已经有啦
                $sourceOptions = libProject::getSourceOptions();
                $this->assign('sourceOptions', $sourceOptions);

                $this->adminUiDisplay();
            }
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
    }

    // /**
    //  * 修改项目
    //  * @return array|false|string
    //  */
    // public function edit()
    // {
    //     try {
    //         $id = trim(Args::params("id/d"));
    //         $project = Db::name('project')->where('id', $id)->find();
    //         if (!$project) {
    //             throw new \Exception('没有找到该项目');
    //         }
    //         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //             $id = trim(Args::params("id/d"));
    //             //                $projectRepoName = trim(Args::params("project_repo_name/1"));
    //             //                $projectUrl = trim(Args::params("project_url/1"));
    //             $projectGroupId = trim(Args::params("project_group_id/d"));

    //             if (!$projectGroupId) {
    //                 throw new \Exception('请选择所属项目组');
    //             }

    //             /***********事务开始***********/
    //             Db::startTrans();
    //             $updateData = [
    //                 'project_group_id' => $projectGroupId,
    //                 'update_time' => time(),
    //             ];
    //             $res = Db::name('project')->where('id', $id)->update($updateData, false, true);
    //             if (!$res) {
    //                 db::rollback();
    //                 throw new \Exception('修改失败#1');
    //             }

    //             Db::commit();
    //             /***********事务结束***********/

    //             ProjectService::autoMake();

    //             return JsCmd::alertCloseRefresh("成功");
    //         } else {
    //             $this->assign('project', $project);

    //             $projectGroups = ProjectGroupService::getOptions();
    //             //            array_unshift($projectGroups, ['id' => 0, 'name' => '请选择所属项目组']); // 已经有啦

    //             $this->assign('projectGroups', $projectGroups); // 只认id和name，算你狠

    //             $this->assign('addons', Constant::ADDONS);
    //             $this->adminUiDisplay('admin/project/add');
    //         }
    //     } catch (\Exception $e) {
    //         $cmd = Alert::make()->icon('5')->msg($e->getMessage())->onOk(null);
    //         return JsCmd::make()->addCmd($cmd)->run();
    //     }
    // }

    public function delete()
    {
        try {
            $id = Args::params('id/1');

            $res = Db::name('project')->where('id', $id)->delete();
            if (!$res) {
                throw new \Exception('删除失败');
            }

            LibProject::autoMake();

            $this->success('成功');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
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
