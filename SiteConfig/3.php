<?php
$configs = [];

//额外信息
$configs['id'] = 3; //文件名
$configs['title'] = '院系新闻(普通列表)'; //标题
$configs['desc'] = '院系新闻,取列表页中的数据'; //描述
$configs['charset'] = 'gb2312'; //charset
//额外信息

$configs['domains'] = ['news.sise.com.cn'];
$configs['scanUrls'] = ['http://news.sise.com.cn/list.php?id-5.html'];
$configs['contentUrlRegexes'] = ['show\.php\?id-\d+.html'];
$configs['helperUrlRegexes'] = ['/list\.php\?id-\d+-page-\d+.html'];
$configs['thread'] = 3;
$configs['interval'] = 5000;
$configs['fields'] = [   
                       ['name'=>'article_title','alias'=>'标题','selector'=>'//*[@id="BContent2"]/h1/text()','selectorType'=>'XPath','required'=>1],
                        ['name'=>'article_content','alias'=>'内容','selector'=>"//div[@class='body']",'selectorType'=>'XPath','required'=>1],
                        ['name'=>'article_date','alias'=>'时间','selector'=>"//*[@id='contextData']",'selectorType'=>'XPath'],
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
    //print_r($site['header']) ;

    if ($site) {
        //echo 111;
    }
    return ;
};
$configs['afterDownloadAttachedPage'] = function (&$page,&$site) {
    return ;
};
$configs['onProcessScanPage'] = function (&$page,&$content,&$site) {
    $pf = PauseFactory::Create( 'Xpath' ); 
    $match = $pf->pause($content,"/html/body/div[@id='container']/div[@id='B']/div[@id='BContent2']/div[@id='ResBox']/div[@id='RB']/h2");
    //print_r($match);

    if ($match) {
        foreach ($match as $key => $value) {
            
            preg_match('|id-(\d+).html" target="_blank">(.*)</a>(.*)|', $value,$match2);
            //print_r($match2);

            $url = 'http://news.sise.com.cn/show.php?id-'.$match2[1].'.html';
            $site['addUrl']($url,['contextData'=>'<div id="contextData">'.trim($match2[3]).'</div>']);
        }
    }
    
    return false;
};
$configs['onProcessHelperPage'] = function (&$page,&$content,&$site) {

    $pf = PauseFactory::Create( 'Xpath' ); 
    $match = $pf->pause($content,"/html/body/div[@id='container']/div[@id='B']/div[@id='BContent2']/div[@id='ResBox']/div[@id='RB']/h2");
    //print_r($match);

    if ($match) {
        foreach ($match as $key => $value) {
            
            preg_match('|id-(\d+).html" target="_blank">(.*)</a>(.*)|', $value,$match2);
            //print_r($match2);

            $url = 'http://news.sise.com.cn/show.php?id-'.$match2[1].'.html';
            $site['addUrl']($url,['contextData'=>'<div id="contextData">'.trim($match2[3]).'</div>']);
        }
    }
    
    return false;
};
$configs['onProcessContentPage'] = function (&$page,&$content,&$site) {
    //echo $content;die;
    return true;
};

$configs['beforeHandleImg'] = function (&$fieldName,&$img) {
    return ;
};
$configs['beforeCacheImg'] = function (&$fieldName,&$url) {
    return ;
};
$configs['afterExtractField'] = function (&$fieldName,&$data,&$page) {
    return ;
};
$configs['beforeCacheImg'] = function (&$page,&$data) {
    return ;
};
$configs['afterExtractPage'] = function (&$page,&$data) {
    return ;
};