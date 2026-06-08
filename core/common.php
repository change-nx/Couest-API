<?php

/*
 * 跳转并带有提示信息
 * @param string $msg 提示信息
 * @param string $url 跳转链接
 */
function alert($msg, $url)
{
	$alert = '<script>';
	$alert .= 'alert("' . $msg . '");';
	$alert .= 'window.location.href="' . $url . '";';
	$alert .= '</script>';
	die($alert);
}

/*
 * 跳转到某一链接
 * @param string $url 链接
 */
function jump($url)
{
	header('Location: ' . $url);
}

/*
 * json输出数据
 * @param int $code 状态码
 * @param string $msg 信息
 * @param object $data 数据
 */
function json($code, $msg, $data = [])
{
    $json = [
        "code" => $code,
        "msg" => $msg,
        "data" => $data
    ];
    die(json_encode($json,320 | JSON_PRETTY_PRINT));
}

/*
 * 登录状态
 * @return bool
 */
function is_admin() {
    session_start();
    if ($_SESSION["status"] == "admin") {
        return true;
    } else {
        return false;
    }
}

/*
 * 添加访问数据
 * @return bool
 */
function addAccess()
{
    $jsonFile = dirname(__DIR__) . '/database/Access.json';
    $today = date('Y-m-d');
    
    $dir = dirname($jsonFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $fp = fopen($jsonFile, 'c+');
    if (!$fp) {
        return false;
    }
    
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        return false;
    }
    
    $stats = array('all' => 0, 'today' => 0, 'yesterday' => 0, 'date' => $today);
    
    $filesize = filesize($jsonFile);
    if ($filesize > 0) {
        $content = fread($fp, $filesize);
        if ($content !== false) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $stats = $decoded;
            }
        }
    }
    
    // 根据日期重置
    if ($stats['date'] != $today) {
        $stats['yesterday'] = $stats['today'];
        $stats['today'] = 0;
        $stats['date'] = $today;
    }
    
    // 增加计数
    $stats['all']++;
    $stats['today']++;
    
    // 写回文件
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($stats));
    fflush($fp);
    
    flock($fp, LOCK_UN);
    fclose($fp);
    
    return true;
}

/*
 * 添加单API调用数据
 * @return bool
 */
function addApiAccess($id)
{
    $jsonFile = dirname(__DIR__) . '/database/ApiAccess.json';
    $id = (string)$id;  // 确保键为字符串数字
    
    $dir = dirname($jsonFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $fp = fopen($jsonFile, 'c+');
    if (!$fp) {
        return false;
    }
    
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        return false;
    }
    
    $stats = array();
    
    $filesize = filesize($jsonFile);
    if ($filesize > 0) {
        $content = fread($fp, $filesize);
        if ($content !== false) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $stats = $decoded;
            }
        }
    }
    
    // 增加指定ID的计数
    if (isset($stats[$id])) {
        $stats[$id]++;
    } else {
        $stats[$id] = 1;
    }
    
    // 写回文件
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($stats));
    fflush($fp);
    
    flock($fp, LOCK_UN);
    fclose($fp);
    
    return true;
}

/*
 * 通过ua判断是否为搜索引擎蜘蛛，并更新统计
 * @return bool
 */
 function is_spider()
{
	$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	if (!empty($agent)) {
		$spiderSite = array(
            // Google 系列
            "Googlebot",
            "Googlebot-Image",
            "Googlebot-Video",
            "Google-Read-Aloud",
            "Google-InspectionTool",
            "GoogleOther",
            "Storebot-Google",
            "Mediapartners-Google",  // AdSense
            "AdsBot-Google",
            "FeedFetcher-Google",
            "Google-Extended",       // AI 控制
            
            // Microsoft / Bing 系列
            "bingbot",               // 必应主爬虫（重要）
            "BingPreview",           // 必应预览图
            "adidxbot",              // 必应广告
            "MSNBot",                // 老版必应
            "MSIECrawler",
            
            // Baidu 系列
            "Baiduspider",
            "Baiduspider+",
            "BaiduGame",
            "BaiDuSpider",
            
            // 其他搜索引擎
            "YandexBot",
            "DuckDuckBot",
            "Applebot",
            "Bytespider",            // 字节跳动/TikTok
            "AhrefsBot",             // SEO 工具
            "SemrushBot",
            "CCBot",                 // Common Crawl
            "GPTBot",                // OpenAI
            "anthropic-ai",          // Claude
            // 360搜索系列
            "360Spider",          // 网页搜索主爬虫（核心）
            "HaoSouSpider",       // 网页搜索备用爬虫（优先使用）
            "360Spider-Image",    // 图片搜索
            "360Spider-Video",    // 视频搜索
        
            "Sosospider+",
            "Sogou web spider",
            "Sogou Spider",
            "Sogou web spider",
            "YoudaoBot",
            "OutfoxBot/YodaoBot",
            "ia_archiver",
            "Alexa (IA Archiver)",
            "The web archive (IA Archiver)",
            "Yahoo! Slurp",
            "Yahoo Slurp",
            "Voila",
            "BSpider",
            "twiceler",
            "Speedy Spider",
            "Heritrix",
            "Python-urllib",
            "Ask",
            "Exabot",
            "Custo",
            "yacy",
            "SurveyBot",
            "legs",
            "lwp-trivial",
            "Nutch",
            "StackRambler",
            "Perl tool",
            "MJ12bot",
            "Netcraft",
            "WGet tools",
            "larbin",
            "Fish search",
            "Java (Often spam bot)",
        );
		foreach ($spiderSite as $val) {
			$str = strtolower($val);
			if (strpos($agent, $str) !== false) {
            	// 统计记录
                $jsonFile = dirname(__DIR__) . '/database/Spider.json';
                $today = date('Y-m-d');
                
                // 确保目录存在
                $dir = dirname($jsonFile);
                if (!is_dir($dir)) {
                    @mkdir($dir, 0755, true);
                }
                
                // 初始化默认统计
                $stats = [
                    'all' => 0,
                    'today' => 0,
                    'yesterday' => 0,
                    'date' => $today
                ];
                
                // 打开文件并加锁
                $fp = @fopen($jsonFile, 'c+');
                if ($fp && flock($fp, LOCK_EX)) {
                    // 读取现有数据
                    $filesize = @filesize($jsonFile);
                    if ($filesize > 0) {
                        rewind($fp);
                        $content = @fread($fp, $filesize);
                        if ($content !== false && !empty($content)) {
                            $decoded = json_decode($content, true);
                            if (is_array($decoded)) {
                                $stats = array_merge($stats, $decoded);
                            }
                        }
                    }
                    
                    // 日期变更处理（修复逻辑）
                    if ($stats['date'] !== $today) {
                        $stats['yesterday'] = $stats['today'];
                        $stats['today'] = 0;
                        $stats['date'] = $today;
                    }
                    
                    // 确保所有字段存在
                    $stats['all'] = ($stats['all'] ?? 0) + 1;
                    $stats['today'] = ($stats['today'] ?? 0) + 1;
                    if (!isset($stats['yesterday'])) {
                        $stats['yesterday'] = 0;
                    }
                    
                    // 写回文件
                    rewind($fp);
                    @ftruncate($fp, 0);
                    @fwrite($fp, json_encode($stats, JSON_UNESCAPED_UNICODE));
                    @fflush($fp);
                    
                    flock($fp, LOCK_UN);
                    @fclose($fp);
                } else {
                    @fclose($fp);
                }
                
                return true;
			}
		}
		return false;
	} else {
		return false;
	}
}