<?php

namespace wslibs\composer_package\app\admin;

use wslibs\composer_package\libs\Constant;
use epii\admin\center\admin_center_addons_controller;
use epii\admin\ui\lib\epiiadmin\jscmd\Alert;
use epii\admin\ui\lib\epiiadmin\jscmd\Close;
use epii\admin\ui\lib\epiiadmin\jscmd\CloseAndRefresh;
use epii\admin\ui\lib\epiiadmin\jscmd\JsCmd;
use epii\admin\ui\lib\epiiadmin\jscmd\Refresh;
use epii\server\Tools;
use epii\template\engine\EpiiViewEngine;

class base extends admin_center_addons_controller
{
    public function __construct()
    {
        // 因为项目目录结构发生了改变，所以得特别设置view引擎
        $engine = new EpiiViewEngine();
        $engine->init(["tpl_dir" => __DIR__ . "/../../view/", "cache_dir" => Tools::getRuntimeDirectory() . "/cache/view/"]);
        $this->setViewEngine($engine);

        $this->assign('__addons', Constant::ADDONS);
    }

    /**
     * 成功后回调响应
     */
    public function success($msg = '操作成功', $sufAction = 'close_and_refresh')
    {
        if ($sufAction === 'close') {
            $action = Close::make();
        } elseif ($sufAction === 'refresh') {
            $action = Refresh::make();
        } else {
            $action = CloseAndRefresh::make();
        }
        $cmd = Alert::make()->icon('6')->msg($msg)->onOk($action);
        exit(JsCmd::make()->addCmd($cmd)->run());
    }

    /**
     * 失败后回调响应
     */
    public function error($msg = '失败')
    {
        $cmd = Alert::make()->icon('5')->msg($msg)->onOk(null);
        exit(JsCmd::make()->addCmd($cmd)->run());
    }
}
