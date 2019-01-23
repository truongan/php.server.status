php.server.status
=================

A simple PHP script to check and display various web server statuses:

No installation and **no database** is required. 
![System status viewing]( https://github.com/truongan/php.server.status/raw/master/system_status.png   "System status viewing")

The first section of the script display connection status of some service running on your system. Simply open the php file and make necessary modificaiton in this region:
```
	$services[] = array("port" => "80", "service" => "Apache", "ip" => "") ;
	$services[] = array("port" => "21", "service" => "FTP", "ip" => "") ;
	$services[] = array("port" => "3306", "service" => "MYSQL", "ip" => "") ;
	$services[] = array("port" => "22", "service" => "Open SSH", "ip" => "") ;
	$services[] = array("port" => "9091", "service" => "Transmission", 	"ip" => "") ;
```
You can comment out service that you don't have or add new service use the similar syntax. 

The second section of the script display system load status, uptime, resource usage information and top Resource usage process.
You can add or remove the mountpoint you want to check for freep space, look for those lines of code:
```
/*
* The disks array list all mountpoint you wan to check freespace
* Display name and path to the moutpoint have to be provide, you can 
*/
$disks[] = array("name" => "local" , "path" => getcwd()) ;
// $disks[] = array("name" => "Your disk name" , "path" => '/mount/point/to/that/disk') ;
```

The last section of the script display network traffic statistic. Those information is gather by vnstat (http://humdi.net/vnstat/), you will need vnstat installed and properly configured before hand. 

You can view various information gather and display by vnstat. The option you pass to vnstat will by specified HTTP GET parameter in your request URL. To view 
```
vnstat -m
```
information, you will request the URL 
```
checkup.php?showtraffic=m
```
