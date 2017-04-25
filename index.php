<?php
set_time_limit(0);
ini_set('memory_limit', '100M');
//ignore_user_abort(true);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

require './vendor/autoload.php';

if (php_sapi_name() != "cli") {
    die("必须在cli下运行 \n"); 
}

//print_r($argc);
if ($argc != 3) { 
    die("Usage: php index.php <1-n> <test|clean|start|stop|restart> \n"); 
}
array_shift($argv); 

//print_r($argv);die;

$cliIncludeFile = $argv[0];
$cliAct = $argv[1];

$siteConfigDir = './SiteConfig/';

if (!$cliIncludeFile) {
    echo "<br>"; 
    foreach (new DirectoryIterator($siteConfigDir) as $fileInfo) {
        if($fileInfo->isDot()) continue;
        if ($fileInfo->getExtension() == 'php') {
            include($siteConfigDir.$fileInfo->getFilename());
            echo '<a href="'.$configs['scanUrls'][0].'" target="_blank">'.$configs['title'].'</a> &nbsp;<a href="?file='.$fileInfo->getBasename().'&act=test" target="_blank">测试</a>&nbsp; 
                                      <a href="?file='.$fileInfo->getBasename().'&act=start" target="_blank">开始</a>&nbsp; 
                                      <a href="?file='.$fileInfo->getBasename().'&act=stop" target="_blank">停止</a><hr />';
            //print_r($configs);


        }
        
    }
}else{

    $t1 = microtime(true);

    $includeFile = $siteConfigDir.$cliIncludeFile.'.php';
    include($includeFile);
    $configs['siteConfigDir'] = $siteConfigDir;

    //print_r($configs);
    if ($cliAct == 'test') {
        $configs['dbConfig'] = include './config.php';
        $instances = new Redis();
        $instances->connect($configs['dbConfig']['redis']['host'], $configs['dbConfig']['redis']['port']);

        $instances->del('jiumozhiQueue'.$configs['id']); 
        $instances->del('jiumozhiQueue'.$configs['id'].'AllUrl'); 
        echo "清理redis队列完毕\n";


        
        @unlink($configs['siteConfigDir'].$configs['id'].'stop.txt');
        $configs['debug'] = 1;
        $configs['debugNum'] = 36;
        $configs['action'] = 'test';
        $crawler = new Crawler($configs);
        $crawler->start(); 
    }elseif ($cliAct == 'start') {

        $configs['dbConfig'] = include './config.php';
        $configs['action'] = 'start';
        //print_r($dbConfig);die;
        

        @unlink($configs['siteConfigDir'].$configs['id'].'stop.txt');
        $crawler = new Crawler($configs);
        $crawler->start(); 
    }elseif ($cliAct == 'restart') {

        $configs['dbConfig'] = include './config.php';
        $configs['action'] = 'restart';
        //print_r($dbConfig);die; 

        @unlink($configs['siteConfigDir'].$configs['id'].'stop.txt');
        $crawler = new Crawler($configs);
        $crawler->start(); 
    }elseif ($cliAct == 'stop') {
        file_put_contents($configs['siteConfigDir'].$configs['id'].'stop.txt', '');
        echo "已经停了吧 - -\n";die;
    }elseif ($cliAct == 'clean') {
        $configs['dbConfig'] = include './config.php';
        $instances = new Redis();
        $instances->connect($configs['dbConfig']['redis']['host'], $configs['dbConfig']['redis']['port']);

        $instances->del('jiumozhiQueue'.$configs['id']); 
        $instances->del('jiumozhiQueue'.$configs['id'].'AllUrl'); 
        echo "清理redis队列完毕\n";

    }


    $t2 = microtime(true);
    echo "耗时".round($t2-$t1,3)."秒\n";
}


//$crawler = new Crawler($configs);
//$crawler->start(); 
