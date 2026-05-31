<?php
/**
 * 使用不同蜘蛛User-Agent测试网站
 */

$targetUrl = "http://qwq.nki.pw/";

$spiderUAs = [
    '百度蜘蛛' => 'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)',
    'Google蜘蛛' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
    '搜狗蜘蛛' => 'Sogou web spider/4.0(+http://www.sogou.com/docs/help/webmasters.htm#07)',
    'Bing蜘蛛' => 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
    '360蜘蛛' => 'Mozilla/5.0 (compatible; 360Spider; +http://www.so.com/help/help_3_2.html)',
];

echo "=== 蜘蛛访问测试 ===\n\n";

foreach ($spiderUAs as $name => $ua) {
    echo "正在使用 {$name} 访问网站...\n";
    echo "User-Agent: {$ua}\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $targetUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "✗ 访问失败: {$error}\n";
    } else {
        echo "✓ 访问成功! HTTP状态码: {$httpCode}\n";
        echo "响应长度: " . strlen($response) . " 字节\n";
    }
    echo "------------------------\n";
    sleep(1);
}

echo "\n测试完成!\n";
echo "请访问网站的数据统计页面查看蜘蛛访问记录。\n";
