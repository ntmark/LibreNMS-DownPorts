<style type="text/css">
form {margin: auto;width: 50%;}
.btn {margin-bottom: 5px;}
</style>
<?php
global $plugin_name;
$plugin_name="DownPorts";

#### variables
$hostname_to_search="";  #string to do regex on devices.hostname in DB
$days_down="";  # should be integer need to check this variable definition for php
$ports_down="checked"; # check if ports are down or up: default=down
$ports_up=""; # check if ports are down or up: default=down
$exclude_shut_checked="checked"; # dont include shutdown ports from ifAlias

#query user input for string to match on hostname
#and how many weeks back to check for down status??
#use this to get switches and port that are down for over this time and output totals + port names


if ($_POST){
        $do_query=0;
        if ($_POST["hostname_to_search"] && $_POST["days_down"]){
                $exclude_shut="";
                $ports_option="";
                if ($_POST["exclude_shut"] && $_POST["exclude_shut"]==1) {
                        $exclude_shut_checked = "checked";
                        $exclude_shut = $POST["exclude_shut"];
                }else {
                        $exclude_shut_checked = "";
                }
                if ($_POST["ports_option"] == "down") {
                        $ports_down="checked";
                        $ports_option="down";
                }elseif ($_POST["ports_option"] == "up"){
                        $ports_up="checked";
                        $ports_down="";
                        $ports_option="up";
                }

                if ($_POST["days_down"]){
                        if (is_numeric($_POST["days_down"])) {
                                $days_down=$_POST["days_down"];
                        }
                        if (!empty($_POST["hostname_to_search"])) {
                                $do_query=1;
                                $hostname_to_search=$_POST["hostname_to_search"];
                        }
                        # now run?

                        #### SQL query for ports.
                        if ($do_query){
                                $query = "
SELECT
 devices.device_id,
 devices.sysName,
 devices.hostname,
 round(devices.uptime/86400,0) as uptime,
 ports.port_id,
 ports.ifAlias,
 ports.ifName,
 ports.ifOperStatus,
 round(((devices.uptime - (ports.ifLastChange/100))/86400),0) as changetime
FROM
 devices
LEFT JOIN
 ports
ON
 devices.device_id = ports.device_id
WHERE
 (devices.hostname like \"%" . $hostname_to_search . "%\" OR devices.sysName like \"%" . $hostname_to_search . "%\") AND
 ports.ifOperStatus = \"".$ports_option."\" AND
 ports.ifType = \"ethernetCsmacd\" AND
 round(((devices.uptime - (ports.ifLastChange/100))/86400),0) >= " . $days_down . " AND
 ports.ifName not like \"Te%\" AND ports.ifName not like \"Twe%\" AND ports.ifName not like \"Ap%\"";
if($exclude_shut_checked == "checked") {
        $query.=" AND ports.ifAlias != \"SHUTDOWN\"";
}
$query.="
ORDER BY
 devices.hostname, ports.ifIndex;
";


                        }
                        #echo "ERROR:'".$_POST["vlan_to_search"]."' is not numeric!<br>\n";
                        #echo "ERROR:'".$query."' is not numeric!<br>\n";



                }
### show form
                $result=array();
                foreach( dbFetchRows($query) as $line){
                        $device_id=$line['device_id'];
                        $sysName=$line['sysName'];
                        $hostname=$line['hostname'];
                        $uptime=$line['uptime'];
                        $port_id=$line['port_id'];
                        $ifAlias=$line['ifAlias'];
                        $ifName=$line['ifName'];
                        $ifOperStatus=$line['ifOperStatus'];
                        $changetime=$line['changetime'];

                        $result[$sysName]['sysName']=$sysName;
                        $result[$sysName]['hostname']=$hostname;
                        $result[$sysName]['uptime']=$uptime;
                        $result[$sysName]['port_id']=$port_id;
                        $result[$sysName]['ports'][$port_id]['ifName']=$ifName;
                        $result[$sysName]['ports'][$port_id]['ifAlias']=$ifAlias;
                        $result[$sysName]['ports'][$port_id]['ifOperStatus']=$ifOperStatus;
                        $result[$sysName]['ports'][$port_id]['changetime']=$changetime;

                }
        }
}

# build and display form
$form="<form action='/plugin/v1/$GLOBALS[plugin_name]' method='post'>";
$form.="Enter hostname to include in search:<input class=\"form-control\" type=\"textbox\" size=50 name=\"hostname_to_search\" value=\"$hostname_to_search\" ><br>\n";
$form.="Enter to search for # of days port in state:<input class=\"form-control\" type=\"number\" name=\"days_down\" value=\"$days_down\" placeholder=\"30 = 1 month, 60 = 2 months, 180 = 6 months\"><br>\n";
$form.="Port state :\n";
$form.="<label><input type=\"radio\" name=\"ports_option\" value=\"down\" $ports_down> Down</label>\n";
$form.="<label><input type=\"radio\" name=\"ports_option\" value=\"up\" $ports_up> Up</label></br>\n";
$form.="Exclude SHUTDOWN labeled or admin down ports:<input type=\"checkbox\" name=\"exclude_shut\" value=\"1\" $exclude_shut_checked></br></br>\n";
$form.=csrf_field();
$form.='<input name="search" value="search" type="submit" class="btn btn-default"><br>';
print $form;


##### output results
if (isset($result)){
        print '<style type="text/css">
.tg  {border-collapse:collapse;border-spacing:0;}
.tg td{font-family:Arial, sans-serif;font-size:14px;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
.tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
.tg .tg-9hbo{background-color:#c0c0c0;font-weight:bold;vertical-align:top}
.tg .tg-q8xn{background-color:#dae8fc;vertical-align:top}
.tg .tg-head{background-color:#999999;vertical-align:top}
.tg .tg-yw4l{vertical-align:top}
</style>
';
#       var_dump($result);
        foreach($result as $device_id => $device_data){
                $sysName=$device_data['sysName'];       #switch devices.sysName
                $hostname=$device_data['hostname'];     #switch devices.hostname
                $uptime=$device_data['uptime'];         #device uptime
                $port_id=$device_data['port_id'];       #librenms ports.port_id
                $porttotal=count($device_data['ports']);
                $n=0;
                $format="tg-head" ; # Set row format
                $table="<table class='tg'><tr><th colspan=7 class='tg-9hbo'>$sysName ($hostname) | Total: $porttotal | Uptime: $uptime (days)</th></tr>";
                $table.="<tr class=\"$format\"><td>ifName</td><td>ifAlias</td><td>ifOperStatus</td><td>LastChanged (days)</tr>";
                foreach($device_data['ports'] as $port => $port_data){
                        $format= ( $n++ % 2 ) ? "tg-q8xn" : "tg-yw4l" ; # Set row format
                        $port_name=$port_data['ifName'];
                        $ifName=$port_data['ifName'];
                        $ifAlias=$port_data['ifAlias'];
                        $ifOperStatus=$port_data['ifOperStatus'];
                        $changetime=$port_data['changetime'];
                        $numinterfaces=count($device_data['ports']);
                        $numinterfaces++;
                        $table.="<tr class=\"$format\" ><td>$port_name</td><td>$ifAlias</td><td>$ifOperStatus</td><td>$changetime</td></tr>\n";
                }
                $table.="</table><br>\n";
                #var_dump($table);
                print $table;

        }
}
