<?php
include __DIR__."/vendor/autoload.php";

$source = new MongoDataSource\MongoDataSource();
$source->setLocation("test/users6");
/*for($i=0;$i<40;$i++){
  $res= $source->insert(array('_id' => "ID-{$i}",'username' => "bob {$i}",'email' => 'bob@example.com','name' => "bob User {$i}"));
  var_dump($res); 
}*/
$res = $source->limit(2,2)->selects(['_id'=>'ref','username'=>'uname','name'=>'alias'])->getMany();
  var_dump($res); 

//$collection = (new MongoDB\Client)->test->users;
/*
$insertOneResult = $collection->insertOne([
    'username' => 'admin',
    'email' => 'admin@example.com',
    'name' => 'Admin User',
]);

printf("Inserted %d document(s)\n", $insertOneResult->getInsertedCount());

var_dump($insertOneResult->getInsertedId());*/