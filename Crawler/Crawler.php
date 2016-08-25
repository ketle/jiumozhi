<?php
/**
 * 
 */
use \Curl\MultiCurl;
use \Curl\Curl;
class Crawler {

    private $curlTimes = 0;
    private $field = [];
    private $queueObj;
    private $curlObj;
    private $configs; 
    private $site = [];
    private $page = [];
    private $skip = '';
    private $pauseDrive; 
    private $db; 
    private $console; 
    private $scanUrlsIndex = 0;

     

    public function __construct($configs){
        //init;
        $this->configs = $configs;
        $this->initSite();
        $this->initPage();
        $this->initDb();
        $this->pauseDrive = PauseFactory::Create( 'Xpath' );




        $temp1 = parse_url($this->configs['scanUrls'][$this->scanUrlsIndex]);
        $this->configs['baseUrl'] = $temp1['scheme'].'://'.$temp1['host'].'/';
        $this->configs['baseUrlPath'] = $temp1['scheme'].'://'.$temp1['host'].$temp1['path'];
        unset($temp1);


        $this->queueObj = new Queue($this->configs['dbConfig']['redis']['host'],$this->configs['dbConfig']['redis']['port'],$this->configs['id'],$this->configs['action']);  
        if ($this->queueObj->getLength()==0) {
            $this->addQueue($this->configs['scanUrls'][$this->scanUrlsIndex],['type'=>1]); 
        }
        
        
    }

