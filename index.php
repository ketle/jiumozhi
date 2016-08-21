<?php
set_time_limit(0);
ini_set('memory_limit', '3000M');
ignore_user_abort(true);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

require './vendor/autoload.php';

$siteConfigDir = './SiteConfig/';

if (!$_GET['file']) {
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

    $includeFile = $siteConfigDir.$_GET['file'];
    include($includeFile);
    $configs['siteConfigDir'] = $siteConfigDir;

    //print_r($configs);
    if ($_GET['act'] == 'test') {
        @unlink($configs['siteConfigDir'].$configs['id'].'stop.txt');
        $configs['debug'] = 1;
        $configs['debugNum'] = 36;
        $crawler = new Crawler($configs);
        $crawler->start(); 
    }elseif ($_GET['act'] == 'start') {

        $configs['dbConfig'] = include './config.php';
        //print_r($dbConfig);die;
        

        @unlink($configs['siteConfigDir'].$configs['id'].'stop.txt');
        $crawler = new Crawler($configs);
        $crawler->start(); 
    }elseif ($_GET['act'] == 'stop') {
        file_put_contents($configs['siteConfigDir'].$configs['id'].'stop.txt', '');
        echo '已经停了吧 - -';die;
    }


    $t2 = microtime(true);
    echo '<br>耗时'.round($t2-$t1,3).'秒';
}


//$crawler = new Crawler($configs);
//$crawler->start(); 
