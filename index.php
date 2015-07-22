<?php 

$limit = 10;
$filename = 'data.php';

if(isset($_GET['firstName']) && isset($_GET['lastName']) && isset($_GET['points'])) {
  
  $row = array(
    'firstName' => $_GET['firstName'],
    'lastName' => $_GET['lastName'],
    'points' => $_GET['points']
  );

  // intialize file  
  if(!file_exists($filename)) {
    file_put_contents($filename, '<?php return array();');
  }
  
  
  // lock and read current
  $fp = fopen($filename, "r+");
  while (!flock($fp, LOCK_EX)) { // wait untill we get lock
    usleep(1000); // 1ms sleep
  }
  
  $data = include $filename;

  $key = json_encode(array($_GET['firstName'],  $_GET['lastName']));
  
  if(!isset($data[$key]) || $data[$key]['points'] < $row['points']) {
    // append data
    $data[$key] = $row;
    
    // remove min
    if(count($data) > $limit) {
      $min = null;
      $key = null;
      foreach($data as $k => $row) {
        if(!$min) {
          $min = $row['points'];
          $key = $k;
        } elseif($row['points'] < $min) {
          $min = $row['points'];
          $key = $k;
        }
      }
      
      // remove min and fix index
      unset($data[$key]);
    }      
  }

  // put contents via lock
  ftruncate($fp, 0);
  fwrite($fp, '<?php return ' . var_export($data, true).';');
  fflush($fp);
  flock($fp, LOCK_UN);
  fclose($fp);
}

if(!isset($data)) {
    $data = file_exists($filename) ? include $filename : array();
}

echo json_encode(array_values($data));