    /**
     * [启动]
     * @return [type] [description]
     */
    public function start(){ 
 
        
        $this->configs['beforeCrawl']($this->site); //beforeCrawl回调
 
        
        while (true) { 
            //执行开关
            if (file_exists($this->configs['siteConfigDir'].$this->configs['id'].'stop.txt') ) {
                break;
            }

            if ($this->configs['debug'] && $this->curlTimes > $this->configs['debugNum']) {
                break;
            }
            //执行开关
                 
            if ($this->queueObj->getLength()==0) {
                break;
            }
            for ($i = 1; $i <= $this->configs['dbConfig']['maxProcess']; ++$i)
            {
                $pid = pcntl_fork();
                if ($pid == -1)
                {
                    echo "fork child process failed\n";
                    exit(0);
                }
                if (!$pid)
                {
                    
                    //判断队列是否为空,下一个ScanUrl
                    if ($this->queueObj->getLength()>0) {
                        $queueArray = $this->removeQueue($this->configs['thread']);
                    }else{
                        //nextScanUrl
                        $this->scanUrlsIndex++;
                        if ($this->configs['scanUrls'][$this->scanUrlsIndex]) {
                            $queueArray[] = ['url'=>$this->configs['scanUrls'][$this->scanUrlsIndex]];                
                        }else{
                            break 2;
                            //die('done');
                        }
                    }
                    //判断队列是否为空,下一个ScanUrl

                    //
                    $this->configs['onChangeProxy']($this->site); //onChangeProxy回调
                    $this->curl($queueArray);
                    unset($queueArray);

                    $this->curlObj->success(function($instance) {
                        $this->log('curl:'.$instance->url); 
                          
                        $this->page['url'] = $instance->url;
              
                        $this->page['raw'] = $instance->response;
                        $this->page['raw'] = $this->convertUtf8($this->page['raw']).$instance->requestHeaders['contextData']; //加上附加数据
                        

                        if ($instance->requestHeaders['contextData']) {
                            $this->log('contextData:'.$instance->requestHeaders['contextData'],3);                    
                        }

                        $this->page['request'] = $instance;//还没改造
         

                        $this->configs['isAntiSpider']($this->page['url'],$this->page['raw']); //isAntiSpider回调
                        $this->configs['afterDownloadPage']($this->page,$this->site); //afterDownloadPage回调 
                        

                        //scanUrls
                        if (in_array($this->page['url'], $this->configs['scanUrls'])) {
                            $this->log('in scanUrls:'.$this->page['url']); 
                                                             
                            $r1 = $this->configs['onProcessScanPage']($this->page,$this->page['raw'],$this->site); //onProcessScanPage 回调
                            //$this->log('preg_match:'.$value.' -> '.$this->page['url']); 
                            if ($r1 == true) {
                                $this->parseAllUrl($this->page['raw']); 
                            } 
                            
                        } 

                        

                        //列表页
                        foreach ($this->configs['helperUrlRegexes'] as $key => $value) {
                            if (preg_match("|".$value."$|", $this->page['url'])) {   
                                $this->log('in helperUrl:'.$this->page['url']); 

                                if (strstr($instance->responseHeaders['content-type'],'application/json')) { //列表页是json数据
                                    $this->page['raw'] = serialize($this->page['raw']);
                                }             
                                $r1 = $this->configs['onProcessHelperPage']($this->page,$this->page['raw'],$this->site); //
                                
                                
                                if (!$this->configs['contentUrlRegexes']) {
                                    //当内容页正则为空时,可以直接解析列表页
                                    //$this->log('xxxxxxxxxxxxxxx:'.$this->page['url']);      
                                    $this->parseData($this->page['url'],$this->page['raw']);
                                }
                                if ($r1 == true) {
                                    $this->parseAllUrl($this->page['raw']); 
                                }
                                break;
                            } else {
                                
                            }
                        }
                        //列表页
                       

                        //内容页                
                        foreach ($this->configs['contentUrlRegexes'] as $key => $value) {
                            if (preg_match("|".$value."$|", $this->page['url'])) {                            
                                $this->log('in contentUrl:'.$this->page['url']);        
                                $r1 = $this->configs['onProcessContentPage']($this->page,$this->page['raw'],$this->site); //onProcessContentPage 回调
                                //$this->log('preg_match:'.$value.' -> '.$this->page['url']); 
                                $this->parseData($this->page['url'],$this->page['raw']);
                                if ($r1 == true) {
                                    $this->parseAllUrl($this->page['raw']); 
                                }
                                break;
                            } else {
                                
                            }
                        } 
                        //内容页  
                        unset($instance);            

                        //ob_flush();
                        //flush();
                        //usleep(1000000);  
                    }); 

                    $this->curlObj->error(function($instance) {
                        $this->log('call to "' . $instance->url . '" was unsuccessful.' . "\n".'error code: ' . $instance->errorCode . "\n".'error message: ' . $instance->errorMessage . "\n",3);
                        unset($instance);       
                    }); 

                    $this->curlObj->start(); 
                    exit($i);

                }
                usleep(1);
            }
            while (pcntl_waitpid(0, $status) != -1)
            {
                $status = pcntl_wexitstatus($status);
                if (pcntl_wifexited($status))
                {
                    //echo "";
                }
                echo "$status finished\n";
            }

            

        }

        


    }
 
    /**
     * [解析所有链接]
     * @param  [string] $content [description]
     * @param  [type] $r       [description]
     * @return [type]          [description]
     */
    public function parseAllUrl($content){
        
        
        //echo $content;
        $urlData = $this->pauseDrive->pause($content,"//a/@href");
        if ($urlData) {
            foreach ($urlData as $key => $value) {
                       
                //$this->log('parseAllUrl url:'.$value);   
                $temp1 = parse_url($value);
                if (!$temp1['host'] || in_array($temp1['host'], $this->configs['domains']) ) {
                    $this->urlRegexes($value); 
                }
                unset($temp1);
                
                
            }
            unset($urlData);
        }
       
    }


