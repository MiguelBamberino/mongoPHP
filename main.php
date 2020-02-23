<?php
include __DIR__."/vendor/autoload.php";

$source = new MongoDataSource\MongoDataSource();
$source->setLocation("test/users");
/*$res= $source->insert(array(
    '_id' => 'bob1',
    'username' => 'admin',
    'email' => 'bob@example.com',
    'name' => 'bob User',));*/
$res = $source->getMany();
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