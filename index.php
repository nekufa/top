<?php 

$limit = 10;
$filename = 'data.php';

if(isset($_GET['firstName']) && isset($_GET['lastname']) && isset($_GET['points'])) {

  // intialize file  
  if(!file_exists($filename)) {
    file_put_contents($filename, '<?php return array();');
  }
  
  // lock and read current
  $fp = fopen($filename, "r+");
  while (!flock($fp, LOCK_EX)) {}
  $data = include $filename;

  // append data
  $data[] = array(
    'firstName' => $_GET['firstName'],
    'lastName' => $_GET['lastName'],
    'points' => $_GET['points']
  );
  
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
    $data = array_values($data);
    unset($data[$key]);
  }
  
  ftruncate($fp, 0); // очищаем файл
  fwrite($fp, '<?php return ' . var_export($data, true).';');
  fflush($fp);
  flock($fp, LOCK_UN);
  fclose($fp);
}

if(!isset($data)) {
    $data = file_exists($this->filename) ? include $this->filename : array();
}

echo json_encode($data);
