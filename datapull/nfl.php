<?php
ini_set('display_errors', 1);
error_reporting(-1);
include_once '../globalincludes/connection.php';

$url = 'https://site.api.espn.com/apis/site/v2/sports/football/nfl/scoreboard';
$json = file_get_contents($url);
$array = json_decode($json, TRUE);
$push_array = array();
$curtime = date('Y-m-d H:i:s');

$columns = "nfl_id,nfl_update_datetime, nfl_date,nfl_day,nfl_gametime,nfl_quarter,nfl_timerem,nfl_homeabb,nfl_homename,nfl_hs,nfl_awayabb,nfl_awayname,nfl_vs,nfl_p,nfl_rz,nfl_ga,nfl_gametag,nfl_week";

foreach ($array['events'] as $key => $value) {
    $week = $array["week"]["number"];
    $eid = new DateTime($array["events"][$key]["date"]);
    $date = $eid->format('Y-m-d');
    $gsis = intval($array["events"][$key]["competitions"][0]["id"]);
    $d = $value2['d'];
    $t = $value2['t'];
    $q = $value2['q'];
    if (isset($value2['k'])) {
        $k = $value2['k'];
    } else {
        $k = '0:00';
    }
    $h = $value2['h'];
    $hnn = $value2['hnn'];
    $hs = intval($value2['hs']);
    $v = $value2['v'];
    $vnn = $value2['vnn'];
    $vs = intval($value2['vs']);
    if (isset($value2['p'])) {
        $p = $value2['p'];
    } else {
        $p = ' ';
    }
    $rz = intval($value2['rz']);
    $ga = $value2['ga'];
    $gt = $value2['gt'];


    $push_array[] = "($gsis, '$curtime', '$date', '$d', '$t', '$q','$k', '$h', '$hnn', $hs, '$v', '$vnn', $vs, '$p', '$rz', '$ga', '$gt', $week)";
}

$values = implode(',', $push_array);

if (!empty($values)) {
    $sql = "INSERT IGNORE INTO betanalyzer.nfl ($columns) VALUES $values";
    $query = $conn1->prepare($sql);
    $query->execute();
}

echo 'success';
