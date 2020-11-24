/*
 Navicat Premium Data Transfer

 Source Server         : 127.0.0.1
 Source Server Type    : MySQL
 Source Server Version : 50730
 Source Host           : 127.0.0.1:3306
 Source Schema         : dev1

 Target Server Type    : MySQL
 Target Server Version : 50730
 File Encoding         : 65001

 Date: 21/05/2020 18:52:17
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for epii_project
-- ----------------------------
-- DROP TABLE IF EXISTS `epii_project`;
CREATE TABLE IF NOT EXISTS `epii_project`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '项目表主键id',
  `project_name` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '项目名称',
  `project_url` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '项目git地址',
  `project_group_id` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '项目组外键id',
  `create_time` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `project_name`(`project_name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '项目表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for epii_project_group
-- ----------------------------
-- DROP TABLE IF EXISTS `epii_project_group`;
CREATE TABLE IF NOT EXISTS `epii_project_group`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '项目组表主键id',
  `project_group_name` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '项目组名称',
  `create_time` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `project_group_name`(`project_group_name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '项目组表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for epii_version
-- ----------------------------
-- DROP TABLE IF EXISTS `epii_version`;
CREATE TABLE IF NOT EXISTS `epii_version`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '项目版本表主键id',
  `source` tinyint(3) UNSIGNED NULL DEFAULT 0 COMMENT '版本来源：1-自有；2-github；3-gitee；4-svn',
  `project_id` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '项目表外键id',
  `repo_name` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '项目仓库名称',
  `version_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '版本名称',
  `version_url` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '版本zip地址',
  `version_json` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '版本package信息（json格式）',
  `create_time` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `project_id`(`project_id`, `version_name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '项目版本表' ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
