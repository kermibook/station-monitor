<?php
/************************************************************/
/*                                                          */     
/* 		Visualizza la dashboard SeismoIOT                 */
/*                                                          */     
/************************************************************/


echo "<html>";
echo "<head>";

echo "<title>SeismoIOT Connectivity Monitor</title>";
echo "<style type=\"text/css\" media=\"all\">@import \"css/station_status.css\"</style>";
echo "</head>";
echo "<body>";

echo "<div id=\"results\"></div>";

define( 'PATH_ROOT', dirname( __FILE__) . '/');    
include(PATH_ROOT."config.inc.php");

$mysqli = new mysqli($db_host,$db_user,$db_pass,$db_name);

// Check connection
if ($mysqli -> connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
  exit();
}

//IF one of relay buttons is pressed
if (isset($_GET["relay"]) && isset($_GET["hostname"])) {
     $relay_hostname=$_GET["hostname"];  
     $relay_device_id=$_GET["device_id"];  
     $relay_number=$_GET["relay"];    
     $current_status=$_GET["current_status"];    //current relay status
     $set_new_status=strtr($current_status,[1,0]);  //invert relay status     

     //Remove all the GET parameters from URL
     ?><script>
     var newURL = location.href.split("?")[0];
     window.history.replaceState('object', document.title, newURL);
     </script><?php

     //Trigger relay with selected value
     $relay_url = "http://admin:admin@" . $relay_hostname . ":81/outs.cgi?out=" . $relay_number;
     $cmd_line = "/bin/bash;/usr/bin/curl -s http://admin:admin@" . $relay_hostname . ":81/outs.cgi?out" . $relay_number . "=" . $set_new_status;
     $output = shell_exec($cmd_line);
     
     //Update seismoIOT.stats with new relay status 
     $query_upd="UPDATE stats SET relay$relay_number=$set_new_status WHERE device_id = $relay_device_id ORDER BY time DESC LIMIT 1";    
	if ($mysqli->query($query_upd) === TRUE) {
          //echo "Record modified successfully";
     } else {
          echo "Error: " . $query_upd . "<br>" . $mysqli->error;
     }
} 

// Let's begin with usual operations: perform query 
if ($result = $mysqli -> query("SELECT * FROM devices")) {
     $num=$result -> num_rows;
} else {
     echo "Query problem";
} 

echo "<div id=\"reloadArea\">";
echo "<table class=\"tbElenco\">";
echo "<tr>";
echo "<th class='banner' colspan='27'><img src=img/banner_seismoIOT3.png></th>";
echo "</tr><tr>";
echo "<th rowspan='2' title=\"Nome Dispositivo\"><b> Station Name </b></th>";
echo "<th colspan='6' class=\"energy-dark\" title=\"Aspetti Energetici\"><b> Energy </b></th>";
echo "<th colspan='6' class=\"signal-dark\" title=\"Aspetti relativi alla trasmissione dati\"><b> Networking </b></th>";
echo "<th colspan='5' class=\"env-dark\" title=\"Consumo totale di energia\"><b> Environment </b></th>";
echo "<th colspan='4' class=\"data-dark\" title=\"Acquisizione dati\"><b> Data </b></th>";
echo "<th colspan='6' class=\"relay-dark\" title=\"Stato relè e generale\"><b> Overall Status and Relay </b></th>";
echo "</tr>";

echo "<tr>";
echo "<th class='energy' colspan='2' title=\"Energia in ingresso\"><b> INP </b></th>";
echo "<th class='energy' colspan='2' title=\"Stato di carica della batteria\"><b> BAT </b></th>";
echo "<th class='energy' colspan='2' title=\"Consumo totale di energia\"><b> OUT </b></th>";
echo "<th colspan='2' class=\"signal\" title=\"Livello segnale di trasmissione\"><b> Strength </b></th>";
echo "<th colspan='2' class=\"signal\" title=\"Qualità del segnale di trasmissione\"><b> Quality </b></th>";
echo "<th class=\"signal\" title=\"Tecnologia di trasmissione attualmente negoziata\"><b> Tech </b></th>";
echo "<th class=\"signal\" title=\"Tempo di latenza\"><b> Lat </b></th>";
echo "<th colspan='2' class=\"env\" title=\"Temperatura ambientale\"><b> T_AMB </b></th>";
echo "<th colspan='2'class=\"env\" title=\"Temperatura apparati\"><b> T_STR </b></th>";
echo "<th class=\"env\" title=\"Umidità ambientale\"><b> HUM </b></th>";
echo "<th class=\"data\" title=\"Dati attualmente trasferiti a partire dalle 00:00\"><b> Today TX </b></th>";
echo "<th class=\"data\" title=\"Velocita' istantanea di trasmissione rilevata\"><b> KB/s </b></th>";
echo "<th class=\"data\" colspan='2' title=\"Indicatore di coerenza tra la dato trasmisso e dato stimato\"><b> Delta </b></th>";  //Download status : arrow up, in recupero / arrow down, sotto la media / arrow equal, normal
echo "<th class=\"relay\" title=\"Stato della stazione\"><b> Status </b></th>";
echo "<th class=\"relay\" title=\"Stato della trasmissione\"><b> SIS </b></th>";
echo "<th class=\"relay\" title=\"Stato della trasmissione\"><b> GPS </b></th>";
echo "<th class=\"relay\" title=\"Stato della trasmissione\"><b> FAN </b></th>";
echo "<th class=\"relay\" title=\"Stato della trasmissione\"><b> CAM </b></th>";
echo "</tr>";

