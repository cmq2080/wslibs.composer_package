<?php
/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2020/5/21
 * Time: 9:18
 */

namespace composer\packages;


use epii\admin\center\libs\AddonsApp;

class App extends AddonsApp
{

    public function install(): bool
    {
        // TODO: Implement install() method.
        // 执行sql文件
        $res = $this->execSqlFile(__DIR__ . "/data/install.sql", "epii_");
        if (!$res) {
            return false;
        }
        // 添加菜单及子菜单
        $pid = $this->addMenuHeader("composer管理");
        if (!$pid) {
            return false;
        }
        $id = $this->addMenu($pid, '项目组', '?app=admin\project_group@index&__addons=addons_composer');
        if (!$id) {
            return false;
        }
        $id = $this->addMenu($pid, '项目', '?app=admin\project@index&__addons=addons_composer');
        if (!$id) {
            return false;
        }

        return true;
    }

    public function update($new_version, $old_version): bool
    {
        // TODO: Implement update() method.
//        $updateSql = __DIR__ . '/data/update_sql/' . $old_version . '-' . $new_version . '.sql';
////        if (is_file($updateSql) === true) {
////            $res = $this->execSqlFile($updateSql, "epii_");
////            if (!$res) {
////                return false;
////            }
////        }

        return true;
    }

    public function onOpen(): bool
    {
        // TODO: Implement onOpen() method.
        return true;
    }

    public function onClose(): bool
    {
        // TODO: Implement onClose() method.
        return true;
    }
}