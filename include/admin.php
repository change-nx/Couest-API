<?php
$type = $_GET["type"]??"";

switch ($type) {
    case "API":
        if (!is_admin()) alert("未登录...","#");
        include(dirname(__DIR__) . "/template/" . $config["template"] . "/ApiControl.html");
        break;
    case "friend":
        if (!is_admin()) alert("未登录...","#");
        include(dirname(__DIR__) . "/template/" . $config["template"] . "/FriendControl.html");
        break;
    case "set":
        if (!is_admin()) alert("未登录...","#");
        include(dirname(__DIR__) . "/template/" . $config["template"] . "/ConfigControl.html");
        break;
    default:
        include(dirname(__DIR__) . "/template/" . $config["template"] . "/login.html");
}