    /**
     * [筛选helper/content规则才能进队列]
     * @param  [type] $url [description]
     * @return [type]      [description]
     */
    public function urlRegexes($url){
 

            foreach ($this->configs['helperUrlRegexes'] as $key => $value) {
                if (preg_match("|^".$value."$|", $url)) {  
                    $this->addQueue($url,['type'=>2]); //进队列
                    break;
                } else {
                    
                }
            } 
            
            

            foreach ($this->configs['contentUrlRegexes'] as $key => $value) {
                if (preg_match("|^".$value."$|", $url)) {                
                    
                    $this->addQueue($url,['type'=>3]); //进队列
                    break;
                } else {
                    
                }
            } 
    }

    
    /**
     * [进队列]
     * @param  [type] $url [description]
     * @param  [type] $opt [description]
     */

    public function addQueue($url,$opt=[]){
        //print_r($this->configs['dbConfig']);die;      
        $this->queueObj = $this->queueObj?$this->queueObj:new Queue($this->configs['dbConfig']['redis']['host'],$this->configs['dbConfig']['redis']['port'],$this->configs['id'],$this->configs['action']);        
        //$this->queueObj->addLast($url);  
        $temp1 = parse_url($url);
        if (!$temp1['scheme']) {    //相对地址
            if (!$temp1['path']) {
                $url = $this->configs['baseUrlPath'].$url; //"?xxxx"
                
            }else{
                if (substr($url, 0,1) == '/') { //"/1.html"
                    $url = substr($url,1);
                }
                $url = $this->configs['baseUrl'].$url; //"1.html"
            }

        }else{
            //绝对地址
        }
        $this->queueObj->addLast(["url"=>$url,"opt"=>$opt]);  
        unset($temp1);
        unset($url);
        unset($opt);

        //$this->log('add queue:'.$url); 

    }


    /**
     * [解析所有内容]
     * @param  [type] $url     [description]
     * @param  [type] $content [description]
     * @return [type]          [description]
     */
    public function parseData($url,$content){
         
        $this->skip = '';//重置下状态;
        $fieldContent = $this->parseFields($this->configs['fields'],$content); 

        //$page['skip'](); 删掉整条
        $skipFlag = $this->array_search_key('skipAllPage998', $fieldContent);
        if ($skipFlag) {
            unset($fieldContent);//site->skip
            unset($skipFlag);
        }

        //transient 删除字段
        $delKey = $this->array_search_key('transient', $this->configs['fields']);
        if ($delKey) {
            foreach ($delKey as $key => $value) {
                $this->log('delKey:'.(string)$value,1);
                $this->array_remove_key($fieldContent, (string)$value);
            }
            unset($delKey);
        }  

        $this->configs['afterExtractPage']($this->page,$fieldContent); //afterExtractPage回调


        if ($this->getDbInstance() && $fieldContent) {
        	$this->getDbInstance()->insert($this->tableName, [
                "site" => $this->configs['id'],
                "url" => $url,
                "data" => json_encode($fieldContent,JSON_UNESCAPED_UNICODE)
            ]);
             
        }

        if ($this->configs['debug'] ) {
            $this->log('fieldContent:');
            $this->log($fieldContent,1);
        }else{
            $this->log('quequeLeft:'.$this->queueObj->getLength(),1);
        }

        unset($fieldContent);
        unset($content);
        



        
       


    } 

