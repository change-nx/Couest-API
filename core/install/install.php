<?php
include dirname(__DIR__) . "/common.php";

if (file_exists(__DIR__ . "/install.lock")) {
    die("系统已安装");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = $_POST["title"]??"";
    $desc = $_POST["desc"]??"";
    $domain = $_POST["domain"]??"";
    $favicon = $_POST["favicon"]??"";
    $link = $_POST["link"]??"";
    $template = $_POST["template"]??"default";
    
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
    
    $put = file_put_contents(dirname(__DIR__, 2) . "/database/config.php", $configContent);
    
    if ($put > 0) {
        file_put_contents(__DIR__."/install.lock"," ");
        json(200,"设置成功");
    } else {
        json(-1,"配置保存失败");
    }
    
}

// 引入页面
include __DIR__ . "/install.html";