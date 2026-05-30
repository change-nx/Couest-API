<?php
include(__DIR__ . "/core/common.php");

// 行为操作
$action = $_GET["action"]??"";

// 检查系统是否安装
if (!file_exists(__DIR__ . "/core/install/install.lock")) {
    alert("未安装系统,正在前往安装...","core/install/install.php");
    exit;
}

// 访问统计
addAccess();
// 蜘蛛统计
is_spider();

// 配置信息
$config = require(__DIR__ . "/database/config.php");

switch ($action) {
    // 友链
    case "friend":
        include __DIR__ . "/include/friend.php";
        break;
    // 后台
    case "admin":
        include __DIR__ . "/include/admin.php";
        break;
    // 首页
    default :
        include __DIR__ . "/include/home.php";
}