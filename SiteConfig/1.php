<?php
$configs = [];

//额外信息
$configs['id'] = 1; //文件名
$configs['title'] = '福利吧(普通列表)'; //标题
$configs['desc'] = '福利吧吧吧吧吧吧吧吧吧吧吧吧吧吧吧吧吧吧'; //描述
//额外信息

$configs['domains'] = ['fuliba.net'];
$configs['scanUrls'] = ['http://fuliba.net/'];
$configs['contentUrlRegexes'] = ['http://fuliba\.net/.+\.html'];
$configs['helperUrlRegexes'] = ['http://fuliba\.net/page/\d+'];
$configs['thread'] = 3;
$configs['interval'] = 5000;
$configs['fields'] = [   
                       ['name'=>'article_title','alias'=>'标题','selector'=>"//h2[@class='entry-name']/text()",'selectorType'=>'XPath','required'=>1],
                        ['name'=>'article_con','alias'=>'标题','selector'=>"//div[@class='entry-content']",'selectorType'=>'XPath','required'=>1],
                        
                    ];
$configs['beforeCrawl'] = function (&$site) {
        //$site['addHeader']("Referer", "http://buluo.qq.com/p/index.html");
        //$site['addCookies']("last_item_date:10733=1467003228; mykeywords=a%3A1%3A%7Bi%3A0%3Bs%3A6%3A%22%E4%BD%90%E7%BD%97%22%3B%7D; PHPSESSID=2mq1jhshc6ssi2rc3j3iontku7; GINFO=uid%3D3519430%26nickname%3Dketle%26group_id%3D0%26avatar_t%3D%26main_group_id%3D0%26common_group_id%3D59; GKEY=0c3d0734c04ae6f2b72632d0553eb116");
                                    
};
$configs['nextScanUrl'] = function (&$url) {
    return ;
};
$configs['onChangeProxy'] = function (&$site) {
    return ;
};
$configs['isAntiSpider'] = function (&$url,&$content) {
    return ;
};
$configs['afterDownloadPage'] = function (&$page,&$site) {
	
    return ;
};
$configs['afterDownloadAttachedPage'] = function (&$page,&$site) {
    return ;
};
$configs['onProcessScanPage'] = function (&$page,&$content,&$site) {
    /*echo 'sss:';
    print_r($site['scanUrls']);
    print_r($site['helperUrls']);
    print_r($site['contentUrls']);*/

    //$site['addUrl']('http://fuliba.net/%e6%88%91%e6%9c%89%e4%b8%80%e4%b8%aa%e6%a2%a6%e6%83%b3%ef%bc%9a%e9%9d%a2%e6%9c%9d%e5%8f%b8%e6%9c%ba%ef%bc%8c%e4%ba%ba%e6%bb%a1%e8%bd%a6%e5%bc%80.html');

    $content = str_replace('<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />', '', $content); //奇怪的网站,一会儿utf8,一会儿gb2312,导致xpath不正常;


    return true;
};
$configs['onProcessHelperPage'] = function (&$page,&$content,&$site) {
    $content = str_replace('<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />', '', $content);
    return true;
};
$configs['onProcessContentPage'] = function (&$page,&$content,&$site) {
    $content = str_replace('<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />', '', $content);
    return true;
};

$configs['beforeHandleImg'] = function (&$fieldName,&$img) {
    return ;
};
$configs['beforeCacheImg'] = function (&$fieldName,&$url) {
    return ;
};
$configs['afterExtractField'] = function (&$fieldName,&$data,&$page) {
    //if ($fieldName == 'article_title' && trim($data) == '翻山新科技，i42.li，配置仅需两步') {
        //print_r($data);die;
        //$page['skip']();
    //}
    return ;
};
$configs['beforeCacheImg'] = function (&$page,&$data) {
    return ;
};
$configs['afterExtractPage'] = function (&$page,&$data) {
    return ;
};