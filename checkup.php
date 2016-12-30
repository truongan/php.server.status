<?php
/*

 *
 * @author      Trường An Phạm Nguyễn
 * @copyright   2011, The authors
 * @license     GNU AFFERO GENERAL PUBLIC LICENSE
 *        http://www.gnu.org/licenses/agpl-3.0.html
 *
 * Jul 27, 2013

Original author:
*       Disclaimer Notice(s)                                                          
*       ex: This code is freely given to you and given "AS IS", SO if it damages      
*       your computer, formats your HDs, or burns your house I am not the one to
*       blame.                                                                     
*       Moreover, don't forget to include my copyright notices and name.               
*   +------------------------------------------------------------------------------+
*       Author(s): Crooty.co.uk (Adam C)                                    
*   +------------------------------------------------------------------------------+

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/  
$data = "";
$data .= "
<style>
td,body
{
	font-family: sans-serif;
	font-size: 8pt;
	color: #444444;
}
</style>
<br>
	<center>
	 <div style=\"border-bottom:1px #999999 solid;width:480;\"><b>
	   <font size='1' color='#3896CC'>Service Status</font></b>
	 </div>  
   </center>
<br>";

//configure script
$timeout = "1";

//set service checks
/* 
The script will open a socket to the following service to test for connection.
Does not test the fucntionality, just the ability to connect
Each service can have a name, port and the Unix domain it run on (default to localhost)
*/
$services = array();


$services[] = array("port" => "80",       "service" => "Apache",                  "ip" => "") ;
$services[] = array("port" => "21",       "service" => "FTP",                     "ip" => "") ;
$services[] = array("port" => "3306",     "service" => "MYSQL",                   "ip" => "") ;
$services[] = array("port" => "22",       "service" => "Open SSH",				"ip" => "") ;
$services[] = array("port" => "9091",     "service" => "Transmission",             	"ip" => "") ;
$services[] = array("port" => "80",       "service" => "Internet Connection",     "ip" => "google.com") ;
$services[] = array("port" => "8082",     "service" => "commafeed",             	"ip" => "") ;
$services[] = array("port" => "8083",     "service" => "Vesta panel",             	"ip" => "") ;


//begin table for status
$data .= "<table width='480' border='1' cellspacing='0' cellpadding='3' style='border-collapse:collapse' bordercolor='#333333' align='center'>";
foreach ($services  as $service) {
	if($service['ip']==""){
	   $service['ip'] = "localhost";
	}

	$fp = @fsockopen($service['ip'], $service['port'], $errno, $errstr, $timeout);
	if (!$fp) {
		$data .= "<tr><td>" . $service['service'] . "</td><td bgcolor='#FFC6C6'>Offline </td></tr>";
	  //fclose($fp);
	} else {
		$data .= "<tr><td>" . $service['service'] . "</td><td bgcolor='#D9FFB3'>Online</td></tr>";
		fclose($fp);
	}

}  
//close table
$data .= "</table>";

echo $data;


//
// SERVER INFORMATION
//

$data1 = "";
$data1 .= "
<br>
	<center>
	 <div style=\"border-bottom:1px #999999 solid;width:480;\"><b>
	   <font size='1' color='#3896CC'>Server Information</font></b>
	 </div>  
   </center><BR>";

$data1 .= "<table width='480' border='1' cellspacing='0' cellpadding='3' style='border-collapse:collapse'  

bordercolor='#333333' align='center'>";

//GET SERVER LOADS
$loadresult = @exec('uptime');  
preg_match("/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/",$loadresult,$avgs);


//GET SERVER UPTIME
$uptime = explode(' up ', $loadresult);
$uptime = explode(',', $uptime[1]);
$uptime = $uptime[0].', '.$uptime[1];

//Get the disk space
function getSymbolByQuantity($bytes) {
	$symbol = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
	$exp = floor(log($bytes)/log(1024));
	
	return sprintf('%.2f '.$symbol[$exp], ($bytes/pow(1024, floor($exp))));
}

$disk_space = getSymbolByQuantity(disk_total_space(getcwd()));
$disk_free = getSymbolByQuantity(disk_free_space(getcwd()));
$disk_free_precent = round(disk_free_space(getcwd())*1.0/disk_total_space(getcwd())*100,2);

//Get ram usage
$total_mem = preg_split('/ +/', @exec('grep MemTotal /proc/meminfo'));
$total_mem = $total_mem[1];
$free_mem = preg_split('/ +/', @exec('grep MemFree /proc/meminfo'));
$cache_mem = preg_split('/ +/', @exec('grep ^Cached /proc/meminfo'));

$free_mem = $free_mem[1] + $cache_mem[1];

$free_mem_percent = round($free_mem*1.0/$total_mem*100,2);

$free_mem = getSymbolByQuantity($free_mem*1024);
$total_mem = getSymbolByQuantity($total_mem*1024);

//Get top mem usage
$tom_mem_arr = array();
$top_cpu_use = array();

//-- The number of processes to display in Top RAM user
$i = 5;


/* ps command:
-e to display process from all user
-k to specify sorting order: - is desc order follow by column name
-o to specify output format, it's a list of column name. = suppress the display of column name
head to get only the first few lines 
	
*/
exec("ps -e k-rss -ocomm=,rss= | head -n $i", $tom_mem_arr, $status);
exec("ps -e k-pcpu -ocomm=,pcpu= | head -n $i", $top_cpu_use, $status);


$top_mem = implode(' KiB <br/>', $tom_mem_arr );
$top_mem = "<pre><b>COMMAND\t\tResident memory</b><br/>" . $top_mem . " KiB</pre>";

$top_cpu = implode(' % <br/>', $top_cpu_use );
$top_cpu = "<pre><b>COMMAND\t\tCPU utilization </b><br/>" . $top_cpu. " %</pre>";

$data1 .= "<tr><td>Server Load Averages </td><td>$avgs[1], $avgs[2], $avgs[3]</td>\n";
$data1 .= "<tr><td>Server Uptime        </td><td>$uptime                     </td></tr>";
$data1 .= "<tr><td>Disk free        </td><td>$disk_free / $disk_space = $disk_free_precent%         </td></tr>";
$data1 .= "<tr><td>RAM free        </td><td>$free_mem / $total_mem = $free_mem_percent%         </td></tr>";
$data1 .= "<tr><td>Top RAM user    </td><td>$top_mem         </td></tr>";
$data1 .= "<tr><td>Top CPU user    </td><td>$top_cpu         </td></tr>";
$data1 .= "</table>";
echo $data1;  

/*
Display bandwidth statistic, require vnstat installed and properly configured.
*/


if (!isset($_GET['showtraffic']) || $_GET['showtraffic'] ==  false) die();

$data2 = "";
$data2 .= "
<br>
	<center>
	 <div style=\"border-bottom:1px #999999 solid;width:480;\"><b>
	   <font size='1' color='#3896CC'>Traffic Information</font></b>
	 </div>  
   </center><BR>";

$data2 .= "<table width='480' border='1' cellspacing='0' cellpadding='3' style='border-collapse:collapse'  

bordercolor='#333333' align='center'>";
$data2 .="<tr><td><pre>";
$traffic_arr = array();
exec('vnstat -' . $_GET['showtraffic'], $traffic_arr, $status);

$traffic = implode("\n", $traffic_arr);

$data2 .="$traffic</pre></td></tr>";
$data2 .='</table>';
echo $data2;
?>