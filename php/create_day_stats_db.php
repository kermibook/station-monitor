<?php
/***************************************************************************/
//
// 		Create day statistics from seismoIOT.stats data 
//
/***************************************************************************/
include("config.inc.php");

$mysqli = new mysqli($db_host,$db_user,$db_pass,$db_name);

// Check connection
if ($mysqli -> connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
  exit();
}

// Perform query 
if ($result = $mysqli -> query("SELECT * FROM devices")) {
     $num=$result -> num_rows;
} else {
     echo "Query problem";
} 

//   23     22       21       20       19       18 	17	 16	  15	   14       13 
//00000 - 86400	- 172800 - 259200 - 345600 - 432000 - 518400 - 604800 - 691200 - 777600 - 864000 

$time_END= mktime (0,0,0);   //timestamp di oggi a mezzanotte (solo per test del day corrente)

// FOR DEBUG METTO TIME END AD ADESSO
//$time_END= time();

//$time_END= mktime (0,0,0);   //timestamp di oggi a mezzanotte
//$time_END= (mktime (0,0,0)) - 86400;    //timestamp di 01 days ago a mezzanotte 
//$time_END= (mktime (0,0,0)) - 172800;   //timestamp di 02 days ago a mezzanotte 
//$time_END= (mktime (0,0,0)) - 259200;   //timestamp di 03 days ago a mezzanotte 
 
$time_BEGIN= $time_END - 86400;  //timestamp di 24 ore precedenti a $time_END
$time_NOW = time();
$day_id=date("Ymd", $time_BEGIN);

//DEBUG
	echo ("Begin Time: ".$time_BEGIN);
	echo ("<br>");
	echo ("End Time: ".$time_END);
	echo ("<br>");
	echo ("Now Time: ".$time_NOW);
	echo ("<br>");
//DEBUG
echo ("<table border=1>");
echo ("<tr><td bgcolor=\"#FFAA00\"><b> Device </b></td><td bgcolor=\"#FFF0F0\"><b> Day RX </b></td><td bgcolor=\"#FFF0F0\"><b>  Day TX </b></td><td bgcolor=\"#CCCCDD\"><b> Latency Avg </b></td><td bgcolor=\"#FFF0F0\"><b> Availab </b></td>");
echo ("<td bgcolor=\"#FF0000\"></td>");
echo ("<td><b> Max Lvl </b></td><td><b> Min Lvl </b></td><td bgcolor=\"#CCCCDD\"><b> Avg Lvl </b></td>");
echo ("<td bgcolor=\"#FF0000\"></td>");
echo ("<td><b> Max Qly </b></td><td><b> Min Qly </b></td><td bgcolor=\"#CCCCDD\"><b> Avg Qly </b></td>");
echo ("<td bgcolor=\"#FF0000\"></td>");
echo ("<td><b> Max T1 </b></td><td><b> Min T1 </b></td><td bgcolor=\"#CCCCDD\"><b> Avg T1 </b></td>");
echo ("<td bgcolor=\"#FF0000\"></td>");
echo ("<td><b> Max T2 </b></td><td><b> Min T2 </b></td><td bgcolor=\"#CCCCDD\"><b> Avg T2 </b></td>");
echo ("<td bgcolor=\"#FF0000\"></td>");
echo ("<td><b> Max H </b></td><td><b> Min H </b></td><td bgcolor=\"#CCCCDD\"><b> Avg H </b></td>");
echo ("<td bgcolor=\"#FF0000\"></td>");
echo ("<td><b> Max V </b></td><td><b> Min V </b></td><td bgcolor=\"#CCCCDD\"><b> Avg V </b></td>");
echo ("<td bgcolor=\"#FF0000\"></td>");
echo ("<td><b> Max I </b></td><td><b> Min I </b></td><td bgcolor=\"#CCCCDD\"><b> Avg I </b></td>");
echo ("<td bgcolor=\"#FF0000\"></td>");
echo ("<td><b> Max O </b></td><td><b> Min O </b></td><td bgcolor=\"#CCCCDD\"><b> Avg O </b></td>");
echo ("<td bgcolor=\"#FF0000\"></td>");
echo ("<td bgcolor=\"#FFF0F0\"><b> tcpKO </b></td><td bgcolor=\"#FFF0F0\"><b> Boot </b></td>");
echo ("<td bgcolor=\"#FFF0F0\"><b> Hndvr </b></td><td bgcolor=\"#FFF0F0\"><b> Top_Cell </b></td></tr>");


