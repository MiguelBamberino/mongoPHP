<?php
include __DIR__."/vendor/autoload.php";

use PPCore\Helpers\ArrayHelper;

$source = new MongoDataSource\MongoDataSource();
$source->setLocation("test/users10");
for($i=1;$i<=40;$i++){
  #$res= $source->insert(array('_id' => $i,'username' => rand(1,10),'email' => 'bob@example.com','name' => "bob User {$i}"));
}
$res = $source
  /*->limit(5)
  ->orderBy('username','ASC')
  ->orderBy('_id','ASC')
  ->selects(['_id'=>'ref','username'=>'uname','name'=>'alias'])*/
  ->where('_id','>',10)
  ->where('_id','<',21)
  ->where('username','=',9)
  ->keyBy('_id')
  ->getMany();
  cliTable($res); 

//$collection = (new MongoDB\Client)->test->users;
/*
$insertOneResult = $collection->insertOne([
    'username' => 'admin',
    'email' => 'admin@example.com',
    'name' => 'Admin User',
]);

printf("Inserted %d document(s)\n", $insertOneResult->getInsertedCount());

var_dump($insertOneResult->getInsertedId());*/

function cliTable(array $data){
  
  if(empty($data)){
    echo "\nNo results\n";return;
  }
  
  $headings = array_keys(ArrayHelper::first($data));
  foreach($headings as $h){
    renderCell($h);
  }
  echo "\n";
  echo str_pad('',count($headings)*18 ,'-');
  echo "\n";
  
  foreach($data as $row){
    foreach($row as $cell){
      renderCell($cell);
    }
    echo "\n";
    
  }
  
}
function renderCell($text,$width=15, $colChar=" | "){
  
  $length = strlen($text);
  if($length == $width){
    echo $text.$colChar;
  }elseif($length<$width){
    
    echo str_pad($text, $width,' ').$colChar; 
  }else{
    echo substr($text,0,$width).$colChar;
  }
  
}