    /**
     * [递归解析字段]
     * @param  [type] $fields  [description]
     * @param  [type] $content [description]
     * @return [type]          [description]
     */
    public function parseFields($fields,$content){
        
        

        foreach ($fields as $k =>  $value) { //第一层
            $pf = $value['selectorType'] != 'Xpath' ?  PauseFactory::Create( $value['selectorType'] ): $this->pauseDrive; 

            if ($value['sourceType']) {
                if ($value['sourceType'] == 'AttachedUrl') {
                    preg_match_all("|\{([^}]+)\}|", $value['attachedUrl'],$match);
                    if ($match[0]) {
                        foreach ($match[0] as $matchKey =>$matchValue) {
                            $value['attachedUrl'] = str_replace($matchValue, $con[$match[1][$matchKey]], $value['attachedUrl'] );
                        }
                    }
                    
                    $curlOneContent = $this->curlOne($value['attachedUrl']);
                    if ($curlOneContent) {
                        $content = $curlOneContent;

                        $this->configs['afterDownloadAttachedPage']($this->page,$this->site); //afterDownloadAttachedPage回调
                    }
                }
            }


            $con[$value['name']] = $pf->pause($content,$value['selector']); 

            //$con[$value['name']] = $pf->pause($content,$value['selector']); 

            /**
             * 控制required,page.skip()
             */
            if ($value['required'] && !$con[$value['name']]) { //判断required
                $con['skipAllPage998'] = 1;//设置一个特殊key,方便查找,找到即可删掉这整条数据;
                $this->log('required:'.$value['name'],3);
                //return $con;
            }
            if ($this->skip) {
                if ($this->skip == 'skipAllPage998') {
                    $con['skipAllPage998'] = 1;//设置一个特殊key,方便查找,找到即可删掉这整条数据;
                }
            }

            
            $this->configs['beforeHandleImg']($value['name'],$img); //beforeHandleImg
            $this->configs['beforeCacheImg']($value['name'],$img); //beforeCacheImg
            $this->configs['afterExtractField']($value['name'],$con[$value['name']],$this->page); //afterExtractField


            if ($value['children']){

                $contentRepeat = $con[$value['name']];
                $con[$value['name']] = [];

                if (is_array($contentRepeat)) {
                    foreach ($contentRepeat as $k2 =>  $repeatedContent) { //每个帖子的所有内容 
                        //if ($value['children']) {
                            $con[$value['name']][$k2] = $this->parseFields($value['children'],(string)$repeatedContent); //遍历

                            /**
                             * 控制required,page.skip()
                             */
                            if (!$con[$value['name']][$k2]) {
                                $this->log('required2:'.$value['name'],3);
                                unset($con[$value['name']][$k2]);
                            }

                            if ($this->skip == $value['name']) {
                                $this->log('skip:'.$value['name'],3);
                                unset($con[$value['name']][$k2]);
                                $this->skip = '';
                            }

                            /*if ($this->skip == 'skipAllPage998') {
                                $this->log('skip all:'.$value['name'],3);
                                unset($con);
                                $this->skip = '';
                                return;
                            }*/

                        //}

                    }
                } 
                     
            } 

        }

        unset($contentRepeat);
        unset($content);
        unset($fields);

        return $con; 

    }


    /**
     * [出队列]
     * @param  integer $c [description]
     * @return [type]     [description]
     */
    public function removeQueue($c=1){
        
        $quequeCount = $this->queueObj->getLength();
        $c = $c>$quequeCount?$quequeCount:$c;
        for ($i=0; $i < $c; $i++) { 

            $queueArray[] = $this->queueObj->removeFirst();
             
        } 

        return $queueArray; 

    }

    

    public function getCurlInstance()
    {
        static $instances = array();
        $key = getmypid();
        if (empty($instances[$key]))
        { 
            $instances[$key] = new MultiCurl();; 
        }
        return $instances[$key];
    }


    /**
     * [多个请求]
     * @param  [array] queueArray [description]
     */

