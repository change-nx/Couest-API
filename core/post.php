<?php
include __DIR__ . "/common.php";

session_start();

$type = $_REQUEST["type"]??"";

if (empty($type)) json(-1,"缺少参数");

// 定义路径
define("__API__", dirname(__DIR__) . "/database/API.json");
define("__FRIEND__", dirname(__DIR__) . "/database/friend.json");
define("__CONFIG__", dirname(__DIR__) . "/database/config.php");
define("__ADMIN__", dirname(__DIR__) . "/database/admin.php");

// 加载文件
define("config", require(dirname(__DIR__)."/database/config.php"));
define("admin", require(dirname(__DIR__)."/database/admin.php"));

// 处理操作
switch ($type) {
    
    // 登录
    case "login":
        $admin = $_REQUEST["admin"]??"";
        $password = $_REQUEST["password"]??"";
        
        if (empty($admin) || empty($password)) {
            json(-1,"缺少参数");
        }
        
        if ($admin == admin["admin"] && $password == admin["password"]) {
            $_SESSION["status"] = "admin";
            json(200,"登录成功");
        } else {
            json(-1,"账号或密码错误");
        }
        
        break;
    
    // 退出登录
    case "clear":
        if (is_admin()) {
            unset($_SESSION["status"]);
            json(200,"已退出登录");
        } else {
            json(-1,"未登录");
        }
        break;
    
    // 更改账密
    case "setAdmin":
        $admin = $_REQUEST["admin"]??"";
        $password = $_REQUEST["password"]??"";
        
        if (empty($admin) || empty($password)) {
            json(-1,"缺少参数");
        }
        
        if (!is_admin()) {
            json(-1,"未登录");
        }
        
        $content = "<?php\nreturn [\n";
        $content .= "    'admin' => '{$admin}',\n";
        $content .= "    'password' => '{$password}'\n";
        $content .= "];";
        
        $put = @file_put_contents(__ADMIN__, $content);
        
        if ($put > 0) {
            json(200,"设置成功");
        } else {
            json(-1,"设置失败");
        }
        
        break;
    
    // 添加API
    case "addApi":
    
        if (!is_admin()) {
            json(-1,"未登录");
        }
        
        // ApiId
        $id = $_REQUEST["id"]??"";
        // 标题
        $title = $_REQUEST["title"]??"";
        // 介绍
        $desc = $_REQUEST["desc"]??"";
        // 地址
        $url = $_REQUEST["url"]??"";
        // 示例地址
        $example = $_REQUEST["example"]??"";
        // 请求方式
        $method = $_REQUEST["method"]??"";
        // 请求参数
        $request = $_REQUEST["request"]??"";
        // 返回参数
        $response = $_REQUEST["response"]??"";
        
        // 懒得检查参数全不全了,累..
        
        // 骗你的宝宝
        if (empty($id) || empty($title) || empty($desc) || empty($url) || empty($example) || empty($method) || empty($request) || empty($response)) {
            json(-1,"缺少参数");
        }
        
        // 请求/返回参数解析处理
        $request = json_decode($request,true);
        $response = json_decode($response,true);
        
        // 合并成json
        $json = [
            "id" => $id,
            "title" => $title,
            "desc" => $desc,
            "url" => $url,
            "example" => $example,
            "method" => $method,
            "request" => $request,
            "response" => $response
        ];
        
        // 读取API列表
        $list = @file_get_contents(__API__);
        $list = json_decode($list,true);
        
        // 添加
        $list[] = $json;
        
        // 写回
        $list = json_encode($list,480);
        @file_put_contents(__API__,$list);
        json(200,"添加成功");
        break;
    
    // 修改API
    case "setApi":
    
        if (!is_admin()) {
            json(-1,"未登录");
        }
        
        // ApiId
        $id = $_REQUEST["id"]??"";
        // 标题
        $title = $_REQUEST["title"]??"";
        // 介绍
        $desc = $_REQUEST["desc"]??"";
        // 地址
        $url = $_REQUEST["url"]??"";
        // 示例地址
        $example = $_REQUEST["example"]??"";
        // 请求方式
        $method = $_REQUEST["method"]??"";
        // 请求参数
        $request = $_REQUEST["request"]??"";
        // 返回参数
        $response = $_REQUEST["response"]??"";
        
        // 懒得检查参数全不全了,累..
        
        // 骗你的宝宝
        if (empty($id) || empty($title) || empty($desc) || empty($url) || empty($example) || empty($method) || empty($request) || empty($response)) {
            json(-1,"缺少参数");
        }
        
        // 请求/返回参数解析处理
        $request = json_decode($request,true);
        $response = json_decode($response,true);
        
        // 合并成json
        $json = [
            "id" => $id,
            "title" => $title,
            "desc" => $desc,
            "url" => $url,
            "example" => $example,
            "method" => $method,
            "request" => $request,
            "response" => $response
        ];
        
        // 读取API列表
        $list = @file_get_contents(__API__);
        $list = json_decode($list,true);
        
        // 添加
        foreach ($list as $key => $value) {
            if ($value["id"] == $id) {
                $list[$key] = $json;
                break;
            }
            continue;
        }
        
        // 写回
        $list = json_encode($list,480);
        @file_put_contents(__API__,$list);
        json(200,"修改成功");
        break;
    
    // 删除API
    case "delApi":
    
        if (!is_admin()) {
            json(-1,"未登录");
        }
        
        $id = $_REQUEST["id"]??"";
        if (empty($id)) json(-1,"缺少参数");
        
        // 读取API列表
        $list = @file_get_contents(__API__);
        $list = json_decode($list,true);
        
        // 添加
        foreach ($list as $key => $value) {
            if ($value["id"] == $id) {
                unset($list[$key]);
                break;
            }
            continue;
        }        
        @file_put_contents(__API__, json_encode($list, 480));
        // 输出结果
        json(200,"删除成功");
        break;
        
    // API列表
    case "getApi":
    
        if (!is_admin()) {
            json(-1,"未登录");
        }
        
        // 读取API列表
        $list = @file_get_contents(__API__);
        $list = json_decode($list,true);
        
        // 输出
        json(200,"获取成功",$list);
        break;
        
    // 友链列表
    case "getFriend":
    
        if (!is_admin()) {
            json(-1,"未登录");
        }
        
        // 读取列表
        $list = @file_get_contents(__FRIEND__);
        $list = json_decode($list,true);
        
        // 输出
        json(200,"获取成功",$list);
        break;
    
    // 添加友链
    case "addFriend":
    
        if (!is_admin()) {
            json(-1,"未登录");
        }
        
        // 读取列表
        $list = @file_get_contents(__FRIEND__);
        $list = json_decode($list,true);
        
        $title = $_REQUEST["title"]??"";
        $desc = $_REQUEST["desc"]??"";
        $image = $_REQUEST["image"]??"";
        $url = $_REQUEST["url"]??"";
        if ( empty($title)
          || empty($desc)
          || empty($image)
          || empty($url)
        ) json(-1,"缺少参数");
        
        $json = [
            "title" => $title,
            "desc" => $desc,
            "image" => $image,
            "url" => $url
        ];
        $list[] = $json;
        
        // 输出
        $list = json_encode($list,480);
        @file_put_contents(__FRIEND__,$list);
        json(200,"添加成功");
        break;
    
    // 删除友链
    case "delFriend":
    
        if (!is_admin()) {
            json(-1,"未登录");
        }
        
        // 读取列表
        $list = @file_get_contents(__FRIEND__);
        $list = json_decode($list,true);
        
        $title = $_REQUEST["title"]??"";
        if (empty($title)) json(-1,"缺少参数");
        
        // 遍历寻找并删除
        foreach ($list as $key => $value) {
            if ($title == $value["title"]) {
                unset($list[$key]);
                break;
            }
            continue;
        }
        
        @file_put_contents(__FRIEND__, json_encode($list, 480));
        json(200,"删除成功");
        break;
    
    // 获取配置信息
    case "getConfig":
    
        if (!is_admin()) {
            json(-1,"未登录");
        }
        
        $template = glob(dirname(__DIR__)."/template/*");
        $config = config;
        foreach ($template as $name) {
            $config["template_list"][] = basename($name);
        }
        
        json(200,"获取成功",$config);
        break;
    
    // 修改配置信息
    case "setConfig":
    
        if (!is_admin()) {
            json(-1,"未登录");
        }
        
        $title = $_REQUEST["title"]??"";
        $desc = $_REQUEST["desc"]??"";
        $domain = $_REQUEST["domain"]??"";
        $favicon = $_REQUEST["favicon"]??"";
        $link = $_REQUEST["link"]??"";
        $template = $_REQUEST["template"]??"default";
        
        if (empty($title) || empty($desc) || empty($domain) ||  empty($favicon) || empty($link)) {
            json(-1,"缺少参数");
        }
        
        $configContent = "<?php\nreturn [\n";
        $configContent .= "    'title' => '{$title}',\n";
        $configContent .= "    'desc' => '{$desc}',\n";
        $configContent .= "    'domain' => '{$domain}',\n";
        $configContent .= "    'favicon' => '{$favicon}',\n";
        $configContent .= "    'link' => '{$link}',\n";
        $configContent .= "    'template' => '{$template}',\n";
        $configContent .= "];\n";
        
        $put = file_put_contents(__CONFIG__, $configContent);
        
        if ($put > 0) {
            json(200,"设置成功");
        } else {
            json(-1,"配置保存失败");
        }
        break;
        
}