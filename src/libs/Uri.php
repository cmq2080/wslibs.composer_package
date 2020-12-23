<?php
/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2020/6/18
 * Time: 15:45
 */

namespace wslibs\composer_package\libs;


class Uri
{
    /**
     * 生成uri
     * @param $controller
     * @param $action
     * @param $query
     * @return string
     */
    public static function make($controller, $action, $query)
    {
        $uri = "?app=$controller@$action&__addons=" . Constant::ADDONS;
        if (is_string($query) !== true) {
            $query = http_build_query($query);
        }
        $uri .= "&" . trim($query, "&");

        return $uri;
    }
}