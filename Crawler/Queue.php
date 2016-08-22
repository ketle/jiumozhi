<?php
class Queue  
{ 
    public $key = 'jiumozhiQueue'; 
    public $queue = []; 
    public $queueAll = []; 
    public $host = '127.0.0.1'; 
    public $port = 6379; 
    

    public function __construct($host,$port,$id,$action){
        $this->host = $host;
        $this->port = $port;
        $this->key = $this->key.$id;

        if ($action == 'restart') {
            $this->getInstance()->del($this->key); 
            $this->getInstance()->del($this->key.'AllUrl'); 
        }
 

    }

    /**
     * [尾部入队]
     * @param [type] $value ['url'=>$url,'opt'=$opt]
     */
    public function addLast($value)  
    { 
        if ($value['opt']['reserve'] == true) { //去重选项
            return $this->getInstance()->rpush($this->key, serialize($value)); 
        }

        //print_r($this->getInstance());

        $allUrl = unserialize($this->getInstance()->get($this->key.'AllUrl')); 
        //echo 'allUrl count:'.count($allUrl)."\n";

        if (!$allUrl[$value["url"]]) {        
            $allUrl[$value["url"]]=1; 
            $this->getInstance()->set($this->key.'AllUrl', serialize($allUrl));             
            return $this->getInstance()->rpush($this->key, serialize($value)); 
        }



    } 
    /**（头部）出队**/ 
    public function removeFirst()  
    { 
        return unserialize($this->getInstance()->lpop($this->key)); 
    } 
    
    /** 获取长度 **/
    public function getLength()  
    { 
        return $this->getInstance()->llen($this->key); 
    }

    public function getInstance()
    {
        static $instances = array();
        $key = getmypid();
        if (empty($instances[$key]))
        {
            $instances[$key] = new Redis();
            //echo 111;
            //print_r($instances[$key]);

            $instances[$key]->connect($this->host, $this->port);
        }
 
        return $instances[$key];
    }
}
