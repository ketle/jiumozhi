<?php
$configs = [];

//额外信息
$configs['id'] = 2; //文件名
$configs['title'] = '落伍者(论坛)'; //标题
$configs['desc'] = '落伍者落伍者落伍者'; //描述
$configs['charset'] = 'gbk'; //charset
//额外信息

$configs['domains'] = ['www.im286.net'];
$configs['scanUrls'] = ['http://www.im286.net/forum-1-1.html'];
$configs['contentUrlRegexes'] = ['thread-\d+-1.html'];
$configs['helperUrlRegexes'] = ['forum-\d+-\d+.html'];
$configs['thread'] = 3;
$configs['interval'] = 5000;
$configs['fields'] = [   
                        [    
                            'name'=>'article_title',
                            'alias'=>'标题',
                            'selector'=>'//*[@id="pt"]/div/a[5]/text()',
                            'selectorType'=>'XPath',
                            'required'=>1
                        ],
                        [    
                            'name'=>'article_content',
                            'alias'=>'内容',
                            'selector'=>"/html/body[@id='nv_forum']/div[@id='wp']/div[@id='ct']/div[@id='pgt']/div[@class='pgt']/div[@class='pg']/a",
                            'selectorType'=>'XPath',
                            'repeated'=>1,
                            'children'=>
                                [   
                                    [    
                                        'name'=>'page',
                                        'alias'=>'分页',
                                        'selector'=>'//text()',
                                        'selectorType'=>'XPath',
                                        'required'=>1,
                                        'transient'=>'page' //临时变量,要删掉
                                    ],
                                    [    
                                        'name'=>'article_content2',
                                        'alias'=>'内容',
                                        'selector'=>'//div[@class="im286table"]',
                                        'selectorType'=>'XPath',
                                        'sourceType'=>'AttachedUrl',
                                        'attachedUrl'=>'{page}',
                                        'repeated'=>1,
                                        'children'=>
                                            [   
                                                [    
                                                    'name'=>'author',
                                                    'alias'=>'作者',
                                                    'selector'=>'//a[@class="xw1"]',
                                                    'selectorType'=>'XPath',
                                                    'required'=>1,
                                                    'transient'=>'author' //临时变量,要删掉
                                                ],
                                                [    
                                                    'name'=>'content',
                                                    'alias'=>'内容',
                                                    'selector'=>'//*[contains(@id,"postmessage_")]',
                                                    'selectorType'=>'XPath',
                                                    'required'=>1
                                                ],
                                            ]
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

    //$site['addUrl']('http://www.im286.net/thread-17437914-1.html');
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
    if ($fieldName == 'article_content') {
        //echo "<br>article_content:";
        //print_r($page['url']);print_r($data) ;

        if ($data) {
            # code...
            array_unshift($data, 1);
            array_pop($data);

            foreach ($data as &$value) {

                $value = str_replace('-1.html','-'.intval(str_replace('...', '', $value)) .'.html',$page['url']);
            }
            //print_r($data) ; 
        } 
        //die;
    }

    if ($fieldName == 'author') {
        //echo "<br>article_content:";
        //print_r($page['url']);print_r($data) ;

        if ($data == '下乡客') {
            $page['skip']();
            //print_r($data) ; 
        } 
        //die;
    }

    
 

    

    //print_r($fieldName);echo "<br>";//die;
    return ;
};
$configs['beforeCacheImg'] = function (&$page,&$data) {
    return ;
};
$configs['afterExtractPage'] = function (&$page,&$data) {
    return ;
};