<?php
/*
 * Author: Anton Manukov;
 */

if(isset($_GET['a_id']) && !empty($_GET['a_id']) && isset($_GET['c_id']) && !empty($_GET['c_id'])){
	$a_id = $_GET['a_id'];
	$c_id = $_GET['c_id'];
	$schedules_link = 'reshet-first://presentEPG?title=EPG&channel_id='.$c_id.'';
	$source = 'https://admin.applicaster.com/v12/accounts/'.$a_id.'/channels/'.$c_id.'/programs.json';
	$data = json_decode(@file_get_contents('http://199.203.217.171/proxy/?source='.urlencode($source)), TRUE);
	$schedules = array();

	if(isset($data) && !empty($data)){
		if(isset($data['programs']) && !empty($data['programs']) && is_array($data['programs']) && count($data['programs']) > 0){
			getSchedules($data);
		} else {
			die('Error! JSON is empty.');
		}
	} else {
		die('Invalid Request! Failed to get JSON.');
	}

} else {
	die('Invalid Request! Check your link and parameters.');
}

function getSchedules($data){
	global $schedules;
	$count = count($data['programs']);
	$current = date('Y/m/d H:i:s');
	
	for($i = 0; $i < $count; $i++){
		$item = $data['programs'][$i];
		$start = getTheDate($item['starts_at']);
		$end = getTheDate($item['ends_at']);

		if($current >= $start && $current < $end){
			$schedules = array(
				'now' => array(
					'name' => $data['programs'][$i]['name'],
					'time' => substr($data['programs'][$i]['starts_at'], 11, 5),
				),
				'next' => array(
					'name' => $data['programs'][$i+1]['name'],
					'time' => substr($data['programs'][$i+1]['starts_at'], 11, 5),
				),
			);
		}
	}
}

function getTheDate($string){
	list($date, $time) = explode(' ', $string);
	return "$date $time";
}

?>

<!DOCTYPE html>
<html lang="he">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no,maximum-scale=1,user-scalable=0">
		<meta name="mobile-web-app-capable" content="yes">

		<title>Epg Reshet</title>

		<link type="text/css" rel="stylesheet" href="./css/style.css?15">
		
		<style>
			<?php if(isset($_GET['fullview']) && !empty($_GET['fullview']) && $_GET['fullview'] == 'true'): ?>
			#wrapper{max-width: initial;}
			<?php endif; ?>
		</style>

		<script type="text/javascript">
			function appReady(){
				setTimeout(function(){
					$('#wrapper').animate({opacity: 1}, 400);
				}, 150);
			}
			document.addEventListener("touchstart", function() {},false);
        </script>
	</head>

	<body onload="appReady();">

		<div id="wrapper">
			<div class="section" id="epg-ticker">
				<?php if(isset($schedules) && !empty($schedules)): ?>

				<div class="tick">
					<p>
						<span class="program-expectation">עכשיו</span>
						<span class="program-title"><?php echo $schedules['now']['name']; ?></span>
						<span class="program-time"><?php echo $schedules['now']['time']; ?></span>
					</p>
				</div>
				<div class="tick">
					<p>
						<span class="program-expectation">הבא</span>
						<span class="program-title"><?php echo $schedules['next']['name']; ?></span>
						<span class="program-time"><?php echo $schedules['next']['time']; ?></span>
					</p>
				</div>

				<?php else: ?>

					<p>Programs not exist!</p>

				<?php endif; ?>
			</div>
			<div class="section" id="broadcast-schedule-link">
				<a href="<?php echo $schedules_link; ?>" target="_self" title="עבור ללוח השידורים">ללוח השידורים</a>
			</div>
		</div>
		<script type="text/javascript" src="./js/jquery.js"></script>

	</body>
</html>
