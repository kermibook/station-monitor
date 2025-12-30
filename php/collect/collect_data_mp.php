<?php
/***************************************************************************/
//
// 		Collect and Store Data from Devices 
//
/***************************************************************************/

define( 'PATH_ROOT', dirname( __FILE__) . '/');    
include(PATH_ROOT."/../config.inc.php");

$mysqli = new mysqli($db_host,$db_user,$db_pass,$db_name);

// Check connection
if ($mysqli -> connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
  exit();
}

// Perform query 
if ($result = $mysqli -> query("SELECT * FROM devices")) {
  //echo "Returned rows are: " . $result -> num_rows;
  $num=$result -> num_rows;
  // Free result set
  ////$result -> free_result();
} else {
  echo "Query problem";
} 

//inizializzo array
$arrLevel = array();
$arrRx = array();
$arrTx = array();
$arrCell = array();
$arrChannel = array();
$arrUptime = array();
$arrConntime = array();
$arrPlmn = array();
$arrLatency = array();
$arrTemp = array();
$arrHum = array();
$arrTempCpu = array();
$arrVolt = array();
$arrConnType = array();
$arrPowerOut = array();
$arrPowerIn = array();
$arrRelay0 = array();
$arrRelay1 = array();
$arrRelay2 = array();
$arrRelay3 = array();
$arrRelay4 = array();
$arrBinOut0 = array();

//Header Tabella HTML 
echo "<table border=1>";
echo "<tr>";
echo "<td><b> Hostname </b></td><td><b> ID </b></td><td><b> Time </b></td><td><b> Latency </b></td>";
echo "<td><b> Uptime </b></td><td><b> ConnTime </b></td><td><b> Offline </b></td>";
echo "<td><b> RX </b></td><td><b> TX </b></td><td><b> Data_Archived </b></td>";
echo "<td><b> PLMN </b></td><td><b> Cell </b></td><td><b> Channel </b></td><td><b> Level </b></td>";
echo "<td><b> Sign_Qlty </b></td><td><b> conntype </b></td>";
echo "<td><b> tempCpu </b></td><td><b> Temp </b></td><td><b> Hum </b></td>";
echo "<td><b> volt </b></td><td><b> Power_in </b></td><td><b> Power_out </b></td>";

echo "<td><b> R0 </b></td><td><b> R1 </b></td><td><b> R2 </b></td>";
echo "<td><b> R3 </b></td><td><b> R4 </b></td><td><b> BinOut0 </b></td>";
echo "</tr>";
//Fine Header Tabella HTML

