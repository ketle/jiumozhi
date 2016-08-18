<?php
$configs = [];

//额外信息
$configs['id'] = 4; //文件名
$configs['title'] = '某p2p(只取列表数据)'; //标题
$configs['desc'] = '只取列表数据'; //描述
//$configs['charset'] = 'gbk'; //charset
//额外信息

$configs['domains'] = ['www.firstp2p.com'];
$configs['scanUrls'] = ['https://www.firstp2p.com/deals?p=1&cate=0'];
$configs['contentUrlRegexes'] = [];
$configs['helperUrlRegexes'] = ['\?p=\d+&cate=0'];
$configs['thread'] = 3;
$configs['interval'] = 5000;
$configs['fields'] = [   
                        [    
                            'name'=>'products',
                            'alias'=>'内容',
                            'selector'=>'//div[contains(@class,"p2p_product")]',
                            'selectorType'=>'XPath',
                            'repeated'=>1,
                            'children'=>
                                [   
                                    [    
                                        'name'=>'product_name',
                                        'alias'=>'作者',
                                        'selector'=>'//h3/a | //h3/span',
                                        'selectorType'=>'XPath',
                                        'required'=>1
                                    ],
                                    [    
                                        'name'=>'product_info',
                                        'alias'=>'内容',
                                        'selector'=>'//h3/a | //h3/span',
                                        'selectorType'=>'XPath',
                                    ],
                                ]
                        ],
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
    return true;
};
$configs['onProcessHelperPage'] = function (&$page,&$content,&$site) {
    return true;
};
$configs['onProcessContentPage'] = function (&$page,&$content,&$site) {
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