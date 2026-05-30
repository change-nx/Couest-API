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
    if (empty($agent)) {
        return false;
    }
    
    $spiderSite = array(
        "TencentTraveler", "Baiduspider+", "BaiduGame", "Googlebot", "msnbot",
        "Sosospider+", "Sogou web spider", "ia_archiver", "Yahoo! Slurp",
        "YoudaoBot", "Yahoo Slurp", "MSNBot", "Java (Often spam bot)",
        "BaiDuSpider", "Voila", "Yandex bot", "BSpider", "twiceler",
        "Sogou Spider", "Speedy Spider", "Google AdSense", "Heritrix",
        "Python-urllib", "Alexa (IA Archiver)", "Ask", "Exabot", "Custo",
        "OutfoxBot/YodaoBot", "yacy", "SurveyBot", "legs", "lwp-trivial",
        "Nutch", "StackRambler", "The web archive (IA Archiver)",
        "Perl tool", "MJ12bot", "Netcraft", "MSIECrawler", "WGet tools",
        "larbin", "Fish search",
    );
    
    foreach ($spiderSite as $val) {
        $str = strtolower($val);
        if (strpos($agent, $str) !== false) {

            $jsonFile = dirname(__DIR__) . '/database/spider.json';
            $today = date('Y-m-d');
            
            $dir = dirname($jsonFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            $fp = fopen($jsonFile, 'c+');
            if (!$fp) {
                return true;
            }
            
            if (!flock($fp, LOCK_EX)) {
                fclose($fp);
                return true;
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
    }
    
    return false;
}

