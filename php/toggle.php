
<?php

if (isset($_GET['url'])){
    $url = $_GET['url'];
} else {
    header("HTTP/1.0 404 Not Found");
    exit;
}

$out = file_get_contents($url);

if($out === FALSE) {
  header('Content-type: application/json');
  echo '{"out":"ko"}';
} else {
  header('Content-type: application/json');
  echo '{"out":"' . $out . '"}';
}
?>

/*
/usr/bin/curl -s http://admin:admin@<LAN_CTRL_IP>/outs.cgi?out2=1 ## SET Relay CLOSE gate is OFF
/usr/bin/curl -s http://admin:admin@<LAN_CTRL_IP>/outs.cgi?out1=0 ## SET Relay OPEN  gate is ON
*/
