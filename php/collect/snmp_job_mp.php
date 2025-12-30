<?php
//error_reporting(0);      # don't show any errors...

snmp_set_valueretrieval(SNMP_VALUE_PLAIN);  
$value = snmp2_walk("$argv[1]", "public", "$argv[2]", 700000, 3);
if ($value) {
  echo $value[0];
} else {
  echo "NULL";
}



?>








