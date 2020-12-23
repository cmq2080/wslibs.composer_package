<?php

/**
 * Created by PhpStorm.
 * User: Adminstrator
 * Date: 2020/6/18
 * Time: 15:40
 */

namespace wslibs\composer_package\libs;


class Constant
{
    const ADDONS = 'wslibs/composer_package';

    const TABLE_PROJECT_GROUP = 'project_group';
    const TABLE_PROJECT = 'project';
    const TABLE_VERSION = 'version';

    const SOURCE_SELF = 1;       // 自建
    const SOURCE_GITHUB = 2;     // github
    const SOURCE_GITEE = 3;      // 码云
    const SOURCE_SVN = 4;        // SVN
}
