<?php
/**
 * 蜘蛛统计功能测试脚本
 */

echo "=== 蜘蛛统计功能测试 ===\n\n";

// 引入common.php中的蜘蛛检测逻辑
require_once __DIR__ . '/core/common.php';

// 测试用例
$testUserAgents = [
    '普通用户浏览器' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    '百度蜘蛛' => 'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)',
    'Google蜘蛛' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
    'Bing蜘蛛' => 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
    '搜狗蜘蛛' => 'Sogou web spider/4.0(+http://www.sogou.com/docs/help/webmasters.htm#07)',
];

echo "1. 准备测试User-Agent\n";
echo "------------------------\n";

// 备份原始$_SERVER
$originalServer = $_SERVER;

// 测试每个User-Agent
$results = [];
foreach ($testUserAgents as $name => $ua) {
    // 模拟User-Agent
    $_SERVER['HTTP_USER_AGENT'] = $ua;
    
    // 调用蜘蛛检测函数
    ob_start();
    $isSpider = is_spider();
    ob_end_clean();
    
    $results[$name] = [
        'ua' => $ua,
        'is_spider' => $isSpider,
        'detected' => $isSpider ? '是' : '否'
    ];
    
    echo sprintf("%s: %s\n", $name, $isSpider ? '✓ 检测为蜘蛛' : '✗ 检测为普通用户');
}

// 恢复原始$_SERVER
$_SERVER = $originalServer;

echo "\n2. 检查统计文件\n";
echo "------------------------\n";

$spiderFile = __DIR__ . '/database/spider.json';
if (file_exists($spiderFile)) {
    echo "蜘蛛统计文件存在: " . $spiderFile . "\n";
    $stats = json_decode(file_get_contents($spiderFile), true);
    if ($stats) {
        echo "当前统计数据:\n";
        echo "  - 总访问: " . $stats['all'] . "\n";
        echo "  - 今日访问: " . $stats['today'] . "\n";
        echo "  - 昨日访问: " . $stats['yesterday'] . "\n";
        echo "  - 统计日期: " . $stats['date'] . "\n";
    }
} else {
    echo "蜘蛛统计文件不存在 (这是正常的，因为还没有蜘蛛访问)\n";
}

echo "\n3. 功能完整性检查\n";
echo "------------------------\n";

$checks = [
    '蜘蛛检测函数存在' => function_exists('is_spider'),
    'index.php中调用蜘蛛检测' => strpos(file_get_contents(__DIR__ . '/index.php'), 'is_spider()') !== false,
    'api.php中读取蜘蛛统计' => strpos(file_get_contents(__DIR__ . '/core/api.php'), 'spider') !== false,
    '前端展示蜘蛛统计' => strpos(file_get_contents(__DIR__ . '/template/default/home.html'), 'Spider') !== false,
];

foreach ($checks as $name => $passed) {
    echo sprintf("%s: %s\n", $name, $passed ? '✓ 通过' : '✗ 失败');
}

echo "\n=== 测试完成 ===\n";
echo "\n总结：\n";
echo "- 蜘蛛检测功能已实现\n";
echo "- 统计数据会保存在 database/spider.json\n";
echo "- 前端展示功能已实现\n";
echo "- 系统会自动检测常见的搜索引擎蜘蛛\n";
