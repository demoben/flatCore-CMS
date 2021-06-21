<?php

$time_s = microtime(true);

//prohibit unauthorized access
require("core/access.php");


// defaults

$entries_per_page = 100;

if($_SESSION['start'] == "") {
	$_SESSION['start'] = 0;
}

if($_REQUEST['start'] != "") {
	$_SESSION['start'] = (int) ($_REQUEST['start']*$entries_per_page);
}

if($_SESSION['filename'] == "") {
	//default: today's logfile
	$_SESSION['filename'] = "logfile" . date('Y') . date('m') . ".sqlite3";
}

if($_POST['select_logfile']) {
	$_SESSION['filename'] = strip_tags($_POST['select_logfile']);
	$_SESSION['start'] = 0; // reset pagination
}




$start = $_SESSION['start'];
$filename = $_SESSION['filename'];





/* scan FC_CONTENT_DIR and return all logfiles */

$log_dir = FC_CONTENT_DIR . "/SQLite";
$logfiles = glob("$log_dir/logfile*");

echo '<fieldset>';
echo '<legend>'.$lang['select_logfile'].'</legend>';
echo '<form action="acp.php?tn=system&sub=stats" method="POST" class="form-inline">';
echo '<div class="form-group mx-sm-3">';
echo '<select name="select_logfile" class="custom-select form-control">';

foreach($logfiles as $fn) {
	
	$fn = basename($fn);
	$get_month = 'm' . substr("$fn", 11, 2);
	$month = $lang[$get_month];
	$get_year = substr("$fn", 7, 4);

	unset($selected);
	if($filename == $fn) { $selected = "selected"; }
   		echo"<option $selected value='$fn'>$month $get_year</option>";
}

echo '</select>';
echo '</div> ';
echo '<div class="form-group">';
echo '<input type="submit" class="btn btn-fc" name="select_log" value="'.$lang['choose'].'">';
echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</div> ';
echo '</form>';
echo '</fieldset>';



$logfile_path = FC_CONTENT_DIR . "/SQLite/$filename";


if(is_file("$logfile_path")) {


// connect to database
$dbh = new PDO("sqlite:$logfile_path");


$sql_stat = "
SELECT count(*) AS 'All',
(SELECT count(*) FROM fc_logfile WHERE log_ua LIKE '%safari%' ) AS 'Safari', 
(SELECT count(*) FROM fc_logfile WHERE log_ua LIKE '%firefox%' ) AS 'Firefox',
(SELECT count(*) FROM fc_logfile WHERE log_ua LIKE '%msie%' ) AS 'Internet Explorer',
(SELECT count(*) FROM fc_logfile WHERE log_ua LIKE '%chrome%' ) AS 'Google Chrome',
(SELECT count(*) FROM fc_logfile WHERE log_ua LIKE '%netscape%' ) AS 'Netscape',
(SELECT count(*) FROM fc_logfile WHERE log_ua LIKE '%opera%' ) AS 'Opera',
(SELECT count(*) FROM fc_logfile WHERE log_ua LIKE '%camino%' ) AS 'Camino',
(SELECT count(*) FROM fc_logfile WHERE log_ua LIKE '%konqueror%' ) AS 'Konqueror',
(SELECT count(*) FROM fc_logfile WHERE log_ua LIKE '%icab%' ) AS 'iCab',
(SELECT count(*) FROM fc_logfile WHERE log_ua LIKE '%ipad%' ) AS 'iPad',
(SELECT count(*) FROM fc_logfile WHERE log_ua LIKE '%iphone%' or log_ua LIKE '%ipod%' ) AS 'iPhone/iPod',
(SELECT count(*) FROM fc_logfile WHERE log_ua LIKE '%bot%' or log_ua LIKE '%java%' or log_ua LIKE '%spider%' ) AS 'Bots'
FROM fc_logfile
";

$stat_result = $dbh->query("$sql_stat")->fetch(PDO::FETCH_ASSOC);



$cnt_entries = $stat_result['All'];

$sql = "SELECT * FROM fc_logfile ORDER BY log_time DESC LIMIT $start, $entries_per_page";

unset($result);
foreach ($dbh->query($sql) as $row) {
	$result[] = $row;
}
   
$cnt_result = count($result);
$dbh = null;


$filesize = round((filesize("$logfile_path") / 1024), 2);

$get_month = 'm' . substr("$filename", 11, 2);
$month = $lang[$get_month];
$get_year = substr("$filename", 7, 4);


echo "<h4>$month $get_year <small>$cnt_entries $lang[logfile_hits] » $filesize kb</small></h4>";

echo '<div class="row">';
echo '<div class="col-md-3">';

echo '<table class="table table-sm table-striped">';

arsort($stat_result);

foreach ($stat_result as $k => $v) {
	if($v > 0){
    	echo '<tr><td>'.$k.':</td><td align="right">'.$v.'</td></tr>';
    }
}


echo '</table>';
echo '</div>';
echo '<div class="col-md-9">';


/* listing */


echo '<div class="scroll-container">';
echo '<table class="table table-sm table-striped">';

for($i=0;$i<$cnt_result;$i++) {

	$log_time_day = date("d.m.Y",$result[$i]['log_time']);
	$log_time = date("H:i:s",$result[$i]['log_time']);
	$log_id = $result[$i]['log_id'];
	$log_ip = filter_var($result[$i]['log_ip'], FILTER_SANITIZE_STRING);
	$log_ua = filter_var($result[$i]['log_ua'], FILTER_SANITIZE_STRING);
	$log_query = filter_var($result[$i]['log_query'], FILTER_SANITIZE_STRING);
	$log_ref = filter_var($result[$i]['log_ref'], FILTER_SANITIZE_STRING);

	echo '<tr><td>Time:</td><td>'.$log_time_day.' '.$log_time.'</td></tr>';
	echo '<tr><td>IP:</td><td>'.$log_ip.'</td></tr>';
	if($log_query != "") {
		echo '<tr><td>Query:</td><td>'.$log_query.'</td></tr>';
	}
	if($log_ref != "") {
		echo '<tr><td>Referer:</td><td>'.$log_ref.'</td></tr>';
	}
	if($log_ua != "") {
		echo '<tr><td class="text-nowrap">User Agent:</td><td>'.$log_ua.'</td></tr>';
	}
	
	echo '<tr><td colspan="2" class="p-0 border-top-0"><hr class="shadow-line mt-1"></td></tr>';

}

echo '</table>';


echo"</div>";



/* pagination */
$pages = ceil($cnt_entries/$entries_per_page);
echo '<hr>';
	for($i=0;$i<$pages;$i++) {
	$nbr = $i+1;
	$pag_class = "btn btn-fc btn-sm";
	if(($i*$entries_per_page) == "$start") { $pag_class = "btn btn-fc btn-sm active"; }
		echo"<a class='$pag_class' href='acp.php?tn=system&sub=stats&start=$i'>$nbr</a> ";
	}
/* pagination */




echo '</div>';

} else {

echo '<div class="alert alert-info">No logfile</div>';

}



?>