while ($obj = mysqli_fetch_object($result)) {	//ciclo su tutte le stazioni
     $hostname=$obj->hostname;
     $device_id=$obj->device_id;   
     // Perform query 
     if ($result1 = $mysqli -> query("SELECT * FROM stats WHERE device_id=$device_id AND time >= $time_BEGIN AND time <= $time_END")) {
          //echo "Returned rows are: " . $result -> num_rows;
          $num1=$result1 -> num_rows;
          // Free result set
          ////$result -> free_result();
     } else {
          echo "Query problem";
     }      

	#####BUG DEVO CALCOLARE TOP CELL SOLO SE C'E' STATA ATTIVITA DURANTE IL GIORNO, se no mi fa una divisione per 0

     ########### CALCOLO TOP CELL - TUTTO VIA SQL #####################     
     //questa query mi serve per calcolare la TOP Cell, la cella a cui la i-sima stazione èstata connessa di più nell'arco della 24h
     if ($result_cell = $mysqli -> query("SELECT cell FROM (SELECT device_id, time, cell FROM stats WHERE device_id=$device_id AND time >= $time_BEGIN AND time <= $time_END AND cell IS NOT NULL GROUP BY cell ORDER BY COUNT(cell) DESC LIMIT 1) AS T1 ORDER BY cell")) {
     } else {
         echo "Problem getting TOP_CELL value";
     }   
     $obj1 = mysqli_fetch_object($result_cell); 
     $top_cell = $obj1->cell;  
     ##############################################################################################


     //Inizializzo Totalizzatori, Contatori, Indici
     $tot_tx = $tot_rx = $tot_lat = $tot_lev = $tot_qly = $tot_Tcpu = $tot_temp = $tot_hum = $tot_volt = $tot_Pinp = $tot_Pout = 0;
     $offline_count = $online_count = $uptime_count = $hndovr_count = $cnct_count = 0;     
     
     //prendiamo il primo record
     $obj1 = mysqli_fetch_object($result1);  
     
     //INIZIALIZZO I VALORI PER I CALCOLO DI MIN E MAX
     $max_lev = $min_lev = $obj1->level;     
     $max_qly = $min_qly = $obj1->signal_qly;
     $max_Tcpu = $min_Tcpu = $obj1->temp_cpu / 100;
     
     $max_temp = $min_temp = $obj1->temp / 10;
     $max_hum = $min_hum = $obj1->hum / 10;
     $max_volt = $min_volt = $obj1->volt / 100;
     $max_Pinp = $min_Pinp = $obj1->power_in;
     $max_Pout = $min_Pout = $obj1->power_out;


     $prev_tx=$obj1->tx;   //prendo il primo valore della query fuori dal ciclo  
     $prev_dev_uptime=$obj1->uptime; //prendo il primo valore della query fuori dal ciclo
     $prev_connect_time=$obj1->connection_date; //prendo il primo valore della query fuori dal ciclo
     $prev_cell=$obj1->cell; //prendo il primo valore della query fuori dal ciclo
     $prev_chan=$obj1->channel; //prendo il primo valore della query fuori dal ciclo    
     
     while ($obj1 = mysqli_fetch_object($result1)) {       //ciclo su tutti gli stati dell'intervallo di tempo selezionato di una stazione
       $rx=$obj1->rx_diff;
       $tx=$obj1->tx_diff;
       
       $stat_id=$obj1->stat_id;		//serve per dare stat_id in cui � rilevato reboot
       $stat_time=$obj1->time;		//serve per dare time del reboot
       $this_tx=$obj1->tx;
       $this_dev_uptime=$obj1->uptime;      
       $this_connect_time=$obj1->connection_date;
       $this_cell=$obj1->cell;
       $this_chan=$obj1->channel;
       
       $lat=$obj1->latency;
       $level=$obj1->level;
       $qly=$obj1->signal_qly;
       $Tcpu=$obj1->temp_cpu / 100;
       $temp=$obj1->temp / 10;
       $hum=$obj1->hum / 10;
       $volt=$obj1->volt / 100;
       $Pinp=$obj1->power_in;
       $Pout=$obj1->power_out;
       $status=$obj1->status;		
       
       if ($status==1)  {  			//if che screma i record della query eseguita su SeismoIOT.stats: (then=online), (else=offline)	
       	  $tot_rx=$tot_rx+$rx;
     	  $tot_tx=$tot_tx+$tx;
	       $tot_lat=$tot_lat+$lat;       
            $online_count=$online_count+1;
	       $tot_lev=$tot_lev+$level;
            $tot_qly=$tot_qly+$qly;
            $tot_Tcpu=$tot_Tcpu+$Tcpu;
            $tot_temp=$tot_temp+$temp;
            $tot_hum=$tot_hum+$hum;
            $tot_volt=$tot_volt+$volt;
            $tot_Pinp=$tot_Pinp+$Pinp;
            $tot_Pout=$tot_Pout+$Pout;
	  
	       if ($level > $max_lev) {							//if per max e min signal level
	  	     $max_lev = $level;
	       } elseif ($level < $min_lev) {
	  	     $min_lev = $level;
	       }

	       if ($qly > $max_qly) {							//if per max e min signal quality
               $max_qly = $qly;
            } elseif ($qly < $min_qly) {
               $min_qly = $qly;
            }

	       if ($Tcpu > $max_Tcpu) {							//if per max e min temperatura CPU
               $max_Tcpu = $Tcpu;
            } elseif ($Tcpu < $min_Tcpu) {
               $min_Tcpu = $Tcpu;
            }

            if ($temp > $max_temp) {							//if per max e min temperatura esterna
               $max_temp = $temp;
            } elseif ($temp < $min_temp) {
               $min_temp = $temp;
            }

            if ($hum > $max_hum) {							//if per max e min umidità esterna
               $max_hum = $hum;
            } elseif ($hum < $min_hum) {
               $min_hum = $hum;
            }

            if ($volt > $max_volt) {							//if per max e min volt esterna
               $max_volt = $volt;
            } elseif ($volt < $min_volt) {
               $min_volt = $volt;
            }

            if ($Pinp > $max_Pinp) {							//if per max e min corrente input 
               $max_Pinp = $Pinp;
            } elseif ($Pinp < $min_Pinp) {
               $min_Pinp = $Pinp;
            }
            
            if ($Pout > $max_Pout) {							//if per max e min corrente output 
               $max_Pout = $Pout;
            } elseif ($Pout < $min_Pout) {
               $min_Pout = $Pout;
            }

            //DEBUG
            //echo ("<br> LEVEL=".$level." ");
            //echo ("<br> LATENCY=".$lat." ");
            //DEBUG
            
	       //i successivi controlli sono ridondanti per eventuali errori sul dato snmp che viaggia su UDP
	       if (($this_tx < $prev_tx) && ($this_connect_time > $prev_connect_time)){	//if per stabilire quante connessioni ppp sono state fatte 		
		     $cnct_count = $cnct_count + 1;		
	       }
            if ($this_dev_uptime < $prev_dev_uptime) {      			//if per stabilire quanti reboot sono stati fatti 	
               $uptime_count=$uptime_count + 1;
            } 

	       $prev_tx = $this_tx;	  
	       $prev_dev_uptime = $this_dev_uptime;
	       $prev_connect_time = $this_connect_time; 

	       if (($this_chan != $prev_chan) || ($this_cell != $prev_cell)) {		//if per stabilire quanti cambi di cella o di canale ci sono stati 		
		     $hndovr_count = $hndovr_count + 1;
	       }
	       $prev_cell = $this_cell;	  
	       $prev_chan = $this_chan;
	  	  	  	  
       } else {
       	  $offline_count = $offline_count + 1;
       }           

     }  //END OF WHILE     

     //DEBUG     
     echo ("<br>");
     echo ("NUM1=".$num1."<br>");
     echo ("OFFLINE_COUNT=".$offline_count."<br>");     
     //DEBUG  
   
     $tot_rx_MB = round (($tot_rx / 1000000), 1);
     $tot_tx_MB = round (($tot_tx / 1000000), 1);
     $avg_lat = round (($tot_lat / $online_count), 1);
     $availability = round (($num1 - $offline_count) / $num1 * 100, 1);
     $avg_lev = round ($tot_lev / $online_count, 1);     	
     $avg_qly = round ($tot_qly / $online_count, 1);     
     $avg_Tcpu = round ($tot_Tcpu / $online_count, 1);     

     $avg_temp = round ($tot_temp / $online_count, 1);     
     $avg_hum = round ($tot_hum / $online_count, 1);     
     $avg_volt = round ($tot_volt / $online_count, 1);     
     $avg_Pinp = round ($tot_Pinp / $online_count, 1);     
     $avg_Pout = round ($tot_Pout / $online_count, 1);     

     

     echo ("<tr><td bgcolor=\"#FFAA00\">" . $device_id."</td><td bgcolor=\"#FFF0F0\">" . $tot_rx_MB." MB</td><td bgcolor=\"#FFF0F0\">" . $tot_tx_MB." MB</td>");
     echo ("<td bgcolor=\"#CCCCDD\">" . $avg_lat." ms</td><td bgcolor=\"#FFF0F0\">" .$availability." %</td>");
     echo ("<td bgcolor=\"#FF0000\"></td>");
     echo ("<td>" . $max_lev." dBm</td><td>" . $min_lev." dBm</td><td bgcolor=\"#CCCCDD\">" .$avg_lev." dBm</td>");
     echo ("<td bgcolor=\"#FF0000\"></td>");
     echo ("<td>" . $max_qly." dB</td><td>" . $min_qly." dB</td><td bgcolor=\"#CCCCDD\">" .$avg_qly." dB</td>");
     echo ("<td bgcolor=\"#FF0000\"></td>");
     echo ("<td>" . $max_Tcpu ."°</td><td>" . $min_Tcpu."°</td><td bgcolor=\"#CCCCDD\">" .$avg_Tcpu."°</td>");
     echo ("<td bgcolor=\"#FF0000\"></td>");
     echo ("<td>" . $max_temp ."°</td><td>" . $min_temp."°</td><td bgcolor=\"#CCCCDD\">" .$avg_temp."°</td>");
     echo ("<td bgcolor=\"#FF0000\"></td>");
     echo ("<td>" . $max_hum ."%</td><td>" . $min_hum."%</td><td bgcolor=\"#CCCCDD\">" .$avg_hum."%</td>");
     echo ("<td bgcolor=\"#FF0000\"></td>");
     echo ("<td>" . $max_volt ." V</td><td>" . $min_volt." V</td><td bgcolor=\"#CCCCDD\">" .$avg_volt." V</td>");
     echo ("<td bgcolor=\"#FF0000\"></td>");
     echo ("<td>" . $max_Pinp ." A</td><td>" . $min_Pinp." A</td><td bgcolor=\"#CCCCDD\">" .$avg_Pinp." A</td>");
     echo ("<td bgcolor=\"#FF0000\"></td>");
     echo ("<td>" . $max_Pout ." A</td><td>" . $min_Pout." A</td><td bgcolor=\"#CCCCDD\">" .$avg_Pout." A</td>");
     echo ("<td bgcolor=\"#FF0000\"></td>");
     echo ("<td bgcolor=\"#FFF0F0\">" .$cnct_count."</td><td bgcolor=\"#FFF0F0\">" . $uptime_count."</td>");
     echo ("<td bgcolor=\"#FFF0F0\">" . $hndovr_count."</td><td bgcolor=\"#FFF0F0\">" . $top_cell."</td></tr>");
     
     

     ///WRITE TO DB PART
     //================================================================================================================================================
     $query_ins="INSERT INTO daily_stats (device_id, start_time, availability, latency, level_avg, level_min, level_max, sig_qly_avg, sig_qly_min, sig_qly_max, temp_cpu_avg, temp_cpu_min, temp_cpu_max, 
                                          temp_ext_avg, temp_ext_min, temp_ext_max, hum_ext_avg, hum_ext_min, hum_ext_max, volt_avg, volt_min, volt_max, power_in_avg, power_in_min, power_in_max, 
                                          power_out_avg, power_out_min, power_out_max, reconnect, reboot, handover, rx_daily, tx_daily, top_cell) 
                                  VALUES ($device_id, $time_BEGIN, $availability, $avg_lat, $avg_lev, $min_lev, $max_lev, $avg_qly, $min_qly, $max_qly, $avg_Tcpu, $min_Tcpu, $max_Tcpu,
                                          $avg_temp, $min_temp, $max_temp, $avg_hum, $min_hum, $max_hum, $avg_volt, $min_volt, $max_volt, $avg_Pinp, $min_Pinp, $max_Pinp,       
                                          $avg_Pout, $min_Pout, $max_Pout, $cnct_count, $uptime_count, $hndovr_count, $tot_rx_MB, $tot_tx_MB, '$top_cell')";

     //Inserimento dati nel db 
     if ($mysqli->query($query_ins) === TRUE) {
       //echo "New record created successfully";
     } else {
       echo "Error: " . $query_ins . "<br>" . $mysqli->error;
     }
     //Fine Inserimento dati nel db
     //==================================================================================================================================================

     //DEBUG
     echo ("<br>");
     echo ($num1."<br>");
     //DEBUG
     //$i++;
}
$mysqli -> close();
?>
