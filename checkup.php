<!DOCTYPE html>
<html lang="en">
<head>
	<title>Server status</title>
	<meta content="text/html" charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
</head>
<html><div class="container">
<?php
/*

 *
 * @author      Trường An Phạm Nguyễn
 * @copyright   2019, The authors
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
$data .= '
<div class="card mb-5">
  <h4 class="card-header text-center">
    Service status
  </h4>
  <div class="card-body">
';


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
$data .= "<table  class='table table-striped table-sm '><thead><tr><th>Service</th><th>Status</th></tr></thead>";
foreach ($services  as $service) {
	if($service['ip']==""){
	   $service['ip'] = "localhost";
	}

	$fp = @fsockopen($service['ip'], $service['port'], $errno, $errstr, $timeout);
	if (!$fp) {
		$data .= "<tr ><td>" . $service['service'] . "</td><td class='table-danger'>Offline </td></tr>";
	  //fclose($fp);
	} else {
		$data .= "<tr><td>" . $service['service'] . "</td><td class='table-success'>Online</td></tr>";
		fclose($fp);
	}

}  
//close table
$data .= "</table>";
$data .= '
  </div>
</div>
';
echo $data;


/* =====================================================================
//
// ////////////////// SERVER INFORMATION  /////////////////////////////////
//
//
* =======================================================================/*/

$data1 = "";
$data1 .= '
<div class="card mb-5">
  <h4 class="card-header text-center">
    Service status
  </h4>
  <div class="card-body">
';


$data1 .= "<table  class='table table-sm'>";

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
	
	return sprintf('%.2f<small>'.$symbol[$exp].'</small>', ($bytes/pow(1024, floor($exp))));
}
function percent_to_color($p){
	if($p < 40) return 'success';
	if($p < 70) return 'warning';
	return 'danger';
}
function format_storage_info($disk_space, $disk_free, $disk_name){
	$str = "";
	$disk_free_precent = 100 - round($disk_free*1.0 / $disk_space*100, 2);
		$str .= '<div class="col p-0 d-inline-flex">';
		$str .= "<span class='mr-2'>" . $disk_name . " " . getSymbolByQuantity($disk_free) . '/'. getSymbolByQuantity($disk_space) ."</span>";
		$str .= '
<div class="progress flex-grow-1 align-self-center">
  <div class="progress-bar progress-bar-striped progress-bar-animated ';
		$str .= 'bg-' . percent_to_color($disk_free_precent) .'
  " role="progressbar" style="width: '.$disk_free_precent.'%;" aria-valuenow="'.$disk_free_precent.'" aria-valuemin="0" aria-valuemax="100">'.$disk_free_precent.'%</div>
</div>
</div>		';

	return $str;

}
function get_disk_free_status($disks){
	$str="";
	$max = 5;
	foreach($disks as $disk){
		if(strlen($disk["name"]) > $max) 
			$max = strlen($disk["name"]);
	}
	
	foreach($disks as $disk){
		$disk_space = disk_total_space($disk["path"]);
		$disk_free = disk_free_space($disk["path"]);

		$str .= format_storage_info($disk_space, $disk_free, $disk['name']);

	}
	return $str;
}


//Get ram usage
$total_mem = preg_split('/ +/', @exec('grep MemTotal /proc/meminfo'));
$total_mem = $total_mem[1];
$free_mem = preg_split('/ +/', @exec('grep MemFree /proc/meminfo'));
$cache_mem = preg_split('/ +/', @exec('grep ^Cached /proc/meminfo'));

$free_mem = $free_mem[1] + $cache_mem[1];


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

$data1 .= "<tr><td>average load</td><td>$avgs[1], $avgs[2], $avgs[3]</td>\n";
$data1 .= "<tr><td>Uptime</td><td>$uptime                     </td></tr>";


$disks = array();
$disks[] = array("name" => "local" , "path" => getcwd()) ;
$data1 .= "<tr><td>Disk free        </td><td>" . get_disk_free_status($disks) . "</td></tr>";

$data1 .= "<tr><td>RAM free        </td><td>". format_storage_info($total_mem *1024, $free_mem *1024, '') ."</td></tr>";
$data1 .= "<tr><td>Top RAM user    </td><td>$top_mem         </td></tr>";
$data1 .= "<tr><td>Top CPU user    </td><td>$top_cpu         </td></tr>";

$data1 .= "</table>";
$data1 .= '
  </div>
</div>
';
echo $data1;  

/* =============================================================================
*
* DISPLAY BANDWIDTH STATISTIC, REQUIRE VNSTAT INSTALLED AND PROPERLY CONFIGURED.
*
* ===============================================================================s
*/


if (!isset($_GET['showtraffic']) || $_GET['showtraffic'] ==  false) die();

$data2 = "";
$data2 .=  '
<div class="card">
  <h4 class="card-header text-center">
    Service status
  </h4>
  <div class="card-body">
';

$data2 .= "<table  class='table table-sm '>";
$data2 .="<tr><td><pre>";
$traffic_arr = array();
exec('vnstat -' . $_GET['showtraffic'], $traffic_arr, $status);

$traffic = implode("\n", $traffic_arr);

$data2 .="$traffic</pre></td></tr>";
$data2 .='</table>';
echo $data2;
?>
</div></html>