<?php
namespace MongoDataSource;
use PPCore\Adapters\DataSources\DataSourceInterface;
use PPCore\Adapters\DataSources\AbstractDataSource;
use PPCore\Helpers\ArrayHelper;
use PPCore\Exceptions\LocationNotSetException;
use PPCore\Exceptions\QuerySyntaxException;

use  MongoDB\Client;
use  MongoDB\Collection;

class MongoDataSource extends AbstractDataSource{
  
  private $dbName;
  private $collectionName;
  private $mongoCollection=null;
  private $primary_key="_id";
  
  public function __construct(){}
  
  public function setLocation(string $location):DataSourceInterface{
    parent::setLocation($location);
    $bits = explode('/',$location);
    $this->dbName = $bits[0];
    $this->collectionName = isset($bits[1])?$bits[1]:'';
    $this->mongoCollection = null;
    return $this;
  }
  
  protected function validateLocation(){
    $loc = $this->getLocation();
    if(empty($loc)){
      throw new LocationNotSetException();
    }
    if(empty($this->dbName)){
      throw new LocationNotSetException("Mongo DB name not set in location string : {db}/{collection}");
    }
    if(empty($this->collectionName)){
      throw new LocationNotSetException("Mongo collection name not set in location string : {db}/{collection}");
    }   
   
  }
  
  private function getCollection(){
    
    $this->validateLocation();
    
    if(!$this->mongoCollection){
      $this->mongoCollection = (new Client)->{$this->dbName}->{$this->collectionName};
    }
    return $this->mongoCollection;
    
  }
  
  private function formatDocument($document):array{
    if(isset($document[$this->primary_key]) && // is the pk in the retrieved document
       (empty($this->selects)  // if select empty, then select *, therefore include pk
        || isset($this->selects[$this->primary_key])  // in key, because using aliases
        || in_array($this->primary_key,$this->selects) ) ){ // in array at int pos, not using aliases
        
         $document[$this->primary_key] = (string)$document[$this->primary_key];
       }
    
    if(count($this->selects)>0){
      $new = [];
      foreach($this->selects as $select){
        $new[$select['as']] = $document[$select['field']];
      }
      return $new;
    }else{
      return (array)$document;
      
    }  
  }
  
  private function applySelects($options){
    if(!empty($this->selects)){
      $options['projection']=[];
      foreach($this->selects as $select){
        $options['projection'][$select['field']]=1;
      }
    }
    return $options;
  }
  
  private function applyWheres($filters){
    
    $operator_map = ['='=>'$eq','!='=>'$ne','IN'=>'$in','NOT IN'=>'$nin',
                     '>'=>'$gt','>='=>'$gte','<'=>'$lt','<='=>'$lte'];
    
    foreach($this->wheres as $where){
      $val = is_numeric($where['value'])?$where['value']*1:$where['value'];
      $op = $operator_map[$where['operator']];
      $filters[$where['attribute']][$op]=$val;
    }
    return $filters;
  }
  
  private function applyOrderBy($options){
    
    foreach($this->orderBy as $o){
    $options['sort'][$o['order']]=($o['dir']==='ASC')?1:-1;
      
    }
    return $options;
  }
  private function applyLimit($options){
    if(isset($this->limit['limit'])){
      $options['limit'] = $this->limit['limit'];
    }
    if(isset($this->limit['offset'])){
      $options['skip'] = $this->limit['offset'];
    }
    return $options;
  }
  
  public function getOne(){
    $this->limit(1);
    $res = $this->getMany();
    return ArrayHelper::first($res);
  }
  
  public function getMany():array{
     $return = [];
     $filters = [];
     $options = [];
     $options = $this->applySelects($options);
     $filters = $this->applyWheres($filters);
     $options = $this->applyOrderBy($options);
     $options = $this->applyLimit($options);
      var_dump($filters);
      var_dump($options);
     $documents = $this->getCollection()->find($filters,$options);
     #$documents = $this->getCollection()->find($filters,['projection'=>['email'=>1],'limit'=>1]);
    
     foreach($documents as $doc){
       if($this->keyBy){
          $ret = $this->formatDocument($doc);
          $return[ $ret[$this->keyBy] ] = $ret;
       }else{
          $return[] = $this->formatDocument($doc);         
       }
     }
    return $return;
  }
  public function getCount():int{}
  
  public function insert(array $data){
    $id = false;
    if(count($data)){
      try{
        $insertOneResult = $this->getCollection()->insertOne($data);
        $id = (string)$insertOneResult->getInsertedId();
      }catch(\Exception $e){        
      
        if( strpos($e->getMessage(),"duplicate key error") ){
          $nextKey = $data[$this->primary_key];
          throw new QuerySyntaxException("Primary key violation, `{$this->primary_key}`:{$nextKey} already exists on `{$this->getLocation()}`");
        }
        throw new QuerySyntaxException($e->getMessage());
      }
    }
    
    return $id;
    
  }
  public function insertMany(array $data):bool{}
  public function update(array $data):bool{}
  
  public function create(array $colls):bool{}
  public function destroy():bool{}
  public function truncate():bool{}
  public function resourceExists():bool{}
}
/*
TODO :

public function getConfig();
>>public function cloneSelf():DataSourceInterface;
>>public function setLocation(string $locationString):DataSourceInterface;
>>public function getLocation():string;
>>public function selects(array $fields):DataSourceInterface;
>>public function where(string $attr, string $operator,$value = null):DataSourceInterface;
>>public function whereIn(string $attr, array $values):DataSourceInterface;
>>public function whereNotIn(string $attr, array $values):DataSourceInterface;
public function andOr(array $conditions):DataSourceInterface;
public function aggregateOrAnd(array $key_pairs_array):DataSourceInterface;
>>public function limit(int $offset,int $limit = null):DataSourceInterface;
>>public function orderBy(string $field,string $direction = 'ASC'):DataSourceInterface;
public function groupBy(string $grouping):DataSourceInterface;
>>public function keyBy(string $key):DataSourceInterface;
public function getOne();
>>public function getMany():array;
public function getCount():int;
public function clearState();
public function insert(array $data);
public function insertMany(array $data):bool;
public function update(array $data):bool ;
public function truncate():bool;
public function destroy():bool;
public function create(array $headings):bool;
public function resourceExists():bool;
*/
