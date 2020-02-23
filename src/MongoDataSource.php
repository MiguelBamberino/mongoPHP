<?php
namespace MongoDataSource;
use PPCore\Adapters\DataSources\DataSourceInterface;
use PPCore\Adapters\DataSources\AbstractDataSource;
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
  
  public function getOne(){ }
  public function getMany():array{
    
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
      //var_dump($insertOneResult->getInsertedId());
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