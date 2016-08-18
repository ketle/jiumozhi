<?php

use DiDom\Document;

interface PauseData
{
    function pause($content,$selector,$opt);
}

class PauseXPath0 implements PauseData
{
    public function __construct( ) { }

    public function pause($content,$selector,$opt=false){ 
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($content);
        libxml_clear_errors();
        $xPath = new DOMXPath($dom);
        $elements = $xPath->query($selector);
        if ($elements->length>1) {
            for ($i=0; $i < $elements->length; $i++) { 
                if ($opt==true) {
                    $ret[] = $elements->item($i)->nodeValue; 
                }else{
                    $ret[] = $elements->item($i)->nodeValue?$dom->saveHtml($elements->item($i)):''; 
                }
                
            }
        }else{
            if ($opt==true) {
                    $ret = $elements->item(0)->nodeValue; 
                }else{
                    $ret = $elements->item($i)->nodeValue?$dom->saveHtml($elements->item(0)):''; 
                }
            
        } 
        return $ret;
    }
}


class PauseXPath implements PauseData
{
    public function __construct( ) { }

    public function pause($content,$selector,$opt=false){ 
        $document = new Document();
        $document->loadHtml($content);
        $lists = $document->xpath($selector);

        if ($lists) {
            if ($lists[1]) {
                foreach($lists as $k =>$list) { 
                    //print_r($selector);//die;

                    if (is_string($list)) {
                        $r[] = trim($list); 
                    }else{
                        $r[] = trim($list->innerHtml()); 
                    } 
                }
            }else{

                if (is_string($lists[0])) {
                    $r = trim($lists[0]); 
                }else{

                    /*echo "<br>\n";
                    print_r($selector);print_r($lists);
                    echo "<br>\n";*/
                    $r = trim($lists[0]->innerHtml()); 
                } 
 
                
            }
        }

        

        return $r;
    }
}


class PauseJsonPath implements PauseData
{
    public function __construct(  ) { }

    public function pause($content,$selector,$opt=false){ 
        //echo 'pause:<br>';
        //print_r($content);
        //print_r($selector);
        //echo '<br>';//die;

        if (is_array($content)) {
            $content = json_encode($content);
        }
        $content = json_decode($content,true);

        if (strpos($selector, '.')) {
            $temp1 = explode('.', $selector);
            foreach ($temp1 as $key => $value) {
                $content = $content[$value];
            }
        }else{
            $content = $content[$selector];
        }

        return $content;
    }
}


class PauseRegex implements PauseData
{
    public function __construct(  ) { }

    public function pause($content,$selector,$opt=PREG_PATTERN_ORDER){ 
         

        preg_match_all($selector,$content,$result,$opt); 
        return isset($result[1][1])?$result[1]:$result[1][0];
    }
}

class PauseCssPath implements PauseData
{

    public function __construct(  ) { }

    public function pause($content,$selector,$opt=false){ 
        
        $document = new Document();
        $document->loadHtml($content);
        $lists = $document->find($selector);

        if ($lists) {
            # code...
            if (count($lists) > 0) {
                foreach($lists as $k =>$list) { 
                    $r[] = $list->innerHtml(); 
                }
            }else{
                $r = $lists[0]->innerHtml();
            }
        }

        

        return $r;
    }
}

class PauseFactory
{
    public static function Create( $method )
    {
        $class = 'Pause'.$method;
        //echo '--'.$class.'--';测试
        return new $class(  );
    }
}
