<?php
error_reporting(E_ERROR | E_PARSE);  //rimuovo dall'output tutti gli errori, rispoetto alla versione su tesla, ho dovuto togliere gli errori perchè mi plottava due righe di warning e on rilevavo il "down 

function ping($host, $port, $timeout) {
  $tB = microtime(true);
  $fP = fSockOpen($host, $port, $errno, $errstr, $timeout);
  if ($errno !== 0) { return "down"; }
  $tA = microtime(true);
  return round((($tA - $tB) * 1000), 0);  
}
#echo ping("$argv[1]", 81, 9);   ###Questo fa il test sull'http del guralp (nat su port 81)
echo ping("$argv[1]", 82, 9);   ###Questo fa il test sull'http del router (nat su port 82)

?>				