    public function curl($queueArray){

        $this->curlObj = $this->getCurlInstance();
        //print_r($this->curlObj);

        if ($this->site['header']) { 
            foreach ($this->site['header'] as $key => $value) {
                
                $this->curlObj->setHeader($key, $value);
            }
        }


        if ($this->site['cookie']) { 
            foreach ($this->site['cookie'] as $key => $value) {
                
                $this->curlObj->setCookie($key, $value);
            }
        }

        
        $this->curlObj->setUserAgent('Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36');

        if ($this->site['userAgent']) { 
                
            $this->curlObj->setUserAgent($this->site['userAgent']);
            
        }


        $this->curlObj->setOpt(CURLOPT_SSL_VERIFYHOST, false);
        $this->curlObj->setOpt(CURLOPT_SSL_VERIFYPEER, false);

        //$this->log($queueArray);

        foreach ($queueArray as $v) {

            if ($v['opt']['header'] ) {

                foreach ($v['opt']['header'] as $key => $value) {
                
                    $this->curlObj->setHeader($key, $value);
                }
            }
            if ($v['opt']['contextData'] ) {
                //contextData扔到header里了
                $this->curlObj->setHeader('contextData', $v['opt']['contextData']);
                
            }

            //$this->curlObj->setHeader('contextData', '房贷收紧福克斯的垃圾焚烧开发建设的开发设计');

            if ($v['opt']['method'] == 'POST') {

                
                //$this->log($v['opt']);
                $this->curlObj->addPost($v['url'],$v['opt']['data']); 
            }else{

                $this->curlObj->addGet($v['url'],$v['opt']['data']); 
            }
            //$this->curlTimes++;

            //$this->log('curlTimes:'.$this->curlTimes); 
        }

    }

    /**
     * [单请求]
     * @param  [type] $url     [description]
     * @param  array  $options [description]
     * @return [type]          [description]
     */
    public function curlOne($url,$options=[]){

        $this->log('curlOne:'.$url); 
        $curl = new Curl();
        $curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);


        if ($this->site['header']) { 
            foreach ($this->site['header'] as $key => $value) {
                
                $curl->setHeader($key, $value);
            }
        } 

        if ($this->site['cookie']) { 
            foreach ($this->site['cookie'] as $key => $value) {
                
                $curl->setCookie($key, $value);
            }
        }

        $this->curlObj->setUserAgent('Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36');
        if ($this->site['userAgent']) { 
                
            $curl->setUserAgent($this->site['userAgent']);
            
        }


        
        
        if ($options['headers'] ) {
            foreach ($options['headers'] as $key => $value) {
                
                $curl->setHeader($key, $value);
            }
           
        } 
        if ($options['method'] == 'POST') {
            $curl->post($url,$options['data']);
        }else{
            //echo $url;
            $curl->get($url);
        }


        $this->curlTimes++;

        $this->log('curlOneTimes:'.$this->curlTimes); 

 

        if ($curl->error) {
            $this->log( 'Url: ' . $url .'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage ,3);
            return false;
        }
        else {
            if ( strstr( $curl->responseHeaders['content-type'],'application/json') ) {                
                $curl->response =   json_encode( $curl->response);
            }

            $this->page['raw'] = $curl->response;
            $this->page['request'] = $curl->request;//还没改造

            if ($options['contextData'] ) {
                $this->page['raw'] .= $options['contextData'];
               
            } 
            return $this->page['raw'];
        }

    }

    /**
     * [内容转utf8]
     * @param  [type] $content [description]
     * @return [type]          [description]
     */
    private function convertUtf8($content){
        if ($this->configs['charset']) {
            //$content = iconv ( $this->configs['charset'], 'utf-8' , $content );
            $content = mb_convert_encoding($content, "utf-8",$this->configs['charset']);
            $content = preg_replace('|charset\s*=\s*(\w+)|i', 'charset=UTF-8', $content);
        }
        return $content;
    }

    /**
     * [删除某个数组的key,下面不能是数组]
     * @param  [type] &$arr [description]
     * @param  [type] $k    [description]
     * @return [type]       [description]
     */
    public function array_remove_key(&$arr, $k){ 
        if ($arr) { 
            foreach ($arr as $key => &$value) { 
                if (is_array($value)) { 
                    $this->array_remove_key($value, $k); 
                } else {  
                    if (trim($key) == $k) { 
                        unset($arr[$k]); 
                    }
                } 
            }  
        }
    }
 

