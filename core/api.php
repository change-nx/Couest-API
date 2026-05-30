<?php
// 引入公用函数库
include __DIR__ . "/common.php";

// 操作类型
$type = $_REQUEST["type"]??"";

if (empty($type)) {
    json(-1,"缺少参数");
}

// 内置读取调用数据
$dataRead = function($type) {
    $allowed = ['access', 'spider'];
    if (!in_array($type, $allowed)) {
        return null;
    }
    
    $fileName = ucfirst($type) . '.json';
    $jsonFile = dirname(__DIR__) . "/database/{$fileName}";
    $today = date('Y-m-d');
    
    $stats = array('all' => 0, 'today' => 0, 'yesterday' => 0, 'date' => $today);
    
    if (file_exists($jsonFile)) {
        $content = file_get_contents($jsonFile);
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            $stats = $decoded;
        }
    }
    
    if ($stats['date'] != $today) {
        $lastDate = new DateTime($stats['date']);
        $nowDate = new DateTime($today);
        $diffDays = $lastDate->diff($nowDate)->days;
        
        if ($diffDays == 1) {
            $stats['yesterday'] = $stats['today'];
        } else if ($diffDays > 1) {
            $stats['yesterday'] = 0;
        }
        
        $stats['today'] = 0;
        $stats['date'] = $today;
        
        file_put_contents($jsonFile, json_encode($stats), LOCK_EX);
    }
    
    return $stats;
};

// 数据
$config = include(dirname(__DIR__) . "/database/config.php");
$API = json_decode(@file_get_contents(dirname(__DIR__) . "/database/API.json") ?: '[]', true) ?: [];
$Access = $dataRead("access");
$spider = $dataRead("spider");
$ApiAccess = json_decode(@file_get_contents(dirname(__DIR__) . "/database/ApiAccess.json") ?: '[]', true) ?: [];
$friend = json_decode(@file_get_contents(dirname(__DIR__) . "/database/friend.json") ?: '[]', true) ?: [];

switch ($type) {

    // 获取全部API
    case "getAllApi":
        // 完备的页面信息
        $newApiList = [];
        
        // 标题
        $newApiList["title"] = $config["title"];
        // 介绍
        $newApiList["desc"] = $config["desc"];
        // 图标
        $newApiList["favicon"] = $config["favicon"];
        // 域名
        $newApiList["domain"] = $config["domain"];
        // 群组
        $newApiList["link"] = $config["link"];

        // 总调用
        $newApiList["Access"] = $Access["all"];
        // 今日调用
        $newApiList["todayAccess"] = $Access["today"];
        // 昨日调用
        $newApiList["yesterdayAccess"] = $Access["yesterday"];
        
        // 总蜘蛛
        $newApiList["Spider"] = $spider["all"];
        // 今日蜘蛛
        $newApiList["todaySpider"] = $spider["today"];
        // 昨日蜘蛛
        $newApiList["yesterdaySpider"] = $spider["yesterday"];
        
        //遍历更新API数据
        foreach ($API as $OneApi) {
            $Id = $OneApi["id"];
            $access = $ApiAccess[$Id]??0;
            $OneApi["Access"] = $access;
            $newApiList["API"][] = $OneApi;
        }
        
        json(200,"获取成功",$newApiList);
        break;
    
    // 获取友链列表
    case "getFriend":
        json(200,"获取成功",$friend);
        break;
    
    // 获取数据统计
case "getStats":
    // 统计API数量
    $apiCount = count($API);
    
    // 统计友链数量
    $friendCount = count($friend);
    
    // 总调用次数
    $totalAccess = $Access["all"] ?? 0;
    
    // 总蜘蛛访问次数
    $totalSpider = $spider["all"] ?? 0;
    
    // 今日调用次数
    $todayAccess = $Access["today"] ?? 0;
    
    // 今日蜘蛛访问次数
    $todaySpider = $spider["today"] ?? 0;
    
    // 昨日调用次数
    $yesterdayAccess = $Access["yesterday"] ?? 0;
    
    // 昨日蜘蛛访问次数
    $yesterdaySpider = $spider["yesterday"] ?? 0;
    
    // API调用排行榜（按调用次数降序排序）
    $apiRanking = [];
    foreach ($API as $OneApi) {
        $Id = $OneApi["id"];
        $access = $ApiAccess[$Id] ?? 0;
        $apiRanking[] = [
            "id" => $Id,
            "title" => $OneApi["title"] ?? "",
            "desc" => $OneApi["desc"] ?? "",
            "access" => $access
        ];
    }
    
    // 按调用次数降序排序
    usort($apiRanking, function($a, $b) {
        return $b['access'] - $a['access'];
    });
    
    // 统计数据
    $stats = [
        "apiCount" => $apiCount,           // API总数
        "friendCount" => $friendCount,     // 友链总数
        "totalAccess" => $totalAccess,     // 总调用次数
        "totalSpider" => $totalSpider,     // 总蜘蛛访问
        "todayAccess" => $todayAccess,     // 今日调用
        "todaySpider" => $todaySpider,     // 今日蜘蛛
        "yesterdayAccess" => $yesterdayAccess, // 昨日调用
        "yesterdaySpider" => $yesterdaySpider, // 昨日蜘蛛
        "apiRanking" => $apiRanking // API排行榜
    ];
    
    json(200, "获取成功", $stats);
    break;
}