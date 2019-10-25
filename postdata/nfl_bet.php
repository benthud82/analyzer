<?php

include_once '../globalincludes/connection.php';

$nfl_id = intval($_POST['nfl_id']);
$nfl_type = ($_POST['nfl_type']);
$nfl_for = ($_POST['nfl_for']);
$nfl_spread = ($_POST['nfl_spread']);
$nfl_amt = ($_POST['nfl_amt']);
$nfl_winamt = ($_POST['nfl_winamt']);
$auto_id = 0;
$datetime = date('Y-m-d H:i:s');

$columns = 'nflbet_autoid,nflbet_id,nflbet_placed_date,nflbet_type,nflbet_for,nflbet_spread,nflbet_amount,nflbet_win';
$values = "$auto_id,$nfl_id,'$datetime', '$nfl_type','$nfl_for','$nfl_spread','$nfl_amt','$nfl_winamt'";

$sql = "INSERT INTO betanalyzer.nfl_bets ($columns) VALUES ($values) ";
$query = $conn1->prepare($sql);
$query->execute();
    