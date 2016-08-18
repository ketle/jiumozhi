<?php
class Queue  
{ 
    public $queue = []; 
    public $queueAll = []; 
    
    /**
     * [尾部入队]
     * @param [type] $value ['url'=>$url,'opt'=$opt]
     */
    public function addLast($value)  
    { 
        if ($value['opt']['reserve'] == true) { //去重选项
            return array_push($this->queue,$value); 
        }

        if (!$this->queueAll[$value["url"]]) {        
            $this->queueAll[$value["url"]]=1; 
            return array_push($this->queue,$value); 
        }
    } 
    /**（头部）出队**/ 
    public function removeFirst()  
    { 
        return array_shift($this->queue); 
    } 
    /*
    public function removeLast()  
    { 
        return array_pop($this->queue); 
    } 
    public function addFirst($value)  
    { 
        return array_unshift($this->queue,$value); 
    } 
    public function makeEmpty()  
    { 
        unset($this->queue);
    } 
    
    public function getFirst()  
    { 
        return reset($this->queue); 
    } 
    public function getLast()  
    { 
        return end($this->queue); 
    }*/
    /** 获取长度 **/
    public function getLength()  
    { 
        return count($this->queue); 
    }
}