$i=0;   
//Questo primo ciclo e' separato dal successivo per permettere d lanciare le istanze di snmp in modo indipendente e velocizzare il tutto
while ($obj = mysqli_fetch_object($result)) {	 //itero sulle stazioni presenti nel DB
     $hostname=$obj->hostname;  
     $device_id=$obj->device_id;  
     $arrLatency[$i]=popen("php ".PATH_ROOT."/ping_job_mp.php $hostname","r");
     $arrUptime[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname .1.3.6.1.2.1.1.3","r");
     $arrConntime[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname .1.3.6.1.4.1.30140.4.17","r");			
     $arrRx[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname .1.3.6.1.2.1.2.2.1.10.2","r");
     $arrTx[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname .1.3.6.1.2.1.2.2.1.16.2","r");

     $arrPlmn[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname .1.3.6.1.4.1.30140.4.2","r");	           
     $arrCell[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname .1.3.6.1.4.1.30140.4.3","r");
     $arrChannel[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname .1.3.6.1.4.1.30140.4.4","r");
     $arrLevel[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname .1.3.6.1.4.1.30140.4.5","r"); 
     $arrSignQly[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname .1.3.6.1.4.1.30140.4.26","r");
     $arrConnType[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname .1.3.6.1.4.1.30140.4.1.0","r");	           

     $arrTemp[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname:162 iso.3.6.1.4.1.7616.4.9.0","r");	           
     $arrHum[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname:162 iso.3.6.1.4.1.7616.4.10.0","r");	           
     $arrTempCpu[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname:162 iso.3.6.1.4.1.7616.3.8.0","r");	           
     $arrVolt[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname:162 iso.3.6.1.4.1.7616.3.7.0","r");	           
     $arrPowerIn[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname:162 iso.3.6.1.4.1.7616.3.2.0","r");	           
     $arrPowerOut[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname:162 iso.3.6.1.4.1.7616.3.1.0","r");	           

     $arrRelay0[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname:162 iso.3.6.1.4.1.7616.1.0.0","r");	           
     $arrRelay1[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname:162 iso.3.6.1.4.1.7616.1.1.0 ","r");	           
     $arrRelay2[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname:162 iso.3.6.1.4.1.7616.1.2.0","r");	           
     $arrRelay3[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname:162 iso.3.6.1.4.1.7616.1.3.0","r");	           
     $arrRelay4[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname:162 iso.3.6.1.4.1.7616.1.4.0","r");	           
     $arrBinOut0[$i]=popen("php ".PATH_ROOT."/snmp_job_mp.php $hostname .1.3.6.1.4.1.30140.2.3.2.0","r");	           
     $i++;
}


$i=0;
$result->data_seek(0); 		                        //reset the pointer to row 0 of $result 
while ($obj = mysqli_fetch_object($result)) {		//itero sulle stazioni presenti nel DB
     $hostname=$obj->hostname;
     $device_id=$obj->device_id;

     $time = time(); 

     $latency=fgets($arrLatency[$i]);
     $uptime=fgets($arrUptime[$i]);		
     $conntime=fgets($arrConntime[$i]);

     $last_time_act=$obj->last_time_act;
     $offline_period=$time - $last_time_act;
     $rx=fgets($arrRx[$i]);
     $tx=fgets($arrTx[$i]);
     $data_arch="100";

     $last_rx=$obj->last_rx;
     $last_tx=$obj->last_tx;

     $plmn=fgets($arrPlmn[$i]);
     $cell=fgets($arrCell[$i]);
     $channel=fgets($arrChannel[$i]);
     $level=fgets($arrLevel[$i]);
     $signQly=fgets($arrSignQly[$i]);
     $conntype=fgets($arrConnType[$i]);

     $temp=fgets($arrTemp[$i]);
     $hum=fgets($arrHum[$i]);
     $tempCpu=fgets($arrTempCpu[$i]);
     $volt=fgets($arrVolt[$i]); 
     $powerIn=fgets($arrPowerIn[$i]);
     $powerOut=fgets($arrPowerOut[$i]);	   

     $relay0=fgets($arrRelay0[$i]);
     $relay1=fgets($arrRelay1[$i]);
     $relay2=fgets($arrRelay2[$i]);
     $relay3=fgets($arrRelay3[$i]);
     $relay4=fgets($arrRelay4[$i]);
     $binout0=fgets($arrBinOut0[$i]);

     //Inserimento Dati Tabella Html
     	   echo "<tr>"; 
     	   echo "<td>" . $hostname . "</td><td>" . $device_id . "</td><td>" . $time . "</td><td>" . $latency . "</td>";
     	   echo "<td>" . $uptime . "</td><td>" . $conntime . "</td><td>" . $offline_period . "</td>";
     	   echo "<td>" . $rx . "</td><td>" . $tx . "</td><td>" . $data_arch . "</td>";
     	   echo "<td>" . $plmn . "</td><td>" . $cell . "</td><td>" . $channel . "</td><td>" . $level . "</td>";
     	   echo "<td>" . $signQly . "</td><td>" . $conntype . "</td>";

     	   echo "<td>" . $tempCpu . "</td><td>" . $temp . "</td><td>" . $hum . "</td>";
     	   echo "<td>" . $volt . "</td><td>" . $powerIn . "</td><td>" . $powerOut . "</td>";

     	   echo "<td>" . $relay0 . "</td><td>" . $relay1 . "</td><td>" . $relay2 . "</td>";
     	   echo "<td>" . $relay3 . "</td><td>" . $relay4 . "</td><td>" . $binout0 . "</td>";
	   echo "</tr>"; 
	   //Fine Inserimento Dati Tabella Html	   	   
	   

	   if (strpos($latency,'down') !== false) {	//se il router non e' raggiungibile
	      $query_upd="UPDATE devices SET health = '0', last_status = '0' WHERE device_id = $device_id";
	      $query_ins="INSERT INTO stats (device_id, time, status, offline_period) VALUES ($device_id, $time, '0', $offline_period)";

           }else{	   	 	   	   	// il router e' raggiungibile     	      
	      
	      //CALCOLO DATI TRASFERITI ======================================================================================	      
	      /*Ad ogni ciclo leggo last_*x dalla tabella devices, se sono minori di *x allora calcolo (*x_diff = *x - last_*x) 
	      se maggiori, vuol dire che il contatore dati si � azzerato per reboot e reconnect, quindi calcolo (*x_diff = *x). 
	      Fatto questo posso aggiornare il db devices con last_*x e il db stats con *x.*/
	      
	      if (($last_rx <= $rx ) && ($last_tx <= $tx)) {   		//I dati sono aumentati rispetto al check precedente, OK 	
		$rx_diff = $rx - $last_rx;
		$tx_diff = $tx - $last_tx; 
	      }else{							//I dati sono minori rispetto al check precedente --> avvenuto un reboot o un reconnect del router
		$rx_diff = $rx;
		$tx_diff = $tx; 
	      }	
	      //FINE CALCOLO DATI TRASFERITI ==================================================================================
	      
	      //UPDATE di "devices" e INSERT di "stats"      	      	       		
      	      $query_upd="UPDATE devices SET last_rx = $rx, last_tx = $tx, connection_date = $conntime, health = '1', last_status = '1', last_level = $level, last_time_act = $time WHERE device_id = $device_id";    
	      $query_ins="INSERT INTO stats (device_id, time, latency, uptime, connection_date, offline_period, rx, tx, rx_diff, tx_diff, data_archived, plmn, cell, channel, level, signal_qly, conntype, temp_cpu, temp, hum, volt, power_in, power_out, relay0, relay1, relay2, relay3, relay4, binout0, status) VALUES ($device_id, $time, $latency, $uptime, $conntime, $offline_period, $rx, $tx, $rx_diff, $tx_diff, $data_arch, $plmn, '$cell', $channel, $level, $signQly, $conntype, $tempCpu, $temp, $hum, $volt, $powerIn, $powerOut, $relay0, $relay1, $relay2, $relay3, $relay4, $binout0, 1)";		  
	   }
	   
  	   //Inserimento dati nel db 
	   if ($mysqli->query($query_ins) === TRUE) {
  		//echo "New record created successfully";
	   } else {
  		echo "Error: " . $query_ins . "<br>" . $mysqli->error;
	   }
	   //Fine Inserimento dati nel db

	   //Update devices.health
	   if ($mysqli->query($query_upd) === TRUE) {
  		//echo "Record modified successfully";
	   } else {
  		echo "Error: " . $query_upd . "<br>" . $mysqli->error;
	   }
	   //End Update devices health	   	   	   

	   $i++;
}
$mysqli -> close();
?>


