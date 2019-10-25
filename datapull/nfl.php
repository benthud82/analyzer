<?php
ini_set('display_errors', 1); error_reporting(-1);
include_once '../globalincludes/connection.php';
$xmlString = 'http://www.nfl.com/liveupdate/scorestrip/ss.xml';
$xml = simplexml_load_file($xmlString) or die("Error: Cannot create object");
$json = json_encode($xml);
$array = json_decode($json, TRUE);
$push_array = array();
$week = intval(8);
$curtime = date('Y-m-d H:i:s');

$columns = "nfl_id,nfl_update_datetime, nfl_date,nfl_day,nfl_gametime,nfl_quarter,nfl_timerem,nfl_homeabb,nfl_homename,nfl_hs,nfl_awayabb,nfl_awayname,nfl_vs,nfl_p,nfl_rz,nfl_ga,nfl_gametag,nfl_week";

foreach ($array['gms']['g'] as $key => $value) {
    foreach ($value as $key2 => $value2) {
        $eid = $value2['eid'];
        $date = date('Y-m-d', strtotime(substr($eid, 0, 8)));
        $gsis = intval($value2['gsis']);
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
    }

    $push_array[] = "($gsis, '$curtime', '$date', '$d', '$t', '$q','$k', '$h', '$hnn', $hs, '$v', '$vnn', $vs, '$p', '$rz', '$ga', '$gt', $week)";
}

$values = implode(',', $push_array);

if (!empty($values)) {
    $sql = "INSERT IGNORE INTO betanalyzer.nfl ($columns) VALUES $values";
    $query = $conn1->prepare($sql);
    $query->execute();
}

echo 'success';