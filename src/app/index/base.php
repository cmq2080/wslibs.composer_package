<?php

namespace wslibs\composer_package\app\index;

use wslibs\composer_package\libs\Constant;
use epii\app\controller;
use epii\server\Tools;
use epii\template\engine\EpiiViewEngine;

class base extends controller
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
    public function success($data = [], $msg = '成功', $code = 0)
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
        ];

        if ($data) {
            $result['data'] = $data;
        }

        exit(json_encode($result, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 失败后回调响应
     */
    public function error($msg = '失败', $code = 1)
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
        ];

        exit(json_encode($result, JSON_UNESCAPED_UNICODE));
    }
}
