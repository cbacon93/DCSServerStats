<?php
	// Copyright 2016 Marcel Haupt
	// http://marcel-haupt.eu/
	//
	// Licensed under the Apache License, Version 2.0 (the "License");
	// you may not use this file except in compliance with the License.
	// You may obtain a copy of the License at
	//
	// http ://www.apache.org/licenses/LICENSE-2.0
	//
	// Unless required by applicable law or agreed to in writing, software
	// distributed under the License is distributed on an "AS IS" BASIS,
	// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	// See the License for the specific language governing permissions and
	// limitations under the License.
	//
	// Github Project: https://github.com/cbacon93/DCSServerStats

	
	
function echoSiteContent($mysqli) {
	if (isset($_GET['pid'])) {
		echoPilotStatistic($mysqli, $_GET['pid']);
	} else {
		echoPilotsTable($mysqli);
	}
}
	
function timeToString($time) {
	$flight_hours = floor($time / 60 / 60);
	$flight_mins = floor($time / 60) - $flight_hours * 60;
	$flight_secs = $time  - $flight_mins * 60 - $flight_hours * 3600;
	
	if ($flight_mins < 10)
		$flight_mins = '0' . $flight_mins;
	if ($flight_secs < 10)
		$flight_secs = '0' . $flight_secs;
		
	return "$flight_hours:$flight_mins:$flight_secs";
}


function echoFooter($mysqli) {
	$result = $mysqli->query("SELECT * FROM dcs_parser_log ORDER BY id DESC LIMIT 1");
	if ($row = $result->fetch_object()) {
	
		echo "Last update at " . date('G:i', $row->time) . " processed " . $row->events . " events in " . $row->durationms .  " ms";
	}
}
	
	
function echoPilotsTable($mysqli) {
	echo "<table class='table_stats'>";
	echo "<tr class='table_header'><th>Pilot</th><th>Flights</th><th>Flight time</th><th>Kills</th><th>Ejections</th><th>Crashes</th><th>last active</th><th>status</th></tr>";
	
	$result = $mysqli->query("SELECT * FROM pilots ORDER BY flighttime DESC");
	$i = 0;
	
	while($row = $result->fetch_object()) {
		$onlinestatus = "<p class='pilot_offline'>On the Ground</p>";
		if ($row->online == 1)
			$onlinestatus = "<p class='pilot_online'>Flying</p>";
		
		echo "<tr onclick=\"window.document.location='?pid=" . $row->id . "'\" class='table_row_" . $i%2 . "'><td>" . $row->name . "</td><td>" . $row->flights . "</td><td>" . timeToString($row->flighttime) . "</td><td>" . $row->kills . "</td><td>" . $row->ejects . "</td><td>" . $row->crashes . "</td><td>" . date('d.m.Y', $row->lastactive) . "</td><td>" . $onlinestatus . "</td></tr>";
		
		$i++;
	}
	
	echo "</table>";
}


function echoPilotStatistic($mysqli, $pilotid) {
	$prep = $mysqli->prepare("SELECT * FROM pilots WHERE id=?");
	$prep->bind_param('i', $pilotid);
	$prep->execute();
	$result = $prep->get_result();
	$row = $result->fetch_object();
	
	echo "Pilot " . $row->name . "<br>";
	
	$prep->close();
	
	
	$prep = $mysqli->prepare("SELECT * FROM flights, aircrafts WHERE flights.pilotid=? AND aircrafts.id=flights.aircraftid ORDER BY flights.id DESC LIMIT 10");
	$prep->bind_param('i', $pilotid);
	$prep->execute();
	$result = $prep->get_result();
	
	echo "<table class='table_stats'>";
	echo "<tr class='table_header'><th>Aircraft</th><th>Coalition</th><th>Takeoff</th><th>Landing</th><th>Duration</th><th>Type of Landing</th></tr>";
	
	$i = 0;
	while($row = $result->fetch_object()) {
		echo "<tr class='table_row_" . $i%2 . "'><td>" . $row->name . "</td><td>" . $row->coalition . "</td><td>" . date('G:i d.m.Y', $row->takeofftime) . "</td><td>" . date('G:i d.m.Y', $row->landingtime) . "</td><td>" . timeToString($row->duration) . "</td><td>" . $row->endofflighttype . "</td></tr>";
		$i++;
	}
	
	echo "</table>";
}
 	
?>