$midnight= mktime (0,0,0);   //timestamp di oggi a mezzanotte
$time_NOW = time ();	     //timestamp di adesso

$medie_tx_day = array(23, 32, 24);
$sec_from_midnight = $time_NOW - $midnight;

$i=0;
while ($obj = mysqli_fetch_object($result)) {    //Ciclo esterno, ogni iterazione lavora sulla stazione successiva individuata dalla query nella tabella "devices"
     echo "<tr>"; 
     $hostname=$obj->hostname;
     $device_id=$obj->device_id;
     $product=$obj->product;
     $firmware=$obj->firmware;	
     $last_level=$obj->last_level;
     
     if ($last_level < -111) {                    //4G -95
     	$sig_level=0;
     } else if ($last_level <= -105) {            //4G -85     
     	$sig_level=1;
     } else if ($last_level <= -97) {             //4G -75     
     	$sig_level=2;
     } else if ($last_level <= -81) {             //4G -65
     	$sig_level=3;	     
     } else if ($last_level > -10) {   //per gestire quando capita talvolta che la stazione comunica segnale 0
     	$sig_level=0;
     } else if ($last_level > -81) {             //4G -65
     	$sig_level=4;     
     }
      
     $last_status=$obj->last_status;
     $health=$obj->health;
     $last_time_act=$obj->last_time_act; 
     $cWarn=""; //rimuovi questa riga quando scommenti il gradiente sopra 

     //Calcolo tx e rx da midnight: BEGIN
     if ($result1 = $mysqli -> query("SELECT time, temp, volt, conntype, power_in, power_out, signal_qly, latency, temp_cpu, hum, relay0, relay1, relay2, relay3 FROM stats WHERE device_id=$device_id ORDER BY time DESC LIMIT 1")) {
          $num1=$result1 -> num_rows;
     } else {
          echo "Query problem";
     }     
     
     //calcolo quanti secondi mancano all'update
     $row=$result1->fetch_row();
     $time=$row[0];
     $time_left_from_update=$time_NOW-$time;
     $TTU=60-$time_left_from_update;   //usato per la progressbar
     $TTU=$TTU+2; //give additional seconds for data collecting

     //Ricavo il tipo di connessione dal codice numerico
     //$conntype=mysql_result($result1,$num1-1,"conntype");		
     $conntype=$row[3];		
     switch ($conntype) {
         case ($conntype == 4):
         	 $techtype="EDGE";
         	 break;
         case ($conntype == 6):
         	 $techtype="UMTS";
    	       break;
    	 case ($conntype == 8):
    	   	 $techtype="HSDPA";
    	   	 break;
    	 case ($conntype == 10):
    	   	 $techtype="HSUPA";
    	   	 break;  
    	 case ($conntype == 12):
    	   	 $techtype="HSPA+";
    	   	 break;
         case ($conntype == 13):
                 $techtype="avLTE";
                 break;
         case ($conntype == 14):
                 $techtype="LTE";
                 break;
         case ($conntype == 15):
                 $techtype="avCDMA";
                 break;
         case ($conntype == 16):
                 $techtype="CDMA";
                 break;
     }
     //VOLT, AMPERE, TEMP, RELAY
     $temp=round($row[1]/10, 0);
     $volt=round($row[2] / 100, 1);	       
     $power_in=round($row[4]/ 100, 2);
     $power_out=round($row[5]/ 100, 2);
     $signal_qly=$row[6];
     $latency=$row[7];
     $temp_cpu=$row[8]/100;
     $hum = round ($row[9]/10, 0);
     $relay0_state=$row[10]; 
     $relay1_state=$row[11]; 
     $relay2_state=$row[12];
     $relay3_state=$row[13];

     //Determino se c'è un input di corrente elettrica dai pannelli o altre fonti
     if ($power_in < 0.08) {
          $pow_inp_status="off";
     } else {
          $pow_inp_status="on";
     }

     //In base al consumo di corrente da parte della stazione valorizzo la stringa per poi usare l'icona corrispondente
     if ($power_out < 0.08) {
          $pow_out_status="LOW";
     } else if ($power_out <= 0.53) {
          $pow_out_status="NOR";
     } else if ($power_out > 0.53) {
          $pow_out_status="FUL";
     }

     //Determino la qualità del segnale per definire l'icona corrispondente
     if ($signal_qly < -20) {
     	$sig_qly=0;
     } else if ($signal_qly <= -19) {
     	$sig_qly=1;
     } else if ($signal_qly <= -15) {
     	$sig_qly=2;
     } else if ($signal_qly <= -10) {
     	$sig_qly=3;	     
     } else if ($signal_qly > -10) {
     	$sig_qly=4;     
     }
     
     //Ricavo $batt_level per associare il corretto file jpg allo stato di carica ("batt".$batt_level.".jpg")
     if ($volt <= 10.3) {
     	$batt_level=0;
	     $volt = "";   //messo per non mostrare 0V, ma solo V senza nessun numero, quando  la stazione non è raggiungibile.
     } else if ($volt <= 10.5) {
     	$batt_level=1;
     } else if ($volt <= 11.5) {
     	$batt_level=2;
     } else if ($volt <= 12.76) {
     	$batt_level=3;	     
     } else if ($volt > 12.76) {
     	$batt_level=4;     
     }
          
     //setto la variabile per l'immagine corrispondente alla temperatura corrente (ambiemtale)
     if ($temp <= 3) {
     	$temp_lvl=0;
     } else if ($temp <= 18) {
     	$temp_lvl=1;
     } else if ($temp <= 28) {
     	$temp_lvl=2;
     } else if ($temp > 28) {
     	$temp_lvl=3;
     }
     //setto la variabile per l'immagine corrispondente alla temperatura corrente (strumentazione)
     if ($temp_cpu <= 3) {
     	$temp_lvl_cpu=0;
     } else if ($temp_cpu <= 22) {
     	$temp_lvl_cpu=1;
     } else if ($temp_cpu <= 40) {
     	$temp_lvl_cpu=2;
     } else if ($temp_cpu > 40) {
     	$temp_lvl_cpu=3;
     }

     //Inizializzo contatori prima del ciclo interno
     $k=0;
     $KBPS=0;
     $tot_tx=0;
     $tot_rx=0;	   
     
     //Calcolo tx e rx da midnight: BEGIN
     if ($result2 = $mysqli -> query("SELECT time, rx_diff, tx_diff, offline_period FROM stats WHERE device_id=$device_id AND time > $midnight")) {
          //echo "Returned rows are: " . $result -> num_rows;
          $num2=$result2 -> num_rows;
          // Free result set
          ////$result -> free_result();
     } else {
          echo "Query problem";
     }     

     while ($obj2 = mysqli_fetch_object($result2)) {	   //ciclo interno per calcolare i dati trasferiti per ogni singola stazione nella tabella "stats"
       $rx=$obj2->rx_diff;
       $tx=$obj2->tx_diff;
       $offline_period=$obj2->offline_period;
              
       if (($rx==NULL) && ($tx==NULL))  {
       	  $KBPS = 0.0;  
       }else{
       	  $tot_rx=$tot_rx+$rx;
     	  $tot_tx=$tot_tx+$tx;
	  $KBPS = round (($tx / $offline_period) / 1024, 1) ;
       }    
     $k++;
     }
       
     $tot_rx = round (($tot_rx / 1048576), 1);
     $tot_tx = round (($tot_tx / 1048576), 1);
     //Calcolo tx e rx da midnight: END

     //Calcolo distanza da tx teorico BEGIN
     $tot_tx_teorico = $medie_tx_day[$i] / 86400 * $sec_from_midnight;
     $distance = round (($tot_tx_teorico - $tot_tx), 0);
     //Calcolo distanza da tx teorico END 
     
     $istant_teo = $medie_tx_day[$i] / 86400 * 1024;		//come sopra, ma vedo la distanza dal transfer rate teorico istantaneo
     $istant_dst = round (($istant_teo - $KBPS), 2);        //come sopra, ma vedo la distanza dal transfer rate teorico istantaneo
     
     if ($istant_dst <= -0.3) {   //ds = download status   ----> -0.3 significa che sto scaricando pi� rapidamente del solito
	$ds=2;
	} elseif ($istant_dst >= 0.4) {			// sto scaricando più lentamente del solito	
	$ds=1;	
	} else {					// sono a livelli normali  //magari controllare se KB 0.0 per mettere icona quando stazione down 
	$ds=0;
     }
		
     //Set Status Color and Time: BEGIN
     if ($health==0)  {  //STATION OFFLINE
        $power_in="--";
        $volt="--";
        $power_out="--";
        $techtype="--";
        $KBPS="--";
        $tot_tx="--";
        $last_level="--";
        $signal_qly="--";
        $distance="--";
        $temp="--";
        $temp_lvl="ko";
        $temp_lvl_cpu="ko";
        $temp_cpu="--";
        $latency="--";
        $hum="--";
        $TTU=0; //SET TIME TO NEXT UPDATE TO 0, ONLY 'CAUSE I WANT THE PROGRESS BAR STAY EMPTY
        $sig_level=0;    //approfitto per impostare al minimo del segnale l'icona che rappresenta il segnale di rete
	   $stat_color="#FF9988";		//setto il colore dello sfondo per la casella di stato a rosso per le stazioni OFFLINE
	   $stat_text_color="lightgrey";	//setto il colore del testo a grigio per le stazioni OFFLINE
	   $offline_time = $time_NOW - $last_time_act;
	   $days = sprintf("%02d",floor($offline_time / 86400));
	   $hours = sprintf("%02d",floor(($offline_time - ($days * 86400)) / 3600));
	   $minutes = sprintf("%02d",floor(($offline_time - ($days * 86400) - ($hours * 3600)) / 60));
	   $seconds = sprintf("%02d",floor($offline_time - ($days * 86400) - ($hours * 3600) - ($minutes * 60)));
	   $relay0_state = "KO";
        $relay1_state = "KO";
        $relay2_state = "KO";
        $relay3_state = "KO";
        //per mettere un leadong 0 per le cifre 0-9: $a = sprintf ("%02d", $numero);

	   if ($days != 0) {
	      $elapsed_t = $days."d ".$hours."h ".$minutes."m ".$seconds."s";
	   } elseif ($hours != 0) {
		 $elapsed_t = $hours."h ".$minutes."m ".$seconds."s";
	   } elseif ($minutes != 0) {
	  	 $elapsed_t = $minutes."m ".$seconds."s";
	   } else {
	    	 $elapsed_t = $seconds."s";	
	   }	    	    		
     } else {    //STATION ONLINE
        $stat_color="#00FF80";		//setto il colore dello sfondo per la casella di stato a VERDE per le stazioni ONLINE
        $stat_text_color="black";       //setto il colore del testo a nero per le stazioni ONLINE
	   $elapsed_t="";
     }  
     //Set Status Color and Time: END
     
     //voglio fare il display delle righe della tabella con colori alternati, in base all'indice "i", quando pari o dispari:
     if (($i%2)==0) {     
	   echo "<tr class=\"normale\" style=color:".$stat_text_color.">";
     } else {
	   echo "<tr class=\"alternata\" style=color:".$stat_text_color.">";
     } 	
     echo "<td rowspan='2' class=\"hostname\">" . strtok($hostname, '.') . "</td>";
     echo "<td rowspan='2' class=\"ico\"><IMG SRC=\"img/sun4-".$pow_inp_status.".png\"</td><td rowspan='2'>" . $power_in . " A</td>";
     echo "<td rowspan='2' class=\"ico-batt\"><IMG SRC=\"img/batt".$batt_level,$cWarn.".png\"</td><td rowspan='2'>" . $volt . " V</td>";
     echo "<td rowspan='2' class=\"ico-powout\"><IMG SRC=\"img/pow-out-".$pow_out_status.".png\"</td><td rowspan='2'>" . $power_out . " A</td>";
     echo "<td rowspan='2' class=\"ico ico-signal\"><IMG SRC=\"img/".$sig_level.".png\"</td><td rowspan='2'>" . $last_level . " dBm</td>";
     echo "<td rowspan='2' class=\"ico ico-signal-qly\"><IMG SRC=\"img/s".$sig_qly.".png\"</td><td rowspan='2'>" . $signal_qly . " dB</td>";         
     echo "<td rowspan='2'><b>" . $techtype ." </b></td><td rowspan='2'>" . $latency." ms</td>";     
     echo "<td rowspan='2' class=\"ico-temp\"><IMG SRC=\"img/temp".$temp_lvl.".png\"</td><td rowspan='2'>" . $temp . "° C</td>";
     echo "<td rowspan='2' class=\"ico-temp-cpu\"><IMG SRC=\"img/temp".$temp_lvl_cpu.".png\"</td><td rowspan='2'>" . $temp_cpu . "° C</td>";
     echo "<td rowspan='2'>" . $hum . "%</td>"; 
     echo "<td rowspan='2'>" . $tot_tx ." MB</td>";
     echo "<td rowspan='2'>" . $KBPS ." KB/s</td>";     
     echo "<td rowspan='2' class=\"ds_ico\"><IMG SRC=\"img/s".$ds .".jpg\"</td>";
     echo "<td rowspan='2'>" . $distance." MB</td>";
     echo "<td class=\"status\" style=background-color:".$stat_color."><div id=\"status\">".$elapsed_t."</div></td>";
     echo "<td rowspan='2' class=\"ico ico-relay-button\"><a href=\"show_device_status_cd2.php?relay=0&hostname=$hostname&current_status=$relay0_state&device_id=$device_id\"><IMG SRC=\"img/relay-".$relay0_state.".png\"</a></div>";
     echo "<td rowspan='2' class=\"ico ico-relay-button\"><a href=\"show_device_status_cd2.php?relay=1&hostname=$hostname&current_status=$relay1_state&device_id=$device_id\"><IMG SRC=\"img/relay-".$relay1_state.".png\"</a></div>";
     echo "<td rowspan='2' class=\"ico ico-relay-button\"><a href=\"show_device_status_cd2.php?relay=2&hostname=$hostname&current_status=$relay2_state&device_id=$device_id\"><IMG SRC=\"img/relay-".$relay2_state.".png\"</a></div>";
     echo "<td rowspan='2' class=\"ico ico-relay-button\"><a href=\"show_device_status_cd2.php?relay=3&hostname=$hostname&current_status=$relay3_state&device_id=$device_id\"><IMG SRC=\"img/relay-".$relay3_state.".png\"</a></div>";
     echo "</tr><tr>";
     echo "<td id=\"myProgress$i\">";
     echo "<div id=\"myBar$i\"></div>";
     echo "</tr>";
     echo "</td>";    
     ?>
     
     <script>
     //appendo la variabile "i" di php a tutte le variabili del js in modo che ogni progressbar viaggi in modo indipendente per ogni stazione
     window.addEventListener('load', move<?php echo $i; ?>)
     var i<?php echo $i; ?> = 0;
     function move<?php echo $i; ?>() {
          if (i<?php echo $i; ?> == 0) {
               i<?php echo $i; ?> = 1;
               var k = <?php echo $i; ?>;
               var elem = document.getElementById("myBar"+k);
               var width<?php echo $i; ?> =<?php echo $TTU;?>;
               width<?php echo $i; ?>=width<?php echo $i; ?>*100/60; //con questa proporzione rimappo il valore max di TTU (valore del crontab) su 100 //se crontab 60sec --> 100:60=x:$TTU
               
               //var id = setInterval(frame, 1000);
               var id = setInterval(frame, 600); 
               //var <?php echo $i; ?>_elapsed_t= <?php echo $elapsed_t;?>;
               var elapsed_t_<?php echo $i; ?> = "<?php echo $elapsed_t;?>";
               function frame() {
                    if (width<?php echo $i; ?> <= 0 && elapsed_t_<?php echo $i; ?>=="") {    //if progress is 0
                         clearInterval(id);
                         i<?php echo $i; ?> = 0;
                         location.reload();
                    } else {
                         width<?php echo $i; ?>--;
                         elem.style.width = width<?php echo $i; ?> + "%";
                         elem.style.backgroundColor = "rgb(84, 193, 220)";
                    }
                    if (elapsed_t_<?php echo $i; ?>!=="") { //se la stazione è offline
                         elem.style.backgroundColor = "lightgrey";
                    }
               }
          }
     }
     </script>
     <?php
    
     $i++;
}

echo "</table>";
echo "</div>"; //chiudo il div reloadArea
echo "<div id=\"output_field\"></div>";	     

echo "</body>";
echo "</html>";

$mysqli -> close();
?>