    public function array_search_key( $search, array $array, $mode = 'key'){    
        $res = array();  
        $temp1 = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));
        foreach ($temp1 as $key => $value) {  
            if ($search === ${${"mode"}}){  
                if($mode == 'key'){  
                    $res[] = $value;  
                }else{  
                    $res[] = $key;  
                }
                unset($search);
            }  
        } 
        unset($temp1);
        return $res;    
    }



    /**
     * [initSite ]
     * @return [type] [description]
     */
    public function initSite(){
        $this->site['scanUrls'] = [];
        $this->site['helperUrls'] = [];
        $this->site['contentUrls'] = [];

        $this->site['addHeader'] = function ($key,$value) {

            $this->site['header'][$key] = $value;
        };
       
        $this->site['addCookie'] = function ($key,$value) {

            $this->site['cookie'][$key] = $value;
        };

        $this->site['addCookies'] = function ($cookies) {
            $temp1 = explode(';', $cookies);
            foreach ($temp1 as $key => $value) {
                $temp2 = explode('=', trim($value));
                $this->site['cookie'][$temp2[0]] = urldecode($temp2[1]);
            }
            unset($temp1);
            
        };

        $this->site['addUrl'] = function ($url,$options=[]) {
            $this->addQueue($url,$options);
        };

        $this->site['requestUrl'] = function ($url,$options=[]) {
            return $this->curlOne($url,$options);
        };

        $this->site['setUserAgent'] = function ($userAgent) {
            $this->site['userAgent'] = $userAgent;
        };


    }

    /**
     * [initPage]
     * @return [type] [description]
     */
    public function initPage(){
          
        $this->page['skip'] = function ($fieldName='') {
            $fieldName = $fieldName?$fieldName:'skipAllPage998';
            $this->skip = $fieldName;
            unset($fieldName);

            $this->log('$this->skip:'.$this->skip,3);
        }; 

    }


    public function getDbInstance()
    {
        static $instances = array();
        $key = getmypid();
        if (empty($instances[$key]))
        { 
            $instances[$key] = new \medoo($this->configs['dbConfig']['db']); 
        }
        return $instances[$key];
    }

    public function initDb(){

        if ($this->configs['action'] == 'start' || $this->configs['action'] == 'restart') {
            
            $this->db = new \medoo($this->configs['dbConfig']['db']); 

            $this->tableName = 'jmz_data'.$this->configs['id'];

            if ($this->configs['action'] == 'restart') {
                $this->db->query("drop TABLE  `".$this->tableName."`;");
            }
            

            $count = $this->db->count($tableName, []);
            
            if (!$count) {
                $this->db->query("CREATE TABLE IF NOT EXISTS `".$this->tableName."` (
                      `id` int(11) NOT NULL,
                      `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      `update_date` datetime DEFAULT NULL,
                      `site` int(5) NOT NULL DEFAULT '0',
                      `url` varchar(200) NOT NULL,
                      `data` longtext,
                      `other` text,
                      `flag` int(3) NOT NULL DEFAULT '1'
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


                    ALTER TABLE `".$this->tableName."`
                      ADD PRIMARY KEY (`id`),
                      ADD UNIQUE KEY `site` (`site`,`url`);


                    ALTER TABLE `".$this->tableName."`
                      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");
            }
            unset($count);




            if ($this->configs['dbConfig']['insertType'] == 2) {
                $this->db->delete($this->tableName, ["site" => $this->configs['id']]);
            }
 
        }
    }
 
    /**
     * [log ]
     * @param  [type]  $str   [description]
     * @param  integer $level [description]
     * @return [type]         [description]
     */
    public function log($str,$level=0){

        $str = is_array($str)?json_encode($str,JSON_UNESCAPED_UNICODE):$str;

        echo date("Y-m-d H:i:s")." ";
        if ($level == 1) { 
            echo "\e[1;33m $str \e[0m \n";
        }elseif ($level == 2) { 
            echo "\e[1;34m $str \e[0m \n";
        }elseif ($level == 3) {
            echo "\e[1;31m $str \e[0m \n";
        }else{
            echo "$str \n";
        }

 
        unset($str);


    }
     
     
}