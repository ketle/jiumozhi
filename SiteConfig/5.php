<?php
$configs = [];

//额外信息
$configs['id'] = 5; //文件名
$configs['title'] = 'huxiu'; //标题
$configs['desc'] = 'huxiu'; //描述
//额外信息

$configs['domains'] = ['huxiu.com'];
$configs['scanUrls'] = ['https://www.huxiu.com/'];
$configs['contentUrlRegexes'] = ['/article/\d+/1.html'];
$configs['helperUrlRegexes'] = ['article_list\?page=\d+'];
$configs['thread'] = 3;
$configs['interval'] = 5000;
$configs['fields'] = [   
                        [    
                            'name'=>'article_title',
                            'alias'=>'标题',
                            'selector'=>'//*[@id="log-send-article"]/div[2]/h1',
                            'selectorType'=>'XPath',
                            'required'=>1
                        ],[    
                            'name'=>'article_title_img',
                            'alias'=>'标题图片',
                            'selector'=>'//*[@class="article-img-box"]/img/@src',
                            'selectorType'=>'XPath'
                        ],
                        [    
                            'name'=>'article_content',
                            'alias'=>'内容',
                            'selector'=>"//div[@id='article_content']",
                            'selectorType'=>'XPath'
                            
                        ],
                    ];
$configs['beforeCrawl'] = function (&$site) {
        $site['addHeader']("Referer", "http://www.huxiu.com/");
        $site['addCookies']("gr_user_id=e321f323-cfeb-4b1a-bc4b-a9644977d262; kr_stat_uuid=CDwxJ24517819; Hm_lvt_e8ec47088ed7458ec32cde3617b23ee3=1471093100; Hm_lvt_713123c60a0e86982326bae1a51083e1=1471069347,1471069528,1471069554,1471103123; _alicdn_sec=57b299062ae8a8c0d74782d6ebbefdff188ab528; aliyungf_tc=AQAAAD5P9Q8NZQsA90fnekPdEbGb5/qw");
                                    
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

$global = [];
$configs['onProcessScanPage'] = function (&$page,&$content,&$site) {
	global $global;
	$global['page'] = 2;
    //echo $content;
    
    $pf = PauseFactory::Create( 'Xpath' ); 
    $match = $pf->pause($content,'//div[contains(@class,"get-mod-more")]/@data-cur_page');
    $pagex = $match;

    $match = $pf->pause($content,'//div[contains(@class,"get-mod-more")]/@data-last_dateline');
    $last_dateline = $match;

    $pf2 = PauseFactory::Create( 'Regex' );
    $match = $pf2->pause($content,"|var huxiu_hash_code='(\w+)'|");
    $huxiu_hash_code = $match;

    $url = 'https://www.huxiu.com/v2_action/article_list?page='.$global['page'];
    $options['method'] = 'POST';
    $options['data'] = ['huxiu_hash_code'=>$huxiu_hash_code,'page'=>$global['page'],'last_dateline'=>$last_dateline];

    //print_r($options);die;
    $site['addUrl']($url,$options);
    $global['huxiu_hash_code'] = $huxiu_hash_code;
    //die;
    //
    //echo $url;

    return true;
};


$configs['onProcessHelperPage'] = function (&$page,&$content,&$site) {
    //echo $content;
    global $global;
    $content = json_decode($content,true); 
    echo 'page:'.$global['page']."<br>\n";
    //print_r($content);
    $global['last_dateline'] = $content['last_dateline'];
    $content = $content['data'];

    //echo $content;die;


    $global['page']++;
    $url = 'https://www.huxiu.com/v2_action/article_list?page='.$global['page'];
    $options['method'] = 'POST';
    $options['data'] = ['huxiu_hash_code'=>$global['huxiu_hash_code'],'page'=>$global['page'],'last_dateline'=>$global['last_dateline']];

    print_r($options);//die;
    echo "<br>\n";
    $site['addUrl']($url,$options);



    //die;
    /*die;
   
    $content2 = unserialize($content);
    $content = json_decode($content,true);
    $content2 =  json_decode( json_encode( $content2),true);
    print_r($content2);die;
    foreach ($content2['data']['items'] as $key => $value) {
        //echo $value['id']."<br>";
        $lastId = $value['id'];
        $site['addUrl']('http://36kr.com/p/'.$lastId.'.html'); //内容页
    }
    $site['addUrl']('http://36kr.com/api/info-flow/main_site/posts?column_id=&b_id='.$lastId.'&per_page=20&_='.time()); //列表页json
    //die;*/
    
    return true;
};
$configs['onProcessContentPage'] = function (&$page,&$content,&$site) {

    return false;
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